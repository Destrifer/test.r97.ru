<?php

namespace models\staff;

use models\Services;

/** 
 * Категории, разрешенные для просмотра персоналу
 */

class StaffCats extends \models\_Model
{

    private static $db = null;
    const TABLE = 'users_cats'; // сведения о выбранных категориях
    const TABLE_SERVICE = 'cats_users'; // обслуживаемые в СЦ категории
    const MAIN_TYPE = 1; // основные
    const RESERVE_TYPE = 2; // резервные


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getStaffCats($userID, $serviceID)
    {
        $serviceID = Services::getServiceUserIDByID($serviceID);
        $rowsService = self::$db->exec('SELECT s.`cat_id`, c.`name`  
        FROM `' . self::TABLE_SERVICE . '` s LEFT JOIN `cats` c ON c.`id` = s.`cat_id`   
        WHERE s.`service_id` = ' . $serviceID . ' AND c.`is_deleted` = 0 ORDER BY c.`name`');
        if (!$rowsService) {
            return [];
        }
        $userCats = self::getUserCheckedCatIDs($userID);
        for ($i = 0, $cnt = count($rowsService); $i < $cnt; $i++) {
            $rowsService[$i]['is_checked'] = in_array($rowsService[$i]['cat_id'], $userCats);
        }
        return $rowsService;
    }


    public static function getCatsTree(array $cats)
    {
        $tree = [];
        foreach ($cats as $cat) {
            $l = mb_substr($cat['name'], 0, 1);
            if (!isset($tree[$l])) {
                $tree[$l] = [];
            }
            $tree[$l][] = $cat;
        }
        return $tree;
    }


    public static function save(array $rawData)
    {
        $userID = $rawData['user_id'];
        $catIDs = $rawData['cat_id'] ?? [];
        $userCats = self::getUserCheckedCatIDs($userID);
        $delCatIDs = array_diff($userCats, $catIDs);
        $addCatIDs = array_diff($catIDs, $userCats);
        if ($delCatIDs) {
            self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `user_id` = ' . $userID . ' AND `cat_id` IN (' . implode(',', $delCatIDs) . ')');
        }
        if ($addCatIDs) {
            $query = '';
            foreach ($addCatIDs as $catID) {
                $query .= ' (' . $userID . ', 1, ' . $catID . '),';
            }
            self::$db->exec('INSERT INTO `' . self::TABLE . '` (`user_id`, `type_id`, `cat_id`) VALUES ' . rtrim($query, ','));
        }
        return ['message' => 'Добавлено категорий: ' . count($addCatIDs) . ', снято: ' . count($delCatIDs) . '.', 'error_flag' => 0,];
    }


    private static function getUserCheckedCatIDs($userID)
    {
        return array_column(self::$db->exec('SELECT c.`cat_id`   
        FROM `' . self::TABLE . '` c  
        WHERE c.`user_id` = ' . $userID), 'cat_id');
    }
}

StaffCats::init();
