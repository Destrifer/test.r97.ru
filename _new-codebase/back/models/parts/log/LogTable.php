<?php

namespace models\parts\log;

use models\parts\Depots;
use models\dicts\Dict;
use models\Models;
use models\Parts;
use models\parts\Log;
use models\Serials;
use models\Services;
use models\User;
use models\Users;
use program\core\RowSet;
use program\core\Time;


class LogTable extends \models\_Model
{

    private static $db = null;
    private static $cache = ['reasons' => []];


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    /**
     * Возвращает массив операций для фильтра
     * 
     * @return array Операции
     */
    public static function getOperations()
    {
        return [
            'in' => 'Приход',
            'out' => 'Расход',
            Log::RETURN_EVENT => 'Возвращена',
            Log::DELETE_REQUEST_EVENT => 'Запрос на утилизацию',
            Log::USE_EVENT => 'Использована в ремонте',
            Log::COLLECT_EVENT => 'Оставлена в СЦ',
            Log::ADD_EVENT => 'Принята',
            Log::DELETE_EVENT => 'Списана/утилизирована',
            Log::DELETE_REQUEST_EVENT => 'Утилизация отменена',
            Log::MOVE_EVENT => 'Перемещена'
        ];
    }


    /**
     * Возвращает заголовки столбцов таблицы 
     * 
     * @return array Колонки
     */
    public static function getCols()
    {
        return [
            ['name' => 'Дата', 'uri' => 'date', 'orderable_flag' => 1],
            ['name' => 'Код запчасти', 'uri' => 'part_id', 'orderable_flag' => 1],
            ['name' => 'Наименование запчасти', 'uri' => 'part_id', 'orderable_flag' => 1],
            ['name' => 'Модель', 'uri' => 'object2_id', 'orderable_flag' => 1],
            ['name' => 'Серийный номер', 'uri' => 'serial', 'orderable_flag' => 1],
            ['name' => 'Действие', 'uri' => 'operation', 'orderable_flag' => 0],
            ['name' => 'Количество', 'uri' => 'num', 'orderable_flag' => 1],
            ['name' => 'Склад', 'uri' => 'depot_id', 'orderable_flag' => 1],
            ['name' => 'Текущие остатки', 'uri' => 'balance', 'orderable_flag' => 1],
            ['name' => 'Наименование СЦ', 'uri' => 'user_id', 'orderable_flag' => 1]
        ];
    }


