<?php

namespace models\parts;

use models\Parts;
use models\Repair;
use models\Serials;
use models\Services;
use models\Support;
use models\transportcompanies\Company;
use models\User;
use program\adapters\DigitalOcean;
use program\core\RowSet;
use program\core\Time;

class Order extends \models\_Model
{

    const TABLE = 'orders';
    const TABLE_PARTS = 'orders_parts';
    const TABLE_EXTRA = 'orders_extra';
    const TABLE_MANUAL = 'orders_manual';
    const STORE_PART = 1;
    const MANUAL_PART = 2;
    private static $db = null;
    private static $statuses = [
        0 => '',
        1 => 'проверка админа',
        2 => 'в обработке',
        3 => 'запчасти отправлены',
        4 => 'получен',
        5 => 'одобрен акт'
    ];
    const NO_STATUS = 0;
    const SERVICE_SENT = 1;
    const ADMIN_CHECKED = 2;
    const STORE_SENT = 3;
    const SERVICE_RECEIVED = 4;
    const CANCELED = 5;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function allOrdersAreReceived($repairID)
    {
        $rows = self::$db->exec('SELECT `status_id` FROM `' . self::TABLE . '` WHERE `repair_id` = ' . $repairID);
        if (!$rows) {
            return true;
        }
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if (!in_array($rows[$i]['status_id'], [self::NO_STATUS, self::CANCELED, self::SERVICE_RECEIVED])) {
                return false;
            }
        }
        return true;
    }


    public static function getOrdersByRepairID($repairID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `repair_id` = ? ORDER BY `id` DESC', [$repairID]);
        if (!$rows) {
            return [];
        }
        $s = Services::getServiceByID($rows[0]['service_id']);
        $serviceName = ($s) ? $s['name'] : '(не найден)';
        $res = [];
        foreach ($rows as $row) {
            $order = self::handleOrder($row);
            $order['service'] = $serviceName;
            $order['parts'] = [];
            $parts = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ?', [$order['id']]);
            $extra = self::getExtra($repairID);
            foreach ($parts as $n => $part) {
                if ($part['origin_id'] == self::STORE_PART) { // запчасть со склада
                    $order['parts'][$n] = self::getStorePart($part['part_id'], $part['depot_id'], $part['qty'], $part['return_flag'], $part['cancel_flag'], $part['receive_flag'], $part['alt_flag']);
                    $order['parts'][$n]['extra_data'] = (isset($extra[$part['part_id']])) ? $extra[$part['part_id']] : [];
                } else { // запчасть вручную
                    $order['parts'][$n] = self::getManualPart($part['part_id'], $part['qty']);
                }
                $order['parts'][$n]['cancel_flag'] = $part['cancel_flag'];
            }
            if (!empty($order['initial_parts']) && User::hasRole('admin', 'store')) {
                $parts = json_decode($order['initial_parts'], true);
                $order['initial_parts'] = [];
                foreach ($parts as $n => $part) {
                    if ($part['origin_id'] == self::STORE_PART) { // запчасть со склада
                        $order['initial_parts'][$n] = self::getStorePart($part['part_id'], $part['depot_id'], $part['num']);
                        $order['initial_parts'][$n]['extra_data'] = (isset($extra[$part['part_id']])) ? $extra[$part['part_id']] : [];
                    } else { // запчасть вручную
                        $order['initial_parts'][$n] = self::getManualPart($part['part_id'], $part['num']);
                    }
                }
            } else {
                $order['initial_parts'] = [];
            }
            if (empty($order['parts']) && empty($order['initial_parts']) && $order['create_date'] != date('d.m.Y')) {
                self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `id` = ' . $order['id']);
                continue;
            }
            $res[] = $order;
        }
        return $res;
    }


    /**
     * Заменяет запчасть в заказе на такую же с другого склада
     * 
     * @param int $orderID Заказ
     * @param int $partID Запчасть
     * @param int $newDepotID Новый склад
     * @param int $num Количество запчасти
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function replaceDepot($orderID, $partID, $newDepotID, $num)
    {
        if (!Balance::isEnough($newDepotID, $partID, $num)) {
            return ['message' => 'Запчасти на данном складе недостаточно.', 'error_flag' => 1];
        }
        $r = self::$db->exec('UPDATE `' . self::TABLE_PARTS . '` SET `depot_id` = ?, `qty` = ? WHERE `order_id` = ? AND `part_id` = ? AND `origin_id` = ?', [$newDepotID, $num, $orderID, $partID, self::STORE_PART]);
        if ($r) {
            return ['message' => '', 'error_flag' => 0];
        }
        return ['message' => 'Произошла ошибка во время замены запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
    }


    public static function isNewOrderAllowed($repairID)
    {
        $repair = Repair::getRepairByID($repairID);
        if (User::hasRole('admin', 'store') && !in_array($repair['status'], ['Запрос у Tesler', 'Ждем з/ч Tesler', 'В обработке', 'Нужны запчасти', 'Запчасти в пути', 'Отклонен', 'Подтвержден'])) {
            return true;
        }
        if (User::hasRole('service', 'master') && !in_array($repair['status'], ['Нужны запчасти', 'Отклонен', 'Подтвержден', 'Подтвержден', 'Запрос на монтаж', 'Запрос на демонтаж', 'Запрос на выезд'])) {
            $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `repair_id` = ' . $repairID . ' AND `status_id` = ' . self::NO_STATUS);
            if (!$rows) {
                return true;
            }
            return false;
        }
        return false;
    }


    public static function saveManualPart(array $data)
    {
        if (empty($data['photo_path'])) {
            return ['message' => 'Пожалуйста, загрузите фото.', 'error_flag' => 1];
        }
        if (empty($data['comment'])) {
            return ['message' => 'Пожалуйста, введите описание.', 'error_flag' => 1];
        }
        $filename =  date('d-') . rand(1, 99999999);
        $p = pathinfo($data['photo_path']);
        $ext = mb_strtolower($p['extension']);
        $path = DigitalOcean::uploadFile($data['photo_path'], 'uploads/orders/manual/' . date('Y-m') . '/' . $filename . '.' . $ext);
        $partID = self::$db->exec(
            'INSERT INTO `' . self::TABLE_MANUAL . '` 
        (`repair_id`, `comment`, `photo_path`) VALUES (?, ?, ?)',
            [$data['repair_id'], trim($data['comment']), $path]
        );
        if (!$partID) {
            return ['message' => 'Во время сохранения произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['part_id' => $partID, 'message' => '', 'error_flag' => 0];
    }


    /**
     * Получает запчасти с собственных складов
     * 
     * @param array $data Данные из формы заказа
     * 
     * @return array Сообщение и флаг ошибка
     */
    public static function takeParts(array $data)
    {
        if (empty($data['parts']['store'])) {
            return ['message' => 'Пожалуйста, выберите запчасти со склада.', 'error_flag' => 1];
        }
        $userRole = User::getData('role');
        self::$db->transact('begin');
        $repair = Repair::getRepairByID($data['repair_id']);
        foreach ($data['parts']['store'] as $part) {
            if ($part['depot_id'] == Parts::MAIN_DEPOT_ID && $userRole == 'service') {
                return ['message' => 'Запчасти должны быть только с собственного склада.', 'error_flag' => 1];
            }
            $r = Log::take($part['id'], $part['depot_id'], $part['qty'], Balance::count($part['id'], $part['depot_id']), $repair['id'], $repair['model_id']);
            if (!$r) {
                return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        $res = self::takePartsFromDepot($data['parts']['store']);
        if (!empty($res['error_flag'])) {
            return $res;
        }
        /* Привязать запчасти к моделям */
        /* if (!empty($repair['serial']) && $userRole == 'master') {
            $serial = Serials::getSerial($repair['serial'], $repair['model_id']);
            foreach ($data['parts']['store'] as $part) {
                Parts::bindToModel($part['id'], $repair['model_id'], $serial['serial']);
            }
        } */
        $date = date('Y-m-d H:i:s');
        $orderID = self::$db->exec('INSERT INTO `' . self::TABLE . '` (`service_id`, `repair_id`, `status_id`, `approve_date`, `send_date`, `receive_date`) VALUES (?, ?, ?, ?, ?, ?)', [$data['service_id'], $data['repair_id'], 4, $date, $date, $date]);
        if (!$orderID) {
            return ['message' => 'Во время сохранения заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = self::saveOrderParts($data['parts'], $orderID);
        if (!$r) {
            return ['message' => 'Во время сохранения запчастей произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = self::applyPartsToRepair($data['repair_id']);
        if (!$r) {
            return ['message' => 'Во время обновления ремонта произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        \models\Log::repair(11, 'Заказ #' . $orderID . ' получен с собственного склада.', $data['repair_id']);
        self::$db->transact('commit');
        return ['message' => '', 'error_flag' => 0];
    }


    public static function getParts(array $filter)
    {
        $where = [];
        if (!empty($filter['repair_id'])) {
            $order = self::getOrder(0, $filter['repair_id']);
            if ($order) {
                $where[] = '`order_id` = ' . $order['id'];
            }
        }
        if (!empty($filter['origin_id'])) {
            $where[] = '`origin_id` = ' . $filter['origin_id'];
        }
        $whereSQL = '';
        if ($where) {
            $whereSQL = 'WHERE ' . implode(' AND ', $where);
        }
        return self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` ' . $whereSQL);
    }


    /**
     * Возвращает кол-во заказанных в ремонте запчастей
     * 
     * @param int $repairID Ремонт
     * 
     * @return int Кол-во запчастей со всех заказов
     */
    public static function getPartsCnt($repairID)
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . self::TABLE_PARTS . '` 
        WHERE `order_id` IN (SELECT `id` FROM `' . self::TABLE . '` WHERE `repair_id` = ' . $repairID . ')');
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    /**
     * Получает доп. информацию об общих запчастях
     * 
     * @param int $repairID Ремонт
     * 
     * @return array Массив с ключами part_id
     */
    private static function getExtra($repairID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_EXTRA . '` WHERE `repair_id` = ?', [$repairID]);
        if (!$rows) {
            return [];
        }
        return RowSet::orderBy('part_id', $rows);
    }


    /**
     * Сохраняет дополнительную информацию от СЦ об общих запчастях
     * 
     * @param array $parts Список запчастей с комментариями и фото
     * @param array $repairID Ремонт
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function saveExtraInfo(array $parts, $repairID)
    {
        foreach ($parts as $partID => $data) {
            if (empty($data['photo_path'])) {
                return ['message' => 'Пожалуйста, загрузите фото.', 'error_flag' => 1];
            }
            if (empty($data['comment'])) {
                return ['message' => 'Пожалуйста, введите описание.', 'error_flag' => 1];
            }
            $filename =  date('d-') . rand(1, 99999999);
            $p = pathinfo($data['photo_path']);
            $ext = mb_strtolower($p['extension']);
            $path = DigitalOcean::uploadFile($data['photo_path'], 'uploads/orders/extra/' . date('Y-m') . '/' . $filename . '.' . $ext);
            self::$db->exec(
                'INSERT INTO `' . self::TABLE_EXTRA . '` 
            (`part_id`, `repair_id`, `comment`, `photo_path`) VALUES (?, ?, ?, ?)',
                [$partID, $repairID, trim($data['comment']), $path]
            );
        }
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Отменяет заказ
     * 
     * @param int $orderID Заказ
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function cancelOrder(array $data)
    {
        $order = self::getOrder($data['order_id']);
        if (!$order) {
            return ['message' => 'Заказ не найден.', 'error_flag' => 1];
        }
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `status_id` = ' . self::CANCELED . ', `cancel_date` = ?, `send_date` = "0000-00-00 00:00:00", `transport_company_id` = 0, `track_num` = "" WHERE `id` = ?', [date('Y-m-d H:i:s'), $data['order_id']]);
        if (!$r) {
            return ['message' => 'К сожалению, произошла ошибка при сохранении: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = self::saveOrderParts($data['parts'], $data['order_id']);
        if (!$r) {
            return ['message' => 'Во время сохранения запчастей произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        Repair::changeStatus($order['repair_id'], 'Одобрен акт');
        $repair = Repair::getRepairByID($order['repair_id']);
        if ($repair['status'] != 'Одобрен акт') {
            \models\Log::repair(1, '"' . $repair['status'] . '" на "Одобрен акт", при отмене заказа запчастей #' . $data['order_id'] . '.', $order['repair_id']);
        }
        $r = self::applyPartsToRepair($order['repair_id'], true);
        if (!$r) {
            return ['message' => 'Во время обновления ремонта произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        // добавить запчасти на склад СЦ
        if (!empty($data['parts']['store']) && in_array($order['status_id'], [self::STORE_SENT, self::SERVICE_RECEIVED])) {
            $depot = Depots::getOrCreateDepot($repair['service_id']);
            $parts = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ? AND `cancel_flag` = 0 AND `origin_id` = ' . self::STORE_PART, [$order['id']]);
            foreach ($parts as $part) {
                $r = Balance::add($part['part_id'], $part['qty'], $depot['id']);
                if (!$r) {
                    return ['message' => 'Во время добавления запчастей произошла ошибка: ' . Balance::$error, 'error_flag' => 1];
                }
            }
        }
        if (!empty($data['message'])) {
            Support::sendMessage(['message' => trim($data['message']), 'repair_id' => $order['repair_id']], 1, Support::getSupportThread($order['repair_id'])['id']);
            notice_add('Новый ответ на ваш запрос.', 'Поступило новое сообщение в службу поддержки.', $repair['service_id'], 'https://crm.r97.ru/edit-repair/' . $repair['id'] . '/step/6/', $data['message']);
        }
        return ['message' => 'Операция выполнена успешно.', 'error_flag' => 0];
    }


    /**
     * Отмечает заказ полученным
     * 
     * @param int $orderID Заказ
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function receiveOrder($orderID)
    {
        $order = self::getOrder($orderID);
        $repair = Repair::getRepairByID($order['repair_id']);
        self::$db->transact('begin');
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `receive_date` = ?, `status_id` = ? WHERE `id` = ?', [date('Y-m-d H:i:s'), 4, $orderID]);
        if (!$r) {
            return ['message' => 'К сожалению, произошла ошибка при сохранении: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        /* Добавление запчасти на склад "Разбор", если ремонт АНРП */
        if ($repair['repair_type_id'] == 4) {
            $parts = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ? AND `origin_id` = ? AND `cancel_flag` = 0', [$order['id'], self::STORE_PART]);
            if ($parts) {
                $depot = Depots::getDepot(['name' => 'Разбор', 'user_id' => $repair['service_id']]);
                if (!$depot) {
                    $depot = Depots::addDepot(['name' => 'Разбор', 'user_id' => $repair['service_id']]);
                    if (!$depot) {
                        return ['message' => 'К сожалению, произошла ошибка при добавлении склада: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                    }
                }
                foreach ($parts as $part) {
                    $r1 = Log::collect($part['id'], $part['depot_id'], $part['qty'], Balance::count($part['id'], $part['depot_id']), $repair['id'], $repair['model_id']);
                    $r2 = Balance::add($part['part_id'], $part['qty'], $depot['id']);
                    if (!$r1 || !$r2) {
                        return ['message' => 'К сожалению, произошла ошибка при добавлении запчастей на склад: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                    }
                }
            }
        }
        \models\Log::repair(22, 'Заказ #' . $orderID . '.', $order['repair_id']);
        self::$db->transact('commit');
        return ['message' => 'Операция выполнена успешно.', 'error_flag' => 0];
    }


    /**
     * Отменяет статус "Одобрен акт" (АНРП) и возвращает заказ в работу
     * 
     * @param int $orderID Заказ
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function reopenOrder($orderID)
    {
        $order = self::getOrder($orderID);
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `cancel_date` = ?, `receive_date` = ?, `status_id` = ' . self::SERVICE_SENT . ' WHERE `id` = ?', ['0000-00-00 00:00:00', '0000-00-00 00:00:00', $orderID]);
        if (!$r) {
            return ['message' => 'К сожалению, произошла ошибка при сохранении: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = Repair::changeStatus($order['repair_id'], 'Нужны запчасти');
        $repair = Repair::getRepairByID($order['repair_id']);
        \models\Log::repair(1, '"' . $repair['status'] . '" на "Нужны запчасти", при повторном открытии заказа запчастей #' . $orderID . '.', $order['repair_id']);
        if (!$r) {
            return ['message' => 'К сожалению, произошла ошибка при обновлении ремонта: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => 'Операция выполнена успешно.', 'error_flag' => 0];
    }


    /**
     * Возвращает заказ в СЦ (как будто не отправлял)
     * 
     * @param int $orderID Заказ
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function returnOrder($orderID, $message = '')
    {
        $d = '0000-00-00 00:00:00';
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `receive_date` = ?, `approve_date` = ?, `send_date` = ?, `cancel_date` = ?, `status_id` = ? WHERE `id` = ?', [$d, $d, $d, $d, 0, $orderID]);
        if (!$r) {
            return ['message' => 'К сожалению, произошла ошибка при сохранении: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $order = self::getOrder($orderID);
        $repair = Repair::getRepairByID($order['repair_id']);
        if ($repair['status'] != 'В работе') {
            $r = Repair::changeStatus($order['repair_id'], 'В работе');
            \models\Log::repair(1, '"' . $repair['status'] . '" на "В работе", при возврате в СЦ заказа запчастей #' . $orderID . '.', $order['repair_id']);
        }
        \models\Log::repair(26, 'Заказ #' . $orderID . ' возвращен в СЦ.', $order['repair_id']);
        if (!empty($message)) {
            Support::sendMessage(['message' => trim($message), 'repair_id' => $order['repair_id']], 1, Support::getSupportThread($order['repair_id'])['id']);
            notice_add('Новое сообщение', 'Поступило новое сообщение в службу поддержки.', $repair['service_id'], 'https://crm.r97.ru/edit-repair/' . $repair['id'] . '/step/6/', $message);
        }
        return ['message' => 'Операция выполнена успешно.', 'error_flag' => 0];
    }


    /**
     * Данные складской запчасти для формы заказа
     * 
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * @param int $qty Заказанное кол-во
     * @param int $returnFlag Возвращена на склад
     * @param int $cancelFlag Отменена кладовщиком
     * @param int $receiveFlag Получена складом
     * @param int $altFlag Является альтернативной запчастью от других моделей
     * 
     * @return array Данные о запчасти и баланс на складе
     */
    public static function getStorePart($partID, $depotID, $qty = 1, $returnFlag = 0, $cancelFlag = 0, $receiveFlag = 0, $altFlag = 0)
    {
        $res = [
            'ordered_qty' => $qty,
            'origin' => 'store',
            'return_flag' => $returnFlag,
            'cancel_flag' => $cancelFlag,
            'receive_flag' => $receiveFlag,
            'alt_flag' => $altFlag,
            'part_data' => [],
            'depot_data' => [],
            'extra_data' => []
        ];
        $res['part_data'] = Parts::getPartByID2($partID);
        $b = Parts::getBalance($partID, $depotID);
        $res['depot_data'] = ($b) ? $b[0] : [];
        return $res;
    }


    /**
     * Данные добавленной вручную запчасти для формы заказа
     * 
     * @param int $partID Запчасть
     * 
     * @return array Данные о запчасти
     */
    public static function getManualPart($partID, $qty = 1)
    {
        $res = [
            'ordered_qty' => $qty,
            'origin' => 'manual',
            'part_data' => [],
            'depot_data' => [],
            'extra_data' => []
        ];
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_MANUAL . '` WHERE `id` = ?', [$partID]);
        if ($rows) {
            $res['part_data'] = $rows[0];
            $res['part_data']['name'] = $res['part_data']['comment'] . ' <b>(заказ вручную)</b>';
            $res['extra_data']['photo_path'] = $res['part_data']['photo_path'];
            $res['extra_data']['comment'] = $res['part_data']['comment'];
            return $res;
        }
        return $res;
    }


    /**
     * Отменить/восстановить запчасть в заказе
     * 
     * @param bool $cancelFlag Заказ
     * @param int $orderID Заказ
     * @param int $partID Запчасть
     * @param string $origin Складская или ручная
     * 
     * @return array Сообщение, флаг ошибки
     */
    public static function setCancelPart($cancelFlag, $orderID, $partID, $origin)
    {
        $originID = ($origin == 'store') ? self::STORE_PART : self::MANUAL_PART;
        $r = self::$db->exec('UPDATE `' . self::TABLE_PARTS . '` SET `cancel_flag` = ? WHERE `part_id` = ? AND `origin_id` = ? AND `order_id` = ?', [$cancelFlag, $partID, $originID, $orderID]);
        if ($r) {
            return ['message' => '', 'error_flag' => 0];
        }
        return ['message' => 'Произошла ошибка при обновлении запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
    }


    /**
     * Получить запчасть от СЦ на склад
     * 
     * @param int $orderID Заказ
     * @param int $partID Запчасть
     * @param int $depotID Склад
     * 
     * @return array Сообщение, флаг ошибки
     */
    public static function receivePartFromService($orderID, $partID, $depotID)
    {
        self::$db->transact('begin');
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ? AND `part_id` = ?', [$orderID, $partID]);
        if (!$rows) {
            return ['message' => 'Запчасть отсутствует в заказе.', 'error_flag' => 1];
        }
        $orderPart = $rows[0];
        $r = self::$db->exec('UPDATE `' . self::TABLE_PARTS . '` SET `receive_flag` = 1 WHERE `id` = ?', [$orderPart['id']]);
        if (!$r) {
            return ['message' => 'Произошла ошибка при получении запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $order = self::getOrder($orderID);
        $r = Log::return($orderPart['part_id'], $depotID, $orderPart['qty'], Balance::count($orderPart['part_id'], $depotID), $order['repair_id']);
        if (!$r || !Balance::add($orderPart['part_id'], $orderPart['qty'], $depotID)) {
            return ['message' => 'Во время возврата произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        self::$db->transact('commit');
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * СЦ возвращает запчасть на склад
     * 
     * @param int $orderID Заказ
     * @param int $partID Запчасть
     * @param string $message Сообщение
     * 
     * @return array Сообщение, флаг ошибки
     */
    public static function returnPartToStore($orderID, $partID, $message = '')
    {
        if (empty($message)) {
            return ['message' => 'Пожалуйста, введите причину возврата запчасти.', 'error_flag' => 1];
        }
        self::$db->transact('begin');
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ? AND `part_id` = ?', [$orderID, $partID]);
        if (!$rows) {
            return ['message' => 'Запчасть отсутствует в заказе.', 'error_flag' => 1];
        }
        $orderPart = $rows[0];
        if ($orderPart['origin_id'] != self::STORE_PART) {
            return ['message' => 'Запчасть должна быть складской.', 'error_flag' => 1];
        }
        if ($orderPart['qty'] == 0 || $orderPart['cancel_flag'] || $orderPart['return_flag']) {
            return ['message' => 'Запчасть не может быть возвращена. Пожалуйста, обратитесь к администратору.', 'error_flag' => 1];
        }
        $order = self::getOrder($orderID);
        $repair = Repair::getRepairByID($order['repair_id']);
        $part = Parts::getPartByID2($orderPart['part_id']);
        $message = 'Сервис запросил возврат запчасти "' . $part['name'] . '", в связи с (' . trim($message) . ')';
        Support::sendMessage(['message' => $message, 'repair_id' => $order['repair_id']], 3, Support::getSupportThread($order['repair_id'])['id']);
        notice_add('Новое сообщение', 'Поступило новое сообщение в службу поддержки.', $repair['service_id'], 'https://crm.r97.ru/edit-repair/' . $repair['id'] . '/step/6/', $message);
        if ($orderPart['depot_id'] != Parts::MAIN_DEPOT_ID) { // вернуть на свой склад Разбор
            $r = Log::return($orderPart['part_id'], $orderPart['depot_id'], $orderPart['qty'], Balance::count($orderPart['part_id'], $orderPart['depot_id']), $order['repair_id']);
            if (!$r || !Balance::add($orderPart['part_id'], $orderPart['qty'], $orderPart['depot_id'])) {
                return ['message' => 'Во время возврата произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            $r = self::$db->exec('UPDATE `' . self::TABLE_PARTS . '` SET `return_flag` = 1, `receive_flag` = 1 WHERE `id` = ?', [$orderPart['id']]);
            if (!$r) {
                return ['message' => 'Произошла ошибка при отправке запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        } else {
            $r = self::$db->exec('UPDATE `' . self::TABLE_PARTS . '` SET `return_flag` = 1 WHERE `id` = ?', [$orderPart['id']]);
            if (!$r) {
                return ['message' => 'Произошла ошибка при отправке запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            if (!in_array($repair['status'], ['Подтвержден', 'Выдан', 'В работе'])) {
                $r = Repair::changeStatus($order['repair_id'], 'В работе');
                if (!$r) {
                    return ['message' => 'Во время обновления статуса ремонта произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                }
            }
        }
        self::$db->transact('commit');
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Удалить запчасть из заказа
     * 
     * @param int $orderID Заказ
     * @param int $partID Запчасть
     * @param string $origin Складская или ручная
     * @param string $message Сообщение для СЦ
     * 
     * @return array Сообщение, флаг ошибки
     */
    public static function delPart($orderID, $partID, $origin, $message = '')
    {
        $originID = ($origin == 'store') ? self::STORE_PART : self::MANUAL_PART;
        /* Запчасть еще не была сохранена */
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `part_id` = ? AND `origin_id` = ? AND `order_id` = ?', [$partID, $originID, $orderID]);
        if (!$rows) {
            return ['message' => '', 'error_flag' => 0];
        }
        /* Удаление из заказа */
        $part = $rows[0];
        $order = self::getOrder($orderID);
        $r = self::$db->exec('DELETE FROM `' . self::TABLE_PARTS . '` WHERE `id` = ?', [$part['id']]);
        if (!$r) {
            return ['message' => 'Произошла ошибка при удалении запчасти: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if ($originID == self::STORE_PART) {
            self::returnPart($order, $part);
        }
        if (!empty($message)) {
            $repair = Repair::getRepairByID($order['repair_id']);
            Support::sendMessage(['message' => trim($message), 'repair_id' => $order['repair_id']], 1, Support::getSupportThread($order['repair_id'])['id']);
            notice_add('Новое сообщение', 'Поступило новое сообщение в службу поддержки.', $repair['service_id'], 'https://crm.r97.ru/edit-repair/' . $repair['id'] . '/step/6/', $message);
        }
        return ['message' => '', 'error_flag' => 0];
    }


    /**
     * Удаление заказа и возврат запчастей
     * 
     * @param array $data Данные
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function deleteOrder(array $data)
    {
        if (!User::hasRole('admin', 'slave-admin', 'master')) {
            return ['message' => 'Недостаточно прав.', 'error_flag' => 1];
        }
        if (empty($data['service_id']) || empty($data['repair_id'])) {
            return ['message' => 'Недостаточно данных.', 'error_flag' => 1];
        }
        self::$db->transact('begin');
        if (empty($data['order_id'])) {
            return ['message' => 'Во время получения ID заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = self::returnOrderParts($data['order_id']);
        if ($r) {
            return $r;
        }
        $r = self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `id` = ' . $data['order_id']);
        if (!$r) {
            return ['message' => 'Во время удаления заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        self::$db->transact('commit');
        return ['message' => 'Заказ успешно удален.', 'error_flag' => 0];
    }


    /**
     * Обновление неотправленного заказа
     * 
     * @param array $data Данные
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function editOrder(array $data)
    {
        if (empty($data['service_id']) || empty($data['repair_id'])) {
            return ['message' => 'Недостаточно данных.', 'error_flag' => 1];
        }
        self::$db->transact('begin');
        if (empty($data['order_id'])) {
            return ['message' => 'Во время получения ID заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = self::saveOrderParts($data['parts'], $data['order_id']);
        if (!$r) {
            return ['message' => 'Во время сохранения запчастей произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        self::$db->transact('commit');
        return ['message' => 'Заказ успешно сохранен.', 'error_flag' => 0];
    }


    /**
     * Обновление заказа
     * 
     * @param array $data Данные
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function updateOrder(array $data)
    {
        if (empty($data['service_id']) || empty($data['repair_id'])) {
            return ['message' => 'Недостаточно данных.', 'error_flag' => 1];
        }
        if (empty($data['parts']['store'])) {
            return ['message' => 'Заказ должен содержать запчасти со склада.', 'error_flag' => 1];
        }
        self::$db->transact('begin');
        if (empty($data['order_id'])) {
            return ['message' => 'Во время получения ID заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = self::returnOrderParts($data['order_id']);
        if ($r) {
            return $r;
        }
        $r = self::saveOrderParts($data['parts'], $data['order_id']);
        if (!$r) {
            return ['message' => 'Во время сохранения запчастей произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $repair = Repair::getRepairByID($data['repair_id']);
        foreach ($data['parts']['store'] as $part) {
            $r = Log::take($part['id'], $part['depot_id'], $part['qty'], Balance::count($part['id'], $part['depot_id']), $repair['id'], $repair['model_id']);
            if (!$r) {
                return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        $res = self::takePartsFromDepot($data['parts']['store']);
        if (!empty($res['error_flag'])) {
            return $res;
        }
        $r = self::applyPartsToRepair($data['repair_id']);
        if (!$r) {
            return ['message' => 'Во время обновления ремонта произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        self::$db->transact('commit');
        return ['message' => 'Заказ успешно сохранен.', 'error_flag' => 0];
    }


    /**
     * Отправляет заказ в СЦ
     * 
     * @param array $data Данные
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function sendOrder(array $data)
    {
        if (empty($data['service_id']) || empty($data['repair_id'])) {
            return ['message' => 'Недостаточно данных.', 'error_flag' => 1];
        }
        if (empty($data['send_date']) || empty($data['transport_company_id'])) {
            return ['message' => 'Пожалуйста, введите данные для отправки (дата, компания).', 'error_flag' => 1];
        }
        if (empty($data['parts']['store'])) {
            return ['message' => 'Заказ должен содержать запчасти со склада.', 'error_flag' => 1];
        }
        $data['track_num'] = trim($data['track_num']);
        $data['send_date'] = trim($data['send_date']);
        self::$db->transact('begin');
        $orderID = self::getOrderID($data);
        if (!$orderID) {
            return ['message' => 'Во время получения ID заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $repair = Repair::getRepairByID($data['repair_id']);
        $repairStatus = 'Запчасти в пути';
        $r = self::saveOrderParts($data['parts'], $orderID);
        if (!$r) {
            return ['message' => 'Во время сохранения запчастей произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `status_id` = ' . self::STORE_SENT . ', `send_date` = ?, `transport_company_id` = ?, `track_num` = ?, `cancel_date` = "0000-00-00 00:00:00" WHERE `id` = ?', [date('Y-m-d', strtotime($data['send_date'])) . ' ' . date('H:i:s'), $data['transport_company_id'], $data['track_num'], $orderID]);
        if (!$r) {
            return ['message' => 'Во время обновления заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        foreach ($data['parts']['store'] as $part) {
            $r = Log::take($part['id'], $part['depot_id'], $part['qty'], Balance::count($part['id'], $part['depot_id']), $repair['id'], $repair['model_id']);
            if (!$r) {
                return ['message' => self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        $res = self::takePartsFromDepot($data['parts']['store']);
        if (!empty($res['error_flag'])) {
            return $res;
        }
        $r = self::applyPartsToRepair($data['repair_id']);
        if (!$r) {
            return ['message' => 'Во время обновления ремонта произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $message = 'Ваш заказ отправлен ' . $data['send_date'] . ' транспортной компанией "' . Company::getCompanyByID($data['transport_company_id'])['name'] . '".';
        if ($data['track_num']) {
            $message .= ' Отследить заказ можно по номеру: ' . $data['track_num'];
        }
        Support::sendMessage(['message' => $message, 'repair_id' => $data['repair_id']], 1, Support::getSupportThread($data['repair_id'])['id']);
        notice_add('Запчасти отправлены', 'К ремонту #' . $data['repair_id'] . ' отправлены запчасти.', $repair['service_id'], 'https://crm.r97.ru/edit-repair/' . $repair['id'] . '/step/6/', $message);
        $r = Repair::changeStatus($data['repair_id'], $repairStatus);
        if (!$r) {
            return ['message' => 'Во время обновления статуса ремонта произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if ($repairStatus != $repair['status']) {
            \models\Log::repair(1, '"' . $repair['status'] . '" на "' . $repairStatus . '", при отправке заказа запчастей #' . $orderID . '.', $data['repair_id']);
        }
        if ($repair['has_questions'] && Repair::setHasQuestions($data['repair_id'], 0)) {
            \models\Log::repair(23, 'Статус снят при отправке запчастей.', $data['repair_id']);
        }
        self::$db->transact('commit');
        return ['message' => 'Заказ успешно отправлен СЦ.', 'error_flag' => 0];
    }


    /**
     * Сохранение заказа
     * 
     * @param array $data Данные
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function saveOrder(array $data)
    {
        if (empty($data['service_id']) || empty($data['repair_id'])) {
            return ['message' => 'Недостаточно данных.', 'error_flag' => 1];
        }
        if (empty($data['parts']['store']) && empty($data['parts']['manual'])) {
            return ['message' => 'Заказ не может быть пуст.', 'error_flag' => 1];
        }
        self::$db->transact('begin');
        $orderID = self::getOrderID($data);
        if (!$orderID) {
            return ['message' => 'Во время получения ID заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        $repair = Repair::getRepairByID($data['repair_id']);
        if ($data['status_id'] == self::NO_STATUS) {
            $repairStatus = 'Нужны запчасти';
            $message = 'Заказ успешно отправлен администратору.';
            $newStatusID = self::SERVICE_SENT;
            \models\Log::repair(11, 'Заказ #' . $orderID . '.', $data['repair_id']);
        } else if ($data['status_id'] == self::SERVICE_SENT) {
            $repairStatus = 'В обработке';
            $message = 'Заказ отправлен в обработку.';
            $newStatusID = self::ADMIN_CHECKED;
        } else {
            return ['message' => 'Статус #' . $data['status_id'] . ' не подлежит сохранению.', 'error_flag' => 1];
        }
        $r = self::saveOrderParts($data['parts'], $orderID);
        if (!$r) {
            return ['message' => 'Во время сохранения запчастей произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if ($data['status_id'] == self::NO_STATUS) {
            $r = self::saveInitialOrder($orderID);
            if (!$r) {
                return ['message' => 'Во время сохранения заказа запчастей произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        switch ($newStatusID) {

            case self::SERVICE_SENT:
                $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `status_id` = ' . self::SERVICE_SENT . ' WHERE `id` = ?', [$orderID]);
                if (!$r) {
                    return ['message' => 'Во время обновления заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                }
                break;

            case self::ADMIN_CHECKED:
                $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `status_id` = ' . self::ADMIN_CHECKED . ', `approve_date` = ?, `cancel_date` = ? WHERE `id` = ?', [date('Y-m-d H:i:s'), '0000-00-00 00:00:00', $orderID]);
                if (!$r) {
                    return ['message' => 'Во время обновления заказа произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
                }
                break;
        }
        if ($repairStatus) {
            $r = Repair::changeStatus($data['repair_id'], $repairStatus);
            if (!$r) {
                return ['message' => 'Во время обновления статуса ремонта произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
            if ($repairStatus != $repair['status']) {
                \models\Log::repair(1, '"' . $repair['status'] . '" на "' . $repairStatus . '", при сохранении заказа запчастей #' . $orderID . '.', $data['repair_id']);
            }
        }
        self::$db->transact('commit');
        if (!empty($data['message'])) {
            Support::sendMessage(['message' => trim($data['message']), 'repair_id' => $data['repair_id']], 3, Support::getSupportThread($data['repair_id'])['id']);
        }
        return ['message' => $message, 'error_flag' => 0, 'order' => self::getOrder($orderID)];
    }


    /**
     * Заполняет проделанную работу во вкладке "Ремонт" на основе заказов
     * 
     * @param int $repairID Ремонт
     * 
     * @return bool Флаг успешного выполнения
     */
    private static function applyPartsToRepair($repairID, $allRepairRejectedFlag = false)
    {
        $rejectRepairFlag = false;
        $orders = self::$db->exec('SELECT `id`, `status_id` FROM `' . self::TABLE . '` WHERE `repair_id` = ?', [$repairID]);
        foreach ($orders as $order) {
            $parts = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ? AND `origin_id` = ' . self::STORE_PART, [$order['id']]);
            foreach ($parts as $part) {
                if ($part['cancel_flag'] == 1) {
                    $rejectRepairFlag = true; // при отказанной запчасти весь ремонт в отказ
                }
                $r = \models\repair\Work::addWork($repairID, $part['part_id'], $part['qty'], $part['depot_id'], $allRepairRejectedFlag || $part['cancel_flag']);
                if (!$r) {
                    return false;
                }
                // \models\Log::repair(18, 'Запчасть #' . $part['part_id'] . ', при заказе запчастей.', $repairID);
            }
        }
        if ($allRepairRejectedFlag || $rejectRepairFlag) { // ремонт отклонен, одобрен акт, АНРП
            \models\Log::repair(3, '"АНРП", так как поставка запчастей было отклонена.', $repairID);
            \models\Repair::rejectRepair($repairID);
        }
        return true;
    }


    private static function returnOrderParts($orderID)
    {
        $order = self::getOrder($orderID);
        $parts = self::$db->exec('SELECT * FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ?', [$orderID]);
        foreach ($parts as $part) {
            self::returnPart($order, $part);
        }
        self::$db->exec('DELETE FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ?', [$orderID]);
        return [];
    }


    /**
     * Возврат запчасти на склад
     * 
     * @param array $order Данные заказа
     * @param array $part Данные запчасти из TABLE_PARTS
     * 
     * @return array Сообщение и флаг ошибки
     */
    private static function returnPart(array $order, array $part)
    {
        if ($part['origin_id'] == self::MANUAL_PART) { // ручные не трогать
            return [];
        }
        \models\repair\Work::deleteWork($order['repair_id'], $part['part_id']);
        /* Возврат на склад, если запчасть уже была списана (по-хорошему, удалять ее из заказа нельзя) */
        if (!$part['cancel_flag'] && in_array($order['status_id'], [self::STORE_SENT, self::SERVICE_RECEIVED])) {
            $p = Parts::getPartByID2($part['part_id']);
            if ($p['attr_id'] == Parts::STD_PART && $part['depot_id'] == Parts::MAIN_DEPOT_ID) { // стандартные с главного не трогать
                return [];
            }
            $r = Log::return($part['part_id'], $part['depot_id'], $part['qty'], Balance::count($part['part_id'], $part['depot_id']), $order['repair_id']);
            if (!$r || !Balance::add($part['part_id'], $part['qty'], $part['depot_id'])) {
                return ['message' => 'Во время возврата произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        return [];
    }


    private static function takePartsFromDepot(array $parts)
    {
        foreach ($parts as $part) {
            $p = Parts::getPartByID2($part['id']);
            if ($p['attr_id'] == Parts::STD_PART && $part['depot_id'] == Parts::MAIN_DEPOT_ID) { // стандартные на главном не трогать
                continue;
            }
            if (!Balance::isEnough($part['depot_id'], $part['id'], $part['qty'])) {
                return ['message' => 'Недостаточно запчасти "' . $p['part_code'] . '".', 'error_flag' => 1];
            }
            if (!Balance::take($part['id'], $part['qty'], $part['depot_id'])) {
                return ['message' => 'Во время списания произошла ошибка: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
            }
        }
        return [];
    }


    private static function getOrderID(array $data)
    {
        if (empty($data['order_id'])) {
            return self::$db->exec('INSERT INTO `' . self::TABLE . '` (`service_id`, `repair_id`) VALUES (?, ?)', [$data['service_id'], $data['repair_id']]);
        }
        return $data['order_id'];
    }


    /**
     * Сохраняет первоначальный заказ для истории
     * 
     * @param int $orderID Заказ
     * 
     * @return bool Результат
     */
    private static function saveInitialOrder($orderID)
    {
        $rows = self::$db->exec('SELECT `id`, `part_id`, `origin_id`, `depot_id`, `qty` AS num FROM `' . self::TABLE_PARTS . '` WHERE `order_id` = ' . $orderID);
        if (!$rows) {
            return true;
        }
        return self::$db->exec('UPDATE `' . self::TABLE . '` SET `initial_parts` = ? WHERE `id` = ?', [json_encode($rows), $orderID]);
    }


    private static function saveOrderParts(array $parts, $orderID)
    {
        // надо беречь id
        function insOrUpd(array $part, $originID, $orderID, $db, $table)
        {
            $part['alt_flag'] = (!empty($part['alt_flag'])) ? 1 : 0;
            $rows = $db->exec('SELECT `id` FROM `' . $table . '` WHERE `part_id` = ? AND `origin_id` = ? AND `depot_id` = ? AND `order_id` = ?', [$part['id'], $originID, $part['depot_id'], $orderID]);
            if ($rows) {
                return $db->exec('UPDATE `' . $table . '` SET `qty` = ? WHERE `id` = ?', [$part['qty'], $rows[0]['id']]);
            }
            return $db->exec(
                'INSERT INTO `' . $table . '` (`part_id`, `origin_id`, `depot_id`, `qty`, `order_id`, `alt_flag`) VALUES (?, ?, ?, ?, ?, ?)',
                [$part['id'], $originID, $part['depot_id'], $part['qty'], $orderID, $part['alt_flag']]
            );
        }

        if (!empty($parts['store'])) {
            foreach ($parts['store'] as $part) {
                $r = insOrUpd($part, self::STORE_PART, $orderID, self::$db, self::TABLE_PARTS);
                if (!$r) {
                    return false;
                }
            }
        }
        if (!empty($parts['manual'])) {
            foreach ($parts['manual'] as $part) {
                $r = insOrUpd($part, self::MANUAL_PART, $orderID, self::$db, self::TABLE_PARTS);
                if (!$r) {
                    return false;
                }
            }
        }
        return true;
    }


    private static function getOrder($orderID = 0, $repairID = 0)
    {
        if (!$orderID) {
            $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `repair_id` = ?', [$repairID]);
        } else {
            $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$orderID]);
        }
        if (!$rows) {
            return [];
        }
        return self::handleOrder($rows[0]);
    }


    private static function handleOrder(array $order)
    {
        $order['status'] = self::$statuses[$order['status_id']];
        $keys = ['create', 'send', 'approve', 'receive', 'cancel'];
        foreach ($keys as $k) {
            if (Time::isEmpty($order[$k . '_date'])) {
                $order[$k . '_date'] = '';
                continue;
            }
            $order[$k . '_time'] = date('H:i', strtotime($order[$k . '_date']));
            $order[$k . '_date'] = date('d.m.Y', strtotime($order[$k . '_date']));
        }
        return $order;
    }


    public static function getOrderStatusName($statusID)
    {
        return self::$statuses[$statusID] ?? '';
    }


    /**
     * Является ли запчасть альтернативной (из других моделей)
     * 
     * @param int $partID Запчасть
     * @param array $parts Список запчастей из текущей модели
     * 
     * @return bool Запчасть является альтернативной
     */
    public static function isAlternativePart($partID, array $parts)
    {
        foreach ($parts as $part) {
            if ($part['id'] == $partID) {
                return false;
            }
        }
        return true;
    }
}


Order::init();
