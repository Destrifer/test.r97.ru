<?php

namespace models\services;


/* Различные настройки для СЦ, ремонта, страны и др. */

class Settings extends \models\_Model
{

  private static $types = ['repair' => 1, 'service' => 2, 'country' => 3, 'problem' => 4];
  private static $db = null;
  const TABLE = 'settings';


  public static function init()
  {
    self::$db = \models\_Base::getDB();
  }


  public static function getSettings($repair = 0, $service = 0, $country = 0)
  {
    $keys = ['repair', 'service', 'country'];
    foreach ($keys as $key) {
      if (!$$key) {
        continue;
      }
      $settings = self::getSettingsBy($key, $$key);
      if ($settings) {
        return $settings;
      }
    }
    return [];
  }


  private static function getSettingsBy($key, $val)
  {
    if (!isset(self::$types[$key])) {
      return [];
    }
    $rows = self::$db->exec('SELECT `settings` FROM `' . self::TABLE . '` WHERE `type` = ? AND `value` = ?', [self::$types[$key], $val]);
    if (!$rows) {
      return $rows;
    }
    return json_decode($rows[0]['settings'], true);
  }


  public static function saveSettings($type, $value, array $settings)
  {
    $rowID = self::getSettingsRowID(self::$types[$type], $value);
    if (!$rowID) {
      return self::$db->exec('INSERT INTO `' . self::TABLE . '` (`type`, `value`, `settings`) VALUES (?, ?, ?)', [self::$types[$type], $value, json_encode($settings)]);
    }
    return self::$db->exec('UPDATE `' . self::TABLE . '` SET `settings` = ? WHERE `id` = ?', [json_encode($settings), $rowID]);
  }



  private static function getSettingsRowID($type, $value)
  {
    $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `type` = ? AND `value` = ? LIMIT 1', [$type, $value]);
    return (!$rows) ? 0 : $rows[0]['id'];
  }


  public static function clearSettings($type, $value)
  {
    self::$db->exec('DELETE FROM `' . self::TABLE . '` WHERE `type` = ? AND `value` = ?', [self::$types[$type], $value]);
  }


  /**
   * Возвращает настройки для СЦ
   * 
   * @param int $serviceID СЦ
   * 
   * @return array Настройки данного СЦ из "Управление СЦ"
   */
  public static function getSettingsByServiceID($serviceID)
  {
    return self::getSettingsBy('service', $serviceID);
  }


  public static function getSettingsByProblemID($problemID)
  {
    return self::getSettingsBy('problem', $problemID);
  }


  public static function saveSettings1($serviceID, array $settings)
  {
    self::$db->exec('INSERT INTO `settings_service` (`service_id`, `settings`) VALUES (?, ?) 
    ON DUPLICATE KEY UPDATE `settings` = VALUES(settings)', [$serviceID, json_encode($settings)]);
  }


  public static function saveGroups()
  {
    foreach ($_POST['country'] as $id => $group) {
      self::$db->exec('INSERT INTO `settings_country` (`country_id`, `settings`) VALUES (?, ?) 
      ON DUPLICATE KEY UPDATE `settings` = VALUES(settings)', [$id, json_encode($group)]);
    }
  }


  public static function getGroups()
  {
    $rows = self::$db->exec('SELECT `id` AS country_id, `name` FROM `countries` ORDER BY `id`');
    for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
      $rowsSet = self::$db->exec('SELECT `' . self::TABLE . '` FROM `settings_country` WHERE `country_id` = ' . $rows[$i]['country_id']);
      if (!$rowsSet) {
        $rows[$i]['settings'] = [];
        continue;
      }
      $rows[$i]['settings'] = json_decode($rowsSet[0]['settings'], true);
    }
    return $rows;
  }
}

Settings::init();
