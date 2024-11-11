<?php

namespace models\models;

use program\core;
use program\adapters;

/** 
 * 2021-06-18
 */

class ImportExcel extends \models\_Model
{
    public static $message = '';
    public static $errors = [];
    public static $log = [];
    private static $db = null;
    private static $cache = [];
    private static $cellsMap = [
        'G' => 'brand',
        'H' => 'cat',
        'C' => 'model_code',
        'D' => 'model',
        'E' => 'serial',
        'A' => 'provider',
        'B' => 'order',
        'M' => 'plant',
        'F' => 'price',
        'N' => 'warranty',
        'I' => 'service_flag',
        'J' => 'lot'
    ];


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function run()
    {
        if (empty($_FILES['excel_file']['tmp_name'])) {
            return;
        }
        $data = self::extractData(adapters\Excel::load($_FILES['excel_file']['tmp_name']));
        if (!$data) {
            self::log('excel', 'err', 'Файл не содержит данных.');
            return;
        }
        $data = self::groupModels($data);
        self::saveToDB($data);
    }


    private static function extractData($xls)
    {
        $sheet = $xls->getSheet(0);
        $lastRow = $sheet->getHighestRow();
        $data = [];
        for ($curRow = 2; $curRow <= $lastRow; $curRow++) {
            $row = [];
            foreach (self::$cellsMap as $col => $key) {
                $row[$key] = trim($sheet->getCell($col . $curRow)->getValue());
            }
            $data[] = $row;
        }
        self::log('excel', 'total_rows', 'Обработано строк: ' . count($data));
        return $data;
    }


    private static function groupModels(array $data)
    {
        $group = [];
        foreach ($data as $row) {
            if (empty($row['model'])) {
                continue;
            }
            $model = $row['model'];
            if (!isset($group[$model])) {
                $group[$model] = [
                    'model' => $row['model'],
                    'model_code' => $row['model_code'],
                    'cat' => $row['cat'],
                    'brand' => $row['brand'],
                    'price' => $row['price'],
                    'warranty' => $row['warranty'],
                    'service_flag' => $row['service_flag'],
                    'serials' => []
                ];
            }
            $group[$model]['serials'][] = [
                'serial' => $row['serial'], 'provider' => $row['provider'],
                'order' => $row['order'], 'plant' => $row['plant'], 'lot' => $row['lot']
            ];
        }
        self::log('excel', 'total_models', 'Найдено моделей: ' . count($group));
        return $group;
    }


