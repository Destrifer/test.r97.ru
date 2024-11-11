<?php

namespace program\core;

class Time
{


  /**
   * Возвращает номер квартала
   * 
   * @param string $date Дата для расчета
   * 
   * @return int Номер квартала
   */
  public static function getQuarterNum($date = '')
  {
    if (self::isEmpty($date)) {
      $date = date('Y-m-d');
    }
    return intval((date('n', strtotime($date)) + 2) / 3);
  }


  public static function format($date, $format = 'd.m.Y')
  {
    if (self::isEmpty($date)) {
      return '';
    }
    return date($format, strtotime(trim($date)));
  }

  public static function getBetween($date1, $date2)
  {
    if (self::isEmpty($date1) || self::isEmpty($date2)) {
      return 0;
    }
    $date1 = new \DateTime(date('d.m.Y', strtotime($date1)));
    $date2 = new \DateTime(date('d.m.Y', strtotime($date2)));
    return $date2->diff($date1)->format("%a");
  }



  public static function isEmpty($date)
  {
    $date = trim($date);
    if (!$date || $date == '0000-00-00') {
      return true;
    }
    $t = strtotime($date);
    if (!is_numeric($t) || (int)date('Y', $t) <= 0) {
      return true;
    }
    return false;
  }


  public static function isBetween($date, $dateFrom, $dateTo)
  {
    $date = self::format($date, 'Y-m-d');
    $dateFrom = self::format($dateFrom, 'Y-m-d');
    $dateTo = self::format($dateTo, 'Y-m-d');
    if ($date < $dateFrom || $date > $dateTo) {
      return false;
    }
    return true;
  }


  public static function getAge($birthDate)
  {
    if (!(int) $birthDate) {
      return 0;
    }
    $dateB = explode('.', date('j.n.Y', strtotime($birthDate)));
    $dateCur = explode('.', date('j.n.Y'));
    $dCur = $dateCur[0];
    $mCur = $dateCur[1];
    $yCur = $dateCur[2];
    $dB = $dateB[0];
    $mB = $dateB[1];
    $yB = $dateB[2];
    if (empty($yB)) {
      return 0;
    }
    if ($mCur > $mB) {
      return $yCur - $yB;
    }
    if ($mCur == $mB) {
      if ($dCur < $dB) {
        return $yCur - $yB - 1;
      }
      return $yCur - $yB;
    }
    return $yCur - $yB - 1;
  }


  public static function formatVerbose($stamp)
  {
    $ret = '';
    $d = date('j.m.Y', strtotime($stamp));
    $b = explode('.', $d);
    $ret .= $b[0];
    $ret .= ' ' . self::getMonthVerbose($b[1]);
    $ret .= ' ' . $b[2];
    return $ret;
  }


  public static function getMonthVerbose($num)
  {
    switch ($num) {

      case 1:
        return 'января';

      case 2:
        return 'февраля';

      case 3:
        return 'марта';

      case 4:
        return 'апреля';

      case 5:
        return 'мая';

      case 6:
        return 'июня';

      case 7:
        return 'июля';

      case 8:
        return 'августа';

      case 9:
        return 'сентября';

      case 10:
        return 'октября';

      case 11:
        return 'ноября';

      case 12:
        return 'декабря';

      default:
        return '';
    }
  }


  public static function getVerbose($num, $type)
  {
    if (!$num) {
      return '';
    }
    switch ($type) {

      case 'year':
        $ar = [' лет', ' год', ' года'];
        break;

      case 'hour':
        $ar = [' часов', ' час', ' часа'];
        break;

      case 'min':
        $ar = [' минут', ' минута', ' минуты'];
        break;

      case 'sec':
        $ar = [' секунд', ' секунда', ' секунды'];
        break;
    }
    $num = (int) $num;
    if (strlen($num) >= 2) {
      $s = substr($num, -2);
      if ($s == 11 || $s == 12 || $s == 13 || $s == 14) {
        return $num . $ar[0];
      }
    }
    $s = $num % 10;
    if ($s == 1) {
      return $num . $ar[1];
    }
    if ($s == 2 || $s == 3 || $s == 4) {
      return $num . $ar[2];
    }
    return $num . $ar[0];
  }


  public static function compare($date1, $date2)
  {
    $date1 = self::format($date1, 'Y-m-d');
    $date2 = self::format($date2, 'Y-m-d');
    if ($date1 < $date2) {
      return -1;
    }
    if ($date1 == $date2) {
      return 0;
    }
    if ($date1 > $date2) {
      return 1;
    }
  }
}
