<?php

namespace models\disposals;

use models\parts\Depots;
use models\User;

/** 
 * Таблица запросов на утилизацию запчастей
 */

class RequestsTable extends \models\_Model
{

    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Обрабатывает ввод (DataTables.js и др.) и возвращает фильтр для запроса к БД
     * 
     * @param array $request Данные запроса
     * 
     * @return array Данные фильтра
     */
    public static function prepareFilter(array $request)
    {
        $res = [];
        if (isset($request['status_id'])) {
            $res['status_id'] = $request['status_id'];
        } else {
            $res['status_id'] = 0;
        }
        if (isset($request['draw'])) {
            $res['draw'] = $request['draw']; // токен для datatables
        }
        if (!empty($request['search[value]'])) {
            $res['search'] = $request['search[value]'];
        }
        if (isset($request['start'])) {
            $res['offset'] = $request['start'];
        }
        if (isset($request['length'])) {
            $res['limit'] = $request['length'];
        }
        $cols = self::getCols();
        if (isset($request['order[0][column]'])) {
            $res['sort'] = $cols[$request['order[0][column]']]['uri'];
        }
        if (isset($request['order[0][dir]'])) {
            $res['dir'] = $request['order[0][dir]'];
        }
        if (User::hasRole('service')) {
            $depot = Depots::getDepot(['user_id' => User::getData('id')]);
            $res['depot_id'] = $depot['id'];
        }
        return $res;
    }


    /**
     * Возвращает заголовки столбцов таблицы 
     * 
     * @return array Колонки
     */
    public static function getCols()
    {
        return [
            ['name' => '', 'uri' => 'operations', 'orderable_flag' => 0],
            ['name' => 'Дата запроса', 'uri' => 'add_date', 'orderable_flag' => 1],
            ['name' => 'Склад', 'uri' => 'depot', 'orderable_flag' => 1],
            ['name' => 'Количество', 'uri' => 'parts_num', 'orderable_flag' => 0]
        ];
    }


    /**
     * Возвращает список запросов
     * 
     * @param array $filter Фильтр
     * 
     * @return array Запросы
     */
    public static function getRequests(array $filter = [])
    {
        return Requests::getRequests($filter);
    }


    public static function getFilterCnt(array $filter = [])
    {
        return Requests::count($filter);
    }


    public static function getTotalCnt()
    {
        return Requests::count();
    }
}


RequestsTable::init();
