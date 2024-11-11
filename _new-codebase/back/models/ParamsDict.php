<?php

namespace models;


/** 
 * v. 0.1
 * 2020-07-01
 */

class ParamsDict extends _Model
{
    const TABLE = 'params_dict';
    const TABLE_SECT = 'params_dict_sections';
    private static $db = null;

    public static function init()
    {
        self::$db = _Base::getDB();
    }

    public static function getParamsBySectionID($sectionID)
    {
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `section_id` = ? ORDER BY `name`', [$sectionID]);
        if (!$rows) {
            throw new \Exception('Parameters of the section # ' . $sectionID . ' not found.');
        }
        return $rows;
    }
}


ParamsDict::init();
