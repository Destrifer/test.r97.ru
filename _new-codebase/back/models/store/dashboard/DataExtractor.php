<?php

namespace models\store\dashboard;

use program\core\App;

/** 
 * Конструирует запрос и извлекает данные из БД
 */

class DataExtractor extends \models\_Model
{

    private static $db = null;
    private static $rowsQuery = '';
    private static $countQuery = '';



    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getRows()
    {
        self::createQuery();
        return self::$db->exec(self::$rowsQuery);
    }


    public static function getRowsCount()
    {
        self::createQuery();
        return self::$db->exec(self::$countQuery)[0]['cnt'];
    }


    private static function createQuery()
    {
        if (!empty(self::$rowsQuery)) {
            return;
        }
        $from  = 'FROM `orders_parts` op 
        LEFT JOIN `orders` o ON op.`order_id` = o.`id` 
        LEFT JOIN `repairs` r ON o.`repair_id` = r.`id`';
        $where = self::getWhere();
        $limit = self::getLimit();
        $order = self::getOrder();
        self::$rowsQuery = "SELECT " . self::getFields() . " $from $where $order $limit";
        self::$countQuery = "SELECT " . self::getFields('count') . " $from $where";
    }


    private static function getFields($set = 'all')
    {
        if ($set == 'all') {
            return 'op.`order_id`, o.`create_date`, o.`status_id`, op.`part_id`, op.`qty` AS part_order_qty, op.`depot_id`, op.`origin_id`, r.`service_id`, r.`id` AS repair_id';
        }
        return 'COUNT(*) AS cnt';
    }


    private static function getOrder()
    {
        $result = '';
        if (!empty(App::$URLParams['sort'])) {
            switch (App::$URLParams['sort']) {
                case 'order_id':
                    $result = 'op.`order_id`';
                    break;
                case 'order_date':
                    $result = 'o.`create_date`';
                    break;
                case 'order_status':
                    $result = 'o.`status_id`';
                    break;
                case 'part_order_qty':
                    $result = 'op.`qty`';
                    break;
            }
            if (!empty(App::$URLParams['dir'])) {
                $result .= ' DESC';
            }
        }
        return $result ?  'ORDER BY ' . $result : '';
    }


    private static function getWhere()
    {
        $where = [self::getWhereTab()];
        return 'WHERE ' . implode(' AND ', $where);
    }


    private static function getWhereTab()
    {
        switch (App::$URL[1]) {
            case 'partsintransit':
                return 'r.`status_admin` = "Запчасти в пути"';
            case 'factory':
                return 'r.`status_admin` = "Заказ на заводе"';
            case 'waittesler':
                return 'r.`status_admin` = "Ждем з/ч Tesler"';
            case 'inprocess':
                return 'r.`status_admin` IN ("В обработке", "Ждем з/ч Tesler", "Запрос у Tesler", "Заказ на заводе")';
            case 'requesttesler':
                return 'r.`status_admin` = "Запрос у Tesler"';
            case 'needparts':
                return 'r.`status_admin` = "Нужны запчасти"';
            case 'questions':
                return 'r.`has_questions` = 1 AND r.`status_admin` IN ("Нужны запчасти", "Запрос у Tesler", "В обработке", "Ждём з/ч Tesler", "Заказ на заводе", "Запчасти в пути")';
            default:
                throw new \Exception('Tab not found.');
        }
    }


    private static function getLimit()
    {
        $pageLen = App::$URLParams['len'] ?? 100;
        $result = 'LIMIT ' . $pageLen;
        if (!empty(App::$URLParams['page'])) {
            $p = App::$URLParams['page'] - 1;
            $result = 'LIMIT ' . $p * $pageLen . ', ' . $pageLen;
        }
        return $result;
    }
}

DataExtractor::init();