    public static function getFilterCnt(array $filter)
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . Log::TABLE . '` log  
        ' . self::where($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    public static function getTotalCnt()
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . Log::TABLE . '` log');
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    /**
     * Возвращает список записей лога
     * 
     * @param array $filter Фильтр
     * 
     * @return array Записи лога
     */
    public static function getLog(array $filter = [])
    {
        self::$cache['reasons'] = Dict::getValues(1);
        $rows = self::$db->exec('SELECT log.*, parts.`name` AS part, groups.`code` AS code, users.`email` AS user, users.`id` AS user_id, users.`role_id` AS user_role_id    
        FROM `' . Log::TABLE . '` log 
        LEFT JOIN `' . Parts::TABLE . '` parts ON parts.`id` = log.`part_id` 
        LEFT JOIN `' . Parts::TABLE_GROUP . '` groups ON groups.`id` = parts.`group_id` 
        LEFT JOIN `' . Users::TABLE . '` users ON users.`id` = log.`user_id` 
        ' . self::where($filter) . ' 
        ORDER BY ' . self::order($filter) . ' ' . self::limit($filter));
        $depots = RowSet::orderBy('id', Depots::getDepots());
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['depot'] = $depots[$rows[$i]['depot_id']]['name'];
            if ($rows[$i]['user_role_id'] == 3) {
                $s = Services::getServiceByID($rows[$i]['user_id']);
                $rows[$i]['user'] = ($s) ? $s['name'] : '(СЦ # ' . $rows[$i]['id'] . ' не найден)';
            } else if ($rows[$i]['user_role_id'] == 1) {
                $rows[$i]['user'] = 'Администратор';
            }
            $rows[$i]['event_type'] = ($rows[$i]['event_id'] < 20) ? 'in' : 'out';
            $rows[$i]['part'] = $rows[$i]['part'];
            $rows[$i]['operation'] = self::getOperation($rows[$i]['event_id'], $rows[$i]['object_id'], $rows[$i]['object2_id'], $rows[$i]['id']);
            $rows[$i]['date'] = Time::format($rows[$i]['date'], 'd.m.Y H:i');
            $rows[$i]['object2'] = self::getObject2($rows[$i]);
            $rows[$i]['part_code'] = $rows[$i]['code'] . $rows[$i]['part_id'];
            $rows[$i]['serial'] = $rows[$i]['serial'];
            $rows[$i]['balance'] = ($rows[$i]['event_type'] == 'in') ? $rows[$i]['balance_before'] + $rows[$i]['num'] : $rows[$i]['balance_before'] - $rows[$i]['num'];
        }
        return $rows;
    }


    private static function order(array $filter)
    {
        $sort = 'log.`date`';
        if (!empty($filter['sort'])) {
            $sort = 'log.`' . $filter['sort'] . '`';
        }
        if (empty($filter['dir']) || $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    /**
     * Возвращает название модели
     * 
     * @param array $row Строка лога
     * 
     * @return string Название модели
     */
    private static function getObject2(array $row)
    {
        if (empty($row['object2_id'])) {
            return '---';
        }
        $model = Models::getModelByID($row['object2_id']);
        if (!empty($row['serial'])) {
            $serialData = Serials::getSerial($row['serial'], $row['object2_id']);
            if ($serialData['provider']) {
                $model['name'] .= ', ' . $serialData['provider'];
            }
            if ($serialData['order']) {
                $model['name'] .= ', ' . $serialData['order'];
            }
        }
        return ($model) ? $model['name'] : '';
    }


    private static function limit(array $filter)
    {
        if (empty($filter['limit'])) {
            return '';
        }
        if (empty($filter['offset'])) {
            return 'LIMIT 0, ' . $filter['limit'];
        }
        return 'LIMIT ' . $filter['offset'] . ', ' . $filter['limit'];
    }


    public static function where(array $filter, $externalFlag = false)
    {
        $where = [];
        if (User::hasRole('service')) {
            $userID = User::getData('id');
            $depot = Depots::getDepot(['user_id' => $userID]);
            if (!$depot) {
                $depot = Depots::addDepot(['user_id' => $userID, 'name' => 'Разбор']);
            }
            $filter['depot'] = $depot['id'];
        }
        if (!empty($filter['date'])) {
            $p = explode('-', $filter['date']);
            $filter['date_from'] = date('Y-m-d 00:00:00', strtotime(trim($p[0])));
            $filter['date_to'] = (isset($p[1])) ? date('Y-m-d 23:59:59', strtotime(trim($p[1]))) : date('Y-m-d 23:59:59');
        }
        if (!empty($filter['date_from']) && !empty($filter['date_to'])) {
            $where[] = 'log.`date` BETWEEN "' . date('Y-m-d', strtotime($filter['date_from'])) . ' 00:00:00" AND "' . date('Y-m-d', strtotime($filter['date_to'])) . ' 23:59:59"';
        }
        if (!empty($filter['search'])) {
            $idSQL = '';
            $filter['search'] = trim($filter['search']);
            if (is_numeric($filter['search'])) {
                $idSQL = ' OR log.`object_id` = ' . $filter['search'];
            }
            $where[] = '(
                        log.`part_id` IN (SELECT `id` FROM `' . Parts::TABLE . '` WHERE `name` LIKE "%' . $filter['search'] . '%")
                        OR log.`object2_id` IN (SELECT `id` FROM `' . Models::TABLE . '` WHERE `name` LIKE "%' . $filter['search'] . '%")
                        ' . $idSQL . ' 
                        OR log.`serial` LIKE "%' . $filter['search'] . '%"
                        )';
        }
        if (!empty($filter['model'])) {
            $where[] = 'log.`object2_id` = ' . $filter['model'];
        }
        if (!empty($filter['part'])) {
            $where[] = 'log.`part_id` = ' . $filter['part'];
        }
        if (!empty($filter['event_id'])) {
            $where[] = 'log.`event_id` = ' . $filter['event_id'];
        }
        if (!empty($filter['object_id'])) {
            $where[] = 'log.`object_id` = ' . $filter['object_id'];
        }
        if (!empty($filter['depot'])) {
            $where[] = 'log.`depot_id` = ' . $filter['depot'];
        }
        if (!empty($filter['user'])) {
            $where[] = 'log.`user_id` = ' . $filter['user'];
        }
        if (!empty($filter['operation'])) {
            if ($filter['operation'] == 'in') {
                $where[] = 'log.`event_id` < 20';
            } else if ($filter['operation'] == 'out') {
                $where[] = 'log.`event_id` >= 20';
            } else {
                $where[] = 'log.`event_id` = ' . $filter['operation'];
            }
        }
        if (!$where) {
            return '';
        }
        if ($externalFlag) {
            return 'SELECT `part_id` FROM `' . Log::TABLE . '` log WHERE ' . implode(' AND ', $where);
        }
        return 'WHERE ' . implode(' AND ', $where);
    }


    /**
     * Возвращает строку описания операции
     * 
     * @param int $eventID Операция
     * @param int $objectID Объект
     * @param int $object2ID Объект 2
     * 
     * @return string Описание операции
     */
    private static function getOperation($eventID, $objectID, $object2ID, $rowID)
    {
        switch ($eventID) {
            case Log::ADD_EVENT:
                return 'Принята.';

            case Log::USE_EVENT:
                return 'Использована: ремонт <a href="/edit-repair/' . $objectID . '/step/3/">#' . $objectID . '</a>';

            case Log::DELETE_EVENT:
                $repair = ($object2ID) ? ' № <a href="/edit-repair/' . $object2ID . '/step/3/" target="_blank">' . $object2ID . '</a>' : '';
                return self::$cache['reasons'][$objectID] . $repair . ' ' . self::getRevertLink($rowID);

            case Log::SHIP_EVENT:
                $ship = ' № <a href="/parts-ship/?id=' . $objectID . '" target="_blank">' . $objectID . '</a>';
                return 'Отправка магазину/потребителю: ' . $ship . ' ' . self::getRevertLink($rowID);

            case Log::RETURN_EVENT:
                return 'Возвращена: ремонт <a href="/edit-repair/' . $objectID . '/step/3/">#' . $objectID . '</a>';

            case Log::DELETE_REQUEST_EVENT:
                return 'Запрошена утилизация: запрос <a href="/disposal-request/?id=' . $objectID . '">#' . $objectID . '</a>';

            case Log::MOVE_EVENT:
                return 'Перемещена: новый склад <a href="/depots/?action=edit&depot-id=' . $objectID . '">#' . $objectID . '</a>';

            case Log::REJECT_DELETE_REQUEST_EVENT:
                return 'Отклонена утилизация: запрос <a href="/disposal-request/?id=' . $objectID . '">#' . $objectID . '</a>';

            case Log::COLLECT_EVENT:
                return 'Оставлена в СЦ: ремонт <a href="/edit-repair/' . $objectID . '/step/3/">#' . $objectID . '</a> ' . self::getRevertLink($rowID);
            default:
                return '';
        }
    }


    private static function getRevertLink($rowID)
    {
		if (User::hasRole('admin')) {
			return ' <br><span data-action="revert" data-id="' . $rowID . '" class="link link_dotted">отменить</span>';
		} else {
			return '';
		}
        
    }
}

LogTable::init();
