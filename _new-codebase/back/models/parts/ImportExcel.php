<?php

namespace models\parts;

use models\Models;
use models\Parts;
use models\Serials;
use program\adapters\Excel;
use program\core\Query;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/** 
 * 2022-04-08
 */

class ImportExcel extends \models\_Model
{
    public static $message = '';
    public static $errors = [];
    public static $log = [];
    private static $db = null;
    private static $cache = ['parts' => []];
    private static $cellsMap = [];
    private static $changeNumFlag = false; // изменять количество запчастей


    public static function init()
    {
        self::$db = \models\_Base::getDB();
        self::$cellsMap = array_flip(ExportExcel::$cellsMap);
    }


    public static function run()
    {
        if (empty($_FILES['excel_file']['tmp_name'])) {
            return;
        }
        self::$changeNumFlag = (!empty($_POST['change_num_flag'])) ? true : false;
        $data = self::extractData(Excel::load($_FILES['excel_file']['tmp_name']));
        if (!$data) {
            self::$log[] = 'Файл не содержит данных.';
            return;
        }
        $data = self::handleData($data);
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
        self::$log[] = 'Обработано строк: ' . count($data);
        return $data;
    }


    private static function handleData(array $data)
    {
        for ($i = 0, $cnt = count($data); $i < $cnt; $i++) {
            $data[$i]['part_id'] = self::getPartID($data[$i]['part_code']);
            $data[$i]['model_id'] = self::getModelID($data[$i]['model']);
            $data[$i]['depot_id'] = self::getDepotID($data[$i]['depot']);
            $data[$i]['vendor_id'] = self::getVendorID($data[$i]['vendor']);
            $data[$i]['attr_id'] = self::getAttrID($data[$i]['attr']);
            $data[$i]['type_id'] = self::getTypeID($data[$i]['type']);
            $data[$i]['group_id'] = self::getGroupID($data[$i]['group']);
            $data[$i]['model_cat_id'] = self::getModelCatID($data[$i]['model_cat']);
            $data[$i]['provider_id'] = self::getProviderID($data[$i]['provider']);
        }
        return $data;
    }


    private static function getProviderID($provider)
    {
        $p = Serials::getProvider(0, $provider);
        return $p['id'];
    }


    private static function getModelCatID($modelCat)
    {
        $c = Models::getCat(0, $modelCat);
        return ($c) ? $c['id'] : 0;
    }


    private static function getGroupID($group)
    {
        $m = [];
        preg_match('/ \(([a-z]{1,3})\)/i', $group, $m);
        $code = (isset($m[1])) ? $m[1] : '';
        $group = trim(str_replace('(' . $code . ')', '', $group));
        $g = Parts::getGroup(0, $group, $code);
        return ($g) ? $g['id'] : 0;
    }


    private static function getAttrID($attr)
    {
        $k = array_search($attr, Parts::$partAttrs);
        return ($k) ? $k : 0;
    }


    private static function getTypeID($type)
    {
        $k = array_search($type, Parts::$partTypes);
        return ($k) ? $k : 0;
    }


    private static function getPartID($partCode, $partName = '')
    {
        if (!empty($partName)) {
            if (!empty(self::$cache['parts'][$partName])) {
                return self::$cache['parts'][$partName];
            }
            return 0;
        }
        if (empty($partCode)) {
            return 0;
        }
        return filter_var($partCode, FILTER_SANITIZE_NUMBER_INT);
    }


    private static function getVendorID($vendor)
    {
        $v = Parts::getVendor(0, $vendor);
        return ($v) ? $v['id'] : 0;
    }


    private static function getDepotID($depot)
    {
        $d = Parts::getDepot(0, $depot);
        return ($d) ? $d['id'] : 0;
    }


    private static function getModelID($model)
    {
        $m = Models::getModel(0, $model);
        return ($m) ? $m['id'] : 0;
    }


