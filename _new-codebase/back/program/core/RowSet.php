<?php


namespace program\core;


class RowSet
{


  public static function groupBy($key, array $rows)
  {
    $ar = array();
    foreach ($rows as $row)
    {
      $ar[$row[$key]][] = $row;
    }
    return $ar;
  }


  public static function orderBy($key, array $rows)
  {
    $ar = array();
    foreach ($rows as $row)
    {
      $ar[$row[$key]] = $row;
    }
    return $ar;
  }


}
