<?php

namespace models\store\dashboard;

use models\Parts;
use models\parts\Balance;
use models\parts\Depots;
use models\parts\Order;
use models\Services;
use program\core\RowSet;

/** 
 * Дополнительная обработка строк БД для помещения в таблицу
 */

class DataHandler extends \models\_Model
{

    private static $db = null;



    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function handleRows(array $rows, array $cols)
    {
        $rows = self::appendPartsInfo($rows);
        $rows = self::appendPartsBalance($rows);
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['service'] = self::getService($rows[$i]);
            $rows[$i]['operations'] = self::getOperations($rows[$i]);
            $rows[$i]['order_date'] = self::getOrderDate($rows[$i]);
            $rows[$i]['order_status'] = self::getStatus($rows[$i]);
            $rows[$i]['part_code'] = self::getPartCode($rows[$i]);
            $rows[$i]['part'] = self::getPart($rows[$i]);
            $rows[$i]['provider_order'] = self::getProviderOrder($rows[$i]);
            $rows[$i]['model'] = self::getModel($rows[$i]);
        }
        return $rows;
    }


    private static function appendPartsBalance(array $rows)
    {
        $depots = RowSet::orderBy('id', Depots::getDepots(['ids' => array_column($rows, 'depot_id')]));
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $b = Balance::get($rows[$i]['part_id'], $rows[$i]['depot_id']);
            $rows[$i]['part_balance_qty'] = $b['qty'] ?? 0;
            $rows[$i]['place'] = $b['place'] ?? '';
            $rows[$i]['depot'] = $depots[$rows[$i]['depot_id']]['name'] ?? '';
        }
        return $rows;
    }


    private static function appendPartsInfo(array $rows)
    {
        $manualIDs = [];
        $storeIDs = [];
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if ($rows[$i]['origin_id'] == Order::MANUAL_PART) {
                $manualIDs[] = $rows[$i]['part_id'];
                continue;
            }
            $storeIDs[] = $rows[$i]['part_id'];
        }
        $partsManual = [];
        if ($manualIDs) {
            $partsManual = self::$db->exec('SELECT `comment` AS name, `id`, `photo_path` FROM `' . Order::TABLE_MANUAL . '` WHERE `id` IN (' . implode(',', $manualIDs) . ')');
            $partsManual = RowSet::orderBy('id', $partsManual);
        }
        $partsStore = [];
        if ($storeIDs) {
            $partsStore = self::$db->exec('SELECT `id`, `group_id`, `name` FROM `' . Parts::TABLE . '` WHERE `id` IN (' . implode(',', $storeIDs) . ')');
            for ($i = 0, $cnt = count($partsStore); $i < $cnt; $i++) {
                $partsStore[$i]['part_code'] = Parts::getPartCode($partsStore[$i]['id'], $partsStore[$i]['group_id']);
                $partsStore[$i]['default_model'] = Parts::getDefaultModel($partsStore[$i]['id']);
            }
            $partsStore = RowSet::orderBy('id', $partsStore);
        }
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if ($rows[$i]['origin_id'] == Order::MANUAL_PART) {
                $rows[$i]['part_info'] = $partsManual[$rows[$i]['part_id']] ?? [];
                if (empty($rows[$i]['part_info']['name'])) {
                    $rows[$i]['part_info']['name'] = '(заказана вручную)';
                }
            } else {
                $rows[$i]['part_info'] = $partsStore[$rows[$i]['part_id']] ?? [];
            }
        }
        return $rows;
    }


    private static function getService(array $row)
    {
        $service = Services::getServiceByID($row['service_id']);
        return $service['name'] ?? '';
    }


    private static function getPartCode(array $row)
    {
        return $row['part_info']['part_code'] ?? '';
    }


    private static function getModel(array $row)
    {
        return $row['part_info']['default_model']['model'] ?? '';
    }


    private static function getProviderOrder(array $row)
    {
        if (empty($row['part_info']['default_model'])) {
            return '';
        }
        return trim($row['part_info']['default_model']['provider'] . ', ' . $row['part_info']['default_model']['order'], ', ');
    }


    private static function getPart(array $row)
    {
        return $row['part_info']['name'] ?? '';
    }


    private static function getStatus(array $row)
    {
        return Order::getOrderStatusName($row['status_id']);
    }


    private static function getOrderDate(array $row)
    {
        return date('d.m.Y', strtotime($row['create_date']));
    }


    private static function getOperations(array $row)
    {
        return '-';
    }
}

DataHandler::init();
