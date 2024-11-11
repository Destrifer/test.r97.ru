<?php

namespace models;

use models\parts\Arrivals;

/** 
 * Страница приходов запчастей /parts-arrivals/
 */

class PartsArrivalsTable extends PartsTable
{


    public static function updateArrivalName(array $request)
    {
        $r = Arrivals::updatePartArrivalID($request['arrival_part_id'], $request['new_arrival_name']);
        if ($r) {
            return ['message' => '', 'is_error' => 0];
        }
        return ['message' => self::$db->getErrorInfo(), 'is_error' => 1];
    }


    protected static function getDefaultFilter(array $filter = [])
    {
        $filter = parent::getDefaultFilter($filter);
        if (!empty($filter['arrival_dates'])) {
            $p = explode('-', $filter['arrival_dates']);
            $dateFrom = date('Y-m-d 00:00:00', strtotime(trim($p[0])));
            $dateTo = (isset($p[1])) ? date('Y-m-d 23:59:59', strtotime(trim($p[1]))) : date('Y-m-d 23:59:59');
            $filter['arrival_dates'] = ['date_from' => $dateFrom, 'date_to' => $dateTo];
        }
        if (empty($filter['sort'])) {
            $filter['sort'] = 'add_date';
            $filter['dir'] = 'desc';
        }
        return $filter;
    }


    public static function getFilterCnt(array $filter)
    {
        return Parts::count(self::getDefaultFilter($filter), 'arrivals');
    }


    public static function getTotalCnt()
    {
        return Parts::count(self::getDefaultFilter(), 'arrivals');
    }


    /**
     * Возвращает заголовки столбцов таблицы 
     * 
     * @return array Колонки
     */
    public static function getCols(array $filter)
    {
        $cols = [
            0 => ['name' => 'Операции', 'uri' => 'operations', 'orderable_flag' => 0],
            1 => ['name' => 'Код', 'uri' => 'part_code', 'orderable_flag' => 1],
            2 => ['name' => 'Наименование', 'uri' => 'name', 'orderable_flag' => 1],
            3 => ['name' => '№ Прихода', 'uri' => 'arrival_name', 'orderable_flag' => 1],
            4 => ['name' => 'Количество', 'uri' => 'arrival_part_num', 'orderable_flag' => 1],
            5 => ['name' => 'Дата прихода', 'uri' => 'add_date', 'orderable_flag' => 1],
            6 => ['name' => 'Склад', 'uri' => 'depot', 'orderable_flag' => 0],
            7 => ['name' => 'Принадлежность', 'uri' => 'type', 'orderable_flag' => 1],
            8 => ['name' => 'Признак', 'uri' => 'attr', 'orderable_flag' => 1]
        ];
        return $cols;
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
        $res = parent::prepareFilter($request);
        $keys = ['arrival_id', 'arrival_dates'];
        foreach ($keys as $k) {
            if (!empty($request[$k])) {
                $res[$k] = $request[$k];
            }
        }
        return $res;
    }



    public static function getParts(array $filter = [])
    {
        $res = [];
        if (!empty($filter['order_col'])) {
            $cols = self::getCols($filter);
            $filter['sort'] = $cols[$filter['order_col']]['uri'];
        }
        $filter = self::getDefaultFilter($filter);
        $rows = Parts::getParts($filter, 'arrivals');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $arrival = Arrivals::getArrivalByID($rows[$i]['arrival_id']);
            $rows[$i]['default_model'] = Parts::getDefaultModel($rows[$i]['id']);
            $rows[$i]['arrival_name'] = $arrival['name'];
            if ($rows[$i]['vendor']) {
                $rows[$i]['name'] .= ', ' . $rows[$i]['vendor']['name'];
            }
            $rows[$i]['add_date'] = date('d.m.Y H:i', strtotime($rows[$i]['add_date']));
            $res[] = $rows[$i];
        }
        return $res;
    }
}


PartsArrivalsTable::init();
