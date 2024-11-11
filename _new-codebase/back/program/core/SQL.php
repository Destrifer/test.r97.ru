<?php


namespace program\core;


class SQL
{


  public static function fields(array $fields, $queryType = 'select')
  {
    if (!$fields)
    {
      throw new \Exception('Have no data to make query marks.');
    }
    $queryType = strtolower($queryType);
    switch ($queryType)
    {

      case 'select' :
        return implode(', ', array_fill(0, count($fields), '?'));

      case 'insert' :
        return '`' . implode('`, `', $fields) . '`';

      case 'update' :
        return '`' . implode('` = ?, `', $fields) . '` = ?';

      default :
        throw new \Exception('Have no such type of query marks (' . $queryType . ').');
    }
  }


  public static function IN(array $data, $marksFlag = true)
  {
    if ($marksFlag) {
      return implode(", ", array_fill(0, count($data), '?'));
    }
    return "'" . implode("', '", $data) . "'";
  }

}
