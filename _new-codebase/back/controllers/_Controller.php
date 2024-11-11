<?php


namespace controllers;

abstract class _Controller
{

  abstract public static function run();

  protected static function runMethod($method)
  {
    $class = get_called_class();
    if (!method_exists($class, $method)) {
      return null;
    }
    return $class::$method();
  }
}
