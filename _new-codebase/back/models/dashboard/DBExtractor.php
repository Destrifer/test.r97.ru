<?php

namespace models\dashboard;

use program\core;
use program\core\App;
use \models\User;
use program\core\Time;


class DBExtractor extends \models\_Model
{
    public static $where = ['`deleted` = 0'];
    public static $paginator = null;
    private static $userRole = '';
    private static $db = null;
    private static $cache = ['repairs_cnt' => null, 'where' => null, 'problems_ids' => []];


    public static function init()
    {
        self::$userRole = User::getData('role');
        switch (self::$userRole) {
            case 'admin':
                if (isset(App::$URLParams['tab']) && App::$URLParams['tab'] == 'deleted') {
                    self::$where = [];
                }
                break;
            case 'service':
                self::$where = ['`deleted` = 0', '`service_id` = ' . User::getData('id')];
                break;
            case 'taker':
                if (isset(App::$URLParams['tab']) && App::$URLParams['tab'] == 'all') {
                    self::$where = ['`deleted` = 0'];
                } else {
                    self::$where = ['`deleted` = 0', '`service_id` = 33'];
                }
                break;
            case 'slave-admin':
            case 'master':
                self::$where = ['`deleted` = 0', '`service_id` = 33'];
                break;
        }
        self::$db = \models\_Base::getDB();
    }


