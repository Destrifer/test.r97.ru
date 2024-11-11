<?php

namespace models\repair;

use models\Counters;
use models\Parts;
use models\User;
use program\core\Query;

class Work extends \models\_Model
{

    private static $db = null;
    const TABLE = 'repairs_work';
    private static $empty = []; // шаблон


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function deleteWorkByID($workID)
    {
        self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `id` = ?', [$workID]);
    }


    public static function deleteWork($repairID, $partID)
    {
        self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `repair_id` = ? AND `part_id` = ?', [$repairID, $partID]);
    }


    public static function getWork($repairID)
    {
        return self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `repair_id` = ?', [$repairID]);
    }


    public static function saveWork(array $data, $workID = 0)
    {
        $query = new Query(self::TABLE);
        if ($workID) {
            self::$db->exec($query->update($data, $workID), $query->params);
        } else {
            $workID = self::$db->exec($query->insert($data), $query->params);
        }
        return $workID;
    }


    public static function addWork($repairID, $partID, $qty, $depotID, $cancelFlag)
    {
        $problemID = 0;
        $repairTypeID = 0;
        if ($cancelFlag) {
            $problemID = 48;
            $repairTypeID = 3;
        }
        $rows = self::$db->exec('SELECT `id`, `problem_id`, `repair_type_id` FROM `' . self::TABLE . '` WHERE `repair_id` = ? AND `part_id` = ? AND `ordered_flag` = 1 AND `depot_id` = ?', [$repairID, $partID, $depotID]);
        if ($rows) {
            if (!$repairTypeID) {
                $problemID = $rows[0]['problem_id'];
                $repairTypeID = $rows[0]['repair_type_id'];
            }
            return self::$db->exec('UPDATE `' . self::TABLE . '` SET `qty` = ?, `problem_id` = ?, `repair_type_id` = ? WHERE `id` = ?', [$qty, $problemID, $repairTypeID, $rows[0]['id']]);
        }
        $part = Parts::getPartByID2($partID);
        if (!$part) {
            return false;
        }
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` (`repair_id`, `name`, `problem_id`, `repair_type_id`, `qty`, `ordered_flag`, `part_id`, `part_type_id`, `depot_id`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [$repairID, $part['name'], $problemID, $repairTypeID, $qty, 1, $partID, $part['type_id'], $depotID]);
    }


    public static function getRepairWorkByID($repairID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `repair_id` = ?', [$repairID]);
        if (isset($rows[0])) {
            self::$empty = array_fill_keys(array_keys($rows[0]), '');
            self::$empty['tpl_flag'] = true;
        }
        $hasPriceFlag = (User::hasRole('admin') && Counters::have('has_price', $repairID));
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['part_block_type'] = self::getBlockType($rows[$i]['problem_id'], $rows[$i]['part_type_id']);
            $rows[$i]['has_price_flag'] = (in_array($rows[$i]['part_block_type'], ['repair', 'diag'])) ? $hasPriceFlag : false;
        }
        return $rows;
    }


    public static function getEmpty()
    {
        if (self::$empty) {
            return self::$empty;
        }
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` LIMIT 1');
        self::$empty = array_fill_keys(array_keys($rows[0]), '');
        self::$empty['tpl_flag'] = true;
        return self::$empty;
    }


    private static function getBlockType($problemID, $partTypeID = 0)
    {
        if (!$problemID && !$partTypeID) {
            return 'repair';
        }
        if (!$problemID) {
            if ($partTypeID == 3) { // аксессуары
                return 'diag';
            }
            return 'repair';
        }
        $rows = self::$db->exec('SELECT `work_type` FROM `details_problem` WHERE `id` = ?', [$problemID]);
        if (!$rows || $rows[0]['work_type'] == 'repair') {
            return 'repair';
        }
        if ($rows[0]['work_type'] == 'diag') {
            return 'diag';
        }
        return 'nonrepair';
    }
}

Work::init();
