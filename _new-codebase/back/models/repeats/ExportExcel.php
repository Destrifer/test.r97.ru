<?php

namespace models\repeats;

use models\Repeats;
use program\core;
use program\adapters;
use program\core\App;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

/** 
 * 2022-03-22
 */

class ExportExcel extends \models\_Model
{
    public static $message = '';
    public static $errors = [];
    private static $tplPath = '/_new-codebase/content/templates/excel/repeats-export.xlsx';


    public static function init()
    {
        // self::$db = \models\_Base::getDB();
    }


    public static function run()
    {
        $data = Repeats::getRepeats(App::$URLParams);
        $xls = adapters\Excel::load(self::$tplPath);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $filename = '';
        if (empty(App::$URLParams['group-by']) || App::$URLParams['group-by'] == 'cats') {
            self::fillCats($sheet, $data);
            $sheet->removeColumn('B');
            $filename = 'категории';
        } else {
            self::fillModels($sheet, $data);
            $filename = 'модели';
        }
        adapters\Excel::display($xls, 'Повторные_ремонты_' . $filename . '_' . date('d.m.Y') . '.xlsx');
    }


    private static function fillCats($sheet, array $data)
    {
        $rowNum = 2;
        foreach ($data as $cat) {
            $sheet->setCellValue('A' . $rowNum, $cat['cat']);
            $sheet->setCellValue('C' . $rowNum, (int)$cat['stat']['total']['cnt']);
            $sheet->setCellValue('D' . $rowNum, (int)$cat['stat']['total']['sum']);
            $sheet->setCellValue('E' . $rowNum, (int)$cat['stat']['discard']['cnt']);
            $sheet->setCellValue('F' . $rowNum, (int)$cat['stat']['discard']['sum']);
            $sheet->setCellValue('G' . $rowNum, (int)$cat['stat']['returns']['cnt']);
            $sheet->setCellValue('H' . $rowNum, (int)$cat['stat']['returns']['sum']);
            $sheet->setCellValue('I' . $rowNum, (int)$cat['stat']['markdown']['cnt']);
            $sheet->setCellValue('J' . $rowNum, (int)$cat['stat']['markdown']['sum']);
            $rowNum++;
        }
    }


    private static function fillModels($sheet, array $data)
    {
        $rowNum = 2;
        foreach ($data as $cat) {
            foreach ($cat['models'] as $model) {
                $sheet->setCellValue('A' . $rowNum, $cat['cat']);
                $sheet->setCellValue('B' . $rowNum, $model['model']);
                $sheet->setCellValue('C' . $rowNum, (int)$model['total']['cnt']);
                $sheet->setCellValue('D' . $rowNum, (int)$model['total']['sum']);
                $sheet->setCellValue('E' . $rowNum, (int)$model['discard']['cnt']);
                $sheet->setCellValue('F' . $rowNum, (int)$model['discard']['sum']);
                $sheet->setCellValue('G' . $rowNum, (int)$model['returns']['cnt']);
                $sheet->setCellValue('H' . $rowNum, (int)$model['returns']['sum']);
                $sheet->setCellValue('I' . $rowNum, (int)$model['markdown']['cnt']);
                $sheet->setCellValue('J' . $rowNum, (int)$model['markdown']['sum']);
                $rowNum++;
            }
        }
    }
}

ExportExcel::init();