    private static function saveToDB(array $data)
    {
        \models\Serials::$updateMode = \models\Serials::NOT_ALLOW_EMPTY;
        foreach ($data as $row) {
            for ($i = 0, $cnt = count($row['serials']); $i < $cnt; $i++) {
                $row['serials'][$i]['provider_id'] = self::getProviderID($row['serials'][$i]['provider']);
                $row['serials'][$i]['plant_id'] = self::getPlantID($row['serials'][$i]['plant']);
            }
            $row['cat_id'] = self::getCatID($row['cat'], $row['brand']);
            $providerIDs = array_unique(array_filter(array_column($row['serials'], 'provider_id')));
            $row['provider_ids'] = implode('|', $providerIDs);
            $row['warranty'] = (empty($row['warranty'])) ? 365 : $row['warranty'];
            $model = self::$db->exec('SELECT * FROM `models` WHERE `name` = ? LIMIT 1', [$row['model']]);
            if ($model) {
                /* Обновление */
                $modelID = $model[0]['id'];
                $r = true;
                $row = self::fillEmptyWithOld($model[0], $row);
                if ($model[0]['provider']) {
                    $providerIDs = array_unique(array_merge(explode('|', $model[0]['provider']), $providerIDs));
                    $row['provider_ids'] = implode('|', $providerIDs);
                }
                $dif = self::getDiffer($model[0], $row);
                if ($dif) {
                    $r = self::$db->exec('UPDATE `models` SET `model_id` = ?,  
                    `service` = ?, `price_usd` = ?, `warranty` = ?, `provider` = ? WHERE `id` = ?', [
                        $row['model_code'], $row['service_flag'],
                        $row['price'], $row['warranty'], $row['provider_ids'], $modelID
                    ]);
                    if ($r) {
                        self::log('models', 'upd', self::getURL('models', $row['model'], $modelID) . ' (' . implode(', ', $dif) . ')');
                    }
                }
            } else {
                /* Добавление */
                $r = $modelID = self::$db->exec('INSERT INTO `models` (`model_id`, `brand`, `name`, 
                `cat`, `service`, `provider`, `price_usd`, `warranty`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
                    $row['model_code'], $row['brand'], $row['model'], $row['cat_id'],
                    $row['service_flag'], $row['provider_ids'], $row['price'], $row['warranty']
                ]);
                if ($r) {
                    self::log('models', 'add', self::getURL('models', $row['model'], $modelID));
                }
            }
            if (!$r) {
                self::log('models', 'err', $row['model'] . ': ' . self::$db->getErrorInfo());
                continue;
            }
            /* Серийные номера */
            foreach ($row['serials'] as $serial) {
                if (empty($serial['serial'])) {
                    continue;
                }
                $s = self::$db->exec('SELECT `id` FROM `serials` 
                        WHERE `serial` = ? AND `model_id` = ?', [$serial['serial'], $modelID]);
                if ($s) {
                    continue;
                }
                $r = \models\Serials::addSerial($serial['serial'], $modelID, $serial['lot'], $serial['order'], $serial['provider_id'], $serial['plant_id']);
                if ($r) {
                    self::log('serials', 'add', self::getURL('models', $row['model'], $modelID) . ': ' . $serial['serial']);
                } else {
                    self::log('serials', 'err', $row['model'] . ': ' . \models\Serials::$message);
                }
            }
        }
    }


    private static function fillEmptyWithOld($modelOld, $modelNew)
    {
        if (empty($modelNew['service_flag'])) {
            $modelNew['service_flag'] = $modelOld['service'];
        }
        if (empty($modelNew['price'])) {
            $modelNew['price'] = $modelOld['price_usd'];
        }
        if (empty($modelNew['warranty'])) {
            $modelNew['warranty'] = $modelOld['warranty'];
        }
        return $modelNew;
    }


    private static function getDiffer($modelOld, $modelNew)
    {
        $dif = [];
        if ($modelOld['cat'] != $modelNew['cat_id']) {
            $dif[] = 'категория';
        }
        if ($modelOld['service'] != $modelNew['service_flag']) {
            $dif[] = 'обслуживание';
        }
        if ($modelOld['provider'] != $modelNew['provider_ids']) {
            $dif[] = 'поставщики';
        }
        if ($modelOld['price_usd'] != $modelNew['price']) {
            $dif[] = 'цена';
        }
        if ($modelOld['warranty'] != $modelNew['warranty']) {
            $dif[] = 'гарантия';
        }
        return $dif;
    }


    private static function getCatID($catName, $brandName)
    {
        if (empty($catName)) {
            return 0;
        }
        if (isset(self::$cache['cat'][$catName])) {
            return self::$cache['cat'][$catName];
        }
        $cat = self::$db->exec('SELECT `id` FROM `cats` WHERE `name` = ? LIMIT 1', [$catName]);
        if (!$cat) {
            self::$cache['cat'][$catName] = 0;
            self::log('cats', 'err', $catName . ': категория не найдена.');
            /* $id = \models\Models::addCat($catName, 0, $brandName);
            if ($id) {
                self::$cache['cat'][$catName] = $id;
                self::log('cats', 'add', self::getURL('cats', $catName, $id));
            } else {
                self::log('cats', 'err', $catName . ': ' . \models\Models::$message);
            } */
        } else {
            self::$cache['cat'][$catName] = $cat[0]['id'];
        }
        return self::$cache['cat'][$catName];
    }


    private static function getProviderID($providerName)
    {
        return self::getID($providerName, 'providers');
    }


    private static function getPlantID($plantName)
    {
        return self::getID($plantName, 'plants');
    }


    private static function getID($name, $table)
    {
        if (empty($name)) {
            return 0;
        }
        if (isset(self::$cache[$table][$name])) {
            return self::$cache[$table][$name];
        }
        $item = self::$db->exec('SELECT `id` FROM `' . $table . '` WHERE `name` = ? LIMIT 1', [$name]);
        if (!$item) {
            $id = self::$db->exec('INSERT INTO `' . $table . '` (`name`) VALUES (?)', [$name]);
            if ($id) {
                self::$cache[$table][$name] = $id;
                self::log($table, 'add', self::getURL($table, $name, $id));
            } else {
                self::log($table, 'err', $name . ': ' . self::$db->getErrorMessage());
            }
        } else {
            self::$cache[$table][$name] = $item[0]['id'];
        }
        return self::$cache[$table][$name];
    }


    private static function log($section, $item, $message)
    {
        if (!isset(self::$log[$section])) {
            self::$log[$section] = [];
        }
        if (!isset(self::$log[$section][$item])) {
            self::$log[$section][$item] = [];
        }
        self::$log[$section][$item][] = $message;
    }


    private static function getURL($type, $text, $id)
    {
        switch ($type) {
            case 'models':
                return '<a href="/edit-model/' . $id . '/" target="_blank">' . $text . '</a>';
            case 'cats':
                return '<a href="/edit-categories/' . $id . '/" target="_blank">' . $text . '</a>';
            case 'providers':
                return '<a href="/edit-provider/' . $id . '/" target="_blank">' . $text . '</a>';
            case 'plants':
                return '<a href="/plants/' . $id . '/" target="_blank">' . $text . '</a>';
        }
        return $text;
    }
}

ImportExcel::init();
