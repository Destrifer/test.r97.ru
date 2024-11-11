<?php

namespace models\dashboard;

use models\Counters;
use models\staff\Staff;
use program\core;
use program\core\Time;
use program\core\App;
use \models\User;

class Data extends \models\_Model
{
    public static $receptStatuses = [1 => 'Гарантийный', 5 => 'Условно-гарантийный', 6 => 'Платный'];
    public static $shipStatuses = [2 => 'Клиентский', 3 => 'Повторный', 1 => 'Предторговый'];
    private static $repairResult = [
        1 => 'Выдаем обратно клиенту (Дефект не обнаружен, Ремонт)',
        2 => 'Оставляем у себя (Подтвердилось)',
        3 => 'Выдаем обратно клиенту (Отказано в гарантии)'
    ];
    /* Максимальный срок ремонта "страна => дни" */
    private static $maxDays = [1 => 45, 2 => 14, 3 => 20, 4 => 10];
    public static $countries = [ // страны, можно грузить из БД countries
        1 => 'Россия',
        2 => 'Беларусь',
        3 => 'Армения',
        4 => 'Казахстан',
        5 => 'Киргизия',
        6 => 'Украина'
    ];
    private static $curTab = '';
    private static $userRole = '';
    private static $db = null;
    private static $cache = ['models' => [], 'returns' => [], 'clients' => [], 'services' => []];
    private static $controls = [
        'take' => ['name' => 'Взять в работу', 'action' => 'take', 'url' => ''],
        'check' => ['name' => 'Выбрать', 'action' => 'check', 'url' => ''],
        'del' => ['name' => 'Удалить карточку', 'url' => '', 'action' => 'del-repair', 'url' => ''],
        'back' => ['name' => 'Вернуть на доработку', 'action' => '', 'url' => ''],
        'appoint_master' => ['name' => 'Назначить мастера', 'action' => 'appoint-master', 'url' => ''],
        'repeated' => ['name' => 'Показать повторные', 'action' => 'show-repeated-repaires', 'url' => ''],
        'label' => ['name' => 'Скачать наклейку', 'action' => '', 'url' => ''],
        'receipt' => ['name' => 'Скачать квитанцию', 'action' => '', 'url' => ''],
        'edit' => ['name' => 'Редактировать', 'action' => '', 'url' => ''],
        'recover' => ['name' => 'Восстановить', 'action' => '', 'url' => ''],
        'del_perm' => ['name' => 'Удалить полностью', 'url' => '', 'action' => 'del-repair-perm'],
        'attention' => ['name' => 'Проверка', 'url' => '', 'action' => 'attention'],
        'days' => ['name' => 'Дней в ремонте', 'url' => '', 'action' => ''],
        'country' => ['name' => 'Страна', 'url' => '', 'action' => ''],
        'refusal' => ['name' => 'Отказ от ремонта', 'url' => '', 'action' => ''],
        'outside' => ['name' => 'Выезд', 'url' => 'javascript:;', 'action' => ''],
        'prototype' => ['name' => 'Создать ремонт на основе текущего', 'url' => '', 'action' => 'prototype'],
        'read' => ['name' => 'Прочитано', 'url' => '', 'action' => ''],
        'unread' => ['name' => 'Не прочитано', 'url' => '', 'action' => ''],
        'consumer' => ['name' => 'Потребитель', 'url' => '', 'action' => ''],
        'shop' => ['name' => 'Магазин', 'url' => '', 'action' => '']
    ];


