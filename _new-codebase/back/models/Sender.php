<?php

namespace models;

use program\core;

/**
 * v. 0.1
 * 2021-03-24
 */

class Sender extends _Model
{

    protected static $db = null;

    public static function init()
    {
        self::$db = _Base::getDB();
    }

    public static function use($senderName)
    {
        switch ($senderName) {
            case 'bell':
                return new sender\Bell(self::$db);
            case 'email':
                $mail = new sender\EMail(self::$db);
                $mail->addAccount(core\App::$config['mail_host'], core\App::$config['mail_username'], core\App::$config['mail_password'], core\App::$config['mail_from'], core\App::$config['mail_reply_to'], true);
                return $mail;
            default:
                throw new \Exception('Sender name #' . $senderName . ' not found.');
        }
    }
}


Sender::init();
