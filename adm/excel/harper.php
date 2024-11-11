<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');

/*    require_once 'vendor/autoload.php';
    error_reporting(1);
        $file = 'models.xls';


        $xls = PHPExcel_IOFactory::load($file);
        $xls->setActiveSheetIndex(0);

        $sheet = $xls->getActiveSheet();

        $array = $sheet->toArray();


        foreach($array as $item) {

        if ($item['0'] != 'Код') {


mysqli_query($db, 'INSERT INTO `models` (
`model_id`,
`name`,
`cat`,
`service`,
`status`
) VALUES (
\''.mysqli_real_escape_string($db, $item['0']).'\',
\''.mysqli_real_escape_string($db, $item['1']).'\',
\''.mysqli_real_escape_string($db, $item['2']).'\',
\''.mysqli_real_escape_string($db, $item['3']).'\',
\''.mysqli_real_escape_string($db, $item['4']).'\'
);') or mysqli_error($db);

        }

        } */

  require_once 'vendor/autoload.php';
    error_reporting(1);
        $file = 'iss.xls';


        $xls = PHPExcel_IOFactory::load($file);
        $xls->setActiveSheetIndex(0);

        $sheet = $xls->getActiveSheet();

        $array = $sheet->toArray();


        foreach($array as $item) {

        if ($item['0'] != 'Код') {


mysqli_query($db, 'INSERT INTO `issues` (
`name`
) VALUES (
\''.mysqli_real_escape_string($db, $item['0']).'\'
);') or mysqli_error($db);

        }

        }



?>

