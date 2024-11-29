<?php

namespace models;

use program\core\SQL;

class Tariffs extends _Model
{
    public static $message = '';
    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getServiceTariffTable($serviceID)
    {
        $rows = self::$db->exec('SELECT `tariff_id` FROM `requests` WHERE `user_id` = ?', [$serviceID]);
        if (empty($rows[0]['tariff_id']) || $rows[0]['tariff_id'] == 2) { // Тариф 2018
            return 'prices_2';
        }
        if($rows[0]['tariff_id'] == 3){
            return 'prices-2023';
        }
        return 'prices';
    }


    public static function getTransportTariffTable($serviceID)
    {
        $rows = self::$db->exec('SELECT `transport_tariff_id` FROM `requests` WHERE `user_id` = ?', [$serviceID]);
        if (empty($rows[0]['transport_tariff_id']) || $rows[0]['transport_tariff_id'] == 1) { // Тариф 2018
            return 'transfer_2';
        }
        return 'transfer';
    }


    public static function massChangeTransportTariff(array $servicesIDs, $tariffID)
    {
        return self::$db->exec('UPDATE `requests` SET `transport_tariff_id` = ? WHERE `user_id` IN (' . SQL::IN($servicesIDs, false) . ')', [$tariffID]);
    }


    public static function massChangeTariff(array $servicesIDs, $tariffID)
    {
        return self::$db->exec('UPDATE `requests` SET `tariff_id` = ? WHERE `user_id` IN (' . SQL::IN($servicesIDs, false) . ')', [$tariffID]);
    }


    public static function sychTariff(array $servicesIDs)
{
    foreach ($servicesIDs as $serviceID) {
        // Получаем tariff_id для текущего serviceID
        $tariffID = self::$db->exec('SELECT tariff_id FROM `requests` WHERE `user_id` = ?', [$serviceID])[0]['tariff_id'];

        // Определяем таблицу на основе tariff_id
        $tableName = match ($tariffID) {
            2 => 'prices-2',
            3 => 'prices-2023',
            default => 'prices', // По умолчанию используем 'prices' для tariff_id = 1 или отсутствующего
        };

        // Получаем данные из нужной таблицы
        $rows = self::$db->exec("SELECT * FROM `$tableName`");

        if ($serviceID == 33) { // ИП Кулиджанов
            self::sychTariffSpecial($rows);
            continue;
        }

        // Удаляем старые записи и добавляем новые
        self::$db->exec('DELETE FROM `prices_service` WHERE `service_id` = ?', [$serviceID]);

        $query = [];
        foreach ($rows as $row) {
            $query[] = '(' . $serviceID . ', ' . $row['cat_id'] . ', ' . $row['block'] . ', ' . $row['element'] . ', ' . $row['acess'] . ', ' . $row['anrp'] . ', ' . $row['ato'] . ')';
        }

        self::$db->exec('INSERT INTO `prices_service` (`service_id`, `cat_id`, `block`, `component`, `access`, `anrp`, `ato`) 
            VALUES ' . implode(',', $query));
    }
}



    private static function sychTariffSpecial(array $tariffs)
    {
        foreach ($tariffs as $row) {
            self::$db->exec('INSERT INTO `prices_service` 
            (`service_id`, `cat_id`, `block`, `component`, `access`, `anrp`, `ato`) 
            VALUES (33, ' . $row['cat_id'] . ', ' . $row['block'] . ', ' . $row['element'] . ', ' . $row['acess'] . ', ' . $row['anrp'] . ', ' . $row['ato'] . ') 
            ON DUPLICATE KEY UPDATE `block` = VALUES(block), `component` = VALUES(component), `access` = VALUES(access), 
            `anrp` = VALUES(anrp), `ato` = VALUES(ato)');
        }
    }
}


Tariffs::init();
