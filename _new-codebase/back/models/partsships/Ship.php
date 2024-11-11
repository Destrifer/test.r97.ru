<?php

namespace models\partsships;

use models\Parts;
use models\parts\Balance;
use models\parts\Log;
use models\parts\log\LogTable;
use models\Serials;
use program\core\Query;

class Ship extends \models\_Model
{

    const TABLE = 'parts2_ships';
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getShipByID($shipID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$shipID]);
        if (!$rows) {
            return [];
        }
        $rows[0]['send_date'] = date('d.m.Y', strtotime($rows[0]['send_date']));
        $rows[0]['parts'] = json_decode($rows[0]['parts'], true);
        $rows[0]['serial_info'] = Serials::getSerial($rows[0]['serial'], $rows[0]['model_id']);
        return $rows[0];
    }


    public static function save(array $rawData)
    {
        if (!empty($rawData['id'])) {
            return self::update($rawData, $rawData['id']);
        }
        $error = self::check($rawData);
        if ($error) {
            return ['message' => $error, 'error_flag' => 1];
        }
        $parts = [];
        for ($i = 0, $cnt = count($rawData['part_id']); $i < $cnt; $i++) {
            $parts[$rawData['part_id'][$i]] = $rawData['part_num'][$i];
        }
        $data = self::prepareData($rawData, $parts);
        $depotID = $data['depot_id']; // склад-отправитель
        $query = new Query(self::TABLE);
        $shipID = self::$db->exec($query->insert($data), $query->params);
        if (!$shipID) {
            return ['message' => 'Не удалось отправить: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        foreach ($parts as $partID => $num) {
            if (!Balance::isEnough($depotID, $partID, $num)) {
                $p = Parts::getPartByID2($partID);
                return ['message' => 'Недостаточно запчасти "' . $p['name'] . '".', 'error_flag' => 1];
            }
            $r = Log::ship($partID, $depotID, $num, Balance::count($partID, $depotID), $shipID, $data['model_id'], $data['serial'], $data['send_date']);
            if (!$r) {
                return ['message' => 'Не удалось списать со склада: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            $r = Balance::take($partID, $num, $depotID);
            if (!$r) {
                return ['message' => 'Не удалось списать со склада: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        return ['message' => 'Запчасть успешно отправлены.', 'error_flag' => 0,];
    }


    private static function update(array $rawData, $shipID)
    {
        $data = self::prepareData($rawData);
        $query = new Query(self::TABLE);
        $r = self::$db->exec($query->update($data, $shipID), $query->params);
        if (!$r) {
            return ['message' => 'Не удалось обновить: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $log = LogTable::getLog(['object_id' => $shipID, 'event_id' => Log::SHIP_EVENT]);
        if ($log) {
            self::$db->exec('UPDATE `' . Log::TABLE . '` SET `serial` = ?, `object2_id` = ? WHERE `id` = ?', [$data['serial'], $data['model_id'], $log[0]['id']]);
        }
        return ['message' => 'Отправка успешно обновлена.', 'error_flag' => 0,];
    }


    private static function prepareData(array $rawData, array $parts = [])
    {
        $data = [];
        if (!empty($rawData['send_date'])) {
            $data['send_date'] = date('Y-m-d H:i:01', strtotime(trim($rawData['send_date'])));
        }
        $data['depot_id'] = $rawData['depot_id'];
        $data['recip'] = trim($rawData['recip']);
        $data['model_id'] = $rawData['model_id'];
        $data['serial'] = trim($rawData['serial']);
        if ($parts) {
            $data['parts'] = json_encode($parts);
        }
        return $data;
    }


    public static function check(array $rawData)
    {
        if (empty($rawData['depot_id'])) {
            return 'Пожалуйста, выберите склад-отправитель.';
        }
        if (empty($rawData['send_date'])) {
            return 'Пожалуйста, введите дату отправки.';
        }
        if (empty($rawData['recip'])) {
            return 'Пожалуйста, введите данные получателя.';
        }
        if (empty($rawData['model_id'])) {
            return 'Пожалуйста, выберите модель.';
        }
        if (empty($rawData['part_id']) || empty(array_filter($rawData['part_num']))) {
            return 'Пожалуйста, выберите запчасти и введите количество.';
        }
        for ($i = 0, $cnt = count($rawData['part_id']); $i < $cnt; $i++) {
            if (empty($rawData['part_num'][$i]) || empty($rawData['part_id'][$i])) {
                return 'Пожалуйста, выберите запчасти и введите количество.';
            }
        }
        return '';
    }
}


Ship::init();
