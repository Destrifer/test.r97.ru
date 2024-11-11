<?php

namespace models;

use models\parts\Balance;
use models\parts\Depots;
use models\parts\Batches;

/** 
 * Страница запчастей /parts/
 */

class PartsTable extends _Model
{

    protected static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    protected static function getDefaultFilter(array $filter = [])
    {
        $filter['del_flag'] = (empty($filter['is_deleted'])) ?  0 : 1;
        $filter['for_each_depot'] = true;
        return $filter;
    }


    /**
     * Возвращает заголовки столбцов таблицы 
     * 
     * @return array Колонки
     */
    public static function getCols(array $filter)
    {
        $cols = [
            0 => ['name' => 'Операции', 'uri' => 'operations', 'orderable_flag' => 0],
            1 => ['name' => 'Код', 'uri' => 'part_code', 'orderable_flag' => 1],
            2 => ['name' => 'Наименование', 'uri' => 'name', 'orderable_flag' => 1],
            3 => ['name' => 'Остатки', 'uri' => 'num', 'orderable_flag' => 1],
            4 => ['name' => 'Утиль', 'uri' => 'disposal_num', 'orderable_flag' => 1],
            5 => ['name' => 'Склад', 'uri' => 'depot', 'orderable_flag' => 0],
            6 => ['name' => 'Принадлежность', 'uri' => 'type', 'orderable_flag' => 1],
            7 => ['name' => 'Признак', 'uri' => 'attr', 'orderable_flag' => 1],
            8 => ['name' => 'Место', 'uri' => 'place', 'orderable_flag' => 0]
        ];
        if (empty($filter['show-disposals'])) {
            unset($cols[4]);
        }
        if (User::hasRole('service')) {
            unset($cols[5]);
            unset($cols[6]);
            unset($cols[7]);
        }
        return $cols;
    }


    /**
     * Обрабатывает ввод (DataTables.js и др.) и возвращает фильтр для запроса к БД
     * 
     * @param array $request Данные запроса
     * 
     * @return array Данные фильтра
     */
    public static function prepareFilter(array $request)
    {
        $res = [];
        if (!empty($request['cat_id'])) {
            $res['cat_id'] = explode(',', $request['cat_id']);
        }
        $keys = ['attr_id', 'have-no-tpl', 'is_deleted', 'type_id', 'part_num', 'country_id', 'depot_id', 'code', 'model_id', 'vendor_id', 'id', 'collect_dates', 'hide-empty', 'show-disposals'];
        foreach ($keys as $k) {
            if (!empty($request[$k])) {
                $res[$k] = $request[$k];
            }
        }
        if (User::hasRole('service')) {
            $depot = Depots::getDepot(['user_id' => User::getData('id')]);
            if (!$depot) {
                $depot = Depots::addDepot(['name' => 'Разбор', 'user_id' => User::getData('id')]);
            }
            if (!$depot) {
                throw new \Exception(self::$db->getErrorInfo());
            }
            $res['depot_id'] = $depot['id'];
        } else if (User::hasRole('slave-admin')) {
            $res['depot_id'] = (!empty($res['depot_id'])) ? $res['depot_id'] : [1, 2]; // Главный и ИП Кулиджанов
        }
        if (!empty($res['show-disposals']) && !empty($res['depot_id']) && $res['depot_id'] != 'all') {
            $res['id_query'] = 'SELECT `part_id` FROM `' . Batches::TABLE . '` WHERE `depot_id` = ' . $res['depot_id'] . ' AND `exp_date` <= "' . date('Y-m-d') . '"';
        }
        if (isset($request['draw'])) {
            $res['draw'] = $request['draw']; // токен для datatables
        }
        if (!empty($request['search[value]'])) {
            $res['search'] = $request['search[value]'];
        }
        if (isset($request['start'])) {
            $res['offset'] = $request['start'];
        }
        if (isset($request['length'])) {
            $res['limit'] = $request['length'];
        }
        if (isset($request['order[0][column]'])) {
            $res['order_col'] = $request['order[0][column]'];
        }
        if (isset($request['order[0][dir]'])) {
            $res['dir'] = $request['order[0][dir]'];
        }
        return $res;
    }


