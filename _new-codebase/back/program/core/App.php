<?php


namespace program\core;

class App
{
  public static $config = array();
  public static $URL = array();
  public static $URLParams = array();


  public static function run()
  {
    self::parseURL();
  }


  private static function parseURL()
  {
    $URLstr = $_SERVER['REQUEST_URI'];
    if (strpos($URLstr, '?') !== false) {
      $a = explode('?', $URLstr);
      $URLstr = $a[0];
      self::parseURLParams($a[1]);
    }
    $URLstr = ltrim(rtrim($URLstr, '/'), '/');
    if (!$URLstr) {
      self::$URL = ['index'];
      return;
    }
    self::$URL = explode('/', $URLstr);
  }


  private static function parseURLParams($str)
  {
    $a = explode('&', $str);
    $cnt = count($a);
    for ($i = 0; $i < $cnt; $i++) {
      $a2 = explode('=', $a[$i]);
      if (isset($a2[1])) {
        self::$URLParams[urldecode($a2[0])] = urldecode($a2[1]);
      }
    }
  }


  public static function getViewPath($name)
  {
    $p = $_SERVER['DOCUMENT_ROOT'] . '/templates/' . $name . '.php';
    if (!is_file($p)) {
      throw new \Exception('Template not found: ' . $name);
    }
    return $p;
  }


  public static function redirect($url)
  {
    if (!$url) {
      header('Location: /');
      exit;
    }
    header('Location: /' . trim($url, '/ ') . '/');
    exit;
  }
}
