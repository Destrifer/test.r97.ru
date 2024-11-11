<?php

namespace models;

use program\core\Query;

/** 
 * Пользователи
 */

class Users extends _Model
{
    public static $message = '';
    private static $db = null;
    const ROLES = [
        1 => 'admin',           // администратор
        2 => 'acct',            // бухгалтер
        3 => 'service',         // сервис
        4 => 'master',          // мастер ИП Кулиджанов
        5 => 'taker',           // приемщик
        6 => 'store',           // кладовщик
        7 => 'slave-admin',     // администратор ИП Кулиджанов
        8 => 'service-manager', // сервис-менеджер по работе с СЦ
        9 => 'info',            // технический ресурс (инфобаза)
        10 => 'vendor'          // ограниченный доступ по выделенным брендам
    ];
    const TABLE = 'users';
    const IS_WAITING = 0;       // ожидает подтверждения
    const IS_ACTIVE = 1;        // активый пользователь
    const IS_BLOCKED = 2;       // заблокированный пользователь


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getStatuses()
    {
        return [
            self::IS_ACTIVE => 'Активен',
            self::IS_BLOCKED => 'Заблокирован',
            self::IS_WAITING => 'Неактивен'
        ];
    }


    public static function getRoles(array $include = [])
    {
        $result = [
            1 => 'Администратор',
            2 => 'Бухгалтер',
            3 => 'Сервис',
            4 => 'Мастер',
            5 => 'Приемщик',
            6 => 'Кладовщик',
            7 => 'Слейв-админ',
            8 => 'Сервис-менеджер',
            9 => 'Тех. ресурс',
            10 => 'Вендор'
        ];
        if ($include) {
            $res = [];
            foreach ($include as $role) {
                $roleID = array_search($role, self::ROLES);
                $res[$roleID] = $result[$roleID];
            }
            return $res;
        }
        return $result;
    }


    public static function register(array $rawData)
    {
        $error = self::checkRegistrationData($rawData);
        if ($error) {
            return ['message' => $error, 'is_error' => 1];
        }
        $data = [
            'login' => trim(mb_strtolower($rawData['login'])),
            'email' => trim(mb_strtolower($rawData['email'])),
            'nickname' => trim($rawData['nickname']),
            'password' => User::getPasswordHash($rawData['password']),
            'role_id' => 3,
            'phone' => trim(mb_strtolower($rawData['phone']))
        ];
        $query = new Query(self::TABLE);
        $userID = self::$db->exec($query->insert($data), $query->params);
        return ($userID) ? ['message' => 'Вы успешно зарегистрированы.', 'is_error' => 0] : ['message' => self::$db->getErrorInfo(), 'is_error' => 1];
    }


    private static function checkRegistrationData(array $data)
    {
        if (empty($data['login']) || empty($data['password']) || empty($data['password_repeat']) || empty($data['phone']) || empty($data['email'])) {
            return 'Пожалуйста, заполните все поля.';
        }
        if (!preg_match('/^[a-zA-Zа-яА-Я\d@.\-_]{2,24}$/', $data['login'])) {
            return 'Логин может содержать буквы a-z, цифры и дефис. От 2 до 24 символов.';
        }
        if (!preg_match('/^[a-zA-Zа-яА-Я\d!@#$%^&*]{6,24}$/', $data['password'])) {
            return 'Пароль может содержать буквы, цифры и знаки ! @ # $ % ^ & *. От 6 до 24 символов.';
        }
        if ($data['password'] != $data['password_repeat']) {
            return 'Пароль и подтверждение не совпадают.';
        }
        $user = self::getUser(['login' => $data['login']]);
        if ($user) {
            return 'Такой логин уже зарегистрирован в системе.';
        }
        return '';
    }


