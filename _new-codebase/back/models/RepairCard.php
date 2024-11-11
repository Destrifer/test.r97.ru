<?php

namespace models;

use models\parts\Order;
use program\core;

/** 
 * v. 0.1
 * 2020-07-01
 */

class RepairCard extends _Model
{
    private static $db = null;
    private static $steps = [
        'check' => ['name' => 'Проверка', 'url' => '/'],
        'acceptance' => ['name' => 'Приемка', 'url' => '/'],
        'photos' => ['name' => 'Фото и видео', 'url' => '/step/4/'],
        'repair' => ['name' => 'Ремонт', 'url' => '/step/2/'],
        'parts' => ['name' => 'Запчасти', 'url' => '/step/3/'],
        'software' => ['name' => 'Схемы и ПО', 'url' => '/step/software/'],
        'docs' => ['name' => 'Акты', 'url' => '/step/5/'],
        'support' => ['name' => 'Поддержка', 'url' => '/step/6/']
    ];

    public static function init()
    {
        self::$db = _Base::getDB();
    }

    public static function getStepsNav($repairID, $curURI)
    {
        $steps = self::$steps;
        if ($repairID) {
            /* Счетчик фото */
            $cnt = '(0/0)';
            $messagesCnt = Support::getMessagesCnt($repairID);
            if ($messagesCnt) {
                $unreadCnt = Support::getUnreadCnt($repairID);
                if ($unreadCnt) {
                    $cnt = '(<b>' . $unreadCnt . '</b>/' . $messagesCnt . ')';
                } else {
                    $cnt = '(0/' . $messagesCnt . ')';
                }
            }
            $steps['support']['name'] .= ' ' . $cnt;
            $photosCnt = Photos::getPhotosCnt($repairID);
            if ($photosCnt) {
                $steps['photos']['name'] .= ' (' . $photosCnt . ')';
            }
            $repair = Repair::getRepairByID($repairID);
            $serial = Serials::getSerial($repair['serial'], $repair['model_id']);
            /* Счетчик запчастей */
            $partsCnt = 0;
            if ($serial['id']) {
                if (!isset($_SESSION['parts_cnt_' . $repairID])) {
                    $_SESSION['parts_cnt_' . $repairID] = Parts::getPartsCnt($repair['model_id'], $serial['serial']);
                }
                $partsCnt = $_SESSION['parts_cnt_' . $repairID];
            }
            $ordPartsCnt = Order::getPartsCnt($repairID);
            if ($partsCnt || $ordPartsCnt) {
                $steps['parts']['name'] .= ' (' . $ordPartsCnt . '/' . $partsCnt . ')';
            }
            /* Счетчик файлов "Схемы и ПО" */
            $softwareCnt = 0;
            if ($serial['id']) { // если номер ввели, то запомнить
                if (!isset($_SESSION['software_cnt_' . $repairID])) {
                    $_SESSION['software_cnt_' . $repairID] = Infobase::getFilesCnt($serial['id']);
                }
                $softwareCnt = $_SESSION['software_cnt_' . $repairID];
            }
            $steps['software']['name'] .= ' (' . $softwareCnt . ')';
        }
        $ret = [];
        foreach ($steps as $uri => $step) {
            $step['url'] = ($repairID) ? '/edit-repair/' . $repairID . $step['url'] : '#';
            $step['cur_flag'] = ($curURI != $uri) ? false : true;
            $ret[] = $step;
        }
        return $ret;
    }


    public static function getAsideControls($repairID)
    {
        $repair = Repair::getRepairByID($repairID);
        if (in_array($repair['status'], ['Подтвержден', 'Отклонен', 'Выдан'])) {
            return [];
        }
        if (User::hasRole('service')) {
            if (in_array($repair['status'], ['На проверке', 'Запрос на демонтаж', 'Запрос на монтаж'])) {
                return [];
            }
            return ['check', 'question'];
        }
        if (User::hasRole('admin')) {
            return ['approve', 'question'];
        }
        if (User::hasRole('master')) {
            return ['close'];
        }
        if (User::hasRole('slave-admin', 'taker')) {
            return ['approve'];
        }
        return [];
    }


