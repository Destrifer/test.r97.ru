<?php

namespace models\parts;

use models\Models;
use models\Parts;
use program\adapters\Excel;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/** 
 * Выгрузка запчастей
 */

class ExportExcel extends \models\_Model
{
    public static $message = '';
    public static $errors = [];
    public static $cellsMap = [
        'part_code' => 'A',
        'name' => 'B',
        'name_1s' => 'C',
        'type' => 'D',
        'group' => 'E',
        'attr' => 'F',
        'depot' => 'G',
        'place' => 'H',
        'qty' => 'I',
        'model_cat' => 'J',
        'model' => 'K',
        'model_serial' => 'L',
        'provider' => 'M',
        'order' => 'N',
        'price' => 'O',
        'weight' => 'P',
        'vendor' => 'Q',
        'description' => 'R',
        'part_num' => 'S',
        'del_flag' => 'T'
    ];
    private static $db = null;
    private static $templatePath = '/_new-codebase/content/templates/excel/parts-export.xlsx';
    private static $rowNum = 2;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function run()
    {
        $parts = self::getParts();
        $xls = Excel::load(self::$templatePath);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        foreach ($parts as $part) {
            self::fill($sheet, $part);
        }
        Excel::display($xls, 'Запчасти_' . date('d.m.Y') . '.xlsx');
    }


    private static function fill($sheet, array $part)
    {
        foreach ($part as $key => $data) {
            $cell = self::getCell($key, self::$rowNum);
            if (!$cell) {
                continue;
            }
            $sheet->setCellValue($cell, $data);
        }
        self::$rowNum++;
    }


    private static function getCell($name, $num)
    {
        if (!isset(self::$cellsMap[$name])) {
            return '';
        }
        return self::$cellsMap[$name] . $num;
    }


    private static function getParts()
    {
        if (empty($_POST['depot']) || $_POST['depot'] == 'all') {
            $depotsSQL = '';
        } elseif ($_POST['depot'] == 'service-only') {
            $depotsSQL = ' WHERE `id` IN (SELECT DISTINCT `part_id` FROM `parts2_balance` WHERE `depot_id` != 1)';
        } else {
            $depotsSQL = ' WHERE `id` IN (SELECT DISTINCT `part_id` FROM `parts2_balance` WHERE `depot_id` = ' . $_POST['depot'] . ')';
        }
        $parts = self::$db->exec('SELECT * FROM `parts2` ' . $depotsSQL . ' ORDER BY `name`');
        for ($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
            $parts[$i] = array_merge($parts[$i], self::getBalanceData($parts[$i]));
            $group = self::getGroup($parts[$i]['group_id']);
            $parts[$i]['group'] = $group['name'] . ' (' . $group['code'] . ')';
            $parts[$i]['part_code'] = $group['code'] . $parts[$i]['id'];
            $parts[$i]['type'] = self::getPartType($parts[$i]['type_id']);
            $parts[$i]['attr'] = self::getPartAttr($parts[$i]['attr_id']);
            $parts[$i]['vendor'] = self::getVendor($parts[$i]['vendor_id']);
        }
        return $parts;
    }


    private static function getBalanceData(array $part)
    {
        $balance = Parts::getBalance($part['id'], Parts::MAIN_DEPOT_ID);
        $models = Parts::getModels($part['id'], 1);
        $r = array_fill_keys(['provider', 'order', 'qty', 'model_serial', 'depot', 'place', 'model', 'model_cat'], '');
        if (!$balance || !$models) {
            $r['qty'] = 0;
            return $r;
        }
        $model = current($models);
        $r['provider'] = $model['serials'][0]['provider'];
        $r['order'] = $model['serials'][0]['order'];
        $r['model_serial'] = $model['serials'][0]['model_serial'];
        $r['depot'] = $balance[0]['depot'];
        $r['place'] = $balance[0]['place'];
        $r['qty'] = $balance[0]['qty'];
        $r['model'] = $model['name'];
        $r['model_cat'] = self::getCat($model['cat_id']);
        return $r;
    }


    private static function getVendor($vendorID)
    {
        $v = Parts::getVendor($vendorID);
        if (!$v) {
            return '';
        }
        return $v['name'];
    }


    private static function getPartAttr($attrID)
    {
        if (isset(Parts::$partAttrs[$attrID])) {
            return Parts::$partAttrs[$attrID];
        }
        return '';
    }


    private static function getPartType($typeID)
    {
        if (isset(Parts::$partTypes[$typeID])) {
            return Parts::$partTypes[$typeID];
        }
        return '';
    }


    private static function getModel($modelID)
    {
        $pt = Models::getModel($modelID);
        if (!$pt) {
            return '';
        }
        return $pt['name'];
    }


    private static function getGroup($groupID)
    {
        $pt = Parts::getGroup($groupID);
        if (!$pt) {
            return ['name' => '', 'code' => ''];
        }
        return $pt;
    }


    private static function getCat($catID)
    {
        $cat = Models::getCat($catID);
        if (!$cat) {
            return '';
        }
        return $cat['name'];
    }
}

ExportExcel::init();
