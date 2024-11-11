<?php

namespace models;

use Exception;

/** 
 * v. 0.1
 * 2021-01-21
 */

class Models extends _Model
{
    public static $message = '';
    private static $db = null;
    private static $cache = ['cats' => [], 'models' => []];
    const TABLE = 'models';


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function inService($modelID, $serviceID)
    {
        if (!$modelID || !$serviceID) {
            throw new Exception('Недостаточно параметров.');
        }
        $rows = self::$db->exec('SELECT `service` FROM `models_users` WHERE `model_id` = ? AND `service_id` = ? LIMIT 1', [$modelID, $serviceID]);
        if ($rows) {
            return trim($rows[0]['service']) == 'Да';
        }
        $rows = self::$db->exec('SELECT `service` FROM `models` WHERE `id` = ?', [$modelID]);
        if ($rows) {
            return trim($rows[0]['service']) == 'Да';
        }
        return false;
    }


    public static function getModelByID($id)
    {
        if (isset(self::$cache['models'][$id])) {
            return self::$cache['models'][$id];
        }
        $rows = self::$db->exec('SELECT * FROM `models` WHERE `id` = ?', [$id]);
        self::$cache['models'][$id] = ($rows) ? $rows[0] : [];
        return self::$cache['models'][$id];
    }


    public static function search($request)
    {
        $request = trim($request);
        if (!$request) {
            return [];
        }
        return self::$db->exec('SELECT `id`, `name` FROM `models` WHERE `name` LIKE ?', ['%' . $request . '%']);
    }


    public static function setServiceFlag($modelID, $serviceID, $serviceFlag)
    {
        $rows = self::$db->exec('SELECT `id` FROM `models_users` WHERE `model_id` = ? AND `service_id` = ?', [$modelID, $serviceID]);
        if ($rows) {
            self::$db->exec('UPDATE `models_users` SET `service` = ? WHERE `id` = ?', [$serviceFlag, $rows[0]['id']]);
            return;
        }
        $rows = self::$db->exec('SELECT `name`, `cat` FROM `models` WHERE `id` = ?', [$modelID]);
        self::$db->exec('INSERT INTO `models_users` (`model_id`, `cat_id`, `name`, `service_id`, `service`) VALUES (?, ?, ?, ?, ?)', [$modelID, $rows[0]['cat'], $rows[0]['name'], $serviceID, $serviceFlag]);
    }

    public static function setServiceFlagBrand(array $servicesIDs, $brandID)
    {
        $rows = self::$db->exec('SELECT `name` FROM `brands` WHERE `id` = ?', [$brandID]);
        if (!$rows) {
            throw new \Exception('Бренд #' . $brandID . ' не найден.');
        }
        $rowsModels = self::$db->exec('SELECT `id`, `service` FROM `models` WHERE `brand` = ?', [$rows[0]['name']]);
        $servicesSQL = implode(',', $servicesIDs);
        foreach ($rowsModels as $model) {
            self::$db->exec('UPDATE `models_users` SET `service` = ? WHERE `service_id` IN (' . $servicesSQL . ') AND `model_id` = ?', [$model['service'], $model['id']]);
        }
    }

    public static function addCat($name, $brandID = 0, $brandName = '')
    {
        $catID = self::$db->exec('INSERT INTO `cats` (`name`) VALUES (?)', [$name]);
        if (!$catID) {
            self::$message = self::$db->getErrorInfo();
            return 0;
        }
        if (!$brandID) {
            $rows = self::$db->exec('SELECT `id` FROM `brands` WHERE `name` = ? LIMIT 1', [$brandName]);
            if (!$rows) {
                self::$message = 'Бренд "' . $brandName . '" не найден.';
                return 0;
            }
            $brandID = $rows[0]['id'];
        }
        self::$db->exec('INSERT INTO `cats_to_brand` (`cat_id`, `brand_id`) VALUES (?, ?)', [$catID, $brandID]);
        return $catID;
    }

    public static function getBrands()
    {
        return self::$db->exec('SELECT `id`, `name` FROM `brands` ORDER BY `name`');
    }

    public static function getCats($brandID = 0)
    {
        $where = '';
        if ($brandID) {
            $where = 'WHERE `id` IN (SELECT `cat_id` FROM `cats_to_brand` WHERE `brand_id` = ' . $brandID . ')';
        }
        return self::$db->exec('SELECT `id`, `name` FROM `cats` ' . $where . ' ORDER BY `name`');
    }


    public static function getModels()
    {
        return self::$db->exec('SELECT `id`, `name` FROM `models` ORDER BY `name`');
    }


    public static function getModelsList()
    {
        return array_column(self::$db->exec('SELECT `id`, `name` FROM `models` WHERE `is_deleted` = 0 ORDER BY `name`'), 'name', 'id');
    }


    public static function getModel($modelID, $modelName = '')
    {
        if (!$modelID && !$modelName) {
            return [];
        }
        $k = $modelID . $modelName;
        if (!isset(self::$cache['models'][$k])) {
            if ($modelName) {
                $rows = self::$db->exec('SELECT * FROM `models` WHERE `name` = ?', [trim($modelName)]);
            } else {
                $rows = self::$db->exec('SELECT * FROM `models` WHERE `id` = ?', [$modelID]);
            }
            self::$cache['models'][$k] = (!$rows) ? [] : $rows[0];
        }
        return self::$cache['models'][$k];
    }


    public static function searchModel($request)
    {
        if (!$request) {
            return [];
        }
        $rows = self::$db->exec('SELECT * FROM `models` WHERE `name` LIKE ?', ['%' . trim($request) . '%']);
        return $rows;
    }


    public static function getCat($catID, $catName = '')
    {
        if (!$catID && !$catName) {
            return [];
        }
        $k = $catID . $catName;
        if (!isset(self::$cache['cats'][$k])) {
            if ($catName) {
                $rows = self::$db->exec('SELECT * FROM `cats` WHERE `name` = ?', [$catName]);
            } else {
                $rows = self::$db->exec('SELECT * FROM `cats` WHERE `id` = ?', [$catID]);
            }
            self::$cache['cats'][$k] = (!$rows) ? [] : $rows[0];
        }
        return self::$cache['cats'][$k];
    }
}


Models::init();
