<?php

namespace models\repaircard;

use models\geo\Countries;
use models\Models;
use models\parts\Balance;
use models\parts\Depots;
use models\Serials;
use models\User;
use program\core\RowSet;

class Parts extends \models\_Model
{

    const TABLE = 'parts2';
    const TABLE_MODELS = 'parts2_models';
    const TABLE_VEND = 'parts2_vendors';
    const TABLE_GROUP = 'parts2_groups';
    const TABLE_DEPOT = 'parts2_depots';
    const TABLE_STAND = 'parts2_standard';
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /* *** */
    /* OLD */
    /* *** */
    public static function getParts($modelID, $serial, $userID = 0)
    {
        $model = Models::getModelByID($modelID);
        $stdParts = $origParts = []; // стандартные и оригинальные запчасти
        $stdParts = self::$db->exec('SELECT * FROM `' . self::TABLE . '` 
            WHERE `id` IN (SELECT `part_id` FROM `' . self::TABLE_STAND . '` 
                           WHERE `cat_id` = ?) 
                       AND `del_flag` = 0 ORDER BY `name`', [$model['cat']]);
        if (!empty($serial)) {
            $origParts = self::$db->exec('SELECT * FROM `' . self::TABLE . '` 
            WHERE `id` IN (SELECT `part_id` FROM `' . self::TABLE_MODELS . '` 
                           WHERE `model_id` = ? AND `model_serial` = ?) AND `del_flag` = 0 AND `attr_id` = ' . \models\Parts::ORIG_PART . ' ORDER BY `name`', [$modelID, $serial]);
        }
        $parts = \models\Parts::flagStandarts(array_merge($origParts, $stdParts));
        $res = [];
        $adminFlag = User::hasRole('admin', 'store');
        for ($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
            $parts[$i]['balance'] = self::getBalance2($parts[$i]['id'], $userID);
            if (!$parts[$i]['balance']) {
                continue;
            }
            $group = \models\Parts::getGroup($parts[$i]['group_id']);
            $parts[$i]['user_flag'] = !empty($parts[$i]['src_uri']); // была создана СЦ при оставлении в Разборе
            $parts[$i]['part_code'] = $group['code'] . $parts[$i]['id'];
            $parts[$i]['group'] = ($group['name']) ? $group['name'] . ' (' . $group['code'] . ')' : '(без группы)';
            $parts[$i]['photos'] = ($parts[$i]['photos']) ? json_decode($parts[$i]['photos']) : [];
            $parts[$i]['description'] = ($adminFlag) ? nl2br($parts[$i]['description']) : '';
            $parts[$i]['attr'] = (isset(\models\Parts::$partAttrs[$parts[$i]['attr_id']])) ? \models\Parts::$partAttrs[$parts[$i]['attr_id']] : '';
            $parts[$i]['type'] = (isset(\models\Parts::$partTypes[$parts[$i]['type_id']])) ? \models\Parts::$partTypes[$parts[$i]['type_id']] : '';
            $res[] = $parts[$i];
        }
        return self::sortParts($res);
    }


    /**
     * Сортировка списка запчастей по правилам
     * 
     * @param array $parts Список запчастей
     * 
     * @return bool Отсортированный список
     */
    private static function sortParts(array $parts)
    {
        if (!$parts) {
            return $parts;
        }
        function isDisasDepot(array $balance)
        {
            foreach ($balance as $depot) {
                if ($depot['depot_id'] == Depots::MAIN_DEPOT_ID) {
                    return false;
                }
            }
            return true;
        }
        function hasParts(array $balance)
        {
            foreach ($balance as $b) {
                if ((int)$b['qty'] > 0) {
                    return true;
                }
            }
            return false;
        }
        $disasOrig = []; // Разбор, оригинальные
        $disasStd = []; // Разбор, стандартные
        $mainOrig = []; // Главный, оригинальные
        $mainStd = []; // Главный, стандартные
        $hasOriginal = []; // Стандартные с оригиналом
        foreach ($parts as $part) {
            if (!empty($part['has_original_flag'])) {
                $hasOriginal[] = $part;
                continue;
            }
            if ($part['attr_id'] == \models\Parts::ORIG_PART) {
                if (isDisasDepot($part['balance'])) {
                    if (empty($part['user_flag']) || hasParts($part['balance'])) {
                        $disasOrig[] = $part;
                    }
                } else {
                    $mainOrig[] = $part;
                }
                continue;
            }
            if (isDisasDepot($part['balance'])) {
                $disasStd[] = $part;
            } else {
                $mainStd[] = $part;
            }
        }
        return array_merge($disasOrig, $disasStd, $mainOrig, $mainStd, $hasOriginal);
    }


    /**
     * Есть ли стандартные запчасти в списке
     * 
     * @param array $parts Список запчастей
     * 
     * @return bool Есть стандартные запчасти
     */
    public static function hasStandardParts(array $parts)
    {
        for ($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
            if (!empty($parts[$i]['has_original_flag'])) {
                return true;
            }
        }
        return false;
    }


    public static function search(array $filter, $repairID)
    {
        $filter['del_flag'] = 0;
        if (empty($filter['show_all'])) {
            $repair = \models\Repair::getRepairByID($repairID);
            $model = Models::getModelByID($repair['model_id']);
            $serial = Serials::getSerial($repair['serial'], $repair['model_id'])['serial'];
            $filter['cat_id'] = $model['cat'];
            $filter['model_id'] = $repair['model_id'];
            $filter['serial'] = $serial;
            if (!empty($filter['depot']) && $filter['depot'] == 'current') {
                $depot = Depots::getDepot(['user_id' => $repair['service_id']]);
                if ($depot) {
                    $filter['depot_id'] = [$depot['id']];
                }
            }
        }
        if (User::hasRole('admin', 'store', 'master')) { // для админа поиск по всем складам 
            $filter['depot_id'] = (!empty($filter['depot_id'])) ? $filter['depot_id'] : [];
        } elseif (empty($filter['depot_id'])) { // для СЦ только по личному и главному
            $mainDepot = Depots::getDepot(['id' => Depots::MAIN_DEPOT_ID]);
            $filter['depot_id'] = [$mainDepot['id']];
            if (User::hasRole('slave-admin')) {
                $depot = Depots::getDepot(['user_id' => 33]); // ИП Кулиджанов
            } else {
                $depot = Depots::getDepot(['user_id' => User::getData('id')]);
            }
            if ($depot) {
                $filter['depot_id'][] = $depot['id'];
            }
        }
        $parts = \models\Parts::getParts($filter);
        $adminFlag = User::hasRole('admin', 'store');
        for ($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
            $parts[$i]['balance'] = self::getPartBalance($parts[$i]['id'], Depots::getDepots(['id' => $filter['depot_id']]));
            $parts[$i]['group'] = ($parts[$i]['group']['name']) ? $parts[$i]['group']['name'] . ' (' . $parts[$i]['group']['code'] . ')' : '(без группы)';
            $parts[$i]['description'] = ($adminFlag) ? nl2br($parts[$i]['description']) : '';
        }
        return self::sortParts($parts);
    }


    private static function getPartBalance($partID, array $depotsMap)
    {
        $result = [];
        $userRole = User::getData('role');
        foreach ($depotsMap as $depot) {
            $balance = Balance::get($partID, $depot['id']);
            if (!$balance) {
                continue;
            }
            if (in_array($userRole, ['admin', 'store', 'master']) || $depot['id'] != Depots::MAIN_DEPOT_ID) {
                $balance['is_visible'] = true;
            }
            $balance['depot'] = $depot;
            $result[] = $balance;
        }
        return $result;
    }


    /* OLD, delete */
    private static function getBalance2($partID, $userID)
    {
        $rows = self::$db->exec('SELECT bal.`depot_id`, bal.`qty`, bal.`place`,  
            dep.`name` AS depot, dep.`user_id` 
            FROM `' . Balance::TABLE . '` bal 
            LEFT JOIN `' . \models\Parts::TABLE_DEPOT . '` dep 
            ON dep.`id` = bal.`depot_id` 
            WHERE bal.`part_id` = ' . $partID . ' AND dep.`user_id` IN (1, ?)', [$userID]);
        return $rows;
    }


    /**
     * Возвращает дерево складов с ключом по стране
     * 
     * @param array $filter Фильтр
     * 
     * @return array Дерево складов "страна => список"
     */
    public static function getDepots()
    {
        $depots = [];
        if (User::hasRole('admin', 'store')) {
            $depots = Depots::getDepots();
        } else {
            $mainDepot = Depots::getDepot(['id' => Depots::MAIN_DEPOT_ID]);
            if (User::hasRole('service', 'slave-admin')) {
                $depots = Depots::getDepots(['user_id' => [User::getData('id'), $mainDepot['user_id']]]);
            }
            if (User::hasRole('master')) {
                $depots = Depots::getDepots(['user_id' => [33, $mainDepot['user_id']]]);
            }
        }
        if (!$depots) {
            return [];
        }
        $result = [];
        foreach ($depots as $depot) {
            $country = (!$depot['country']) ? '- все -' : $depot['country'];
            if (!isset($result[$country])) {
                $result[$country] = [];
            }
            $result[$country][] = $depot;
        }
        return $result;
    }


    public static function getCountries()
    {
        $countries = Countries::getCountries();
        return $countries;
    }


    public static function getGroups()
    {
        $groups = \models\Parts::getGroups();
        $res = [];
        foreach ($groups as $group) {
            $res[$group['id']] = $group['name'];
        }
        return $res;
    }


    /**
     * Получает список групп из списка запчастей
     * 
     * @param array $parts Массив запчастей
     * 
     * @return array Массив групп
     */
    public static function filterGroups(array $parts)
    {
        if (!$parts) {
            return [];
        }
        $groups = [];
        foreach ($parts as $part) {
            $groups[$part['group_id']] = $part['group'];
        }
        return $groups;
    }
}

Parts::init();
