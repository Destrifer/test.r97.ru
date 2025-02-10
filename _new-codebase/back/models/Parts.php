<?php

namespace models;

use models\parts\Arrivals;
use models\parts\Balance;
use models\parts\Depots;
use models\parts\Log;
use models\parts\log\LogTable;
use models\partsships\Ship;
use program\adapters\DigitalOcean;
use program\core\File;
use program\core\FS;
use program\core\Query;

/** 
 * Запчасти - главный модуль
 */

class Parts extends _Model
{

    public static $partTypes = [3 => 'Аксессуар', 1 => 'Блочный', 2 => 'Компонентный'];
    public static $partAttrs = [1 => 'Оригинальная', 2 => 'Стандартная'];
    /* Причины списания */
    private static $db = null;
    private static $cache = ['groups' => [], 'vendors' => [], 'depots' => []];
    const TABLE = 'parts2';
    const TABLE_MODELS = 'parts2_models';
    const TABLE_BALANCE = 'parts2_balance';
    const TABLE_VEND = 'parts2_vendors';
    const TABLE_GROUP = 'parts2_groups';
    const TABLE_DEPOT = 'parts2_depots';
    const TABLE_STD = 'parts2_standard';
    const TABLE_STAND = 'parts2_standard';
    const MAIN_DEPOT_ID = 1;
    const ORIG_PART = 1;
    const STD_PART = 2;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    /**
     * Отменяет удаление запчасти
     * 
     * @param array $rawData Информация
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function move(array $rawData)
    {
        if (empty($rawData['target_depot_id']) || empty($rawData['part_ids']) || empty($rawData['depot_ids'])) {
            return ['message' => 'Пожалуйста, выберите запчасти и склад.', 'is_error' => 1];
        }
        $targetDepotID = $rawData['target_depot_id'];
        $partIDs = explode(',', $rawData['part_ids']);
        $depotIDs = explode(',', $rawData['depot_ids']);
        self::$db->transact('begin');
        for ($i = 0, $cnt = count($partIDs); $i < $cnt; $i++) {
            $partID = $partIDs[$i];
            $depotID = $depotIDs[$i];
            if ($depotID == $targetDepotID) {
                continue;
            }
            $balance = Balance::get($partID, $depotID);
            if (empty($balance['qty'])) {
                continue;
            }
            $r = Log::move($partID, $depotID, $targetDepotID, $balance['qty'], Balance::count($partID, $depotID));
            if (!$r) {
                return ['message' => self::$db->getErrorInfo(), 'is_error' => 1];
            }
            $r = Balance::take($partID, $balance['qty'], $depotID);
            if (!$r) {
                return ['message' => self::$db->getErrorInfo(), 'is_error' => 1];
            }
            $r = Log::add($partID, $targetDepotID, $balance['qty'], Balance::count($partID, $targetDepotID));
            if (!$r) {
                return ['message' => self::$db->getErrorInfo(), 'is_error' => 1];
            }
            $r = Balance::add($partID, $balance['qty'], $targetDepotID);
            if (!$r) {
                return ['message' => self::$db->getErrorInfo(), 'is_error' => 1];
            }
        }
        self::$db->transact('commit');
        return ['message' => 'Запчасти перемещены.', 'is_error' => 0];
    }


    /**
     * Отменяет удаление запчасти
     * 
     * @param int $partID Запчасть
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function restore($partID)
    {
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `del_flag` = 0 WHERE `id` = ?', [$partID]);
        if ($r) {
            return ['message' => 'Запчасть восстановлена.', 'is_error' => 0];
        }
        return ['message' => 'Во время восстановления запчасти произошла ошибка: ' . self::$db->getErrorInfo(), 'is_error' => 1];
    }


    /**
     * Клонирует выбранную запчасть
     * 
     * @param int $partID Запчасть для клонирования
     * @param int $depotID Склад
     * 
     * @return array Сообщение и флаг ошибки
     */
    public static function clone($partID, $depotID)
    {
        $depotID = (!$depotID || !is_numeric($depotID)) ? 1 : $depotID;
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$partID]);
        if (!$rows) {
            return ['message' => 'Запчасть не найдена.', 'is_error' => 1];
        }
        self::$db->transact('begin');
        $part = $rows[0];
        $part['name'] .= ' - КОПИЯ';
        $part['name_1s'] .= ' - КОПИЯ';
        $query = new Query(self::TABLE);
        $data =  [
            'group_id' => $part['group_id'], 'name' => $part['name'], 'name_1s' => $part['name_1s'],
            'extra' => $part['extra'], 'description' => $part['description'],
            'name_id' => $part['name_id'], 'type_id' => $part['type_id'], 'attr_id' => $part['attr_id'],
            'weight' => $part['weight'], 'price' => $part['price'],
            'part_num' => $part['part_num'], 'vendor_id' => $part['vendor_id'], 'del_flag' => $part['del_flag']
        ];
        $newPartID = self::$db->exec($query->insert($data), $query->params);
        if (!$newPartID) {
            self::$db->transact('rollback');
            return ['message' => 'Во время создания запчасти произошла ошибка: ' . self::$db->getErrorInfo(), 'is_error' => 1];
        }
        $query = new Query(Balance::TABLE);
        $data =  [
            'part_id' => $newPartID, 'depot_id' => $depotID
        ];
        $r = self::$db->exec($query->insert($data), $query->params);
        if (!$r) {
            self::$db->transact('rollback');
            return ['message' => 'Во время создания запчасти произошла ошибка: ' . self::$db->getErrorInfo(), 'is_error' => 1];
        }
        self::$db->transact('commit');
        return ['message' => 'Запчасть ' . $newPartID . ' успешно создана.', 'is_error' => 0];
    }


    public static function count(array $filter = [], $type = 'parts')
    {
        $where = self::where($filter);
        if ($type == 'depots') {
            $rows = self::$db->exec('SELECT 
            COUNT(*) AS cnt 
            FROM `' . self::TABLE_BALANCE . '` b 
            LEFT JOIN `' . self::TABLE . '` p 
            ON b.`part_id` = p.`id` '
                . ($where ? 'WHERE ' . implode(' AND ', $where) : ''));
        } elseif ($type == 'arrivals') {
            $rows = self::$db->exec('SELECT 
            COUNT(*) AS cnt  
            FROM `' . Arrivals::TABLE_PARTS . '` b 
            LEFT JOIN `' . self::TABLE . '` p 
            ON b.`part_id` = p.`id` 
            ' . ($where ? 'WHERE ' . implode(' AND ', $where) : ''));
        } else {
            $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM (SELECT 
            COUNT(*) 
            FROM `' . self::TABLE . '` p 
            LEFT JOIN `' . self::TABLE_BALANCE . '` b 
            ON b.`part_id` = p.`id` 
            ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . ' 
            GROUP BY b.`part_id`) t');
        }
        return ($rows) ? $rows[0]['cnt'] : 0;
    }


    public static function save(array $rawData)
    {
        self::$db->transact('begin');
        $partID = $rawData['part_id'];
        $data = self::prepareData($rawData);
        $query = new Query(self::TABLE);
        $redirURL = '';
        if (!$partID) {
            $r = $partID = self::$db->exec($query->insert($data), $query->params);
            if ($partID) { // завести баланс на главном
                $r = Balance::add($partID, 0, Parts::MAIN_DEPOT_ID);
            }
            $redirURL = '/parts/';
        } else {
            $r = self::$db->exec($query->update($data, $partID), $query->params);
            /* Очистить привязку к категориям, если вдруг запчасть была стандартной, а стала оригинальной */
            if (isset($data['attr_id']) && $data['attr_id'] == Parts::ORIG_PART) {
                self::$db->exec('DELETE FROM `' . Parts::TABLE_STAND . '` WHERE `part_id` = ?', [$partID]);
            }
            $redirURL = '/parts/';
        }
        if (!$r) {
            return ['message' => 'Не удалось сохранить запчасть: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        if (!empty($rawData['depot_id'])) {
            $error = self::handleDepots($rawData['del_depot_ids'], $rawData['depot_id'], $rawData['place'], $partID);
            if ($error) {
                return ['message' => $error, 'error_flag' => 1];
            }
        }
        $error = self::handleHistory($rawData, $partID);
        if ($error) {
            return ['message' => $error, 'error_flag' => 1];
        }
        self::handlePhotos(((!empty($rawData['photos'])) ? $rawData['photos'] : []), $rawData['del_photo_paths'], $partID);
        self::handleModels($rawData['del_model_ids'], $partID);
        self::handleSerials(((!empty($rawData['serial_ids'])) ? $rawData['serial_ids'] : []), $rawData['del_serial_ids'], $partID);
        self::$db->transact('commit');
        return ['message' => 'Запчасть успешно сохранена.', 'error_flag' => 0, 'redir_url' => $redirURL];
    }


    private static function handleHistory(array $rawData, $partID)
    {
        /* Приход */
        if (!empty($rawData['history_in']['num'])) {
            foreach ($rawData['history_in']['num'] as $n => $num) {
                $depotID = $rawData['history_in']['depot_id'][$n];
                if (!$depotID || empty($num)) {
                    continue;
                }
                $arrivalDate = date('Y-m-d H:i:01', strtotime(trim($rawData['history_in']['date_time'][$n])));
                if (!empty($rawData['history_in']['arrival_name'][$n])) {
                    $r = Arrivals::add([
                        'depot_id' => $depotID,
                        'part_id' => $partID,
                        'part_num' => filter_var($num, FILTER_SANITIZE_NUMBER_INT),
                        'add_date' => $arrivalDate,
                        'name' => $rawData['history_in']['arrival_name'][$n]
                    ]);
                    if (!$r) {
                        return self::$db->getErrorInfo();
                    }
                }
                $r = Log::add($partID, $depotID, $num, Balance::count($partID, $depotID), 0, $arrivalDate);
                if (!$r) {
                    return self::$db->getErrorInfo();
                }
                $r = Balance::add($partID, $num, $depotID);
                if (!$r) {
                    return self::$db->getErrorInfo();
                }
            }
        }
        /* Расход */
        if (!empty($rawData['history_out']['num'])) {
            foreach ($rawData['history_out']['num'] as $n => $num) {
                $depotID = $rawData['history_out']['depot_id'][$n];
                if ($rawData['history_out']['reason_id'][$n] == 4) { // отправка потребителю
                    $res = Ship::save([
                        'depot_id' => $depotID,
                        'recip' => $rawData['history_out']['recip'][$n],
                        'model_id' => $rawData['history_out']['model_id'][$n],
                        'serial' => $rawData['history_out']['serial'][$n],
                        'send_date' => $rawData['history_out']['date_time'][$n],
                        'part_id' => [$partID],
                        'part_num' => [$num]
                    ]);
                    if ($res['error_flag']) {
                        return $res['message'];
                    }
                    continue;
                }
                if (!$depotID || empty($num)) {
                    continue;
                }
                if (!Balance::isEnough($depotID, $partID, $num)) {
                    return 'Недостаточно запчастей, чтобы списать. Сейчас на складе ' . Balance::count($partID, $depotID) . ' шт.';
                }
                $date = date('Y-m-d H:i:01', strtotime($rawData['history_out']['date_time'][$n]));
                $r = Log::delete($partID, $depotID, $num, Balance::count($partID, $depotID), $rawData['history_out']['reason_id'][$n], $rawData['history_out']['repair_id'][$n], $date);
                if (!$r) {
                    return self::$db->getErrorInfo();
                }
                $r = Balance::take($partID, $num, $depotID);
                if (!$r) {
                    return self::$db->getErrorInfo();
                }
            }
        }
        return '';
    }


    public static function saveDepot(array $rawData)
    {
        $depotID = $rawData['depot_id'];
        $data = ['name' => trim($rawData['name']), 'user_id' => $rawData['user_id']];
        $query = new Query(self::TABLE_DEPOT);
        $redirURL = '';
        if (!$depotID) {
            $r = $depotID = self::$db->exec($query->insert($data), $query->params);
            $redirURL = '/depots/';
        } else {
            $r = self::$db->exec($query->update($data, $depotID), $query->params);
        }
        if (!$r) {
            return ['message' => 'Не удалось сохранить склад: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => 'Склад успешно сохранен.', 'error_flag' => 0, 'redir_url' => $redirURL];
    }


    private static function handleDepots($delDepotsJSON, array $depotIDs, array $places, $partID)
    {
        /* Удаление складов */
        /* $delDepots = (empty($delDepotsJSON)) ? [] : json_decode($delDepotsJSON);
        if ($delDepots) {
            self::$db->exec('DELETE FROM `' . self::TABLE_BALANCE . '` WHERE `id` IN (' . implode(',', $delDepots) . ')');
        } */
        foreach ($depotIDs as $n => $depotID) {
            Balance::setPlace($places[$n], $partID, $depotID);
        }
        return '';
    }


    private static function handleModels($delModelsJSON, $partID)
    {
        /* Удаление моделей */
        $delModels = (empty($delModelsJSON)) ? [] : json_decode($delModelsJSON);
        if (!$delModels) {
            return;
        }
        self::$db->exec('DELETE FROM `' . self::TABLE_MODELS . '` WHERE `part_id` = ' . $partID . ' AND `model_id` IN (' . implode(',', $delModels) . ')');
    }


    private static function handleSerials(array $serialIDs, $delSerialsJSON, $partID)
    {
        /* Удаление серийных номеров */
        $delSerials = (empty($delSerialsJSON)) ? [] : json_decode($delSerialsJSON);
        if ($delSerials) {
            $rows = self::$db->exec('SELECT `model_id`, `serial` FROM `serials` WHERE `id` IN (' . implode(',', $delSerials) . ')');
            foreach ($rows as $row) {
                self::$db->exec('DELETE FROM `' . self::TABLE_MODELS . '` WHERE `part_id` = ? AND `model_id` = ? AND `model_serial` = ?', [$partID, $row['model_id'], $row['serial']]);
            }
        }
        /* Добавление серийных номеров */
        $rows = self::$db->exec('SELECT `model_id`, `serial` FROM `serials` WHERE `id` IN (' . implode(',', $serialIDs) . ')');
        foreach ($rows as $row) {
            $model = Models::getModelByID($row['model_id']);
            if (!$model) {
                continue;
            }
            self::$db->exec('INSERT INTO `' . self::TABLE_MODELS . '` 
                (`part_id`, `model_cat_id`, `model_id`, `model_serial`) 
                VALUES (?, ?, ?, ?)', [$partID, $model['cat'], $row['model_id'], $row['serial']]);
        }
    }


    private static function prepareData(array $rawData)
    {
        $data = [];
        $data['group_id'] = $rawData['group_id'];
        $data['name'] = trim($rawData['name']);
        $data['name_1s'] = trim($rawData['name_1s']);
        if (isset($rawData['extra'])) {
            $data['extra'] = json_encode([
                'name' => trim($rawData['extra']['name'] ?? ''),
                'model_id' => $rawData['extra']['model_id'] ?? 0,
                'cat_id' => $rawData['extra']['cat_id'] ?? 0,
                'is_counted' => ($rawData['extra']['is_counted'] ?? 0) // на складе посчитали
            ]);
        }
        $data['description'] = trim($rawData['description']);
        $data['type_id'] = $rawData['type_id'];
        $data['attr_id'] = $rawData['attr_id'];
        $data['name_id'] = $rawData['name_id'];
        $data['weight'] = trim(str_replace(',', '.', $rawData['weight']));
        $data['price'] = trim($rawData['price']);
        $data['part_num'] = trim($rawData['part_num']);
        $data['vendor_id'] = $rawData['vendor_id'];
        $data['own_flag'] = (empty($rawData['own_flag'])) ? 0 : 1;
        return $data;
    }


    /**
     * Возвращает кол-во запчастей
     * 
     * @param int $modelID Model ID
     * @param string $serial Серийный номер модели
     * 
     * @return array Список запчастей с балансом по складам
     */
    public static function getPartsCnt($modelID, $serial)
    {
        $model = Models::getModelByID($modelID);
        $stdPartsCnt = $origPartsCnt = 0; // стандартные и оригинальные запчасти
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . self::TABLE . '` 
            WHERE `id` IN (SELECT `part_id` FROM `' . self::TABLE_STAND . '` 
                           WHERE `cat_id` = ?) 
                       AND `del_flag` = 0', [$model['cat']]);
        $stdPartsCnt = ($rows) ? $rows[0]['cnt'] : 0;
        if (!empty($serial)) {
            $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `' . self::TABLE . '` 
            WHERE `id` IN (SELECT `part_id` FROM `' . self::TABLE_MODELS . '` 
                           WHERE `model_id` = ? AND `model_serial` = ?) AND `del_flag` = 0', [$modelID, $serial]);
            $origPartsCnt = ($rows) ? $rows[0]['cnt'] : 0;
        }
        return $stdPartsCnt + $origPartsCnt;
    }


    /**
     * Привязывает запчасть к модели
     * 
     * @param int $partID Запчасть
     * @param int $modelID Модель
     * @param string $serial Серийный номер
     * 
     * @return void
     */
    public static function bindToModel($partID, $modelID, $serial)
    {
        if (!$partID || !$modelID) {
            return;
        }
        $rows = self::$db->exec(
            'SELECT `id` FROM `' . self::TABLE_MODELS . '` WHERE `part_id` = ? AND `model_id` = ? AND `model_serial` = ? LIMIT 1',
            [$partID, $modelID, trim($serial)]
        );
        if ($rows) {
            return;
        }
        $model = Models::getModelByID($modelID);
        self::$db->exec(
            'INSERT INTO `' . self::TABLE_MODELS . '` (`part_id`, `model_cat_id`, `model_id`, `model_serial`) VALUES (?, ?, ?, ?)',
            [$partID, $model['cat'], $modelID, trim($serial)]
        );
    }


    /**
     * Создать оригинальную запчасть на основе стандартной
     * 
     * @param int $stdPartID Стандартная запчасть
     * @param int $repairID Ремонт (для создания имени новой запчасти)
     * 
     * @return int ID новой запчасти
     */
    public static function createOriginalPart($stdPartID, $repairID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$stdPartID]);
        if (!$rows) {
            return 0;
        }
        $part = $rows[0];
        if ($part['attr_id'] != self::STD_PART) {
            return $stdPartID; // уже оригинальная
        }
        /* Подготовка данных */
        $repair = Repair::getRepairByID($repairID);
        $model = Models::getModelByID($repair['model_id']);
        $serialInfo = Serials::getSerial($repair['serial'], $repair['model_id']);
        $cat = Models::getCat($model['cat']);
        /* Проверка на существование запчасти */
        $srcURI = $model['id'] . ':' . $part['id'];
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `src_uri` = ?', [$srcURI]);
        if ($rows) {
            $newPartID = $rows[0]['id'];
        } else {
            /* Добавление новой пользовательской запчасти */
            $part['name'] = trim($part['name']) . ' - ' . trim($model['name']);
            $part['name_1s'] = $part['name'];
            $part['src_uri'] = $srcURI;
            $part['description'] = 'Модель: ' . trim($model['name']) . ', категория: ' . trim($cat['name']) . '.';
            $part['attr_id'] = self::ORIG_PART;
            unset($part['id']);
            $query = new Query(self::TABLE);
            $newPartID = self::$db->exec($query->insert($part), $query->params);
            if (!$newPartID) {
                return 0;
            }
        }
        self::bindToModel($newPartID, $repair['model_id'], $serialInfo['serial']);
        return $newPartID;
    }


    private static function handlePhotos(array $photos, $delPathsJSON, $partID)
    {
        $delPhotos = (empty($delPathsJSON)) ? [] : json_decode($delPathsJSON);
        /* Удаление фото */
        foreach ($delPhotos as $path) {
            if (strpos($path, 'digitalocean') === false) {
                continue; // временные фото удалять не обязательно
            }
            DigitalOcean::delete($path);
        }
        /* Загрузка фото */
        $newPhotos = [];
        foreach ($photos as $path) {
            if (strpos($path, 'digitalocean') === false) { // загрузить фото
                $filename =  date('d-Hi-s') . rand(1, 99999999);
                $parts = pathinfo($path);
                $ext = mb_strtolower($parts['extension']);
                $path = DigitalOcean::uploadFile($path, 'uploads/photos/parts/' . FS::getVolByID($partID) . '/' . $partID . '/' . $filename . '.' . $ext);
            }
            $newPhotos[] = $path;
        }
        $photosJSON = (!$newPhotos) ? '' : json_encode($newPhotos);
        self::$db->exec('UPDATE `' . self::TABLE . '` SET `photos` = ? WHERE `id` = ?', [$photosJSON, $partID]);
    }


    public static function getPartsByModelID($modelID)
    {
        return self::$db->exec('SELECT `id`, `list` AS name FROM `parts` WHERE `model_id` = ? AND `parent_id` = ? ORDER BY `name`', [$modelID, 0]);
    }


    /**
     * Возвращает "Модель+Завод+Заказ" по умолчанию (первая в базе) для запчасти
     * 
     * @param int $partID Запчасть
     * 
     * @return array Модель, завод, заказ
     */
    public static function getDefaultModel($partID)
    {
        $rows = self::$db->exec('SELECT `model_serial`, `model_id` FROM `' . self::TABLE_MODELS . '` WHERE `part_id` = ? ORDER BY `id` ASC LIMIT 1', [$partID]);
        if (!$rows) {
            return [];
        }
        $model = Models::getModelByID($rows[0]['model_id']);
        $serial = Serials::getSerial($rows[0]['model_serial'], $rows[0]['model_id']);
        return [
            'model' => $model['name'] ?? '',
            'provider' => $serial['provider'] ?? '',
            'order' => $serial['order'] ?? ''
        ];
    }


    public static function getParts(array $filter = [], $type = 'parts')
    {
        $where = self::where($filter);
        if ($type == 'depots') {
            $rows = self::$db->exec('SELECT 
            p.*, b.`qty` AS num, b.`depot_id`, b.`place`, b.`disposal_num`  
            FROM `' . self::TABLE . '` p 
            RIGHT JOIN `' . self::TABLE_BALANCE . '` b 
            ON b.`part_id` = p.`id`
            ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '  
            ORDER BY ' . self::sort($filter) . ' ' . self::limit($filter));
        } elseif ($type == 'arrivals') {
            $rows = self::$db->exec('SELECT 
            p.*, b.`id` AS arrival_part_id, b.`part_num` AS arrival_part_num, b.`depot_id`, b.`arrival_id`, b.`add_date`  
            FROM `' . Arrivals::TABLE_PARTS . '` b 
            LEFT JOIN `' . self::TABLE . '` p 
            ON b.`part_id` = p.`id` 
            ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '  
            ORDER BY ' . self::sort($filter) . ' ' . self::limit($filter));
        } else {
            $rows = self::$db->exec('SELECT 
            p.*, SUM(b.`qty`) AS num 
            FROM `' . self::TABLE . '` p 
            LEFT JOIN `' . self::TABLE_BALANCE . '` b 
            ON b.`part_id` = p.`id`
            ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . ' 
            GROUP BY b.`part_id` 
            ORDER BY ' . self::sort($filter) . ' ' . self::limit($filter));
        }
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if (!empty($rows[$i]['depot_id'])) {
                $rows[$i]['depot'] = Depots::getDepot(['id' => $rows[$i]['depot_id']])['name'];
            }
            $rows[$i]['user_flag'] = !empty($rows[$i]['src_uri']);
            $rows[$i]['group'] = self::getGroup($rows[$i]['group_id']);
            $rows[$i]['photos'] = ($rows[$i]['photos']) ? json_decode($rows[$i]['photos']) : [];
            $rows[$i]['part_code'] = self::createPartCode($rows[$i], $rows[$i]['group']);
            $rows[$i]['vendor'] = self::getVendor($rows[$i]['vendor_id']);
            $rows[$i]['attr'] = (isset(self::$partAttrs[$rows[$i]['attr_id']])) ? self::$partAttrs[$rows[$i]['attr_id']] : '';
            $rows[$i]['type'] = (isset(self::$partTypes[$rows[$i]['type_id']])) ? self::$partTypes[$rows[$i]['type_id']] : '';
        }
        return self::flagStandarts($rows);
    }


    /**
     * Находит стандартные запчасти, у которых есть оригинал и устанавливает им флаг и имя
     * 
     * @param array $parts Список запчастей
     * 
     * @return bool Список с флагами
     */
    public static function flagStandarts(array $parts)
    {
        $stdPartsKeys = []; // ключи стандартных запчастей
        $origPartsKeys = []; // ключи оригинальных запчастей
        foreach ($parts as $key => $part) {
            if ($part['attr_id'] == self::STD_PART) {
                $stdPartsKeys[] = $key;
            } else {
                if (empty($parts[$key]['user_flag'])) { // НЕ добавлена СЦ
                    $origPartsKeys[] = $key;
                }
            }
        }
        if (!$stdPartsKeys || !$origPartsKeys) {
            return $parts;
        }
        foreach ($stdPartsKeys as $stdKey) {
            foreach ($origPartsKeys as $origKey) {
                /* Сравнение по группе и шаблону имени */
                if ($parts[$origKey]['group_id'] == $parts[$stdKey]['group_id'] && $parts[$origKey]['name_id'] == $parts[$stdKey]['name_id']) {
                    $parts[$stdKey]['name'] = $parts[$stdKey]['name'] . ' (выберите оригинальную)';
                    $parts[$stdKey]['has_original_flag'] = true;
                    break;
                }
            }
        }
        return $parts;
    }


    private static function sort(array $filter)
    {
        $sort = 'p.`name`';
        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case 'part_code':
                    $sort = 'p.`id`';
                    break;

                case 'disposal_num':
                    $sort = 'b.`disposal_num`';
                    break;

                case 'add_date':
                    $sort = 'b.`add_date`';
                    break;

                case 'arrival_name':
                    $sort = 'b.`arrival_id`';
                    break;

                case 'arrival_part_num':
                    $sort = 'b.`part_num`';
                    break;

                case 'num':
                    $sort = '`num`';
                    break;

                case 'depot_id':
                    $sort = 'b.`depot_id`';
                    break;

                default:
                    $sort = 'p.`' . $filter['sort'] . '`';
            }
        }
        if (!empty($filter['dir']) && $filter['dir'] != 'asc') {
            $sort .= ' DESC';
        }
        return $sort;
    }


    private static function where(array $filter)
    {
        $where = [];
        $idSubquery = [];
        if (!empty($filter['id'])) {
            $where[] = 'p.`id` = ' . $filter['id'];
        } else if (!empty($filter['ids'])) {
            if (is_array($filter['ids'])) {
                $where[] = 'p.`id` IN (' . implode(',', $filter['ids']) . ')';
            } else {
                $where[] = 'p.`id` IN (' . $filter['ids'] . ')';
            }
        }
        if (isset($filter['user_flag'])) {
            if ($filter['user_flag']) {
                $where[] = 'p.`src_uri` != ""';
            } else {
                $where[] = 'p.`src_uri` = ""';
            }
        }
        if (!empty($filter['show-disposals'])) {
            $where[] = 'b.`disposal_num` != 0';
        }
        if (!empty($filter['arrival_id'])) {
            $where[] = 'b.`arrival_id` = ' . $filter['arrival_id'];
        }
        if (!empty($filter['have-no-tpl'])) {
            $where[] = 'p.`name_id` = 0 AND p.`src_uri` IN ("", "0")';
        }
        if (!empty($filter['search'])) {
            $where[] = self::getSearchWhere($filter['search']);
        }
        if (!empty($filter['arrival_dates'])) {
            $where[] = 'b.`add_date` BETWEEN  "' . $filter['arrival_dates']['date_from'] . '" AND "' . $filter['arrival_dates']['date_to'] . '"';
        }
        if (!empty($filter['collect_dates'])) {
            $p = explode('-', $filter['collect_dates']);
            $dateFrom = trim($p[0]);
            $dateTo = (isset($p[1])) ? trim($p[1]) : date('Y-m-d');
            $subquery = LogTable::where(['date_from' => $dateFrom, 'date_to' => $dateTo, 'event_id' => Log::COLLECT_EVENT], true);
            if ($subquery) {
                $idSubquery[] = $subquery;
            }
        }
        if (!empty($filter['part_num'])) {
            $where[] = 'p.`part_num` LIKE "%' . trim($filter['part_num']) . '%"';
        }
        if (isset($filter['del_flag'])) {
            $where[] = 'p.`del_flag` = ' . (($filter['del_flag']) ? 1 : 0);
        }
        if (!empty($filter['attr_id'])) {
            $where[] = 'p.`attr_id` = ' . $filter['attr_id'];
        }
        if (!empty($filter['depot_id']) && $filter['depot_id'] != 'all') {
            if (is_array($filter['depot_id'])) {
                $where[] = 'b.`depot_id` IN (' . implode(',', $filter['depot_id']) . ')';
            } else {
                $where[] = 'b.`depot_id` = ' . $filter['depot_id'];
            }
        }
        if (!empty($filter['type_id'])) {
            $where[] = 'p.`type_id` = ' . $filter['type_id'];
        }
        if (!empty($filter['vendor_id'])) {
            $where[] = 'p.`vendor_id` = ' . $filter['vendor_id'];
        }
        if (!empty($filter['group_id'])) {
            $where[] = 'p.`group_id` = ' . $filter['group_id'];
        }
        if (!empty($filter['order']) || !empty($filter['provider_id'])) {
            $w = [];
            if (!empty($filter['order'])) {
                $w[] = '`order` = "' . trim($filter['order']) . '"';
            }
            if (!empty($filter['provider_id'])) {
                $w[] = '`provider_id` = ' . $filter['provider_id'];
            }
            $rowsSerials = self::$db->exec('SELECT `serial` FROM `' . Serials::TABLE . '` WHERE ' . implode(' AND ', $w));
            if ($rowsSerials) {
                $serials = array_column($rowsSerials, 'serial');
                $w = (count($serials) == 1) ? '= "' . $serials[0] . '"' : 'IN ("' . implode('","', $serials) . '")';
                $idSubquery[] = 'SELECT `part_id` FROM `' . self::TABLE_MODELS . '` WHERE `model_serial` ' . $w;
            }
        }
        if (!empty($filter['hide-empty'])) {
            $where[] = 'b.`qty` != 0';
        }
        if (!empty($filter['model_id'])) {
            $subquery = 'SELECT `part_id` FROM `' . self::TABLE_MODELS . '` WHERE `model_id` = ' . $filter['model_id'];
            if (!empty($filter['serial'])) {
                $subquery .= ' AND `model_serial` = "' . trim($filter['serial']) . '"';
            }
            $idSubquery[] = $subquery;
            if (!empty($filter['cat_id'])) {
                if (is_array($filter['cat_id'])) {
                    $catSQL = '`cat_id` IN (' . implode(',', $filter['cat_id']) . ')';
                } else {
                    $catSQL = '`cat_id` = ' . $filter['cat_id'];
                }
                $idSubquery[] = 'SELECT `part_id` FROM `' . self::TABLE_STD . '` WHERE ' . $catSQL;
            }
        } elseif (empty($filter['model_id']) && !empty($filter['cat_id'])) {
            if (is_array($filter['cat_id'])) {
                $catSQL = '`cat_id` IN (' . implode(',', $filter['cat_id']) . ')';
                $mCatSQL = '`model_cat_id` IN (' . implode(',', $filter['cat_id']) . ')';
            } else {
                $catSQL = '`cat_id` = ' . $filter['cat_id'];
                $mCatSQL = '`model_cat_id` = ' . $filter['cat_id'];
            }
            if (empty($filter['attr_id'])) { // искать везде, если не указан стандартный или оригинальный признак 
                $idSubquery[] = 'SELECT `part_id` FROM `' . self::TABLE_MODELS . '` WHERE ' . $mCatSQL . ' UNION SELECT `part_id` FROM `' . self::TABLE_STAND . '` WHERE ' . $catSQL;
            } else if ($filter['attr_id'] == Parts::ORIG_PART) { // искать только в оригинальных
                $idSubquery[] = 'SELECT `part_id` FROM `' . self::TABLE_MODELS . '` WHERE ' . $mCatSQL;
            } else if ($filter['attr_id'] == Parts::STD_PART) { // искать только в стандартных
                $idSubquery[] = 'SELECT `part_id` FROM `' . self::TABLE_STD . '` WHERE ' . $catSQL;
            }
        }
        if ((empty($filter['depot_id']) || $filter['depot_id'] == 'all') && !empty($filter['country_id'])) {
            $depots = Depots::getDepots(['country_id' => $filter['country_id']]);
            $where[] = 'b.`depot_id` IN (' . implode(',', array_column($depots, 'id')) . ')';
        }
        if (!empty($filter['code'])) {
            $where[] = 'p.`group_id` IN (SELECT `id` FROM `' . self::TABLE_GROUP . '` WHERE `code` = "' . trim($filter['code']) . '")';
        }
        if ($idSubquery) {
            if (!empty($filter['id_query'])) {
                $where[] = 'p.`id` IN (' . $filter['id_query'] . ' AND `part_id` IN (' . implode(' UNION ', $idSubquery) . '))';
            } else {
                $where[] = 'p.`id` IN (' . implode(' UNION ', $idSubquery) . ')';
            }
        } else {
            if (!empty($filter['id_query'])) {
                $where[] = 'p.`id` IN (' . $filter['id_query'] . ')';
            }
        }
        return $where;
    }


    private static function getSearchWhere($search)
    {
        $p = explode(',', trim($search));
        if (count($p) > 1) {
            array_pop($p); // удалить название производителя из названия запчасти
        }
        $search = implode(',', $p);
        $res = 'p.`name` LIKE "%' . $search . '%"';
        $id = filter_var($search, FILTER_SANITIZE_NUMBER_INT);
        $code = preg_replace('/[\d]/', '', $search);
        $groupSubQuery = '';
        if ($code) {
            $groupSubQuery = ' AND p.`group_id` IN (SELECT `id` FROM `' . self::TABLE_GROUP . '` WHERE `code` LIKE "%' . $code . '%")';
        }
        $idSubQuery = '';
        if ($id) {
            $idSubQuery = ' OR (p.`id` LIKE "' . $id . '%")';
        }
        $res .= ' OR p.`part_num` LIKE "%' . $search . '%" ' . $idSubQuery . $groupSubQuery;
        return '(' . $res . ')';
    }


    private static function limit(array $filter)
    {
        if (empty($filter['limit']) || $filter['limit'] < 0) {
            return '';
        }
        if (empty($filter['offset'])) {
            return 'LIMIT 0, ' . $filter['limit'];
        }
        return 'LIMIT ' . $filter['offset'] . ', ' . $filter['limit'];
    }


    public static function getPartCode($partID, $groupID)
    {
        $g = self::getGroup($groupID);
        return $g['code'] . $partID;
    }


    private static function createPartCode(array $part, array $group)
    {
        return $group['code'] . $part['id'];
    }


    public static function getPartByID($partID)
    {
        $rows = self::$db->exec('SELECT `id`, `list` AS name FROM `parts` WHERE `id` = ?', [$partID]);
        if ($rows) {
            return $rows[0];
        }
        return [];
    }


    public static function getPartByID2($partID)
    {
        $rows = self::$db->exec('SELECT * FROM `parts2` WHERE `id` = ?', [$partID]);
        if ($rows) {
            $rows[0]['group'] = self::getGroup($rows[0]['group_id']);
            $rows[0]['vendor'] = self::getVendor($rows[0]['vendor_id']);
            $rows[0]['photos'] = ($rows[0]['photos']) ? json_decode($rows[0]['photos']) : [];
            $rows[0]['part_code'] = self::createPartCode($rows[0], $rows[0]['group']);
            $rows[0]['attr'] = (isset(self::$partAttrs[$rows[0]['attr_id']])) ? self::$partAttrs[$rows[0]['attr_id']] : '';
            $rows[0]['type'] = (isset(self::$partTypes[$rows[0]['type_id']])) ? self::$partTypes[$rows[0]['type_id']] : '';
            return $rows[0];
        }
        return [];
    }


    public static function getDepots()
    {
        $rows = self::$db->exec('SELECT dep.*, u.`role_id` AS owner_type   
        FROM `' . self::TABLE_DEPOT . '` dep 
        LEFT JOIN `' . Users::TABLE . '` u  
        ON u.`id` = dep.`user_id` 
        ORDER BY dep.`id`');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if ($rows[$i]['owner_type'] == 1) {
                $rows[$i]['owner'] = 'Администратор';
                continue;
            }
            $s = Services::getServiceByID($rows[$i]['user_id']);
            $rows[$i]['owner'] = (!$s) ? '(не найден)' : $s['name'];
            if ($rows[$i]['name'] == 'Разбор') {
                $rows[$i]['name'] .= ' - ' . $rows[$i]['owner'];
            }
        }
        return $rows;
    }


    public static function getGroup($groupID, $groupName = '', $groupCode = '')
    {
        $empty = ['id' => 0, 'name' => '', 'code' => ''];
        $k = $groupID . $groupName . $groupCode;
        if (!$groupID && !$groupName) {
            return $empty;
        }
        if (!isset(self::$cache['groups'][$k])) {
            if ($groupName) {
                $rows = self::$db->exec('SELECT * FROM `parts2_groups` WHERE `name` = ? AND `code` = ?', [trim($groupName), trim($groupCode)]);
            } else {
                $rows = self::$db->exec('SELECT * FROM `parts2_groups` WHERE `id` = ?', [$groupID]);
            }
            self::$cache['groups'][$k] = (!$rows) ? $empty : $rows[0];
        }
        return self::$cache['groups'][$k];
    }


    public static function getVendor($vendorID, $vendorName = '')
    {
        if (!$vendorID && !$vendorName) {
            return [];
        }
        $k = $vendorID . $vendorName;
        if (!isset(self::$cache['vendors'][$k])) {
            if ($vendorName) {
                $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_VEND . '` WHERE `name` = ?', [trim($vendorName)]);
            } else {
                $rows = self::$db->exec('SELECT * FROM `' . self::TABLE_VEND . '` WHERE `id` = ?', [$vendorID]);
            }
            self::$cache['vendors'][$k] = (!$rows) ? [] : $rows[0];
        }
        return self::$cache['vendors'][$k];
    }


    public static function getVendors()
    {
        return self::$db->exec('SELECT `id`, `name` FROM `' . self::TABLE_VEND . '` ORDER BY `name`');
    }


    public static function getGroups()
    {
        $rows = self::$db->exec('SELECT `id`, `name`, `code` FROM `parts2_groups` ORDER BY `name`');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['name'] .= ' (' . $rows[$i]['code'] . ')';
        }
        return $rows;
    }


    public static function getDepot($depotID, $depotName = '')
    {
        if (!$depotID && !$depotName) {
            return [];
        }
        $k = $depotID . $depotName;
        if (!isset(self::$cache['depots'][$k])) {
            if ($depotName) {
                $rows = self::$db->exec('SELECT * FROM `parts2_depots` WHERE `name` = ?', [trim($depotName)]);
            } else {
                $rows = self::$db->exec('SELECT * FROM `parts2_depots` WHERE `id` = ?', [$depotID]);
            }
            self::$cache['depots'][$k] = (!$rows) ? [] : $rows[0];
        }
        return self::$cache['depots'][$k];
    }


    public static function delDepot($depotID)
    {
        $r = self::$db->exec('DELETE FROM `' . self::TABLE_DEPOT . '` WHERE `id` = ?', [$depotID]);
        if (!$r) {
            return;
        }
    }


    /**
     * Возвращает баланс запчасти по каждому складу
     * 
     * @param int $partID Запчасть
     * 
     * @return array Данные о балансе
     */
    public static function getDepotsBalance($partID)
    {
        $balance = self::getBalance($partID);
        if (!$balance) {
            return $balance;
        }
        for ($i = 0, $cnt = count($balance); $i < $cnt; $i++) {
            $balance[$i]['depot'] = Depots::getDepot(['id' => $balance[$i]['depot_id']]);
        }
        return $balance;
    }


    public static function getBalance($partID, $depotID = null)
    {
        /* Не выводить доп. склады, если там пусто */
        if ($depotID) {
            if (is_array($depotID)) {
                $depotSQL = 'b.`depot_id` IN (' . implode(',', $depotID) . ')';
            } else {
                $depotSQL = 'b.`depot_id` = ' . $depotID;
            }
        } else {
            $depotSQL = '(b.`depot_id` = 1 OR (b.`depot_id` != 1 AND b.`qty` > 0))';
        }
        $rows = self::$db->exec('SELECT 
        b.`id`, b.`depot_id`, b.`qty`, b.`place`, d.`name` AS depot, d.`user_id`, r.`name` AS service_name    
        FROM `' . self::TABLE_BALANCE . '` b 
        LEFT JOIN `' . self::TABLE_DEPOT . '` d   
        ON d.`id` = b.`depot_id`  
        LEFT JOIN `' . Services::TABLE . '` r 
        ON r.`user_id` = d.`user_id` 
        WHERE b.`part_id` = ' . $partID . ' AND ' . $depotSQL . ' ORDER BY b.`id`');
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            if (!empty($rows[$i]['service_name'])) {
                $rows[$i]['depot'] .= ' - ' . $rows[$i]['service_name'];
            }
        }
        return $rows;
    }


    public static function getModels($partID, $limit = 0)
    {
        $limitSQL = ($limit) ? ' LIMIT ' . $limit : '';
        $rows = self::$db->exec('SELECT 
        p.`id`, p.`model_cat_id`, p.`model_id`, p.`model_serial` 
        FROM `' . self::TABLE_MODELS . '` p 
        WHERE p.`part_id` = ? ORDER BY p.`id`' . $limitSQL, [$partID]);
        $res = [];
        /* Группировка по моделям */
        foreach ($rows as $row) {
            $modelID = $row['model_id'];
            if (!isset($res[$modelID])) {
                $m = Models::getModel($modelID);
                if (!$m || !isset($m['id'], $m['cat'], $m['name'])) {
									// Обработка случая, когда данные отсутствуют
									$res[$modelID] = ['id' => null, 'cat_id' => null, 'name' => null, 'serials' => []];
									continue; // Пропускаем итерацию
								}
            }
            $s = Serials::getSerial($row['model_serial'], $row['model_id']);
            $row['provider'] = $s['provider'];
            $row['order'] = $s['order'];
            $row['model_serial_id'] = $s['id'];
            $row['full_model_serial'] = self::getFullSerial($s);
            $res[$modelID]['serials'][] = $row;
        }
        return $res;
    }


    public static function getSerials($modelID)
    {
        $serials = Serials::getSerials($modelID);
        for ($i = 0, $cnt = count($serials); $i < $cnt; $i++) {
            $serials[$i]['full_model_serial'] = self::getFullSerial($serials[$i]);
            $serials[$i]['model_serial_id'] = $serials[$i]['id'];
        }
        return $serials;
    }


    public static function saveModelCats(array $rawData)
    {
        if (empty($rawData['part_id'])) {
            return [];
        }
        self::$db->exec('DELETE FROM `' . self::TABLE_STD . '` WHERE `part_id` = ?', [$rawData['part_id']]);
        if (empty($rawData['cat_id'])) {
            return [];
        }
        $query = 'INSERT INTO `' . self::TABLE_STD . '` (`part_id`, `cat_id`) VALUES ';
        foreach ($rawData['cat_id'] as $catID) {
            $query .= '(' . $rawData['part_id'] . ', ' . $catID . '),';
        }
        self::$db->exec(rtrim($query, ','));
        return [];
    }


    public static function getModelCats($partID)
    {
        $checked = [];
        $rows = self::$db->exec('SELECT `cat_id` FROM `' . self::TABLE_STD . '` WHERE `part_id` = ?', [$partID]);
        if ($rows) {
            $checked = array_column($rows, 'cat_id', 'cat_id');
        }
        $cats = self::$db->exec('SELECT 
        `id`, `name` FROM `cats` 
        WHERE `id` IN 
        (SELECT `cat_id` FROM `cats_to_brand` WHERE `brand_id` IN (1, 2, 4, 5, 6, 8, 9, 12, 14, 20, 21, 29, 30, 33, 35, 36, 57)) 
        AND `is_deleted` = 0 
        ORDER BY `name`');
        $tree = [];
        foreach ($cats as $cat) {
            $l = mb_substr($cat['name'], 0, 1);
            if (!isset($tree[$l])) {
                $tree[$l] = [];
            }
            $cat['checked_flag'] = isset($checked[$cat['id']]);
            $tree[$l][] = $cat;
        }
        return $tree;
    }


    private static function getFullSerial(array $serial)
    {
        $p = ($serial['provider']) ? $serial['provider'] : '(завод не указан)';
        return trim($serial['serial'] . ', ' . implode(', ', [$p, $serial['order']]));
    }


    public static function uploadTmpPhoto()
    {
        $res = ['message' => '', 'path' => ''];
        $file = new File('', '0');
        if (!$file->exists()) {
            $res['message'] = 'Файл не выбран.';
            return $res;
        }
        $file->setPath('/_new-codebase/uploads/temp/', md5($file->name . time()));
        $res['path'] = $file->path;
        return $res;
    }


    public static function delete($partID)
    {
        self::$db->exec('UPDATE `' . self::TABLE . '` SET `del_flag` = 1 WHERE `id` = ' . $partID);
    }
}


Parts::init();
