<?php

namespace models;

/** 
 * Текущий пользователь
 */

class User extends _Model
{

    private static $pepper = '7R4#m3m@d!F6hc5m4%w7&@Ot^$jo'; // доп. соль
    const VER = 1;                                           // версия cookie и сессии
    private static $data = [];                               // данные пользователя


    public static function init()
    {
        self::authBySession() || self::authByCookie();
    }


    private static function authBySession()
    {
        if (empty($_SESSION['user']['ver']) || $_SESSION['user']['ver'] < self::VER) {
            return false;
        }
        self::$data = $_SESSION['user'];
        return true;
    }


    private static function authByCookie()
    {
        if (!$c = self::getCookie()) {
            return false;
        }
        $data = Users::getUser(['id' => $c['id']]);
        if (!$data) {
            self::setCookie(null);
            return false;
        }
        self::$data = $data;
        self::setSession($data);
        return true;
    }


    /**
     * Проверяет, имеет ли пользователь роль
     * 
     * @param array func_get_args Список проверяемых ролей
     * 
     * @return bool Соответствует ли одной из переданных ролей
     */
    public static function hasRole()
    {
        return (!empty(self::$data['role']) && in_array(self::$data['role'], func_get_args()));
    }


    public static function isAuth()
    {
        return !empty(self::$data['id']);
    }


    public static function isActive()
    {
        return !empty(self::$data['is_active']);
    }


    /**
     * Хеш для хранения пароля в БД
     * 
     * @param string $password Чистый пароль
     * 
     * @return string Хеш пароля
     */
    public static function getPasswordHash($password)
    {
        return password_hash($password . self::$pepper, PASSWORD_DEFAULT);
    }


    private static function getCookie()
    {
        if (empty($_COOKIE['auth_token'])) {
            return [];
        }
        $d = explode('.', $_COOKIE['auth_token']);
        if (empty($d[0]) || empty($d[1]) || empty($d[2])) {
            return [];
        }
        $result = ['ver' => $d[2], 'id' => $d[1], 'hash' => $d[0]];
        return ($result['ver'] < self::VER) ? [] : $result;
    }


    private static function setCookie($id = null, $hash = null, $isRemember = false)
    {
        if (!$id) {
            setcookie('auth_token', '', 0, '/', str_replace('www.', '', $_SERVER["SERVER_NAME"]), false, false);
            return;
        }
        $token = $hash . '.' . $id . '.' . self::VER;
        $t = $isRemember ? time() + 10000000 : 0;
        setcookie('auth_token', $token, $t, '/', str_replace('www.', '', $_SERVER["SERVER_NAME"]), false, false);
    }


    public static function loginAs($login)
    {
        $data = Users::getUser(['login' => $login]);
        if (!$data) {
            return ['message' => 'Пользователь с логином "' . $login . '" не найден.', 'is_error' => 1];
        }
        self::setSession($data);
        self::setCookie($data['id'], $data['password'], false);
        self::$data = $data;
        return ['message' => 'Вы успешно вошли.', 'is_error' => 0];
    }


    public static function login($login, $password, $isRemember = true)
    {
        $data = Users::getUser(['login' => $login]);
        if (!$data) {
            return ['message' => 'Пользователь с логином "' . $login . '" не найден.', 'is_error' => 1];
        }
        if ($data['is_blocked']) {
            return ['message' => 'Аккаунт деактивирован.', 'is_error' => 1];
        }
        if (!password_verify($password . self::$pepper, $data['password'])) {
            return ['message' => 'Неверный пароль.', 'is_error' => 1];
        }
        if ($data['is_waiting'] && $data['role'] == 'service') {
            if (empty($data['service_id'])) {
                self::setSession($data);
                self::setCookie($data['id'], $data['password'], $isRemember);
                header('Location: /service-info/');
                exit;
            }
            return ['message' => 'Аккаунт временно неактивен до подтверждения администратором.', 'is_error' => 1];
        }
        self::setSession($data);
        self::setCookie($data['id'], $data['password'], $isRemember);
        self::$data = $data;
        return ['message' => 'Вы успешно вошли.', 'is_error' => 0];
    }


    public static function logout()
    {
        self::setSession(null);
        self::setCookie(null);
        self::$data = [];
    }


    private static function setSession($data = null)
    {
        if (!$data) {
            unset($_SESSION['user']);
            return;
        }
        unset($data['password']);
        $_SESSION['user'] = $data;
        $_SESSION['user']['ver'] = self::VER;
    }


    /**
     * Возвращает данные пользователя по ключу
     * 
     * @param string $key Ключ
     * 
     * @return mixed Данные
     */
    public static function getData($key)
    {
        return (isset(self::$data[$key])) ? self::$data[$key] : null;
    }
}


User::init();
