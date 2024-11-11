<?php
# Вывод ошибок:
error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(0);
//ini_set('session.cookie_domain', 'crm.r97.ru');
//session_set_cookie_params(7200, "/", "crm.r97.ru", false, false);

ini_set('session.cookie_lifetime',84600);
ini_set('session.gc_maxlifetime',84600);
session_start();

# Ставим зону:
date_default_timezone_set('Europe/Moscow');
setlocale(LC_ALL, array ('ru_RU.UTF-8'));

# Данные для доступа к базе:
$db['host'] = 'localhost';
$db['user'] = 'test_r97_ru';
$db['pass'] = 'TavtvkwryTTII3fv';
$db['base'] = 'test_r97_ru';


# Соединение с базой данных:
$db = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['base']);
mysqli_query($db, 'SET NAMES utf8;');
mysqli_query($db, 'set collation_connection=utf8_general_ci;');


# Переменные:
$config['url'] = 'https://'.$_SERVER['HTTP_HOST'].'/';
$config['url_clean'] = $_SERVER['HTTP_HOST'];
$config['ip'] = $_SERVER['REMOTE_ADDR'];

# Загружаем конфиги в память:
$sql = mysqli_query($db, 'SELECT `name`, `value` FROM `configuration`;');
      while ($row = mysqli_fetch_array($sql)) {
        $config[$row['name']] = rtrim($row['name']);
        $config[$row['name']] = rtrim($row['value']);
      }

/*require_once($_SERVER['DOCUMENT_ROOT'].'/includes/SxGeo.php');
$SxGeo = new SxGeo($_SERVER['DOCUMENT_ROOT'].'/includes/SxGeoCity.dat');
$config['geo'] = $SxGeo->get($config['ip']); */

?>