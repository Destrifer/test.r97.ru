<?php

namespace models;


/** 
 * v. 0.1
 * 2020-08-26
 */

class Counters extends _Model
{
    const TABLE = 'counters';
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }

    public static function getTotalCount($name, $userID)
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . self::TABLE . '` WHERE `name` = ? AND `user_id` = ?', [$name, $userID]);
        return (int)$rows[0]['cnt'];
    }

    public static function add($name, $repairID, $userID)
    {
        self::$db->exec('INSERT INTO `' . self::TABLE . '` (`name`, `user_id`, `repair_id`) VALUES (?, ?, ?)', [$name, $userID, $repairID]);
    }

    public static function delete($name, $repairID, $userID)
    {
        self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `name` = ? AND `user_id` = ? AND `repair_id` = ?', [$name, $userID, $repairID]);
    }

    public static function have($name, $repairID, $userID = 0)
    {
        $where = '';
        if ($userID) {
            $where = ' AND `user_id` = "' . ((int)$userID) . '" ';
        }
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . self::TABLE . '` WHERE `name` = ? AND `repair_id` = ? ' . $where, [$name, $repairID]);
        return (bool)$rows[0]['cnt'];
    }
}


Counters::init();
