<?php

namespace program\core;

class FS
{

  const maxFilesQty = 500;

  public static function removeFolder($dir)
  {
    $dir = trim($dir, '/');
    if ($objs = glob($dir . "/*")) {
      foreach ($objs as $obj) {
        is_dir($obj) ? self::removeFolder($obj) : unlink($obj);
      }
    }
    rmdir($dir);
  }

  public static function getVolByID($id)
  {
    return str_pad(ceil($id / self::maxFilesQty), 7, '0', STR_PAD_LEFT);
  }


  public static function getFileExt($path)
  {
    $parts = pathinfo($path);
    return $parts['extension'];
  }
}