    public static function getRepairs()
    {
        $curPage = (!empty(App::$URLParams['page'])) ? App::$URLParams['page'] : 1;
        self::$paginator = new core\Paginator(self::getCnt(), ((!empty($_POST['num_per_page'])) ? $_POST['num_per_page'] : 80), $curPage);
        $where = self::getWhere();
        return self::$db->exec('SELECT 
        `model_id`, `return_id`, `client_id`, `master_user_id`, `master_id`, `serial`, `anrp_number`, `receive_date`, 
        `finish_date`, `rsc`, `id`, `create_date`, `client` AS client_name, `client_type`, `name_shop` AS shop_name, 
        `bugs` AS client_defect, `begin_date`, `visual`, `visual_comment`, `complex`, `refuse_doc_flag`, 
        `sell_date`, `status_by_hand`, `no_serial`, `status_ship_id` AS ship_status_id,   
        `anrp_use`, `out_date`, `ready_date`, `status_id` AS recept_status_id, `status_admin` AS status, 
        `total_price`, `onway`, `onway_type` AS transport_zone, `service_id`, `imported`, `approve_date`, 
        `attention_flag`, `transport_cost`, `parts_cost`, `install_cost`, `dismant_cost`, `repair_final`, `serial_invalid_flag`, 
        `has_questions`, `status_user_read` AS is_unread_service, `status_admin_read` AS is_unread_admin               
        FROM `repairs` WHERE ' . $where['query'] . ' 
        ORDER BY ' . self::getOrder() . ' LIMIT ' . self::$paginator->getLimit(), $where['params']);
    }


    private static function getOrder()
    {
        $filed = (empty($_POST['sort_field'])) ? 'id' : $_POST['sort_field'];
        $dir = (empty($_POST['sort_dir']) || $_POST['sort_dir'] == 'asc') ? 'ASC' : 'DESC';
        return '`' . $filed . '` ' . $dir;
    }


    public static function getCnt()
    {
        if (self::$cache['repairs_cnt'] === null) {
            $where = self::getWhere();
            $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `repairs` WHERE ' . $where['query'], $where['params']);
            self::$cache['repairs_cnt'] = (isset($rows[0]['cnt'])) ? $rows[0]['cnt'] : 0;
        }
        return self::$cache['repairs_cnt'];
    }


    public static function getWhere()
    {
        if (self::$cache['where'] !== null) {
            return self::$cache['where'];
        }
        $where = self::$where;
        $params = [];
        if (!empty(App::$URLParams['tab'])) {
            $where[] = self::getTabWhere(App::$URLParams['tab']);
        }
        if (!empty(App::$URLParams['color'])) {
            $where[] = self::getColorWhere(App::$URLParams['color']);
        }
        if (!empty(App::$URLParams['search'])) {
            $where[] = self::getSearchWhere(App::$URLParams['search']);
        }
        if (!empty(App::$URLParams['model'])) {
            $where[] = '`model_id` = ' . App::$URLParams['model'];
        }
        if (!empty(App::$URLParams['plant']) || !empty(App::$URLParams['order']) || !empty(App::$URLParams['provider'])) {
            $where[] = self::getPlantOrderWhere();
        }
        if (!empty(App::$URLParams['master'])) {
            $where[] = '`master_user_id` = ' . App::$URLParams['master'];
        }
        if (!empty(App::$URLParams['client_type'])) {
            $where[] = '`client_type` = ' . App::$URLParams['client_type'];
        }
        if (!empty(App::$URLParams['country'])) {
            $where[] = '`service_id` IN (SELECT `user_id` FROM `requests` WHERE `country` = ' . App::$URLParams['country'] . ')';
        }
        if (!empty(App::$URLParams['service'])) {
            $where[] = '`service_id` = ' . App::$URLParams['service'];
        }
        if (!empty(App::$URLParams['outside'])) {
            $where[] = '`onway` = ' . ((App::$URLParams['outside'] == 1) ? 1 : 0);
        }
        if (!empty(App::$URLParams['exc_service'])) {
            $where[] = '`service_id` != ' . App::$URLParams['exc_service'];
        }
        if (!empty(App::$URLParams['issue'])) { // симптом
            $where[] = '`disease` = "' . App::$URLParams['issue'] . '"';
        }
        if (!empty(App::$URLParams['id'])) {
            $where[] = '`id` = ' . App::$URLParams['id'];
        }
        if (!empty(App::$URLParams['recept_status'])) { // гарантийный и др.
            $where[] = '`status_id` = ' . App::$URLParams['recept_status'];
        }
        if (!empty(App::$URLParams['ship_status'])) { // клиентский и др.
            $where[] = '`status_ship_id` = ' . App::$URLParams['ship_status'];
        }
        if (!empty(App::$URLParams['serial'])) {
            $where[] = '`serial` LIKE "%' . App::$URLParams['serial'] . '%"';
        }
        if (!empty(App::$URLParams['status'])) {
            $where[] = '`status_admin` = "' . App::$URLParams['status'] . '"';
        }
        if (!empty(App::$URLParams['defect_cl'])) { // неисправность со слов клиента (заявленная)
            $where[] = '`bugs` LIKE "%' . App::$URLParams['defect_cl'] . '%"';
        }
        if (!empty(App::$URLParams['client'])) { // принят от...
            $where[] = '(`name_shop` LIKE "%' . App::$URLParams['client'] . '%" OR `client` LIKE "%' . App::$URLParams['client'] . '%")';
        }
        $dates = ['sell_date', 'begin_date', 'out_date', 'receive_date', 'finish_date'];
        foreach ($dates as $dateField) {
            if (!empty(App::$URLParams[$dateField])) {
                $p = explode('-', App::$URLParams[$dateField]);
                if (empty($p[1])) {
                    continue;
                }
                $where[] = '`' . $dateField . '` BETWEEN "' . Time::format($p[0], 'Y-m-d') . '" AND "' . Time::format($p[1], 'Y-m-d') . '"';
            }
        }
        self::$cache['where'] = ['query' => implode(' AND ', array_filter($where)), 'params' => $params];
        return self::$cache['where'];
    }


    private static function getPlantOrderWhere()
    {
        $query = [];
        if (!empty(App::$URLParams['model'])) {
            $query = ['`model_id` = ' . App::$URLParams['model']];
        }
        if (!empty(App::$URLParams['plant'])) {
            $query[] = '`plant_id` = ' . App::$URLParams['plant'];
        }
        if (!empty(App::$URLParams['order'])) {
            $query[] = '`order` = "' . trim(App::$URLParams['order']) . '"';
        }
        if (!empty(App::$URLParams['provider'])) {
            $query[] = '`provider_id` = ' . App::$URLParams['provider'];
        }
        $rows = self::$db->exec('SELECT `serial` FROM `serials` WHERE ' . implode(' AND ', $query));
        if (!$rows) {
            return '`serial` = "-1"';
        }
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['serial'] = substr($rows[$i]['serial'], 0, 9);
        }
        if (count($rows) == 1) {
            return '`serial` LIKE "' . $rows[0]['serial'] . '%"';
        }
        return '`serial` REGEXP "' . implode('|', array_column($rows, 'serial')) . '"';
    }


    private static function getColorWhere($color)
    {
        switch ($color) {
            case 'blue':
                return '`anrp_number` != ""';

            case 'darkblue':
                return '`model_id` = 0';

            case 'yellow':
                return '`serial` = "" OR `model_id` = 0 OR `complex` = "" OR (`client_type` = 1 AND `name_shop` = "") OR `refuse_doc_flag` = "" OR `bugs` = "" OR (`visual` = "" AND `visual_comment` = "")';

            case 'red':
                return '`status_ship_id` = 3 AND `anrp_number` = ""';

            case 'brown':
                return '`parts_cost` > 0';

            case 'darkgreen':
                return '`status_id` = 6';

            case 'gray':
                return '`status_by_hand` = 1 OR `status_id` = 5';

            case 'purple':
                return '`serial_invalid_flag` = 1';

            default:
                return '';
        }
    }


