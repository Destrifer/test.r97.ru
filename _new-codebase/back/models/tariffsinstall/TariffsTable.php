<?php

namespace models\tariffsinstall;

use models\cats\Cats;
use program\core\RowSet;

/** 
 * Страница тарифов монтажа /tariffs-install/
 */

class TariffsTable extends \models\_Model
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
            ['name' => 'Категория', 'uri' => 'name', 'orderable_flag' => 1],
            ['name' => 'Демонтаж (руб.)', 'uri' => 'dismant_cost', 'orderable_flag' => 0],
            ['name' => 'Монтаж (руб.)', 'uri' => 'install_cost', 'orderable_flag' => 0]
        ];
    }


    /**
     * Обновляет стоимость
     * 
     * @param array $rawData Данные из таблицы
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function updateCost(array $rawData)
    {
        foreach ($rawData as $data) {
            if ($data['field'] == 'install-cost') {
                $field = 'install_cost';
            } else {
                $field = 'dismant_cost';
            }
            $r = Tariffs::update([$field => $data['value']], $data['cat_id']);
        }
        if (!$r) {
            return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Возвращает список категорий с возможностью монтажа
     * 
     * @param array $filter Фильтр
     * 
     * @return array Категории
     */
    public static function getCats(array $filter = [])
    {
        $filter['install_flag'] = 1;
        $cats = Cats::getCats($filter);
        $costs = RowSet::orderBy('cat_id', Tariffs::getCosts(['cat_ids' => array_column($cats, 'id')]));
        for ($i = 0, $cnt = count($cats); $i < $cnt; $i++) {
            $catID = $cats[$i]['id'];
            if (!isset($costs[$catID])) {
                Tariffs::create(['cat_id' => $catID]);
                $cats[$i]['install_cost'] = 0;
                $cats[$i]['dismant_cost'] = 0;
            } else {
                $cats[$i]['install_cost'] = $costs[$catID]['install_cost'];
                $cats[$i]['dismant_cost'] = $costs[$catID]['dismant_cost'];
            }
        }
        return $cats;
    }


    public static function getFilterCnt(array $filter = [])
    {
        $filter['install_flag'] = 1;
        return Cats::count($filter);
    }


    public static function getTotalCnt()
    {
        return Cats::count(['install_flag' => 1]);
    }
}


TariffsTable::init();
