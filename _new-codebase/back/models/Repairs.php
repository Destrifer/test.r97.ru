<?php

namespace models;

class Repairs extends _Model
{
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    /**
     * Удаляет созданные, но не заполненные карточки ремонта
     * 
     * @return void
     */
    public static function clearUnused()
    {
        $date = new \DateTime();
        $date->modify('-6 days');
        $rows = self::$db->exec('SELECT `id` FROM `repairs` WHERE `status_admin` = "Принят"  
                    AND `name_shop` = "" 
                    AND `city_shop` = "" AND `address_shop` = "" AND `phone_shop` = "" 
                    AND `serial` = ""  AND `disease` = "0" AND `total_price` = 0 
                    AND `anrp_number` = "" AND `return_id` = 0 AND `imported` = 0  
                    AND `doubled` = 0 AND `create_date` < "' . $date->format('Y-m-d 00:00:00') . '"');
        foreach ($rows as $row) {
            self::$db->exec('DELETE FROM `repairs` WHERE `id` = ?', [$row['id']]);
            self::$db->exec('DELETE FROM `log` WHERE `object_id` = ? AND `cat_id` = 1', [$row['id']]);
        }
    }
}

Repairs::init();