    private static function getSearchWhere($req)
    {
        $req = trim($req);
        $query = [];
        if (preg_match('/^[0-9]{1,8}$/', $req)) {
            $query[] = '`id` = ' . $req;
        } else {
            $rows = self::$db->exec('SELECT `user_id` AS id FROM `requests` WHERE `name` LIKE "%' . $req . '%"');
            if ($rows) {
                $query[] = '`service_id` IN (' . implode(',', array_column($rows, 'id')) . ')';
            }
            $rows = self::$db->exec('SELECT `id` FROM `clients` WHERE `name` LIKE "%' . $req . '%"');
            if ($rows) {
                $query[] = '`client_id` IN (' . implode(',', array_column($rows, 'id')) . ')';
            }
            $query[] = '`bugs` LIKE "%' . $req . '%"';
            $query[] = '`client` LIKE "%' . $req . '%"';
        }
        $rows = self::$db->exec('SELECT `id` FROM `models` WHERE `name` LIKE "%' . $req . '%"');
        if ($rows) {
            $query[] = '`model_id` IN (' . implode(',', array_column($rows, 'id')) . ')';
        }
        $rows = self::$db->exec('SELECT `id` FROM `returns` WHERE `name` LIKE "%' . $req . '%"');
        if ($rows) {
            $query[] = '`return_id` IN (' . implode(',', array_column($rows, 'id')) . ')';
        }
        $q = ($query) ? implode(' OR ', $query) . ' OR ' : '';
        return '(' . $q . '`rsc` LIKE "%' . $req . '%" OR `serial` LIKE "%' . $req . '%" OR `name_shop` LIKE "%' . $req . '%" OR `imported_model` LIKE "%' . $req . '%")';
    }


    public static function getTabWhere($uri)
    {
        if (empty($uri)) {
            return '';
        }
        $masterSQL = '';
        if (self::$userRole == 'master') {
            $masterSQL = ' AND `master_user_id` = ' . User::getData('id');
        }
        switch ($uri) {
            case 'approve':
                return '`status_admin` IN ("Подтвержден", "Утилизирован", "Выдан")';
            case 'cancelled':
                return '`status_admin` = "Отклонен"';
            case 'partsintransit':
                return '`status_admin` = "Запчасти в пути"';
            case 'factory':
                return '`status_admin` = "Заказ на заводе"';
            case 'inprocess':
                return '`status_admin` = "В обработке"';
            case 'waittesler':
                return '`status_admin` = "Ждем з/ч Tesler"';
            case 'waitroch':
                return '`status_admin` = "Ждем з/ч Roch"';
            case 'inprocess2':
                return '`status_admin` IN ("В обработке", "Ждем з/ч Tesler", "Ждем з/ч Roch", "Запрос у Tesler", "Запрос у Roch", "Заказ на заводе")';
            case 'approvedact':
                return '`status_admin` = "Одобрен акт"';
            case 'factorytesler':
                return '`status_admin` = "Запрос у Tesler"';
            case 'factoryroch':
                return '`status_admin` = "Запрос у Roch"';
            case 'softreq':
                return '`status_admin` = "Запрос ПО"';
            case 'needparts':
                return '`status_admin` = "Нужны запчасти"';
            case 'oncheck':
                return '`status_admin` = "На проверке"';
            case 'questions':
                return '`has_questions` = 1';
            case 'accepted':
                return '`status_admin` = "Принят"';
            case 'inprogress':
                return '`status_admin` = "В работе" ' . $masterSQL;
            case 'outside':
                return '`status_admin` IN ("Запрос на демонтаж", "Запрос на монтаж", "Выезд подтвержден", "Выезд отклонен", "Запрос на выезд")';
            case 'ready':
                return '`status_admin` = "Подтвержден" ' . $masterSQL;;
            case 'issued':
                return '`status_admin` = "Выдан" ' . $masterSQL;;
            case 'disposed': // утиль
                return '`status_admin` IN ("Подтвержден", "Выдан") AND `problem_id` IN (' . self::getProblemsIDs('disposed') . ') AND `repair_final` = 2';
            case 'markdown': // уценка
                return '`status_admin` IN ("Подтвержден", "Выдан") AND `problem_id` IN (' . self::getProblemsIDs('markdown') . ') AND `repair_final` = 2';
            case 'deleted':
                return '`deleted` = 1';
            default:
                return '';
        }
    }


    private static function getProblemsIDs($type)
    {

        switch ($type) {
            case 'disposed':
                if (!isset(self::$cache['problems_ids']['disposed'])) {
                    $rows = self::$db->exec('SELECT `id` FROM `details_problem` WHERE `work_type` = "nonrepair"');
                    self::$cache['problems_ids']['disposed'] = implode(',', array_column($rows, 'id'));
                }
                return self::$cache['problems_ids']['disposed'];
                break;
            case 'markdown':
                if (!isset(self::$cache['problems_ids']['markdown'])) {
                    $rows = self::$db->exec('SELECT `id` FROM `details_problem` WHERE `work_type` IN ("repair", "diag")');
                    self::$cache['problems_ids']['markdown'] = implode(',', array_column($rows, 'id'));
                }
                return self::$cache['problems_ids']['markdown'];
                break;
            default:
                throw new \Exception('Wrong type: ' . $type);
        }
    }
}


DBExtractor::init();
