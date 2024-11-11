<?php

namespace models\dashboard;

class Settings extends \models\_Model
{

    const PATH = '/_new-codebase/back/settings/dashboard/';
    private static $cache = [];


    /* Получает все возможные столбцы */
    public static function getAllCols()
    {
        if (isset(self::$cache['all_cols'])) {
            return self::$cache['all_cols'];
        }
        $json = \models\Settings::get('cols:dashboard');
               /*   if(\models\User::isAdmin() && !empty(App::$URLParams['t'])){
    $c = (array)json_decode($json, true);
        $r = [
            'status' => $c['status'],
            'controls' => $c['controls'],
            'work_cost' => $c['work_cost'],
'parts_cost' => $c['parts_cost'],
'transport_cost' => $c['transport_cost'],
            'id' => $c['id'],
            'service' => $c['service'],
            'service_address' => $c['service_address'], 
'rsc' => $c['rsc'],
'master' => $c['master'],
'model' => $c['model'],
'serial' => $c['serial'],
'client_defect' => $c['client_defect'],
'provider' => $c['provider'],
'order' => $c['order'],
'sell_date' => $c['sell_date'],
'shop' => $c['shop'],
'client' => $c['client'],
'create_date' => $c['create_date'],
'receive_date' => $c['receive_date'],
'received_from' => $c['received_from'],
'recept_status' => $c['recept_status'],
'ship_status' => $c['ship_status'],
'begin_date' => $c['begin_date'],
'ready_date' => $c['ready_date'],
'out_date' => $c['out_date'],
'anrp_number' => $c['anrp_number'],
        ];
        \models\Settings::save('cols:dashboard', json_encode($r), 'Все столбцы дашборда');  exit; 
        }  */
        if (empty($json)) {
            throw new \Exception('Cols JSON is empty.');
        }
        self::$cache['all_cols'] = (array)json_decode($json, true);
        return self::$cache['all_cols'];
    }


    /* Сохраняет все возможные столбцы */
    public static function saveAllCols()
    {
        if (empty($_POST['cols'])) {
            return;
        }
        \models\Settings::save('cols:dashboard', json_encode($_POST['cols']), 'Все столбцы дашборда');
        unset(self::$cache['all_cols']);
    }


    /* Сохраняет доступные столбцы для каждого типа пользователей: index => uri */
    public static function savePermissions($userRole)
    {
        $cols = [];
        if (isset($_POST[$userRole])) {
            $cols = $_POST[$userRole];
        }
        \models\Settings::save($userRole . ':perms:cols:dashboard', json_encode($cols), 'Доступные столбцы: ' . $userRole);
    }


    /* Столбцы, доступные конкретному пользователю: index => uri */
    public static function getPermissions($userRole)
    {
        $json = \models\Settings::get($userRole . ':perms:cols:dashboard');
        if (empty($json)) {
            return [];
        }
        return (array)json_decode($json, true);
    }


    public static function saveColsWidth($userRole, $userID, $tab)
    {
        if (empty($_POST['width'])) {
            return;
        }
        $newWidth = json_decode($_POST['width'], true);
        $cols = self::getCurrentCols($userID, $tab);
        if (!$cols) {
            $cols = self::getAvailableCols($userRole, []);
        }
        $newCols = [];
        $n = 0;
        foreach ($cols as $uri => $col) {
            $col['width'] = $newWidth[$n];
            $newCols[$uri] = $col;
            $n++;
        }
        \models\Settings::save($userID . ':' . $tab . ':cols:dashboard', json_encode($newCols), 'Столбцы дашборда для ' . $userID . ' (' . $tab . ')');
    }


    public static function save($uri, $val)
    {
        return \models\Settings::save($uri, $val, 'Кол-во строк дашборда');
    }


    public static function get($uri)
    {
        return \models\Settings::get($uri);
    }


    public static function saveCurrentCols($userRole, $userID, $tab)
    {
        if (empty($_POST['cols'])) {
            return;
        }
        $curCols = self::getCurrentCols($userID, $tab);
        $availCols = self::getAvailableCols($userRole, $curCols);
        $newURIs = explode(',', $_POST['cols']);
        $newCols = [];
        foreach ($newURIs as $uri) {
            $newCols[$uri] = (isset($curCols[$uri])) ? $curCols[$uri] : $availCols[$uri];
        }
        \models\Settings::save($userID . ':' . $tab . ':cols:dashboard', json_encode($newCols), 'Столбцы дашборда для ' . $userID . ' (' . $tab . ')');
    }


    public static function getCurrentCols($userID, $tab)
    {
        $tab = ($tab) ? $tab : 'all';
        $json = \models\Settings::get($userID . ':' . $tab . ':cols:dashboard');
        if (empty($json)) {
            return [];
        }
        $allCols = self::getAllCols();
        $curCols = (array)json_decode($json, true);
        foreach ($curCols as $uri => $col) {
            $curCols[$uri]['name'] = $allCols[$uri]['name'];
            $curCols[$uri]['sort_col'] = $allCols[$uri]['sort_col'];
        }
        return $curCols;
    }


    public static function getAvailableCols($userRole, array $curCols)
    {
        $perms = self::getPermissions($userRole);
        if (empty($perms)) {
            return [];
        }
        $allCols = self::getAllCols();
        $ret = [];
        foreach ($perms as $uri) {
            if (!isset($curCols[$uri])) {
                $ret[$uri] = $allCols[$uri];
            }
        }
        return $ret;
    }
}


Settings::init();
