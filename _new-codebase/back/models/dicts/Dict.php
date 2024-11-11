<?php

namespace models\dicts;

use program\core\Query;

class Dict extends \models\_Model
{

    const TABLE = 'dicts';
    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function getDictByID($dictID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `id` = ?', [$dictID]);
        if (!$rows) {
            return [];
        }
        $rows[0]['values'] = (!empty($rows[0]['values'])) ? json_decode($rows[0]['values'], true) : [];
        return $rows[0];
    }


    public static function getValues($dictID, array $exclude = [])
    {
        $dict = self::getDictByID($dictID);
        if (empty($dict['values'])) {
            throw new \Exception('Dict #' . $dictID . ' not found.');
        }
        $res = [];
        $exclude = array_flip($exclude);
        foreach ($dict['values'] as $value) {
            if (!isset($exclude[$value['val']])) {
                $res[$value['val']] = $value['name'];
            }
        }
        return $res;
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
            $dictID = $rawData['id'];
            self::$db->exec($query->update($data, $dictID), $query->params);
        } else {
            $dictID = self::$db->exec($query->insert($data), $query->params);
        }
        if (!$dictID) {
            return ['message' => 'Не удалось сохранить: ' . self::$db->getErrorInfo(), 'error_flag' => 1];
        }
        return ['message' => 'Справочник успешно сохранен.', 'error_flag' => 0,];
    }



    private static function prepareData(array $rawData, array $parts = [])
    {
        $data = [];
        $data['name'] = trim($rawData['name']);
        $data['values'] = json_encode($rawData['value']);
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


Dict::init();
