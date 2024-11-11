<?php


namespace program\core;

/**
 * v. 1.0
 * 2020-07-10
 */

class Cache
{
  private static $folder = 'cache';


  public static function setFolder($folder)
  {
    self::$folder = trim($folder, '/');
    if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/' . self::$folder)) {
      if (!mkdir($_SERVER["DOCUMENT_ROOT"] . '/' . self::$folder, 0755, true)) {
        throw new \Exception('Не удалось создать директорию "' . $_SERVER["DOCUMENT_ROOT"] . '/' . self::$folder . '".');
      }
    }
  }


  public static function has($key)
  {
    if (file_exists(self::get($key))) {
      return true;
    }
    return false;
  }


  public static function get($key)
  {
    return $_SERVER['DOCUMENT_ROOT'] . '/' . self::$folder . '/' . $key . '.cache';
  }


  public static function clear($key)
  {
    return unlink($_SERVER['DOCUMENT_ROOT'] . '/' . self::$folder . '/' . $key . '.cache');
  }


  public static function clearAll()
  {
    $files = glob($_SERVER["DOCUMENT_ROOT"] . '/' . self::$folder . '/*.*');
    foreach ($files as $f) {
      unlink($f);
    }
  }


  public static function write($key, $data)
  {
    $f = fopen(self::get($key), 'w');
    flock($f, LOCK_EX);
    fwrite($f, $data);
    fflush($f);
    flock($f, LOCK_UN);
    fclose($f);
  }
}
