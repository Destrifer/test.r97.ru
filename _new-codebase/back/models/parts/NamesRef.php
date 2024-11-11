<?php

namespace models\parts;

/** 
 * Шаблоны имен запчастей
 */

class NamesRef extends \models\_Model
{

    const TABLE = 'parts2_names';
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getNames(array $filter = [])
    {
        return self::$db->exec('SELECT * FROM `' . self::TABLE . '`   
        ' . self::where($filter) . self::order($filter) . self::limit($filter));
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


    private static function order(array $filter)
    {
        $sort = '';
        if (isset($filter['order'])) {
            switch ($filter['order']) {
                case 0:
                    $sort = '`id`';
                    break;
                case 2:
                    $sort = '`en`';
                    break;
                default:
                    $sort = '`ru`';
            }
        } else {
            return '';
        }
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return ' ORDER BY ' . $sort;
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
        if (isset($request['order[0][column]'])) {
            $res['order'] = $request['order[0][column]'];
        }
        if (isset($request['order[0][dir]'])) {
            $res['dir'] = $request['order[0][dir]'];
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
            ['name' => '#', 'uri' => 'id', 'orderable_flag' => 0],
            ['name' => 'Русский', 'uri' => 'ru', 'orderable_flag' => 1],
            ['name' => 'Английский', 'uri' => 'en', 'orderable_flag' => 1]
        ];
    }


    public static function addName($ru, $en)
    {
        return self::$db->exec('INSERT INTO `' . self::TABLE . '` (`ru`, `en`) VALUES (?, ?)', [$ru, $en]);
    }


    /**
     * Обновляет шаблоны
     * 
     * @param array $rawData Данные из таблицы
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function save(array $rawData)
    {
        foreach ($rawData as $data) {
            $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `' . $data['field'] . '` = ? WHERE `id` = ?', [trim($data['value']), $data['id']]);
        }
        if (!$r) {
            return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => '', 'error_flag' => 0];
    }


    public static function getFilterCnt(array $filter = [])
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . self::TABLE . '`      
        ' . self::where($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    private static function where(array $filter)
    {
        $where = [];
        if (!empty($filter['id'])) {
            $where[] = '`id` = ' . $filter['id'];
        }
        if (!empty($filter['search'])) {
            $req = trim($filter['search']);
            $where[] = '`ru` LIKE "%' . $req . '%" OR `en` LIKE "%' . $req . '%"';
        }
        if ($where) {
            return ' WHERE ' . implode(' AND ', $where);
        }
        return '';
    }


    public static function getTotalCnt()
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . self::TABLE . '`');
        return ($rows) ? $rows[0]['cnt'] : 0;
    }
}

NamesRef::init();
