<?php

namespace models\disposals;

use models\parts\Balance;
use models\parts\Depots;
use models\parts\Log;
use models\repaircard\Support;
use models\User;
use program\core\Query;

/** 
 * Запросы на утилизацию запчастей
 */

class Requests extends \models\_Model
{

    private static $db = null;
    const TABLE = 'disposals_requests';
    const APPROVED_STATUS = 1;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function create(array $data)
    {
        $query = new Query(self::TABLE);
        unset($_SESSION['cache_requests_cnt']);
        return self::$db->exec($query->insert($data), $query->params);
    }


    public static function update(array $rawData)
    {
        if (!User::hasRole('admin')) {
            return ['message' => 'Операция запрещена.', 'error_flag' => 1];
        }
        $request = Requests::getRequest(['id' => $rawData['id']]);
        if ($request['status_id'] == self::APPROVED_STATUS) {
            return ['message' => 'Данный запрос уже обработан.', 'error_flag' => 1];
        }
        $depot = Depots::getDepot(['id' => $request['depot_id']]);
        self::$db->transact('begin');
        $nums = array_column(Parts::getParts(['request_id' => $request['id']]), 'num', 'part_id');
        $query = new Query(self::TABLE);
        foreach ($rawData['parts'] as $rowID => $partData) {
            if (!empty($partData['is_checked']) && empty($partData['disposed_num'])) {
                self::$db->transact('rollback');
                return ['message' => 'Пожалуйста, введите количество утилизируемых запчастей.', 'error_flag' => 1];
            }
            if (empty($partData['disposed_num']) && empty($partData['comment'])) {
                self::$db->transact('rollback');
                return ['message' => 'Пожалуйста, введите комментарии для СЦ.', 'error_flag' => 1];
            }
            $partID = $partData['part_id'];
            $r = Parts::update(['comment' => $partData['comment'], 'disposed_num' => $partData['disposed_num']], $rowID);
            if (!$r) {
                self::$db->transact('rollback');
                return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            if ($nums[$partID] > $partData['disposed_num']) {
                $returnNum = $nums[$partID] - $partData['disposed_num'];
                $r = Log::rejectDeleteRequest($partID, $depot['id'], $returnNum, Balance::count($partID, $request['depot_id']), $request['id']);
                if (!$r) {
                    self::$db->transact('rollback');
                    return ['message' => 'Ошибка логирования: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                }
                $r = Balance::add($partID, $returnNum, $depot['id'], true);
                if (!$r) {
                    self::$db->transact('rollback');
                    return ['message' => 'Ошибка при возврате запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                }
            }
            if ($partData['disposed_num']) {
                if (!self::logDelete($partID, $depot['id'], $partData['disposed_num'])) {
                    self::$db->transact('rollback');
                    return ['message' => 'Ошибка логирования: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                }
            }
        }
        $r = self::$db->exec($query->update(['status_id' => self::APPROVED_STATUS], $request['id']), $query->params);
        if (!$r) {
            self::$db->transact('rollback');
            return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        self::$db->transact('commit');
        unset($_SESSION['cache_requests_cnt']);
        unset($_SESSION['cache_disp_parts_cnt']);
        notice_add('Запрос на утилизацию обработан', 'Ознакомьтесь с результатом и сообщениями администратора.', $depot['user_id'], 'https://crm.r97.ru/disposal-request/?id=' . $request['id'], 'Запрос на утилизацию <a href="https://crm.r97.ru/disposal-request/?id=' . $request['id'] . '">#' . $request['id'] . '</a> обработан.');
        return ['message' => 'Запчасти утилизированы.', 'error_flag' => 0];
    }


    private static function logDelete($partID, $depotID, $num)
    {
        $models = \models\Parts::getModels($partID);
        $model = reset($models);
        $serial = (!empty($model['serials'])) ? reset($model['serials'])['model_serial'] : '';
        $reasonID = 6; // Исправные запчасти, срок хранения которых истек
        return Log::delete($partID, $depotID, $num, Balance::count($partID, $depotID), $reasonID, 0, '', $serial);
    }


    public static function getRequest(array $filter = [])
    {
        $filter['limit'] = 1;
        $rows = self::getRequests($filter);
        if (!$rows) {
            return [];
        }
        return $rows[0];
    }


    public static function getRequests(array $filter = [])
    {
        $rows = self::$db->exec('SELECT c.* FROM `' . self::TABLE . '` c  
        ' . self::where($filter) . self::order($filter) . self::limit($filter));
        $d = Depots::getDepots(['ids' => array_column($rows, 'depot_id')]);
        $depots = array_column($d, 'name', 'id');
        $userIDs = array_column($d, 'user_id', 'id');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['add_time'] = date('H:i', strtotime($rows[$i]['add_date']));
            $rows[$i]['add_date'] = date('d.m.Y', strtotime($rows[$i]['add_date']));
            $rows[$i]['parts_num'] = Parts::count(['request_id' => $rows[$i]['id']]);
            $rows[$i]['depot'] = $depots[$rows[$i]['depot_id']];
            $rows[$i]['user_id'] = $userIDs[$rows[$i]['depot_id']];
        }
        return $rows;
    }


    public static function count(array $filter = [])
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . self::TABLE . '` c     
        ' . self::where($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    private static function order(array $filter)
    {
        $sort = ' ORDER BY c.`add_date`';
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    private static function where(array $filter)
    {
        $where = [];
        if (!empty($filter['id'])) {
            $where[] = 'c.`id` = ' . $filter['id'];
        }
        if (isset($filter['status_id'])) {
            $where[] = 'c.`status_id` = ' . $filter['status_id'];
        }
        if (isset($filter['depot_id'])) {
            $where[] = 'c.`depot_id` = ' . $filter['depot_id'];
        }
        if ($where) {
            return ' WHERE ' . implode(' AND ', $where);
        }
        return '';
    }


    private static function limit(array $filter)
    {
        if (empty($filter['limit'])) {
            return '';
        }
        if (empty($filter['offset'])) {
            return ' LIMIT ' . $filter['limit'];
        }
        return ' LIMIT ' . $filter['offset'] . ', ' . $filter['limit'];
    }
}


Requests::init();
