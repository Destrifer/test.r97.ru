<?php

namespace models\parts;

use program\adapters\Excel;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/** 
 * Выгрузка таблицы запчастей с приходами
 */

class PartsTableArrivalsExcel extends \models\_Model
{

    private static $db = null;
    private static $templatePath = '/_new-codebase/content/templates/excel/parts-table-arrivals.xlsx';
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
        $n = 1;
        $totalNum = 0;
        foreach ($parts as $part) {
            $sheet->insertNewRowBefore(self::$rowNum + 1, 1);
            $totalNum += $part['arrival_part_num'];
            $sheet->setCellValue('A' . self::$rowNum, $n);
            $sheet->setCellValue('B' . self::$rowNum, $part['part_num']);
            $sheet->setCellValue('C' . self::$rowNum, $part['part_code']);
            $sheet->setCellValue('D' . self::$rowNum, $part['name']);
            if ($part['default_model']) {
                $d = [$part['default_model']['model'], $part['default_model']['provider'], $part['default_model']['order']];
                $sheet->setCellValue('E' . self::$rowNum, trim(implode(', ', array_filter($d)), ' ,'));
            } else {
                $sheet->setCellValue('E' . self::$rowNum, '');
            }
            $sheet->setCellValue('F' . self::$rowNum, $part['arrival_part_num']);
            $sheet->setCellValue('G' . self::$rowNum, date('d.m.Y', strtotime($part['add_date'])));
            $sheet->setCellValue('H' . self::$rowNum, $part['arrival_name']);
            self::$rowNum++;
            $n++;
        }
        $sheet->removeRow(self::$rowNum);
        $sheet->setCellValue('F' . self::$rowNum, $totalNum);
        Excel::display($xls, 'Запчасти_приход_' . date('d.m.Y') . '.xlsx');
    }
}

PartsTableArrivalsExcel::init();
