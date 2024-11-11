<?php

/**
 * v. 1 
 * 2020-07-02
 */
function back_autoload($n)
{
  global $config;
  $f = $_SERVER["DOCUMENT_ROOT"] . '/' . $config['dir_back'] . '/' . str_replace('\\', '/', $n) . '.php';
  if (!is_file($f)) {
    return;
  }
  include $f;
}


spl_autoload_register("back_autoload");
