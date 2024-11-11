<?php

namespace models\dicts;

/** 
 * Страница справочников /dicts/
 */

class Dicts extends \models\_Model
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
            ['name' => 'Название', 'uri' => 'name', 'orderable_flag' => 1],
            ['name' => 'Операции', 'uri' => 'operations', 'orderable_flag' => 0]
        ];
    }


    /**
     * Возвращает список справочников
     * 
     * @param array $filter Фильтр
     * 
     * @return array Справочники
     */
    public static function getDicts(array $filter = [])
    {
        $rows = self::$db->exec('SELECT *     
        FROM `' . Dict::TABLE . '`  
        ' . self::getWhere($filter) . ' 
        ORDER BY `id` DESC ' . self::getLimit($filter));
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
        FROM `' . Dict::TABLE . '`    
        ' . self::getWhere($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    public static function getTotalCnt()
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . Dict::TABLE . '`');
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    private static function getWhere(array $filter)
    {
        return '';
    }
}


Dicts::init();
