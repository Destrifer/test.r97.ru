<?php

namespace models\repair;

use models\Params;
use models\parts\Order;
use models\Repair;
use models\repaircard\Photos;
use models\User;

class Check extends \models\_Model
{

    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function hasCommonErrors($repairID)
    {
        $repair = Repair::getRepairByID($repairID);
        $res = ['message' => '', 'url' => '', 'error_flag' => 0];
        if (in_array($repair['status'], ['На проверке', 'Запрос на демонтаж', 'Запрос на монтаж', 'Запрос на выезд', 'Подтвержден', 'Отклонен', 'Выдан'])) {
            $notice = '';
            if (in_array($repair['status'], ['Подтвержден', 'Отклонен', 'Выдан'])) {
                $notice = 'Ремонт уже завершен.';
            } else if (in_array($repair['status'], ['На проверке', 'Запрос на демонтаж', 'Запрос на монтаж', 'Запрос на выезд'])) {
                $notice = 'Пожалуйста, дождитесь проверки администратором.';
            }
            return ['message' => 'В статусе "' . $repair['status'] . '" отправка невозможна. ' . $notice, 'url' => '', 'error_flag' => 1];
        }
        return $res;
    }


    public static function hasPartsErrors($repairID)
    {
        $res = ['message' => '', 'url' => '', 'error_flag' => 0];
        if (Order::allOrdersAreReceived($repairID)) {
            return $res;
        }
        return ['message' => 'Пожалуйста, получите запчасти перед отправкой ремонта.', 'url' => '/edit-repair/' . $repairID . '/step/3/', 'error_flag' => 1];
    }


    public static function hasFillErrors($repairID)
    {
        $res = ['message' => '', 'url' => '', 'error_flag' => 0];
        $repair = Repair::getRepairByID($repairID);
        if (!$repair) {
            return $res;
        }
        if (empty($repair['model_id']) || empty($repair['client_type'])) {
            return ['message' => 'Не заполнены данные приемки: модель, тип клиента.', 'url' => '/edit-repair/' . $repairID . '/', 'error_flag' => 1];
        }
        if ((empty($repair['master_id']) && empty($repair['master_user_id'])) || empty($repair['bugs']) || empty($repair['disease'])) {
            return ['message' => 'Не заполнены данные на вкладке Ремонт.', 'url' => '/edit-repair/' . $repairID . '/step/2/?errors=1', 'error_flag' => 1];
        }
        $work = self::$db->exec('SELECT `name`, `problem_id`, `repair_type_id`, `part_id` FROM `' . Work::TABLE . '` WHERE `repair_id` = ?', [$repairID]);
        if (!$work) {
            return ['message' => 'Не заполнена проделанная работа на вкладке Ремонт.', 'url' => '/edit-repair/' . $repairID . '/step/2/?errors=1', 'error_flag' => 1];
        }
        foreach ($work as $w) {
            if (empty($w['problem_id']) || empty($w['repair_type_id']) || empty($w['part_id'])) {
                return ['message' => 'Не заполнены данные проделанной работы на вкладке Ремонт.', 'url' => '/edit-repair/' . $repairID . '/step/2/?errors=1', 'error_flag' => 1];
            }
        }
        if (!User::hasRole('admin') && !empty($repair['attention_flag'])) {
            return ['message' => 'Ремонт с уведомлением, обратитесь к админу!', 'url' => '/edit-repair/' . $repairID . '/step/6/', 'error_flag' => 1];
        }
        return $res;
    }


