<?php

namespace models;

use program\core;
use program\adapters;

/** 
 * v. 0.1
 * 2020-07-22
 */

class ServiceManagement extends _Model
{
    private static $db = null;
    private static $template = '';

    public static function init()
    {
        self::$db = _Base::getDB();
        self::$template = core\App::$config['dir_content'] . '/templates/excel/services.xlsx';
    }


    public static function showServicesExcel()
    {
        $rows = self::getServices();
        adapters\Excel::display(self::createXls($rows), 'service.xlsx');
    }

    private static function createXls(array $services)
    {
        $xls = adapters\Excel::load(self::$template);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Сервисы');
        $sheet->insertNewRowBefore(3, count($services) - 1);
        $n = 1;
        $rowNum = 2;
        foreach ($services as $r) {
            $sheet->setCellValue('A' . $rowNum, $n);
            $sheet->setCellValue('B' . $rowNum, trim($r['service_name']));
            $sheet->setCellValue('C' . $rowNum, trim($r['country']));
            $sheet->setCellValue('D' . $rowNum, trim($r['city']));
            $sheet->setCellValue('E' . $rowNum, trim($r['address']));
            $sheet->setCellValue('F' . $rowNum, trim($r['phones']));
            $rowNum++;
            $n++;
        }
        return $xls;
    }

    private static function getServices()
    {
        $rows = self::$db->exec('SELECT 
        usr.`id`, usr.`login` AS email, srv.`name` AS service_name, 
        srv.`phones`, srv.`phisical_adress` AS address, cit.`fcity_name` AS city, 
        cntr.`name` AS country    
        FROM `'.Users::TABLE.'` usr 
        LEFT JOIN `requests` srv ON usr.`service_id` = srv.`id` 
        LEFT JOIN `cityfull` cit ON cit.`fcity_id` = srv.`city` 
        LEFT JOIN `billing` bil ON bil.`service_id` = usr.`id` 
        LEFT JOIN `countries` cntr ON cntr.`id` = cit.`fcity_country` 
        WHERE usr.`role_id` = 3 AND usr.`status_id` = 1 
        AND srv.`mod` = 1 ORDER BY cntr.`name` ASC, cit.`fcity_name` ASC');
        return $rows;
    }
}


ServiceManagement::init();
