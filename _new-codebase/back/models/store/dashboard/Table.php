<?php

namespace models\store\dashboard;

use program\core\App;

/** 
 * Таблица дашборда
 */

class Table extends \models\_Model
{


    public static function getPagination()
    {
        $totalCnt = DataExtractor::getRowsCount();
        $len = App::$URLParams['len'] ?? 100;
        return ['pagesCnt' => ceil($totalCnt / $len), 'totalCnt' => $totalCnt];
    }


    public static function getState()
    {
        return ['sort' => 'order_id', 'dir' => '', 'len' => 10];
    }


    public static function getCols()
    {
        $cols = ColsBuilder::getCols(App::$URL[1]);
        if($cols){
           // return $cols; 
        }
        return [
            ['name' => 'Статус заказа', 'uri' => 'order_status', 'is_sortable' => true, 'width' => 100],
            ['name' => 'Операции', 'uri' => 'operations', 'is_sortable' => false, 'width' => 100],
            ['name' => 'Дата заказа', 'uri' => 'order_date', 'is_sortable' => true, 'width' => 100],
            ['name' => '№ ремонта', 'uri' => 'repair_id', 'is_sortable' => true, 'width' => 100],
            ['name' => 'Наименование СЦ', 'uri' => 'service', 'is_sortable' => true, 'width' => 100],
            ['name' => 'Наименование запчасти', 'uri' => 'part', 'is_sortable' => false, 'width' => 100],
            ['name' => 'Код запчасти', 'uri' => 'part_code', 'is_sortable' => false, 'width' => 100],
            ['name' => 'Кол-во заказа', 'uri' => 'part_order_qty', 'is_sortable' => true, 'width' => 100],
            ['name' => 'Модель', 'uri' => 'model', 'is_sortable' => true, 'width' => 100],
            ['name' => 'Завод, заказ', 'uri' => 'provider_order', 'is_sortable' => false, 'width' => 100],
            ['name' => 'Остаток на складе', 'uri' => 'part_balance_qty', 'is_sortable' => false, 'width' => 100],
            ['name' => 'Склад', 'uri' => 'depot', 'is_sortable' => false, 'width' => 100],
            ['name' => 'Место', 'uri' => 'place', 'is_sortable' => false, 'width' => 100]
        ];
    }


    public static function getRows()
    {
        $rows = DataExtractor::getRows();
        $rows = DataHandler::handleRows($rows, self::getCols());
        return $rows;
    }
}

Table::init();
