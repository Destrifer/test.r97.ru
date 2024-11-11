<?php

namespace models\transportcompanies;

use program\core\Query;

class Company extends \models\_Model
{

    const TABLE = 'transport_companies';
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getCompanyByID($shipID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$shipID]);
        if (!$rows) {
            return [];
        }
        return $rows[0];
    }


    public static function save(array $rawData)
    {
        $error = self::check($rawData);
        if ($error) {
            return ['message' => $error, 'error_flag' => 1];
        }
        $data = self::prepareData($rawData);
        $query = new Query(self::TABLE);
        if (!empty($rawData['id'])) {
            $compID = $rawData['id'];
            self::$db->exec($query->update($data, $compID), $query->params);
        } else {
            $compID = self::$db->exec($query->insert($data), $query->params);
        }
        if (!$compID) {
            return ['message' => 'Не удалось сохранить: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => 'Компания успешно сохранена.', 'error_flag' => 0,];
    }



    private static function prepareData(array $rawData, array $parts = [])
    {
        $data = [];
        $data['name'] = trim($rawData['name']);
        $data['url'] = trim($rawData['url']);
        return $data;
    }


    public static function check(array $rawData)
    {
        if (empty($rawData['name'])) {
            return 'Пожалуйста, введите название.';
        }
        return '';
    }
}


Company::init();
