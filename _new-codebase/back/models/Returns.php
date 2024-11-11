<?php

namespace models;

use program\core;

/** 
 * v. 0.1
 * 2021-06-30
 */

class Returns extends _Model
{
    public static $message = '';
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getReadyDate($returnID)
    {
        $rows = self::$db->exec('SELECT MAX(finish_date) AS date FROM `repairs` WHERE `return_id` = ?', [$returnID]);
        return core\Time::format($rows[0]['date']);
    }


    /** Возвращает процент завершенных ремонтов партии */
    public static function getBatchProgress($returnsID)
    {
        $done = self::$db->exec('SELECT COUNT(*) AS cnt FROM `repairs` WHERE `return_id` = ' . $returnsID . ' AND `deleted` = 0 AND `status_admin` IN ("Подтвержден", "Выдан")');
        if (!$done[0]['cnt']) {
            return 0;
        }
        $total = self::$db->exec('SELECT COUNT(*) AS cnt FROM `repairs` WHERE `return_id` = ' . $returnsID . ' AND `deleted` = 0');
        if (!$total[0]['cnt']) {
            return 0;
        }
        return round($done[0]['cnt'] / $total[0]['cnt'] * 100);
    }


    public static function getBatchByID($id)
    {
        if (!$id) {
            return [];
        }
        $rows = self::$db->exec('SELECT * FROM `returns` WHERE `id` = ' . $id);
        if (!$rows) {
            return [];
        }
        return $rows[0];
    }
}


Returns::init();