    public static function saveUser(array $rawData)
    {
        $data = self::prepareData($rawData);
        $query = new Query(self::TABLE);
        if (!empty($rawData['id'])) {
            $userID = $rawData['id'];
            self::$db->exec($query->update($data, $userID), $query->params);
            \models\Log::user(21, 'Через панель администратора.', $userID);
        } else {
            $userID = self::$db->exec($query->insert($data), $query->params);
            if ($userID) {
                \models\Log::user(20, 'Через панель администратора.', $userID);
            }
        }
        if (!$userID) {
            return ['message' => 'Не удалось сохранить: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if (!empty($rawData['new_password'])) {
            $r = self::setPassword($userID, $rawData['new_password']);
            if (!$r) {
                return ['message' => 'К сожалению, во время смены пароля произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            if (!empty($rawData['notify'])) {
                Sender::use('email')->from('robot@crm.r97.ru')->to([$data['email']])->send('Данные для входа', ['nickname' => $data['nickname'], 'login' => $data['login'], 'password' => $rawData['new_password']], 'account-data', true);
            }
            \models\Log::user(19, 'Через панель администратора.', $userID);
        }
        return ['message' => 'Пользователь успешно сохранен.', 'error_flag' => 0,];
    }


    private static function prepareData(array $rawData)
    {
        $data = [];
        $data['login'] = trim($rawData['login']);
        $data['nickname'] = trim($rawData['nickname']);
        $data['email'] = trim($rawData['email']);
        $data['phone'] = trim($rawData['phone']);
        $data['role_id'] = $rawData['role_id'];
        $data['status_id'] = $rawData['status_id'];
        $data['service_id'] = $rawData['service_id'];
        return $data;
    }


    public static function getUser(array $filter = [])
    {
        $where = self::where($filter);
        $rows = self::$db->exec('SELECT u.*, r.`name` AS service FROM `' . self::TABLE . '` u 
        LEFT JOIN `requests` r ON u.`service_id` = r.`id` ' . $where . ' LIMIT 1');
        if (!$rows) {
            return [];
        }
        $rows = self::handleRows($rows);;
        return $rows[0];
    }


    /**
     * Отправляет администратору сообщение с запросом о восстановлении доступа
     * 
     * @param array $data Данные запроса
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function sendPasswordRequestMessage(array $data)
    {
        if (empty($data['email']) || empty($data['message'])) {
            return ['message' => 'Пожалуйста, заполните все поля.', 'is_error' => 1];
        }
        Sender::use('email')->from('robot@crm.r97.ru')->to([self::getEmail(1)])->send('Запрос на восстановление доступа', ['email' => trim($data['email']), 'message' => trim($data['message'])], 'recover-password-request');
        return ['message' => '', 'is_error' => 0];
    }


    public static function recoverPassword(array $data)
    {
        $request = trim($data['request']);
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `login` = ? OR `email` = ? LIMIT 1', [$request, $request]);
        if (!$rows) {
            return ['message' => 'Пользователь с таким логином/e-mail не найден.', 'is_error' => 1];
        }
        $user = $rows[0];
        if ($user['status_id'] != self::IS_ACTIVE) {
            return ['message' => 'Аккаунт неактивен. Для выяснения причин, пожалуйста, напишите администратору.', 'is_error' => 1];
        }
        Sender::use('email')->from('robot@crm.r97.ru')->to([$user['email']])->send('Восстановление пароля', ['nickname' => $user['nickname'], 'url' => 'https://crm.r97.ru/reset-password/?user-id=' . $user['id'] . '&key=' . self::getUserHash($user)], 'recover-password', true);
        return ['message' => '', 'is_error' => 0];
    }


    public static function resetPassword(array $data)
    {
        if (empty($data['user-id']) || empty($data['key'])) {
            return ['message' => 'Недостаточно параметров.', 'is_error' => 1];
        }
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$data['user-id']]);
        if (!$rows) {
            return ['message' => 'Пользователь не найден.', 'is_error' => 1];
        }
        $user = $rows[0];
        if ($user['status_id'] != self::IS_ACTIVE) {
            return ['message' => 'Аккаунт неактивен. Для выяснения причин, пожалуйста, напишите администратору.', 'is_error' => 1];
        }
        if ($data['key'] != self::getUserHash($user)) {
            return ['message' => 'Ключ недействителен, попробуйте сделать запрос еще раз.', 'is_error' => 1];
        }
        $password = self::generatePassword();
        $r = self::setPassword($user['id'], $password);
        if (!$r) {
            return ['message' => 'К сожалению, во время смены пароля произошла ошибка. Пожалуйста, обратитесь к администратору.', 'is_error' => 1];
        }
        \models\Log::user(19, 'При восстановление пароля.', $user['id']);
        Sender::use('email')->from('robot@crm.r97.ru')->to([$user['email']])->send('Данные для входа', ['nickname' => $user['nickname'], 'login' => $user['login'], 'password' => $password], 'account-data', true);
        return ['message' => 'Письмо с новым паролем отправлено на вашу почту.', 'is_error' => 0];
    }


    private static function getUserHash(array $user)
    {
        return md5($user['login'] . '7iRyCK2tb9!4*yP9fZ@8' . date('Ymd'));
    }


    private static function generatePassword($length = 8)
    {
        $password = '';
        $arr = array(
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'
        );
        for ($i = 0; $i < $length; $i++) {
            $password .= $arr[random_int(0, count($arr) - 1)];
        }
        return $password;
    }


    private static function setPassword($userID, $newPassword)
    {
        $hash = User::getPasswordHash($newPassword);
        return self::$db->exec('UPDATE `' . self::TABLE . '` SET `password` = ? WHERE `id` = ?', [$hash, $userID]);
    }


    public static function getUsers(array $filter = [])
    {
        $filter = ($filter) ? $filter : ['status_id' => self::IS_ACTIVE, 'role_id' => [1, 3]];
        $rows = self::$db->exec('SELECT u.*, r.`name` AS service FROM `' . self::TABLE . '` u 
        LEFT JOIN `requests` r ON u.`service_id` = r.`id` '
            . self::where($filter) . self::order($filter) . self::limit($filter));
        return self::handleRows($rows);
    }


    private static function limit(array $filter)
    {
        if (empty($filter['limit'])) {
            return '';
        }
        if (empty($filter['offset'])) {
            return ' LIMIT 0, ' . $filter['limit'];
        }
        return ' LIMIT ' . $filter['offset'] . ', ' . $filter['limit'];
    }


    private static function handleRows(array $rows)
    {
        $roles = self::getRoles();
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if ($rows[$i]['id'] == 33) {
                $rows[$i]['role'] = self::ROLES[7];
            } else {
                $rows[$i]['role'] = self::ROLES[$rows[$i]['role_id']] ?? '';
            }
            $rows[$i]['is_waiting'] = false;
            $rows[$i]['is_active'] = false;
            $rows[$i]['is_blocked'] = false;
            if ($rows[$i]['status_id'] == self::IS_WAITING) {
                $rows[$i]['status'] = 'Неактивен';
                $rows[$i]['is_waiting'] = true;
            } else if ($rows[$i]['status_id'] == self::IS_BLOCKED) {
                $rows[$i]['status'] = 'Заблокирован';
                $rows[$i]['is_blocked'] = true;
            } else {
                $rows[$i]['status'] = 'Активен';
                $rows[$i]['is_active'] = true;
            }
            $rows[$i]['is_active'] = $rows[$i]['status_id'] == self::IS_ACTIVE;
            $rows[$i]['role_name'] = $roles[$rows[$i]['role_id']] ?? '(не установлена)';
            $rows[$i]['registered_at'] = date('d.m.Y H:i', strtotime($rows[$i]['registered_at']));
        }
        return $rows;
    }


    public static function count(array $filter = [])
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt     
        FROM `' . self::TABLE . '` u       
        ' . self::where($filter));
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    private static function order(array $filter)
    {
        $field = (!empty($filter['sort'])) ? $filter['sort'] : 'id';
        $sort = ' ORDER BY u.`' . $field . '`';
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    private static function where(array $filter)
    {
        $where = [];
        if (!empty($filter['active']) || !empty($filter['is_active'])) {
            $where[] = 'u.`status_id` = ' . self::IS_ACTIVE;
        } else if (!empty($filter['inactive'])) {
            $where[] = 'u.`status_id` != ' . self::IS_ACTIVE;
        } else if (!empty($filter['status_id'])) {
            $where[] = 'u.`status_id` = ' . $filter['status_id'];
        }
        if (!empty($filter['role_id'])) {
            if (is_array($filter['role_id'])) {
                $where[] = 'u.`role_id` IN (' . implode(',', $filter['role_id']) . ')';
            } else {
                $where[] = 'u.`role_id` = ' . $filter['role_id'];
            }
        }
        if (isset($filter['service_id'])) {
            $where[] = 'u.`service_id` = ' . $filter['service_id'];
        }
        if (!empty($filter['role'])) {
            if (is_array($filter['role'])) {
                $roleIDs = [];
                foreach ($filter['role'] as $role) {
                    $roleIDs[] = array_search($role, self::ROLES);
                }
                if ($roleIDs) {
                    $where[] = 'u.`role_id` IN (' . implode(',', $roleIDs) . ')';
                }
            } else {
                $roleID = array_search($filter['role'], self::ROLES);
                if ($roleID) {
                    $where[] = 'u.`role_id` = ' . $roleID;
                }
            }
        }
        if (!empty($filter['login'])) {
            $where[] = 'u.`login` = "' . $filter['login'] . '"';
        }
        if (!empty($filter['id'])) {
            $where[] = 'u.`id` = ' . $filter['id'];
        }
        if (!empty($filter['search'])) {
            $where[] = self::getSearchWhere($filter['search']);
        }
        if ($where) {
            return 'WHERE ' . implode(' AND ', $where);
        }
        return '';
    }


    private static function getSearchWhere($search)
    {
        $search = trim($search);
        $res = [];
        $res[] = 'u.`login` LIKE "%' . $search . '%"';
        $res[] = 'u.`nickname` LIKE "%' . $search . '%"';
        $res[] = 'u.`email` LIKE "%' . $search . '%"';
        $res[] = 'u.`phone` LIKE "%' . $search . '%"';
        $res[] = 'r.`name` LIKE "%' . $search . '%"';
        return '(' . implode(') OR (', $res) . ')';
    }


    public static function getEmail($userID)
    {
        $rows = self::$db->exec('SELECT `email` FROM `' . self::TABLE . '` WHERE `id` = ' . $userID);
        if (!$rows) {
            return '';
        }
        return $rows[0]['email'];
    }


    public static function isBlocked($userID)
    {
        $rows = self::$db->exec('SELECT `status_id` FROM `' . self::TABLE . '` WHERE `id` = ' . $userID);
        if (!$rows) {
            return false;
        }
        return $rows[0]['status_id'] != self::IS_ACTIVE;
    }


    public static function setStatus($statusID, $userID)
    {
        return self::$db->exec('UPDATE `' . self::TABLE . '` SET `status_id` = ? WHERE `id` = ?', [$statusID, $userID]);
    }
}


Users::init();
