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


    public static function sychTransportTariff(array $servicesIDs)
	{
			foreach ($servicesIDs as $serviceID) {
					// Получаем transport_tariff_id из таблицы requests
					$rowsTariff = self::$db->exec(
							'SELECT `transport_tariff_id` FROM `requests` WHERE `user_id` = ?', 
							[$serviceID]
					);

					// Если запрос ничего не вернул или поле пустое — пусть будет 1 по умолчанию
					$transportTariffID = $rowsTariff[0]['transport_tariff_id'] ?? 1;

					// Определяем таблицу по transport_tariff_id
					switch ($transportTariffID) {
							case 2:
									$tableName = 'transfer';
									break;
							case 3:
									$tableName = 'transfer_3';
									break;
							default:
									// Если 1 или не задано явно — используем transfer_2
									$tableName = 'transfer_2';
					}

					// Получаем строки из нужной таблицы
					$rows = self::$db->exec("SELECT * FROM `$tableName`");

					// Удаляем старые записи для этого сервиса
					self::$db->exec('DELETE FROM `transfer_service` WHERE `service_id` = ?', [$serviceID]);

					// Формируем массив для массовой вставки
					$query = [];
					foreach ($rows as $row) {
							$query[] = '(' 
									. $serviceID . ', ' 
									. $row['cat_id'] . ', ' 
									. $row['shop'] . ', ' 
									. $row['buyer'] . ', ' 
									. $row['zone_1'] . ', ' 
									. $row['zone_2'] . ', ' 
									. $row['zone_3'] . ', ' 
									. $row['zone_4'] 
									. ')';
					}

					// Записываем новые данные в transfer_service
					if (!empty($query)) {
							self::$db->exec(
									'INSERT INTO `transfer_service` 
									(`service_id`, `cat_id`, `shop`, `buyer`, `zone_1`, `zone_2`, `zone_3`, `zone_4`) 
									VALUES ' . implode(',', $query)
							);
					}
			}
	}
}


Transport::init();
