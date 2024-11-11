<?php

namespace models\parts;

use program\adapters\Excel;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/** 
 * Выгрузка таблицы запчастей
 */

class PartsTableExcel extends \models\_Model
{

    public static $cellsMap = [
        'part_code' => 'A',
        'name' => 'B',
        'num' => 'C',
        'disposal_num' => 'D',
        'depot' => 'E',
        'type' => 'F',
        'attr' => 'G',
        'place' => 'H'
    ];
    private static $db = null;
    private static $templatePath = '/_new-codebase/content/templates/excel/parts-table.xlsx';
    private static $rowNum = 2;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function generate(array $parts, array $filter)
    {
        $xls = Excel::load(self::$templatePath);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        foreach ($parts as $part) {
            self::fill($sheet, $part);
        }
        if (empty($filter['show-disposals'])) {
            $sheet->removeColumn(self::$cellsMap['disposal_num']);
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
}

PartsTableExcel::init();
