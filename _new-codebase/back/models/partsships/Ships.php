<?php

namespace models\partsships;

use models\Models;
use models\Parts;

/** 
 * Страница запчастей /parts-ships/
 */

class Ships extends \models\_Model
{

    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Возвращает заголовки столбцов таблицы 
     * 
     * @return array Колонки
     */
    public static function getCols()
    {
        return [
            ['name' => '#', 'uri' => 'id', 'orderable_flag' => 1],
            ['name' => 'Дата', 'uri' => 'send_date', 'orderable_flag' => 1],
            ['name' => 'Получатель', 'uri' => 'recip', 'orderable_flag' => 1],
            ['name' => 'Код запчасти', 'uri' => 'part_codes', 'orderable_flag' => 0],
            ['name' => 'Наименование', 'uri' => 'part_names', 'orderable_flag' => 0],
            ['name' => 'Количество', 'uri' => 'part_nums', 'orderable_flag' => 0],
            ['name' => 'Модель', 'uri' => 'model', 'orderable_flag' => 1],
            ['name' => 'Серийный номер', 'uri' => 'serial', 'orderable_flag' => 1],
            ['name' => '', 'uri' => 'operations', 'orderable_flag' => 0],
        ];
    }


    /**
     * Возвращает список отправок
     * 
     * @param array $filter Фильтр
     * 
     * @return array Отправки
     */
    public static function getShips(array $filter = [])
    {
        $rows = self::$db->exec('SELECT *     
        FROM `' . Ship::TABLE . '`  
        ' . self::getWhere($filter) . ' 
        ORDER BY `id` DESC ' . self::getLimit($filter));
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $m = Models::getModelByID($rows[$i]['model_id']);
            $rows[$i]['model'] = $m['name'];
            $rows[$i]['part_codes'] = [];
            $rows[$i]['part_names'] = [];
            $rows[$i]['part_nums'] = [];
            $parts = json_decode($rows[$i]['parts'], true);
            foreach($parts as $partID => $num){
                $part = Parts::getPartByID2($partID);
                $rows[$i]['part_codes'][] = $part['part_code'];
                $rows[$i]['part_names'][] = $part['name'];
                $rows[$i]['part_nums'][] = $num;
            }
            $rows[$i]['send_date'] = date('d.m.Y', strtotime($rows[$i]['send_date']));
        }
        return $rows;
    }


    private static function getLimit(array $filter)
    {
        if (empty($filter['limit'])) {
            return '';
        }
        if (empty($filter['offset'])) {
            return 'LIMIT 0, ' . $filter['limit'];
        }
        return 'LIMIT ' . $filter['offset'] . ', ' . $filter['limit'];
    }


    public static function getFilterCnt(array $filter)
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . Ship::TABLE . '`    
        ' . self::getWhere($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    public static function getTotalCnt()
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . Ship::TABLE . '`');
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    private static function getWhere(array $filter)
    {
        return '';
    }
}


Ships::init();
