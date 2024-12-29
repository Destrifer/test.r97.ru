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


		public static function massChangeTransportTariff(array $servicesIDs, int $tariffID)
		{
				// 1) Меняем тариф для всех сервисов из списка
				self::$db->exec(
						'UPDATE `requests` 
						SET `transport_tariff_id` = ? 
						WHERE `user_id` IN (' . SQL::IN($servicesIDs, false) . ')',
						[$tariffID]
				);

				// 2) Определяем таблицу, из которой будем брать записи
				switch ($tariffID) {
						case 2:
								$tableName = 'transfer';
								break;
						case 3:
								$tableName = 'transfer_3';
								break;
						default:
								// Считаем, что "1" или пустое значение → 'transfer_2'
								$tableName = 'transfer_2';
				}

				// 3) Получаем все строки из выбранной таблицы
				$rows = self::$db->exec("SELECT * FROM `$tableName`");

				// 4) Для каждого сервиса чистим старые данные и вставляем новые
				foreach ($servicesIDs as $serviceID) {
						self::$db->exec(
								'DELETE FROM `transfer_service` WHERE `service_id` = ?',
								[$serviceID]
						);

						// Формируем массив значений для вставки
						$values = [];
						foreach ($rows as $row) {
								$values[] = '(' 
										. (int)$serviceID . ', ' 
										. (int)$row['cat_id'] . ', ' 
										. (int)$row['shop'] . ', ' 
										. (int)$row['buyer'] . ', ' 
										. (float)$row['zone_1'] . ', ' 
										. (float)$row['zone_2'] . ', ' 
										. (float)$row['zone_3'] . ', ' 
										. (float)$row['zone_4'] 
								. ')';
						}

						// Выполняем массовую вставку (если есть что вставлять)
						if (!empty($values)) {
								self::$db->exec(
										'INSERT INTO `transfer_service` 
										(`service_id`, `cat_id`, `shop`, `buyer`, `zone_1`, `zone_2`, `zone_3`, `zone_4`) 
										VALUES ' . implode(',', $values)
								);
						}
				}

				return true;
		}


		
    public static function massChangeTariff(array $servicesIDs, $tariffID)
{
    // Обновляем тариф для всех переданных сервисов
    self::$db->exec(
        'UPDATE `requests` SET `tariff_id` = ? WHERE `user_id` IN (' . SQL::IN($servicesIDs, false) . ')',
        [$tariffID]
    );

    // Определяем таблицу на основе tariffID
    switch ($tariffID) {
        case 2:
            $tableName = 'prices_2';
            break;
        case 3:
            $tableName = 'prices-2023';
            break;
        default:
            $tableName = 'prices'; // По умолчанию используем 'prices'
            break;
    }

    // Получаем данные из нужной таблицы
    $rows = self::$db->exec("SELECT * FROM `$tableName`");

    // Синхронизируем каждый сервис
    foreach ($servicesIDs as $serviceID) {
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

    return true;
}



    public static function sychTariff(array $servicesIDs)
{
    foreach ($servicesIDs as $serviceID) {
        // Получаем tariff_id для текущего serviceID
        $tariffID = self::$db->exec('SELECT tariff_id FROM `requests` WHERE `user_id` = ?', [$serviceID])[0]['tariff_id'];

        // Определяем таблицу на основе tariff_id
        switch ($tariffID) {
            case 2:
                $tableName = 'prices_2';
                break;
            case 3:
                $tableName = 'prices-2023';
                break;
            default:
                $tableName = 'prices'; // По умолчанию используем 'prices'
                break;
        }

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
