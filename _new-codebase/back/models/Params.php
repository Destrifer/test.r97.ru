<?php

namespace models;


/** 
 * v. 0.1
 * 2020-11-24
 */

class Params extends _Model
{
    private static $db = null;
    private static $sections = ['filling_rules' => 1];

    public static function init()
    {
        self::$db = _Base::getDB();
    }

    public static function getParams($section, $paramID)
    {
        if(!isset(self::$sections[$section])){
            throw new \Exception('Params section not found: #' . $section);
        }
        $rows = self::$db->exec('SELECT `value` FROM `params` WHERE `section_id` = ? AND `param_id` = ?', [self::$sections[$section], $paramID]);
        if(!$rows){
            return [];
        }
        return json_decode($rows[0]['value'], true);
    }

    public static function saveParams($section, $paramID, array $values)
    {
        self::$db->exec('INSERT INTO `params` (`section_id`, `param_id`, `value`) VALUE (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(value)', [self::$sections[$section], $paramID, json_encode($values)]);
    }

}


Params::init();
