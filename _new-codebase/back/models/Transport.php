<?php

namespace models;

class Transport extends _Model
{
    public static $message = '';
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getCost($zone, $serviceID, $catID)
    {
        $k = str_replace('zone', 'zone_', $zone);
        if ($serviceID) {
            $rows = self::$db->exec('SELECT `' . $k . '` FROM `transfer_service` WHERE `cat_id` = ' . $catID . ' AND `service_id` = ' . $serviceID);
            if (!empty($rows[0][$k])) {
                return $rows[0][$k];
            }
        }
        $table = Tariffs::getTransportTariffTable($serviceID);
        $rows = self::$db->exec('SELECT `' . $k . '` FROM `' . $table . '` WHERE `cat_id` = ' . $catID);
        return ($rows) ? $rows[0][$k] : 0;
    }


    public static function sychTariff(array $servicesIDs)
    {
        $rows = self::$db->exec('SELECT * FROM `transfer`');
        foreach ($servicesIDs as $serviceID) {
            self::$db->exec('DELETE FROM `transfer_service` WHERE `service_id` = ?', [$serviceID]);
            $query = [];
            foreach ($rows as $row) {
                $query[] = '(' . $serviceID . ', ' . $row['cat_id'] . ', ' . $row['shop'] . ', ' . $row['buyer'] . ', ' . $row['zone_1'] . ', ' . $row['zone_2'] . ', ' . $row['zone_3'] . ', ' . $row['zone_4'] . ')';
            }
            self::$db->exec('INSERT INTO `transfer_service` (`service_id`, `cat_id`, `shop`, `buyer`, `zone_1`, `zone_2`, `zone_3`, `zone_4`) 
                VALUES ' . implode(',', $query));
        }
    }
}


Transport::init();
