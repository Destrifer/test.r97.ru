<?php

namespace models\models;

use program\core;
use program\adapters;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/** 
 * 2021-06-16
 */

class ExportExcel extends \models\_Model
{
    public static $message = '';
    public static $errors = [];
    private static $db = null;
    private static $templatePath = '/_new-codebase/content/templates/excel/models-export.xlsx';
    private static $cellsMap = [
        'brand' => 'G',
        'cat_name' => 'H',
        'model_id' => 'C',
        'name' => 'D',
        'serial' => 'E',
        'provider' => 'A',
        'order' => 'B',
        'plant' => 'M',
        'price_usd' => 'F',
        'warranty' => 'N',
        'service' => 'I',
        'lot' => 'J'
    ];


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function run()
    {
        $models = self::getModels();
        $xls = adapters\Excel::load(self::$templatePath);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        foreach ($models as $model) {
            self::fill($sheet, $model);
        }
        /* $cols = range('A', 'K');
        foreach ($cols as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        } */
        adapters\Excel::display($xls, 'Модели_' . date('d.m.Y') . '.xlsx');
    }


    private static function fill($sheet, array $model)
    {
        static $rowNum = 2;
        if (!$model['serials']) {
            $model['serials'] = ['n' => ['n']];
        }
        foreach ($model['serials'] as $serial) {
            foreach ($serial as $key => $data) {
                $cell = self::getCell($key, $rowNum);
                if (!$cell) {
                    continue;
                }
                $sheet->setCellValue($cell, $data);
            }
            foreach ($model as $key => $data) {
                $cell = self::getCell($key, $rowNum);
                if (!$cell) {
                    continue;
                }
                $sheet->setCellValue($cell, $data);
            }
            $rowNum++;
        }
    }


    private static function getCell($name, $num)
    {
        if (!isset(self::$cellsMap[$name])) {
            return '';
        }
        return self::$cellsMap[$name] . $num;
    }


    private static function getModels()
    {
        if (empty($_POST['cat_id']) && !empty($_POST['brand_id'])) {
            $ids = array_column(\models\Models::getCats($_POST['brand_id']), 'id');
        } else {
            $ids = $_POST['cat_id'];
        }
        $models = self::$db->exec('SELECT m.`id`, m.`model_id`, m.`brand`, m.`name`, m.`service`, 
        m.`price_usd`, m.`warranty`, c.`name` AS cat_name 
        FROM `models` m LEFT JOIN `cats` c ON c.`id` = m.`cat` WHERE m.`cat` IN (' . core\SQL::IN($ids, false) . ')');
        for ($i = 0, $cnt = count($models); $i < $cnt; $i++) {
            $models[$i]['serials'] = self::getSerials($models[$i]['id']);
        }
        return $models;
    }


    private static function getSerials($modelID)
    {
        $serials = self::$db->exec('SELECT * FROM `serials` WHERE `model_id` = ' . $modelID);
        for ($i = 0, $cnt = count($serials); $i < $cnt; $i++) {
            $serials[$i]['provider'] = self::getProvider($serials[$i]['provider_id']);
            $serials[$i]['plant'] = self::getPlant($serials[$i]['plant_id']);
        }
        return $serials;
    }


    private static function getProvider($providerID)
    {
        if (!$providerID) {
            return '';
        }
        $rows = self::$db->exec('SELECT `name` FROM `providers` WHERE `id` = ' . $providerID);
        if (!empty($rows[0]['name'])) {
            return $rows[0]['name'];
        }
        return '';
    }


    private static function getPlant($plantID)
    {
        if (!$plantID) {
            return '';
        }
        $rows = self::$db->exec('SELECT `name` FROM `plants` WHERE `id` = ' . $plantID);
        if (!empty($rows[0]['name'])) {
            return $rows[0]['name'];
        }
        return '';
    }
}

ExportExcel::init();