    public static function init()
    {
        self::$curTab = (!empty(App::$URLParams['tab'])) ? App::$URLParams['tab'] : '';
        self::$db = \models\_Base::getDB();
        self::$userRole = User::getData('role');
        switch (self::$userRole) {
            case 'admin':
                if (self::$curTab == 'deleted') {
                    $c = ['check', 'del_perm', 'repeated', 'recover', 'label', 'receipt', 'refusal', 'outside', 'consumer', 'shop', 'edit'];
                } else {
                    $c = ['days', 'country', 'check', 'del', 'prototype', 'attention', 'repeated', 'label', 'receipt', 'refusal', 'outside', 'read', 'unread', 'consumer', 'shop', 'edit'];
                }
                break;
            case 'slave-admin':
                $c = ['days', 'check', 'del', 'prototype', 'attention', 'back', 'appoint_master', 'repeated', 'label', 'receipt', 'refusal', 'outside', 'consumer', 'shop', 'edit'];
                break;
            case 'taker':
                $c = ['days', 'check', 'del', 'prototype', 'back', 'appoint_master', 'repeated', 'label', 'receipt', 'refusal', 'outside', 'consumer', 'shop', 'edit'];
                break;
            case 'service':
                $c = ['days', 'del', 'label', 'receipt', 'refusal', 'read', 'unread', 'consumer', 'shop', 'edit'];
                break;
            case 'master':
                if (self::$curTab == 'accepted') {
                    $c = ['days', 'attention', 'label', 'receipt', 'repeated', 'refusal', 'outside', 'consumer', 'shop', 'edit', 'take'];
                } else {
                    $c = ['days', 'attention', 'label', 'receipt', 'repeated', 'refusal', 'outside', 'consumer', 'shop', 'edit'];
                }
                break;
            default:
                $c = self::$controls;
        }
        $controls = [];
        foreach ($c as $uri) {
            $controls[$uri] = self::$controls[$uri];
        }
        self::$controls = $controls;
    }


