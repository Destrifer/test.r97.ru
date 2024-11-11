<?php

namespace models\users;

use models\User;
use models\Users;

/** 
 * Страница пользователей /users/
 */

class UsersTable extends \models\_Model
{


    /**
     * Обрабатывает ввод (DataTables.js и др.) и возвращает фильтр для запроса к БД
     * 
     * @param array $request Данные запроса
     * 
     * @return array Данные фильтра
     */
    public static function prepareFilter(array $request)
    {
        if (isset($request['draw'])) {
            $res['draw'] = $request['draw']; // токен для datatables
        }
        if (!empty($request['show-inactive'])) {
            $res['inactive'] = true;
        } else {
            $res['active'] = true;
        }
        if (User::hasRole('admin')) {
            if (!empty($request['service-id'])) {
                $res['service_id'] = $request['service-id'];
            }
        } else {
            $res['service_id'] = User::getData('service_id');
        }
        if (!empty($request['role-id'])) {
            $res['role_id'] = explode(',', $request['role-id']);
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
            ['name' => 'ID', 'uri' => 'id', 'orderable_flag' => 1],
            ['name' => 'Логин', 'uri' => 'login', 'orderable_flag' => 1],
            ['name' => 'Имя', 'uri' => 'nickname', 'orderable_flag' => 1],
            ['name' => 'СЦ', 'uri' => 'service', 'orderable_flag' => 1],
            ['name' => 'E-mail', 'uri' => 'email', 'orderable_flag' => 1],
            ['name' => 'Роль', 'uri' => 'role_id', 'orderable_flag' => 1],
            ['name' => 'Статус', 'uri' => 'status_id', 'orderable_flag' => 1],
            ['name' => 'Операции', 'uri' => 'operations', 'orderable_flag' => 0]
        ];
    }


    /**
     * Возвращает список пользователей
     * 
     * @param array $filter Фильтр
     * 
     * @return array Категории
     */
    public static function getUsers(array $filter = [])
    {
        $users = Users::getUsers($filter);
        return $users;
    }


    public static function changeStatus($newStatus, $userID)
    {
        $messages = [Users::IS_ACTIVE => 'Пользователь разблокирован.', Users::IS_BLOCKED => 'Пользователь заблокирован.'];
        $statusID = ($newStatus == 'active') ? Users::IS_ACTIVE : Users::IS_BLOCKED;
        $r = Users::setStatus($statusID, $userID);
        if ($r) {
            return ['message' => $messages[$statusID], 'error_flag' => 0];
        }
        return ['message' => 'Во время обновления произошла ошибка, пожалуйста, обратитесь к администратору.', 'error_flag' => 1];
    }


    public static function getFilterCnt(array $filter = [])
    {
        return Users::count($filter);
    }


    public static function getTotalCnt()
    {
        return Users::count();
    }
}


UsersTable::init();
