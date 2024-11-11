<?php

namespace models;

use program\core\Time;

class Log extends _Model
{

    const TABLE = 'log';
    const TABLE_EVENTS = 'log_events';
    const REPAIR = 1;
    const USER = 2;
    const PARTS = 3;
    public static $cats = [
        1 => 'Ремонты',
        2 => 'Пользователи',
        3 => 'Запчасти'
    ];
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    /**
     * Логирование событий запчастей
     * 
     * @param int $eventID Событие
     * @param string $message Сообщение
     * @param int $partID Запчасть
     * 
     * @return void
     */
    public static function part($eventID, $message, $partID)
    {
        self::add($eventID, $message, self::PARTS, $partID);
    }


    /**
     * Логирование событий ремонтов
     * 
     * @param int $eventID Событие
     * @param string $message Сообщение
     * @param int $repairID Ремонт
     * 
     * @return void
     */
    public static function repair($eventID, $message, $repairID)
    {
        self::add($eventID, $message, self::REPAIR, $repairID);
    }


    /**
     * Логирование событий пользователей
     * 
     * @param int $eventID Событие
     * @param string $message Сообщение
     * @param int $userID Пользователь
     * 
     * @return void
     */
    public static function user($eventID, $message, $userID)
    {
        self::add($eventID, $message, self::USER, $userID);
    }


    private static function add($eventID, $message, $catID, $objectID)
    {
        return self::$db->exec('INSERT INTO `log` (`event_id`, `message`, `user_id`, `cat_id`, `object_id`) 
        VALUES (?, ?, ?, ?, ?)', [$eventID, trim($message), User::getData('id'), $catID, $objectID]);
    }


    public static function get(array $filter = [])
    {
        $rows = self::$db->exec('SELECT log.*, e.`name` AS event, u.`email` AS user 
        FROM `' . self::TABLE . '` log 
        LEFT JOIN `' . self::TABLE_EVENTS . '` e ON e.`id` = log.`event_id` 
        LEFT JOIN `users` u ON u.`id` = log.`user_id` 
        ' . self::getWhere($filter) . ' 
        ORDER BY log.`id` DESC LIMIT 100');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['cat'] = self::$cats[$rows[$i]['cat_id']];
            $rows[$i]['date'] = Time::format($rows[$i]['date'], 'd.m.Y H:i');
            $rows[$i]['object'] = self::getObject($rows[$i]['cat_id'], $rows[$i]['object_id']);
        }
        return $rows;
    }


    private static function getWhere(array $filter)
    {
        $where = [];
        if (!empty($filter['cat'])) {
            $where[] = 'log.`cat_id` = ' . $filter['cat'];
        }
        if (!empty($filter['object'])) {
            $where[] = 'log.`object_id` = ' . $filter['object'];
        }
        if (!empty($filter['user'])) {
            $where[] = 'log.`user_id` = ' . $filter['user'];
        }
        if (!empty($filter['event'])) {
            $where[] = 'log.`event_id` = ' . $filter['event'];
        }
        if ($where) {
            return 'WHERE ' . implode(' AND ', $where);
        }
        return '';
    }


    public static function getEvents()
    {
        return self::$db->exec('SELECT * FROM `' . self::TABLE_EVENTS . '` ORDER BY `name`');
    }


    private static function getObject($catID, $objectID)
    {
        if (empty($catID) || empty($objectID)) {
            return [];
        }
        switch ($catID) {
            case self::REPAIR:
                return ['name' => '#' . $objectID, 'url' => '/edit-repair/' . $objectID . '/'];

            case self::USER:
                return ['name' => '#' . $objectID, 'url' => '/user/?id=' . $objectID];
            default:
                return [];
        }
    }
}


Log::init();