    public static function getRows()
    {
        $rows = DBExtractor::getRepairs();
        /* Функция форматирования денеж. сумм */
        $moneyFormat = function ($val) {
            return number_format($val, 2, ',', ' ');
        };
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $model = self::getModel($rows[$i]['model_id']);
            $rows[$i]['model'] = ($model['name']) ? $model['name'] : '!!!';
            $return = self::getReturn($rows[$i]['return_id']);
            $rows[$i]['return'] = $return['name'];
            $client = self::getClient($rows[$i]['client_id']);
            $rows[$i]['client'] = (!empty($client['name'])) ? $client['name'] : $rows[$i]['shop_name'];
            $rows[$i]['shop'] = $rows[$i]['shop_name'];
            $rows[$i]['repeated_flag'] = self::getRepeatedFlag($rows[$i]['id'], $rows[$i]['ship_status_id'], $rows[$i]);
            $rows[$i]['till_date'] = self::getTillDate($rows[$i]['receive_date'], $client['days']);
            $service = self::getService($rows[$i]['service_id']);
            $rows[$i]['service'] = $service['name'];
            $rows[$i]['service_address'] = $service['address'];
            $rows[$i]['days_in'] = self::getDaysInRepair($rows[$i], $service);
            $rows[$i]['receive_date'] = Time::format($rows[$i]['receive_date']);
            $rows[$i]['ready_date'] = Time::format($rows[$i]['ready_date']);
            $serial = \models\Serials::getSerial($rows[$i]['serial'], $rows[$i]['model_id']);
            $rows[$i]['provider'] = $serial['provider'];
            $rows[$i]['order'] = $serial['order'];
            $rows[$i]['approve_date'] = Time::format($rows[$i]['approve_date']);
            $rows[$i]['sell_date'] = Time::format($rows[$i]['sell_date']);
            $rows[$i]['finish_date'] = Time::format($rows[$i]['finish_date']);
            $rows[$i]['create_date'] = Time::format($rows[$i]['create_date']);
            $rows[$i]['begin_date'] = Time::format($rows[$i]['begin_date']);
            $rows[$i]['out_date'] = Time::format($rows[$i]['out_date']);
            $rows[$i]['master'] = self::getMaster($rows[$i]['master_user_id'], $rows[$i]['master_id'], $rows[$i]['imported']);
            $rows[$i]['recept_status'] = ((isset(self::$receptStatuses[$rows[$i]['recept_status_id']])) ? self::$receptStatuses[$rows[$i]['recept_status_id']] : '');
            $rows[$i]['ship_status'] = ((isset(self::$shipStatuses[$rows[$i]['ship_status_id']])) ? self::$shipStatuses[$rows[$i]['ship_status_id']] : '');
            $rows[$i]['group_name'] = self::getGroupName($rows[$i], $return, $client);
            $rows[$i]['controls'] = self::getControls($rows[$i], $service['country_id']);
            $rows[$i]['received_from'] = $rows[$i]['client_name'];
            $rows[$i]['work_cost'] = self::getWorkCost($rows[$i], $moneyFormat);
            $rows[$i]['parts_cost'] = $moneyFormat($rows[$i]['parts_cost']);
            $rows[$i]['transport_cost'] = $moneyFormat($rows[$i]['transport_cost']);
            $rows[$i]['install_cost'] = $moneyFormat($rows[$i]['install_cost']);
            $rows[$i]['dismant_cost'] = $moneyFormat($rows[$i]['dismant_cost']);
            $rows[$i]['repair_results'] = (isset(self::$repairResult[$rows[$i]['repair_final']])) ? self::$repairResult[$rows[$i]['repair_final']] : '';
            $rows[$i]['row_color'] = self::getRowColor($rows[$i]);
        }
        if (in_array(self::$userRole, ['admin', 'service'])) {
            return $rows;
        }
        return core\RowSet::groupBy('group_name', $rows);
    }


    private static function getRepeatedFlag($repairID, $shipStatusID, $row)
    {
        // return $shipStatusID == 3;
        if ($shipStatusID == 3) { // повторный
            $r = \models\Repair::isRepeated($repairID); // проверка из-за возможности ошибки
            if (!$r) {
                $newID = 0;
                if ($row['client_type'] == 2) {
                    $newID = (!Time::isEmpty($row['sell_date'])) ? 2 : 1;
                } else {
                    $newID = 2;
                }
                self::$db->exec('UPDATE `repairs` SET `status_ship_id` = ' . $newID . ' WHERE `id` = ' . $repairID);
            }
            return $r;
        }
        return false;
    }


    private static function getDaysInRepair(array $row, array $service)
    {
        if (!isset(self::$maxDays[$service['country_id']])) {
            return ['days' => 0, 'color' => 'gray'];
        }
        $date2 = (!Time::isEmpty($row['approve_date'])) ? $row['approve_date'] : date('Y-m-d');
        $d = ['days' => Time::getBetween($row['receive_date'], $date2), 'color' => 'gray'];
        if (!$d['days'] || $row['recept_status_id'] == 6 || !Time::isEmpty($row['approve_date'])) { // платный
            return $d;
        }
        $maxDays = self::$maxDays[$service['country_id']];
        $p = $d['days'] / ($maxDays / 100);
        if ($d['days'] >= $maxDays) {
            $d['color'] = 'brown';
            $d['time_expired_flag'] = 1;
        } elseif ($p >= 85) {
            $d['color'] = 'red';
        } elseif ($p >= 75) {
            $d['color'] = 'orange';
        } elseif ($p >= 60) {
            $d['color'] = 'yellow';
        }
        return $d;
    }


    private static function getWorkCost(array $row, $ft)
    {
        if (User::hasRole('master')) {
            return Staff::getWorkCost($row['master_user_id'], $row['total_price']);
        }
        return $ft($row['total_price']);
    }


    private static function getControls(array $row, $countryID)
    {
        $c = self::$controls;
        if (self::$curTab == 'deleted' || ($row['status'] != 'Принят' && self::$userRole == 'taker')) {
            unset($c['del']);
        }
        if (isset($c['read'])) {
            $c['read']['url'] = $c['unread']['url'] = '/edit-repair/' . $row['id'] . '/step/6/?from=questions';
            if (self::$userRole == 'service') {
                if ($row['is_unread_service']) {
                    unset($c['read']);
                } else {
                    unset($c['unread']);
                }
            } else {
                if ($row['is_unread_admin']) {
                    unset($c['read']);
                } else {
                    unset($c['unread']);
                }
            }
        }
        if (isset($c['consumer'])) {
            if ($row['client_type'] == 2) { // магазин
                unset($c['consumer']);
            } elseif ($row['client_type'] == 1) { // потребитель
                unset($c['shop']);
            } else {
                unset($c['consumer']);
                unset($c['shop']);
            }
        }
        if (isset($c['recover'])) {
            $c['recover']['url'] = '/comeback-repair/' . $row['id'] . '/';
        }
        if (isset($c['outside'])) {
            if (!$row['onway']) {
                unset($c['outside']);
            } elseif ($row['service_id'] == 33) { // ИП Кулиджанов
                $c['outside']['url'] = '/get-receipt-outside/?repair-id=' . $row['id'];
            }
        }
        if (isset($c['del'])) {
            if (self::$userRole == 'service' && !in_array(self::$curTab, ['accepted', 'inprogress', 'cancelled']) && !in_array($row['status'], ['В работе', 'Принят'])) {
                unset($c['del']);
            }
        }
        if (isset($c['appoint_master'])) {
            if (self::$userRole == 'slave-admin' && (in_array($row['status'], ['Выдан', 'Подтвержден', 'Отклонен']) || in_array(self::$curTab, ['markdown', 'disposed']))) {
                unset($c['appoint_master']);
            } elseif (self::$userRole == 'taker' && !in_array($row['status'], ['Принят', 'В работе', 'Выезд отклонен', 'Выезд подтвержден', 'Запрос на выезд'])) {
                unset($c['appoint_master']);
            }
        }
        if (isset($c['refusal']) && $row['refuse_doc_flag'] != 'y') {
            unset($c['refusal']);
        }
        if (isset($c['back'])) { // вернуть в работу
            if (!in_array($row['status'], ['Выдан', 'Подтвержден', 'Утилизирован'])) {
                unset($c['back']);
            } else {
                if (self::$userRole == 'taker') {
                    if (!in_array(self::$curTab, ['ready', 'issued'])) {
                        unset($c['back']);
                    }
                    if (date('my') != date('my', strtotime($row['approve_date']))) {
                        unset($c['back']);
                    }
                } else {
                    if (!in_array(self::$curTab, ['ready', 'issued', 'markdown', 'disposed'])) {
                        unset($c['back']);
                    }
                }
            }
        }
        if (isset($c['country'])) {
            if (isset(self::$countries[$countryID])) {
                $c['country']['name'] = self::$countries[$countryID];
            } else {
                unset($c['country']);
            }
        }
        if (isset($c['edit'])) {
            $from = '?from=' . self::$curTab;
            $editURL = ''; // вкладка "Приемка"
            switch ($row['status']) {
                case 'Подтвержден':
                    $editURL = 'step/5/'; // вкладка "Акты"
                    break;

                case 'Отклонен':
                case 'Есть вопросы':
                    $editURL = 'step/6/'; // вкладка "Поддержка"
                    break;

                case 'Одобрен акт':
                case 'В работе':
                    $editURL = 'step/2/'; // вкладка "Ремонт"
                    break;

                case 'В обработке':
                case 'Нужны запчасти':
                case 'Запчасти в пути':
                case 'Запрос у Tesler':
                case 'Ждем з/ч Tesler':
                case 'Запрос у Roch':
                case 'Ждем з/ч Roch':
                case 'Заказ на заводе':
                    $editURL = 'step/3/'; // вкладка "Запчасти"
                    break;
            }
            $c['edit']['url'] = '/edit-repair/' . $row['id'] . '/' . $editURL . $from;
        }
        if (isset($c['label'])) {
            $c['label']['url'] = '/get-label/' . $row['id'] . '/';
        }
        if (isset($c['receipt'])) {
            $c['receipt']['url'] = '/get-receipt/' . $row['id'] . '/';
        }
        if (isset($c['back'])) {
            $c['back']['url'] = '/re-edit-repair/' . $row['id'] . '/';
        }
        if (!$row['repeated_flag']) {
            unset($c['repeated']);
        }
        return $c;
    }


    private static function getRowColor(array $row)
    {
        if (self::$userRole == 'service') {
            if (Counters::have('approved', $row['id'], User::getData('id'))) {
                return 'blue';
            }
        }
        if ($row['model'] == '!!!') { // модель загружена из Excel, но в базе не найдена
            return 'darkblue';
        }
        if ($row['parts_cost'] != '0,00') { // Есть запчасть СЦ
            return 'brown';
        }
        if ($row['recept_status_id'] == 6) { // Платный
            return 'darkgreen';
        }
        if ($row['recept_status_id'] == 1) { // Гарантийный
            if (empty($row['serial']) || empty($row['model_id']) || empty($row['client_defect']) || (empty($row['visual']) && empty($row['visual_comment'])) || empty($row['complex']) || empty($row['refuse_doc_flag']) || ($row['client_type'] == 1 && empty(trim($row['shop_name'])))) {
                return 'yellow';
            }
        }
        if (!empty($row['serial_invalid_flag'])) {
            return 'purple';
        }
        if ($row['anrp_use'] && $row['anrp_number']) {
            return 'blue';
        }
        if ($row['repeated_flag']) {
            return 'red';
        }
        if ($row['status_by_hand'] == 1 || $row['recept_status_id'] == 5) { // Условно-гарантийный
            return 'gray';
        }
        return 'no-color';
    }


    private static function getGroupName(array $row, array $return, array $client)
    {
        $name = [];
        if ($row['client_type'] == 1) {
            $name[] = $row['client_name'];
            $name[] = $row['shop_name'];
        } else {
            $name[] = (!empty($client['name'])) ? $client['name'] : $row['shop_name'];
            $name[] = $return['name'];
        }
        $name = array_filter($name);
        if ($row['till_date']) {
            $name[] = 'Закончить до ' . $row['till_date'];
        }
        if (!$name) {
            return '-';
        }
        return implode(' / ', $name);
    }


    private static function getTillDate($receiveDate, $days)
    {
        if (Time::isEmpty($receiveDate)) {
            return '';
        }
        $days = ($days) ? $days : 45;
        return date('d.m.Y', strtotime($receiveDate . ' + ' . $days . ' days'));
    }


    private static function getMaster($masterUserID, $masterID, $importedFlag = false)
    {
        if ($masterUserID) {
            $master = Staff::getStaff(['id' => $masterUserID]);
        } else {
            $rows = self::$db->exec('SELECT `name`, `surname`, `third_name` AS thirdname FROM `repairmans` WHERE `id` = ' . $masterID);
            $master = $rows ? $rows[0] : [];
        }
        if (!$master) {
            if ($importedFlag) {
                return 'Без мастера';
            }
            return '';
        }
        return $master['surname'] . ' ' . $master['name'] . ' ' . $master['thirdname'];
    }


    private static function getService($serviceID)
    {
        if (!$serviceID) {
            return ['name' => '', 'address' => '', 'country_id' => 0];
        }
        if (!isset(self::$cache['services'][$serviceID])) {
            $rows = self::$db->exec('SELECT `name`, `phisical_adress` AS address, `country` AS country_id FROM `requests` WHERE `user_id` = ' . $serviceID);
            if ($rows) {
                self::$cache['services'][$serviceID] = $rows[0];
            } else {
                self::$cache['services'][$serviceID] = ['name' => '', 'address' => '', 'country_id' => 0];
            }
        }
        return self::$cache['services'][$serviceID];
    }


    private static function getClient($clientID)
    {
        if (!$clientID) {
            return ['name' => '', 'days' => 0];
        }
        if (!isset(self::$cache['clients'][$clientID])) {
            $rows = self::$db->exec('SELECT `name`, `days` FROM `clients` WHERE `id` = ' . $clientID);
            if (!$rows) {
                self::$cache['clients'][$clientID] = ['name' => '(не найден)', 'days' => 0];
            } else {
                self::$cache['clients'][$clientID] = $rows[0];
            }
        }
        return self::$cache['clients'][$clientID];
    }


    private static function getReturn($returnID)
    {
        if (!isset(self::$cache['returns'][$returnID])) {
            $rows = self::$db->exec('SELECT `name`, `date_farewell` AS out_date FROM `returns` WHERE `id` = ' . $returnID);
            if (!$rows) {
                self::$cache['returns'][$returnID] = ['name' => '', 'out_date' => ''];
            } else {
                self::$cache['returns'][$returnID] = $rows[0];
            }
        }
        return self::$cache['returns'][$returnID];
    }


    private static function getModel($modelID)
    {
        if (!isset(self::$cache['models'][$modelID])) {
            $rows = self::$db->exec('SELECT `id`, `name`, `cat`, `warranty` FROM `models` WHERE `id` = ' . $modelID);
            if (!$rows) {
                self::$cache['models'][$modelID] = ['id' => 0, 'name' => '', 'cat' => 0, 'warranty' => 0];
            } else {
                self::$cache['models'][$modelID] = $rows[0];
            }
        }
        return self::$cache['models'][$modelID];
    }


    public static function getPagination()
    {
        return DBExtractor::$paginator->getPagination();
    }


    public static function getCnt()
    {
        return DBExtractor::getCnt();
    }


    public static function getNumPerPage()
    {
        return 80;
    }
}


Data::init();
