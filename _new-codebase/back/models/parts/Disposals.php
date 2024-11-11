<?php

namespace models\parts;

use models\Parts;
use models\User;

class Disposals extends \models\_Model
{


    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Отправляет запрос на утилизацию запчастей 
     * 
     * @param array $data Данные формы
     * 
     * @return array Флаг ошибки и сообщение
     */
    public static function sendDisposeRequest(array $data)
    {
        if (!User::hasRole('service')) {
            return ['message' => 'Разрешено только для СЦ.', 'error_flag' => 1];
        }
        if (empty($data['parts'])) {
            return ['message' => 'Список запчастей пуст.', 'error_flag' => 1];
        }
        $depot = Depots::getDepot(['user_id' => User::getData('id')]);
        self::$db->transact('begin');
        $requestID = \models\disposals\Requests::create(['depot_id' => $depot['id']]);
        if (!$requestID) {
            return ['message' => 'Ошибка при создании запроса: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        foreach ($data['parts'] as $partKey => $_) {
            list($partID, $depotID) = explode(':', $partKey);
            $num = $data['parts'][$partKey]['num'];
            if (!Balance::isEnough($depot['id'], $partID, $num)) {
                $part = Parts::getPartByID2($partID);
                return ['message' => 'Недостаточно запчасти "' . $part['name'] . '". Сейчас на складе ' . Balance::count($partID, $depot['id']) . ' шт.', 'error_flag' => 1];
            }
            $r = Log::deleteRequest($partID, $depot['id'], $num, Balance::count($partID, $depot['id']), $requestID);
            if (!$r) {
                return self::$db->getErrorInfo();
            }
            $r = Balance::take($partID, $num, $depot['id']);
            if (!$r) {
                return ['message' => 'Ошибка при списании запчастей: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            $r = \models\disposals\Parts::create(['part_id' => $partID, 'num' => $num, 'request_id' => $requestID]);
            if (!$r) {
                return ['message' => 'Ошибка при добавлении запчастей: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        self::$db->transact('commit');
        unset($_SESSION['cache_requests_cnt']);
        unset($_SESSION['cache_disp_parts_cnt']);
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Обновляет предрассчитанное количество запчастей на утилизацию
     * 
     * @return void
     */
    public static function updateDisposalNum()
    {
        $rows = self::$db->exec('SELECT `id`, `part_id`, `depot_id` FROM `' . Balance::TABLE . '` WHERE `qty` > 0');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            self::$db->exec('UPDATE `' . Balance::TABLE . '` SET `disposal_num` = ? WHERE `id` = ?', [Batches::getDisposalPartNum($rows[$i]['part_id'], $rows[$i]['depot_id']), $rows[$i]['id']]);
        }
    }


    /**
     * Утилизирует запчасти 
     * 
     * @param array $data Данные формы
     * 
     * @return array Флаг ошибки и сообщение
     */
    public static function disposeParts(array $data)
    {
        if (empty($data['parts'])) {
            return ['message' => 'Список запчастей пуст.', 'error_flag' => 1];
        }
        if (empty($data['date_time']) || empty($data['reason_id'])) {
            return ['message' => 'Не выбрана дата, либо причина списания.', 'error_flag' => 1];
        }
        self::$db->transact('begin');
        foreach ($data['parts'] as $part) {
            $num = (int)$part['num'];
            $depotID = $part['depot_id'];
            $partID = $part['id'];
            if (!Balance::isEnough($depotID, $partID, $num)) {
                self::$db->transact('rollback');
                $part = Parts::getPartByID2($partID);
                return ['message' => 'Недостаточно запчасти "' . $part['name'] . '". Сейчас на складе ' . Balance::count($partID, $depotID) . ' шт.', 'error_flag' => 1];
            }
            $date = date('Y-m-d H:i:01', strtotime($data['date_time']));
            $models = Parts::getModels($partID);
            $model = reset($models);
            $serial = (!empty($model['serials'])) ? reset($model['serials'])['model_serial'] : '';
            $r = Log::delete($partID, $depotID, $num, Balance::count($partID, $depotID), $data['reason_id'], 0, $date, $serial);
            if (!$r) {
                self::$db->transact('rollback');
                return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            $r = Balance::take($partID, $num, $depotID);
            if (!$r) {
                self::$db->transact('rollback');
                return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        self::$db->transact('commit');
        return ['message' => '', 'error_flag' => 0];
    }
}

Disposals::init();