    public static function getFilterCnt(array $filter)
    {
        return Parts::count(self::getDefaultFilter($filter), (!empty($filter['for_each_depot']) ? 'depots' : 'parts'));
    }


    public static function getTotalCnt()
    {
        return Parts::count(self::getDefaultFilter(), (!empty($filter['for_each_depot']) ? 'depots' : 'parts'));
    }


    /**
     * Возвращает кол-во готовых к утилизации запчастей
     * 
     * @return int Кол-во запчастей
     */
    public static function getDisposalPartsCount()
    {
        $depot = Depots::getDepot(['user_id' => User::getData('id')]);
        return Batches::getDisposalPartsCount($depot['id']);
    }


    /**
     * Возвращает список выбранных на утилизацию запчастей
     * 
     * @param array $filter Фильтр
     * 
     * @return array Список запчастей
     */
    public static function getDisposalParts(array $filter = [])
    {
        if (empty($filter['depot_ids']) || empty($filter['part_ids'])) {
            return ['is_error' => 1, 'message' => 'Не выбраны склады или запчасти.'];
        }
        $depotIDs = explode(',', $filter['depot_ids']);
        $partIDs = explode(',', $filter['part_ids']);
        $result = [];
        foreach ($partIDs as $i => $partID) {
            $rows = self::$db->exec('SELECT 
            p.`id`, p.`name`, b.`depot_id`, b.`qty` AS num FROM `' . Balance::TABLE . '` b 
            LEFT JOIN `' . Parts::TABLE . '` p ON p.`id` = b.`part_id` 
            WHERE b.`part_id` = ? AND b.`depot_id` = ?', [$partID, $depotIDs[$i]]);
            if (!$rows) {
                continue;
            }
            $rows[0]['disposal_num'] = Batches::getDisposalPartNum($rows[0]['id'], $rows[0]['depot_id']);
            $part = $rows[0];
            $part['depot'] = Depots::getDepot(['id' => $part['depot_id']])['name'];
            $result[] = $part;
        }
        return $result;
    }


    public static function getParts(array $filter = [])
    {
        if (!empty($filter['order_col'])) {
            $cols = self::getCols($filter);
            $filter['sort'] = $cols[$filter['order_col']]['uri'];
        }
        $filter = self::getDefaultFilter($filter);
        $rows = Parts::getParts($filter, (!empty($filter['for_each_depot']) ? 'depots' : 'parts'));
        $userRole = User::getData('role');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if ($rows[$i]['vendor'] && $userRole != 'service') {
                $rows[$i]['name'] .= ', ' . $rows[$i]['vendor']['name'];
            }
        }
        return $rows;
    }


    public static function getDepotsList()
    {
        $filter = [];
        if (User::hasRole('slave-admin')) {
            $filter = ['id' => [1, 2]];
        }
        $depots = Depots::getDepots($filter);
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


    public static function getCodesList(array $filter = [])
    {
        return self::$db->exec('SELECT DISTINCT `code` FROM `' . Parts::TABLE_GROUP . '` ORDER BY `code`');
    }


    public static function getModelsList(array $filter = [])
    {
        $catWhere = (!empty($filter['cat_id'])) ? 'AND `cat` = ' . $filter['cat_id'] : '';
        return self::$db->exec('SELECT `id`, `name` FROM `' . Models::TABLE . '` WHERE `id` IN (SELECT `model_id` FROM `' . Parts::TABLE_MODELS . '`) ' . $catWhere . ' ORDER BY `name`');
    }


    public static function getCatsList(array $filter = [])
    {
        return self::$db->exec('SELECT `id`, `name` FROM `' . Cats::TABLE . '` WHERE `id` IN (SELECT `model_cat_id` FROM `' . Parts::TABLE_MODELS . '`) ORDER BY `name`');
    }


    public static function getPartsList(array $filter = [])
    {
        return self::$db->exec('SELECT `id`, `name` FROM `' . Parts::TABLE . '` WHERE `del_flag` = 0 ORDER BY `name`');
    }
}


PartsTable::init();