    /* Проверка фото */
    public static function hasPhotoErrors($repairID)
    {
        $res = ['message' => '', 'url' => '', 'error_flag' => 0, 'errors' => []];
        $repair = Repair::getRepairByID($repairID);
        /* Подготовка информации */
        if (!$repair) {
            return $res;
        }
        $rowsC = self::$db->exec('SELECT `name` FROM `cats_users` WHERE `cat_id` = ? AND `service_id` = ?', [$repair['cat_id'], $repair['service_id']]);
        if (!$rowsC) {
            $rowsC = self::$db->exec('SELECT `name` FROM `cats` WHERE `id` = ?', [$repair['cat_id']]);
        }
        $repair['cat_name'] = (isset($rowsC[0]['name'])) ? $rowsC[0]['name'] : '';
        $tvCatFlag = (mb_strpos($repair['cat_name'], 'Телевизор') !== false);
        /* У мастеров с заполненным АНРП, либо платным ремонтом не проверяется */
        if ((User::hasRole('master') && !empty($repair['anrp_number'])) || $repair['status_id'] == 6) { // платный
            return $res;
        }
        /* Подготовка информации */
        $rows = self::$db->exec('SELECT `url`, `photo_id`, `no_photo_flag` FROM `repairs_photo` WHERE `repair_id` = ?', [$repairID]);
        $photos = array_column($rows, 'url', 'photo_id');
        $noPhoto = array_column($rows, 'no_photo_flag', 'photo_id'); // отметка о том, что фото отсутствует у СЦ
        $photosTypes = Photos::getPhotosTypes();
        $rows = self::$db->exec('SELECT `problem_id` FROM `repairs_work` WHERE `repair_id` = ?', [$repairID]);
        $p = (!$rows) ? [] : array_column($rows, 'problem_id');
        $smartTVFlag = in_array(60, $p); // Если "(РЕМОНТ) Ошибка ПО на smart TV или smart box - (прошивка с флешки)"
        /* У мастеров проверяется только при списании ТВ */
        if (User::hasRole('slave-admin', 'master')) {
            if (!$tvCatFlag || $repair['repair_type_id'] != 4 || $smartTVFlag) {
                return $res;
            }
        }
        /* Фото дефекта категории ТВ обязательно (кроме мастеров) */
        if ($tvCatFlag && empty($photos[3]) && !User::hasRole('slave-admin', 'master') && !in_array(5, $p)) { // дефект отсутствует
            $res['error_flag'] = 1;
            $res['errors'][] = $photosTypes[3];
            $res['message'] = 'Пожалуйста, загрузите необходимые фото.';
            $res['url'] = '/edit-repair/' . $repairID . '/step/4/#error';
            return $res;
        }
        if ($smartTVFlag) { // 
            $paramID = 1; // Ремонт и тестирование, вся техника
        } else {
            /* Если АНРП */
            if ($repair['repair_type_id'] == 4) {
                /* Если ТВ */
                if ($tvCatFlag) {
                    $paramID = 2; // Без ремонта (АНРП), ТВ
                } else {
                    $paramID = 3; // Без ремонта (АНРП), кроме ТВ
                }
            }
            /* Если АТО */ elseif ($repair['repair_type_id'] == 5) { // АТО
                $paramID = 4; // Без ремонта (АТО), вся техника
            } else {
                $paramID = 1; // Ремонт и тестирование, вся техника
            }
        }
        $params = Params::getParams('filling_rules', $paramID);
        /* Проверка на наличие фото документов */
        if (Repair::hasOwnParts($repairID)) {
            if (!isset($photos[11])) {
                $res['errors'][] =  'Фото собственной запчасти';
            }
            if (!isset($photos[12])) {
                $res['errors'][] =  'Фото чека на покупку запчасти';
            }
        }
        if ($repair['refuse_doc_flag'] == 'y' && !isset($photos[8])) {
            $res['errors'][] = 'Фото заявления об отказе от гарантийного ремонта';
        }
        if ($repair['talon'] == 'Гарантийный талон+Чек' && (!isset($photos[6]) && !isset($photos[7]))) {
            $res['errors'][] = 'Фото кассового чека и гар. талона';
        } elseif (preg_match('/ККЧ|КЧ|Чек/iu', $repair['talon'])) {
            if (!isset($photos[6])) {
                $res['errors'][] = 'Фото кассового чека (фото гар. талона желательно)';
            }
            unset($params[7]);
        } elseif ($repair['talon'] == 'Гарантийный талон') {
            if (!isset($photos[7])) {
                $res['errors'][] = 'Фото гар. талона (фото чека желательно)';
            }
            unset($params[6]);
        } elseif ($repair['talon'] == 'Документы отсутствуют') {
            unset($params[6]);
            unset($params[7]);
        }
        if (!empty($repair['serial']) && !isset($photos[1])) {
            $res['errors'][] = 'Фото шильдика с серийным номером';
        } else {
            unset($params[1]);
        }
        if (!$res['errors']) {
            /* Проверка по параметрам, установленным вручную в панели управления /filling-rules/ */
            foreach ($params as $k => $_) {
                if (empty($photos[$k]) && empty($noPhoto[$k])) {
                    $res['errors'][] = $photosTypes[$k];
                }
            }
        }
        if ($res['errors']) {
            $res['error_flag'] = 1;
            $res['message'] = 'Пожалуйста, загрузите необходимые фото.';
            $res['url'] = '/edit-repair/' . $repairID . '/step/4/#error';
        }
        return $res;
    }
}

Check::init();
