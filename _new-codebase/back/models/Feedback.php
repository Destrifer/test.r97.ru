<?php

namespace models;


class Feedback extends _Model
{

    const TABLE_MESSAGES = 'feedback_messages';
    const TABLE_TALKS = 'feedback_admin';
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function editMessage($messageID, $newMessage)
    {
        return self::$db->exec('UPDATE `' . self::TABLE_MESSAGES . '` SET `message` = ? WHERE `id` = ?', [trim($newMessage), $messageID]);
    }


    public static function getMessage($messageID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_MESSAGES . '` WHERE `id` = ?', [$messageID]);
        if ($rows) {
            $rows[0]['talk_id'] = $rows[0]['feedback_id'];
            return $rows[0];
        }
        return [];
    }


    public static function deleteMessage($messageID)
    {
        return self::$db->exec('DELETE FROM `' . self::TABLE_MESSAGES . '` WHERE `id` = ?', [$messageID]);
    }


    public static function getTalk($talkID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_TALKS . '` WHERE `id` = ?', [$talkID]);
        if ($rows) {
            return $rows[0];
        }
        return [];
    }
}


Feedback::init();
