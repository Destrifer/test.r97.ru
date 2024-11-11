<?php

namespace models\repair;

class Util extends \models\_Model
{

    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Обновляет флаг "серийный номер неверный"
     * 
     * @param string $serial Номер
     * @param int $modelID Модель
     * 
     * @return void
     */
    public static function updateSerialInvalidFlag($serial, $modelID)
    {
        if (empty($serial) || empty($modelID)) {
            return;
        }
        $len = floor(strlen($serial) / 2);
        $rows = self::$db->exec('SELECT `id`, `serial`, `serial_invalid_flag` FROM `repairs` WHERE `model_id` = ? AND `serial` LIKE ? AND `no_serial` = 0', [$modelID, substr(0, $len, $serial) . '%']);
        foreach ($rows as $row) {
            if (!\models\Serials::isValid($row['serial'], $modelID)) {
                if (!$row['serial_invalid_flag']) {
                    self::$db->exec('UPDATE `repairs` SET `serial_invalid_flag` = 1 WHERE `id` = ?', [$row['id']]);
                }
            } else {
                if ($row['serial_invalid_flag']) {
                    self::$db->exec('UPDATE `repairs` SET `serial_invalid_flag` = 0 WHERE `id` = ?', [$row['id']]);
                }
            }
        }
    }
}

Util::init();