    private static function saveToDB(array $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['name_1s'])) {
                continue;
            }
            $partID = self::savePart($row);
            if (!$partID) {
                self::$log[] = 'Ошибка! "' . self::getPartLink($row, $partID) . '": ' . self::$db->getErrorInfo();
                continue;
            }
            self::saveBalance($row, $partID);
            self::saveModel($row, $partID);
        }
    }


    private static function savePart(array $rawData)
    {
        $partID = (!empty($rawData['part_id'])) ? $rawData['part_id'] : self::getPartID('', $rawData['name']);
        $query = new Query(Parts::TABLE);
        $data = [];
        $data['group_id'] = $rawData['group_id'];
        $data['name'] = $rawData['name'];
        $data['name_1s'] = $rawData['name_1s'];
        $data['description'] = $rawData['description'];
        $data['type_id'] = $rawData['type_id'];
        $data['attr_id'] = $rawData['attr_id'];
        $data['weight'] = $rawData['weight'];
        $data['price'] = $rawData['price'];
        $data['part_num'] = $rawData['part_num'];
        $data['vendor_id'] = $rawData['vendor_id'];
        $data['del_flag'] = $rawData['del_flag'];
        if ($partID) {
            self::$log[] = self::getPartLink($data, $partID) . ': обновлена';
            self::$db->exec($query->update($data, $partID), $query->params);
        } else {
            self::$log[] = self::getPartLink($data, $partID) . ': добавлена';
            $partID = self::$db->exec($query->insert($data), $query->params);
            self::$cache['parts'][$data['name']] = $partID;
        }
        return $partID;
    }


    private static function saveBalance(array $rawData, $partID)
    {
        $balance = Balance::get($partID, Parts::MAIN_DEPOT_ID);
        if (!$balance) {
            Log::add($partID, Parts::MAIN_DEPOT_ID, $rawData['qty'], 0);
            self::$db->exec('INSERT INTO `' . Parts::TABLE_BALANCE . '` 
            (`part_id`, `depot_id`, `qty`, `place`) 
            VALUES (' . $partID . ', ' . Parts::MAIN_DEPOT_ID . ', ' . $rawData['qty'] . ', "' . trim($rawData['place']) . '")');
            self::$log[] = self::getPartLink($rawData, $partID) . ': приход ' . $rawData['qty'] . ' шт.';
        } else {
            if (self::$changeNumFlag) {
                $newNum = $rawData['qty'];
                if ($balance['qty'] > $newNum) {
                    Log::delete($partID, Parts::MAIN_DEPOT_ID, $balance['qty'] - $newNum, Balance::count($partID, Parts::MAIN_DEPOT_ID), 1);
                    self::$log[] = $rawData['name_1s'] . ': корректировка остатков с ' . $balance['qty'] . ' до ' . $newNum . ' шт.';
                } elseif ($balance['qty'] < $newNum) {
                    $dif = $newNum - $balance['qty'];
                    Log::add($partID, Parts::MAIN_DEPOT_ID, $dif, Balance::count($partID, Parts::MAIN_DEPOT_ID));
                    self::$log[] = self::getPartLink($rawData, $partID) . ': приход ' . $dif . ' шт.';
                }
            } else {
                $newNum = $balance['qty'];
            }
            self::$db->exec('UPDATE `' . Parts::TABLE_BALANCE . '` SET 
            `place` = "' . trim($rawData['place']) . '", 
            `qty` = ' . $newNum . ' 
            WHERE `id` = ' . $balance['id']);
        }
    }


    private static function saveModel(array $rawData, $partID)
    {
        if (!$rawData['model_id']) {
            return;
        }
        $query = new Query(Parts::TABLE_MODELS);
        $data = [];
        $data['part_id'] = $partID;
        $data['model_cat_id'] = $rawData['model_cat_id'];
        $data['model_id'] = $rawData['model_id'];
        $data['model_serial'] = $rawData['model_serial'];
        $r = self::$db->exec($query->insert($data), $query->params);
        if ($r) {
            self::$log[] = 'Модель "' . $rawData['model'] . '" (' . $rawData['model_serial'] . ') добавлена для запчасти "' . self::getPartLink($rawData, $partID) . '"';
        }
        return $r;
    }


    private static function getPartLink(array $part, $partID)
    {
        return '<a href="/part/?id=' . $partID . '" target="_blank">' . $part['name_1s'] . '</a>';
    }
}

ImportExcel::init();
