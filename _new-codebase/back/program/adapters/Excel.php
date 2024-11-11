<?php


namespace program\adapters;

class Excel
{
    public static $excelVendor = '_new-codebase/back/vendor/excel/vendor/autoload.php';


    public static function init()
    {
        if (!class_exists('PHPExcel_IOFactory')) {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/' . self::$excelVendor;
        }
    }


    public static function create()
    {
        return \PHPExcel_IOFactory::load($_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/back/vendor/excel/blank.xlsx');
    }


    public static function load($path)
    {
        $path = ltrim($path, '/');
        $p = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
        if (!is_file($p)) {
            $p = '/' . $path;
            if (!is_file($p)) {
                throw new \Exception('File "' . $path . '" does not exist.');
            }
        }
        return \PHPExcel_IOFactory::load($p);
    }


    public static function save($xls, $path)
    {
        $path = trim($path, ' /');
        $objWriter = \PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($_SERVER['DOCUMENT_ROOT'] . '/' . $path);
    }


    public static function display($xls, $filename)
    {
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=" . $filename);
        $objWriter = \PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}

Excel::init();
