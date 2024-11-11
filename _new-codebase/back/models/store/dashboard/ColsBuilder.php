<?php

namespace models\store\dashboard;

use models\Storage;
use models\User;
use program\core\App;

/** 
 * Настраиваемые столбцы дашборда
 */

class ColsBuilder extends \models\_Model
{

    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getCols($tabURI)
    {
        return Storage::get(self::generateStorageKey($tabURI));
    }


    public static function saveCols($tabURI, $colsData)
    {
        Storage::save(self::generateStorageKey($tabURI), $colsData);
    }


    private static function generateStorageKey($tabURI)
    {
        return User::getData('id') . ':' . $tabURI . ':dashboard-store';
    }
}

ColsBuilder::init();
