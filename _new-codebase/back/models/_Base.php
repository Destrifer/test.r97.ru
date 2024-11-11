<?php

namespace models;

use program\core;

abstract class _Base extends _Model
{
  private static $db = null;

  public static function getDB()
  {
    global $config;
    if (!self::$db) {
      self::$db = new core\DB($config['db_host'], $config['db_name'], $config['db_user'], $config['db_pass'], [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES `utf8`']);
    }
    return self::$db;
  }
}
