<?php

namespace models;

/** 
 * v. 0.1
 * 2021-06-21
 */

class Serials extends _Model
{
    const ALLOW_EMPTY = 1;
    const NOT_ALLOW_EMPTY = 2;
    const TABLE = 'serials';
    public static $updateMode;
    public static $message = '';
    private static $db = null;
    private static $cache = ['providers' => [], 'serials' => []];
    private static $emptySerial = ['id' => 0, 'serial' => '', 'order' => '', 'lot' => 0, 'provider' => '', 'plant' => '', 'provider_id' => 0, 'plant_id' => 0, 'model_id' => 0];


    public static function init()
    {
        self::$db = _Base::getDB();
        self::$updateMode = self::ALLOW_EMPTY;
    }


    public static function addSerial($serial, $modelID, $lot, $order, $providerID, $plantID)
{
    if (empty($serial) || empty($modelID)) {
        self::$message = 'Модель или серийный номер отсутствуют.';
        return false;
    }

    $serial = trim($serial);

    // Получаем максимальное значение id из таблицы
    $currentMaxId = self::$db->query('SELECT MAX(id) AS max_id FROM `serials`')->fetch()['max_id'];
    $newId = $currentMaxId + 1;

    // Выполняем запрос с указанным id
    $query = 'INSERT INTO `serials` (`id`, `model_id`, `serial`, `provider_id`, `order`, `plant_id`, `lot`) VALUES 
        (' . (int)$newId . ', ' . (int)$modelID . ', "' . $serial . '", "' . $providerID . '", "' . $order . '", "' . $plantID . '", "' . $lot . '")';

    $result = self::$db->exec($query);

    if (!$result) {
        self::$message = self::$db->getErrorInfo();
        return false;
    }

    return true;
}



    public static function delSerial($serialID)
    {
        if (!$serialID) {
            return false;
        }
        return self::$db->exec('DELETE FROM `serials` WHERE `id` = ?', [$serialID]);
    }


    public static function getSerial($serial, $modelID)
    {
        $serial = trim($serial);
        if (!$serial) {
            return self::$emptySerial;
        }
        $serialInfo = self::$emptySerial;
        $cacheKey = $serial . $modelID;
        if (isset(self::$cache['serials'][$cacheKey])) {
            return self::$cache['serials'][$cacheKey];
        }
        $rows = self::$db->exec('SELECT * FROM `serials` WHERE `model_id` = ?', [$modelID]);
        foreach ($rows as $row) {
            if (!self::isTplMatch($serial, $row['serial'], $row['lot'])) {
                continue;
            }
            $serialInfo = $row;
            $serialInfo['provider'] = self::getProvider($serialInfo['provider_id'])['name'];
            $serialInfo['plant'] = self::getPlant($serialInfo['plant_id']);
            break;
        }
        self::$cache['serials'][$cacheKey] = $serialInfo;
        return $serialInfo;
    }


    public static function getSerialByID($serialID)
    {
        $cacheKey = $serialID;
        if (isset(self::$cache['serials'][$cacheKey])) {
            return self::$cache['serials'][$cacheKey];
        }
        $rows = self::$db->exec('SELECT * FROM `serials` WHERE `id` = ?', [$serialID]);
        if (!$rows) {
            return self::$emptySerial;
        }
        $rows[0]['provider'] = self::getProvider($rows[0]['provider_id'])['name'];
        $rows[0]['plant'] = self::getPlant($rows[0]['plant_id']);
        self::$cache['serials'][$cacheKey] = $rows[0];
        return $rows[0];
    }


    public static function getSerials($modelID)
    {
        $rows = self::$db->exec('SELECT * FROM `serials` WHERE `model_id` = ?', [$modelID]);
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['provider'] = self::getProvider($rows[$i]['provider_id'])['name'];
            $rows[$i]['plant'] = self::getPlant($rows[$i]['plant_id']);
        }
        return $rows;
    }


    public static function getProvider($providerID, $providerName = '')
    {
        $empty = ['id' => 0, 'name' => ''];
        if (!$providerID && !$providerName) {
            return $empty;
        }
        $k = $providerID . $providerName;
        if (!isset(self::$cache['providers'][$k])) {
            if ($providerName) {
                $rows = self::$db->exec('SELECT * FROM `providers` WHERE `name` = ?', [$providerName]);
            } else {
                $rows = self::$db->exec('SELECT * FROM `providers` WHERE `id` = ' . $providerID);
            }
            self::$cache['providers'][$k] = (!$rows) ? $empty : $rows[0];
        }
        return self::$cache['providers'][$k];
    }


    public static function getPlant($plantID)
    {
        $rows = self::$db->exec('SELECT `name` FROM `plants` WHERE `id` = ' . $plantID);
        if (!isset($rows[0]['name'])) {
            return '';
        }
        return $rows[0]['name'];
    }


    public static function updateSerial($serialID, $serial, $lot, $order, $providerID, $plantID)
    {
        $serial = trim($serial);
        if (self::$updateMode == self::NOT_ALLOW_EMPTY) {
            $origSerial = self::$db->exec('SELECT * FROM `serials` WHERE `id` = ?', [$serialID]);
            if (!$origSerial) {
                return false;
            }
            $providerID = ($providerID) ? $providerID : $origSerial[0]['provider_id'];
            $plantID = ($plantID) ? $plantID : $origSerial[0]['plant_id'];
            $lot = ($lot) ? $lot : $origSerial[0]['lot'];
            $order = ($order) ? $order : $origSerial[0]['order'];
        }
        return self::$db->exec('UPDATE `serials` SET `serial` = ?, `provider_id` = ?, `order` = ?, 
            `plant_id` = ?, `lot` = ? WHERE `id` = ?', [$serial, $providerID, $order, $plantID, $lot, $serialID]);
    }


    public static function isValid($serial, $modelID)
    {
        $serial = trim($serial);
        $rows = self::$db->exec('SELECT `serial`, `lot` FROM `serials` WHERE `model_id` = ?', [$modelID]);
        if (!$rows) {
            return false;
        }
        foreach ($rows as $row) {
            if (self::isTplMatch($serial, $row['serial'], $row['lot'])) {
                return true;
            }
        }
        return false;
    }


    private static function isTplMatch($serial, $serialTpl, $lot)
    {
        if (empty($serialTpl) || empty($lot)) {
            return false;
        }
        $lotLen = strlen($lot);
        $m = [];
        preg_match('/^([0-9a-z- ]+)([0-9]{' . $lotLen . '})([a-z]*)$/i', $serial, $m);
        /* 
            $m[1] - База номера
            $m[2] - Кол-во в партии
            $m[3] - Завершающие буквы (если есть)
        */
        if (empty($m[1]) || (int)$m[2] > $lot) {
            return false;
        }
        if (preg_match('/^' . $m[1] . '[0-9]{' . $lotLen . '}' . $m[3] . '$/i', $serialTpl)) {
            return true;
        }
        return false;
    }
}


Serials::init();
