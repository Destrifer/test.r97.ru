<?php

namespace models;


class Documents extends _Model
{
    protected static $db = null;
    private static $templatesPath = '';


    public static function init()
    {
        global $config;
        self::$templatesPath = $config['dir_content'] . '/templates/excel';
    }


    public static function displayDocument($type, array $data)
    {
        $doc = self::getDocument($type, $data);
        $doc->display();
    }


    public static function getDocument($type, array $data)
    {
        switch ($type) {

            case 'detail-report':
                if (empty($data['month']) || empty($data['year']) || empty($data['service-id']) || empty($data['brand'])) {
                    throw new \Exception('Детальный отчет - недостаточно данных.');
                }
                $doc = new documents\DetailReport(self::$templatesPath, $data['month'], $data['year'], $data['service-id'], $data['brand']);
                break;

            case 'receipt-outside':
                if (empty($data['repair-id'])) {
                    throw new \Exception('Квитанция выездного ремонта - недостаточно данных.');
                }
                $doc = new documents\ReceiptOutside(self::$templatesPath, $data['repair-id']);
                break;

            default:
                throw new \Exception('Type of the document does not supported: ' . $type);
        }
        return $doc;
    }
}


Documents::init();
