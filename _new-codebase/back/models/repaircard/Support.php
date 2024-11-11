<?php

namespace models\repaircard;

use models\Repair;
use models\User;

class Support extends \models\_Model
{

    private static $db = null;
    const TABLE_MESSAGES = 'feedback_messages';
    const TABLE_THREADS = 'feedback_admin';


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Отправляет сообщение на страницу поддержки ремонта
     * 
     * @param string $message Сообщение
     * @param int $repairID Ремонт
     * 
     * @return int ID сообщения
     */
    public static function sendMessage($message, $repairID)
    {
        $message = trim($message);
        if (empty($message) || !$repairID) {
            return 0;
        }
        $threadID = self::getThreadID($repairID, $message);
        if (!$threadID) {
            $threadID = self::createThread($repairID, $message);
        }
        $messageID = 0;
        $read = 0;
        $userType = 1;
        if (User::hasRole('admin')) {
            $read = 1;
            $userType = 2;
        }
        $messageID = self::$db->exec('INSERT INTO `' . self::TABLE_MESSAGES . '` 
                            (`feedback_id`, `message`, `user_type`, `date`, `read`) 
                            VALUES (?, ?, ?, ?, ?)', [$threadID, $message, $userType, date('Y-m-d H:i:s'), $read]);
        if (!$messageID) {
            return 0;
        }
        self::setReadStatus($threadID);
        return $messageID;
    }


    /**
     * Устанавливает статус "прочитано" темы
     * 
     * @param int $threadID Тема
     * 
     * @return void
     */
    public static function setReadStatus($threadID)
    {
        if (User::hasRole('admin')) {
            self::$db->exec('UPDATE `' . self::TABLE_THREADS . '` SET
        `read_admin` = 1, 
        `read` = 0 
        WHERE `id` = ' . $threadID);
        } else {
            self::$db->exec('UPDATE `' . self::TABLE_THREADS . '` SET
        `read_admin` = 0, 
        `read` = 1 
        WHERE `id` = ' . $threadID);
        }
    }


    /**
     * Получает ID темы
     * 
     * @param int $repairID Ремонт
     * 
     * @return int ID темы
     */
    public static function getThreadID($repairID)
    {
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE_THREADS . '` WHERE `repair_id` = ?', [$repairID]);
        if (!$rows) {
            return 0;
        }
        return $rows[0]['id'];
    }


    /**
     * Cоздает тему
     * 
     * @param int $repairID Ремонт
     * @param string $message Первое сообщение
     * 
     * @return int ID темы
     */
    public static function createThread($repairID, $message)
    {
        $threadID = self::getThreadID($repairID);
        if ($threadID) {
            return $threadID;
        }
        return self::$db->exec('INSERT INTO `' . self::TABLE_THREADS . '` (`repair_id`, `status`, `user_id`, `message`, `date`) VALUES (?, ?, ?, ?, ?)', [$repairID, 'Вопрос открыт', User::getData('id'), $message, date('Y-m-d H:i:s')]);
    }
}

Support::init();