    public static function getSummary($repairID)
    {
        $repair = [];
        $model = [];
        $client = [];
        $service = [];
        $statuses = [];
        $rows = self::$db->exec('SELECT 
        rep.`id`, rep.`status_admin`, rep.`approve_date`, rep.`service_id`, rep.`create_date`, rep.`client`, rep.`client_id`, rep.`client_type`, rep.`address`, 
        rep.`phone`, rep.`name_shop`, rep.`phone_shop`, rep.`address_shop`, rep.`bugs`, rep.`model_id`, rep.`serial`, rep.`has_questions`    
        FROM `repairs` rep WHERE rep.`id` = ?', [$repairID]);
        $repair = $rows[0];

        if ($repair['model_id']) {
            $rows = self::$db->exec('SELECT `name` FROM `models` WHERE `id` = ?', [$repair['model_id']]);
            if (isset($rows[0])) {
                $model = $rows[0];
            }
        }

        if ($repair['client_id']) {
            $rows = self::$db->exec('SELECT `name`, `address`, `phone`, `contact_name` FROM `clients` WHERE `id` = ?', [$repair['client_id']]);
            if (isset($rows[0])) {
                $client['name'] = (!empty($rows[0]['name'])) ? $rows[0]['name'] : $rows[0]['contact_name'];
                $client['address'] = $rows[0]['address'];
                $client['phone'] = $rows[0]['phone'];
            }
        }

        if (!$client) {
            if ($repair['client_type'] == 1) {
                $client['name'] = $repair['client'];
                $client['address'] = $repair['address'];
                $client['phone'] = $repair['phone'];
            } else {
                $client['name'] = $repair['name_shop'];
                $client['address'] = $repair['address_shop'];
                $client['phone'] = $repair['phone_shop'];
            }
        }

        $rows = self::$db->exec('SELECT `name`, `phisical_adress` FROM `requests` WHERE `user_id` = ?', [$repair['service_id']]);
        $service = $rows[0];

        if (User::hasRole('admin', 'slave-admin')) {
            $rows = ParamsDict::getParamsBySectionID(1);
            foreach ($rows as $row) {
                /* Временно убрать статус из списка */
                if ($row['name'] == 'Одобрен акт' && in_array($repair['status_admin'], ['Нужны запчасти', 'Запрос у Tesler', 'В обработке', 'Заказ на заводе', 'Ждем з/ч Tesler'])) {
                    continue;
                }
                if (!User::hasRole('admin') && in_array($row['name'], ['Заказ на заводе', 'Запрос у Tesler', 'Запрос ПО'])) {
                    continue;
                }
                $statuses[] = $row['name'];
            }
        }
        if (mb_strlen($repair['bugs']) > 100) {
            $repair['bugs'] = mb_substr($repair['bugs'], 0, 100) . '...';
        }
        return [
            'repair_id' => $repair['id'],
            'status' => $repair['status_admin'],
            'approve_date' => ($repair['approve_date'] == '0000-00-00') ? '' : core\Time::format($repair['approve_date']),
            'receive_date' => core\Time::format($repair['create_date']),
            'service_name' => htmlspecialchars($service['name']),
            'service_address' => $service['phisical_adress'],
            'client_name' => htmlspecialchars($client['name']),
            'client_address' => $client['address'],
            'client_phone' => $client['phone'],
            'shop_name' => '',
            'shop_phone' => '',
            'model_name' => $model['name'],
            'serial' => $repair['serial'],
            'defect_client' => $repair['bugs'],
            'statuses' => $statuses,
            'has_questions' => $repair['has_questions']
        ];
    }

    public static function getMaster($masterID)
    {
        $rows = self::$db->exec('SELECT * FROM `repairmans` WHERE `id` = ?', [$masterID]);
        if (!$rows) {
            return '';
        }
        return $rows[0]['surname'] . ' ' . $rows[0]['name'] . ' ' . $rows[0]['third_name'];
    }
}


RepairCard::init();
