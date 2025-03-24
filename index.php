<?php

if ($_SERVER['HTTP_HOST'] == 'service.harper.ru') {
header('Location: https://crm.r97.ru');
exit;
}

require_once $_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/back/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/dispatcher.php';

use models\Parts;
use models\User;
use models\Users;
use program\core\App;
use program\core\FS;
use program\core\Time;

App::$config = $config;
App::run();

if ($_GET['query'] == 'cron') {
  require $_SERVER['DOCUMENT_ROOT'] . '/templates/cron.php';
  exit;
}

if ($_GET['query'] == 'mail-cron') {
  require $_SERVER['DOCUMENT_ROOT'] . '/templates/mail-cron.php';
  exit;
}

if (App::$URL[0] == 'reset-password') {
  $result = Users::resetPassword(App::$URLParams);
  echo '<h4>' . $result['message'] . '</h4>';
  exit;
}

if ($_GET['query'] == 'service-info') { // продолжение регистрации СЦ
  require $_SERVER['DOCUMENT_ROOT'].'/templates/serviceinfo.php';
  exit;
}

if (in_array(App::$URL[0], ['login', 'registration', 'recover-password'])) {
  if (User::isActive()) {
    App::redirect('dashboard');
  }
  $token = md5(date('Ymd') . '^8yD3&4nz6HXRa7gtm!$');
  if (!empty($_POST['action']) && !empty($_POST['token']) && $_POST['token'] == $token) {
    switch ($_POST['action']) {
      
      case 'login':
        $result = User::login($_POST['login'], $_POST['password'], $_POST['is_remember']);
        break;

      case 'registration':
        $result = Users::register($_POST);
        if (!$result['is_error']) {
          User::loginAs($_POST['login']);
          App::redirect('service-info');
        }
        break;

      case 'recover-password':
        $result = Users::recoverPassword($_POST);
        break;

      case 'send-message':
        $result = Users::sendPasswordRequestMessage($_POST);
        break;

      default:
        App::redirect('login');
    }
    if (!$result['is_error']) {
      App::redirect('dashboard');
    }
    $message = $result['message'];
  }
  require App::getViewPath(App::$URL[0]);
  exit;
}
 

if (!User::isActive()) {
  App::redirect('login');
}


if (App::$URL[0] == 'logout') {
  User::logout();
  header('Location: /');
  exit;
}


if (isset($dispatcher[App::$URL[0]])) {
  $controller = "controllers\\" . $dispatcher[App::$URL[0]];
  $controller::run();
}


if((App::$URL[0] == 'index' && empty(App::$URLParams)) || App::$URL[0] == 'dashboard') {
  if(User::hasRole('acct')){
    require App::getViewPath('payment2');
    exit;
  }
  if(User::hasRole('store')){
    require App::getViewPath('dashboard-store');
    exit;
  }
  require App::getViewPath('dashboard');
  exit;
}


if(App::$URL[0] == 'upload-parts'){
  require App::getViewPath('upload-parts');
  exit;
}

if(App::$URL[0] == 'staff'){
  if(User::hasRole('admin', 'slave-admin')){
    require App::getViewPath('staff');
  }
  exit;
}

if(App::$URL[0] == 'users'){
  if(!User::hasRole('admin', 'slave-admin')){
    App::redirect('');
  }
  require App::getViewPath('users');
  exit;
}

if(App::$URL[0] == 'user'){
  if(!User::hasRole('admin', 'slave-admin')){
    App::redirect('');
  }
  require App::getViewPath('user');
  exit;
}

if(App::$URL[0] == 'dashboard3'){
  require App::getViewPath('dashboard4');
  exit;
}


if(App::$URL[0] == 'tariff-sync'){
  require App::getViewPath('tariff-sync');
  exit;
}


if(App::$URL[0] == 'part'){
  require App::getViewPath('part');
  exit;
}


if(App::$URL[0] == 'tariff-transport-sync'){
  require App::getViewPath('tariff-transport-sync');
  exit;
}


# Настройки сервисов:
if (App::$URL[0] == 'services-settings') {
  require $_SERVER['DOCUMENT_ROOT'].'/templates/services-settings.php';
  exit;
}

# Тарифы транспорт:
if (App::$URL[0] == 'transport-rate') {
  require $_SERVER['DOCUMENT_ROOT'].'/templates/transport-rate.php';
  exit;
}

# Завод-сборщик:
if (App::$URL[0] == 'plants') {
  require $_SERVER['DOCUMENT_ROOT'].'/templates/plants.php';
  exit;
}

# Настройки пользователя:
if (App::$URL[0] == 'settings-user') {
  require $_SERVER['DOCUMENT_ROOT'].'/templates/settings-user.php';
  exit;
}

# Шаблоны имен запчастей
if (App::$URL[0] == 'part-names-ref') {
  if(User::hasRole('admin', 'store', 'slave-admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/part-names-ref.php';
  }
  exit;
}

# Модели бренда:
if (App::$URL[0] == 'models-brand') {
  require $_SERVER['DOCUMENT_ROOT'].'/templates/models-brand.php';
  exit;
}

# Склады:
if (App::$URL[0] == 'depots') {
  if(!User::hasRole('admin')){
    exit;
  }
  require $_SERVER['DOCUMENT_ROOT'].'/templates/depots.php';
  exit;
}

# Правила заполнения фото:
if ($_GET['query'] == 'filling-rules') {
  require_once($_SERVER['DOCUMENT_ROOT'].'/templates/filling-rules.php');
  exit;
}

# История запчастей:
if ($_GET['query'] == 'parts-log') {
  require_once($_SERVER['DOCUMENT_ROOT'].'/templates/parts-log.php');
  exit;
}

# Дашборд (настройки):
if ($_GET['query'] == 'dashboard-settings') {
  require_once($_SERVER['DOCUMENT_ROOT'].'/templates/dashboard-settings.php');
  exit;
}

# Дашборд:
if ($_GET['query'] == 'dashboard2') {
  # Назначаем шаблон:
  $template = 85;
}


# Дашборд:
if ($_GET['query'] == 'services') {
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 5;
  } else if (User::hasRole('acct')) {
  $template = 120;
  }
}

# Дашборд:
if ($_GET['query'] == 'stat') {
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 86;
  }
}

# Дашборд:
if ($_GET['query'] == 'stat-master') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('taker', 'slave-admin')) {
  $template = 113;
  }
}

# Личная отчетность мастеров:
if ($_GET['query'] == 'reports-personal') {
  
  
  if (User::hasRole('master')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/reports-personal.php';
    exit;
  }
}

# Личная статистика мастеров:
if ($_GET['query'] == 'stat-master-personal') {
  
  
  if (User::hasRole('master')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/stat-master-personal.php';
    exit;
  }
}

# Дашборд:
if ($_GET['query'] == 'logs') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 81;
  }
}

# Дашборд:
if ($_GET['query'] == 'tickets') {
  
  
  # Назначаем шаблон:
  $template = 34;
}

# Дашборд:
if ($_GET['query'] == 'upload-serials') {
  
  
  if (User::hasRole('admin')) {
  # Назначаем шаблон:
  $template = 49;
  }
}

# Дашборд:
if ($_GET['query'] == 'models') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin', 'master', 'taker')) {
  $template = 21;
  }
}

# Дашборд:
if ($_GET['query'] == 'prices-2023') {
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/prices-2023.php';
    exit;
  }
}

# Дашборд:
if ($_GET['query'] == 'prices') {
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 22;
  }
}

# Отправить запчасти:
if ($_GET['query'] == 'parts-ship') {
  
  
  if(!User::hasRole('admin')){
    exit;
  }
  require $_SERVER['DOCUMENT_ROOT'].'/templates/parts-ship.php';
  exit;
}

# Отправки запчастей:
if ($_GET['query'] == 'parts-ships') {
  
  
  if(!User::hasRole('admin')){
    exit;
  }
  require $_SERVER['DOCUMENT_ROOT'].'/templates/parts-ships.php';
  exit;
}

# Транспортные компании:
if ($_GET['query'] == 'transport-companies') {
  
  
  if(!User::hasRole('admin')){
    exit;
  }
  require $_SERVER['DOCUMENT_ROOT'].'/templates/transport-companies.php';
  exit;
}

# Справочник:
if ($_GET['query'] == 'dict') {
  
  
  if(!User::hasRole('admin')){
    exit;
  }
  require $_SERVER['DOCUMENT_ROOT'].'/templates/dict.php';
  exit;
}

# Справочники:
if ($_GET['query'] == 'dicts') {
  
  
  if(!User::hasRole('admin')){
    exit;
  }
  require $_SERVER['DOCUMENT_ROOT'].'/templates/dicts.php';
  exit;
}

# Транспортная компания:
if ($_GET['query'] == 'transport-company') {
  
  
  if(!User::hasRole('admin')){
    exit;
  }
  require $_SERVER['DOCUMENT_ROOT'].'/templates/transport-company.php';
  exit;
}

# Второй тариф (временно):
if ($_GET['query'] == 'prices-2') {
  
  
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/prices-2.php';
    exit;
  }
}

# Массовая смена тарифа:
if ($_GET['query'] == 'mass-tariff-change') {
  
  
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/mass-tariff-change.php';
    exit;
  }
}

# Логи системы:
if ($_GET['query'] == 'log') {
  
  
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/log.php';
    exit;
  }
}

# Запросы на утилизацию:
if ($_GET['query'] == 'disposal-requests') {
  
  
  require $_SERVER['DOCUMENT_ROOT'].'/templates/disposal-requests.php';
  exit;
}

# Запрос на утилизацию:
if ($_GET['query'] == 'disposal-request') {
  
  
  require $_SERVER['DOCUMENT_ROOT'].'/templates/disposal-request.php';
  exit;
}

# Тарифы монтаж:
if ($_GET['query'] == 'tariffs-install') {
  
  
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/tariffs-install.php';
    exit;
  }
}

# Массовая смена тарифа на транспорт:
if ($_GET['query'] == 'mass-transport-tariff-change') {
  
  
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/mass-transport-tariff-change.php';
    exit;
  }
}


# Дашборд:
if ($_GET['query'] == 'transfer') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 72;
  }
}

# Второй тариф (временно):
if ($_GET['query'] == 'transfer-2') {
  
  
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/transfer-2.php';
    exit;
  }
}

# Третий тариф (временно):
if ($_GET['query'] == 'transfer-3') {
  
  
  if (User::hasRole('admin')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/transfer-3.php';
    exit;
  }
}

# Дашборд:
if ($_GET['query'] == 'providers') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 39;
  }
}

# Дашборд:
if ($_GET['query'] == 'categories') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 54;
  }
}

# Дашборд:
if ($_GET['query'] == 'problems') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 63;
  }
}

# Дашборд:
if ($_GET['query'] == 'problems-brand') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 147;
  }
}

# Дашборд:
if ($_GET['query'] == 'repair-types') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 64;
  }
}

# Дашборд:
if ($_GET['query'] == 'repair-types-brand') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 144;
  }
}

# Дашборд:
if ($_GET['query'] == 'groups') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 45;
  }
}

# Дашборд:
if ($_GET['query'] == 'repairmans') {
  
  
  # Назначаем шаблон:
  $template = 42;
}

# Дашборд:
if ($_GET['query'] == 'parts') {
  if (User::hasRole('admin', 'slave-admin', 'master', 'service')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/parts.php';
    exit;
  }
}

if ($_GET['query'] == 'part-arrival') {
  if (User::hasRole('admin', 'slave-admin', 'store')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/part-arrival.php';
    exit;
  }
}

if ($_GET['query'] == 'parts-arrivals') {
  if (User::hasRole('admin', 'slave-admin', 'store')) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/parts-arrivals.php';
    exit;
  }
}

# Дашборд:
if ($_GET['query'] == 'get-parts-list') {
  $template = 128;
}

# Дашборд:
if ($_GET['query'] == 'add-repair') {
  $template = 51;
}

# Дашборд:
if ($_GET['query'] == 'return-dashboard') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('master', 'taker', 'slave-admin')) {
  $template = 115;
  }
}

# Дашборд:
if ($_GET['query'] == 'return-finance') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('master', 'taker', 'slave-admin')) {
  $template = 136;
  }
}

# Дашборд:
if ($_GET['query'] == 'payments-from-combined') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('acct')) {
  $template = 125;
  }
}

# Дашборд:
if ($_GET['query'] == 'payments-v3-archive') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('acct')) {
  $template = 126;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-price') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 24;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-repairman') {
  
  
  # Назначаем шаблон:
  $template = 41;
}

# Дашборд:
if ($_GET['query'] == 'add-parts') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 31;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-issue') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin')) {
  $template = 71;
  }
}


# Дашборд:
if ($_GET['query'] == 'add-contrahens') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin')) {
  $template = 133;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-model') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  $template = 26;
  }
}


# Дашборд:
if ($_GET['query'] == 'add-provider') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 38;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-categories') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 52;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-group') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 44;
  }
}

# Дашборд:
if ($_GET['query'] == 'requests') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 30;
  }
}

# Дашборд:
if ($_GET['query'] == 'requests') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 30;
  }
}

# Дашборд:
if ($_GET['query'] == 'my-services') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 82;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-my-service') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 83;
  }
}

# Дашборд:
if ($_GET['query'] == 'issues') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin')) {
  $template = 66;
  }
}

# Дашборд:
if ($_GET['query'] == 'contrahens') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin')) {
  $template = 132;
  }
}

# Дашборд:
if ($_GET['query'] == 'mass-upload') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('taker', 'slave-admin')) {
  $template = 99;
  }
}

# Дашборд:
if ($_GET['query'] == 'returns') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('taker', 'slave-admin')) {
  $template = 100;
  }
}

# Дашборд:
if ($_GET['query'] == 'parts-history') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 95;
  }
}

# Детализированный отчет:
if ($_GET['query'] == 'get-detail-report') {
models\Documents::displayDocument('detail-report', App::$URLParams);
exit;
}


# Контакты:
if ($_GET['query'] == 'cities') {
  
  
      # Назначаем шаблон:
      if (User::hasRole('admin')) {
      $template = 67;
      }
}

# Контакты:
if ($_GET['query'] == 'countries') {
  
  
      # Назначаем шаблон:
      if (User::hasRole('admin')) {
      $template = 129;
      }
}

# Контакты:
if ($_GET['query'] == 'brands') {
  
  
      # Назначаем шаблон:
      if (User::hasRole('admin')) {
      $template = 89;
      }
}

# Контакты:
if ($_GET['query'] == 'combined') {
  
  
      # Назначаем шаблон:
      if (User::hasRole('acct')) {
      $template = 119;
      } else if (User::hasRole('admin')) {
      $template = 104;
      }
}

# Контакты:
if ($_GET['query'] == 'clients') {
  
  
      # Назначаем шаблон:
      if (User::hasRole('admin', 'slave-admin', 'taker')) {
      $template = 96;
      }
}

# Контакты:
if ($_GET['query'] == 'add-brands') {
  
  
      # Назначаем шаблон:
      if (User::hasRole('admin')) {
      $template = 88;
      }
}

# Дашборд:
if ($_GET['query'] == 'config') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 36;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-ticket') {
  
  
  # Назначаем шаблон:
  $template = 57;
}



# Дашборд:
if ($_GET['query'] == 'add-problem') {
  
  
  # Назначаем шаблон:
  $template = 61;
}

# Дашборд:
if ($_GET['query'] == 'add-client') {
  
  
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  # Назначаем шаблон:
  $template = 97;
  }
}

# Дашборд:
if ($_GET['query'] == 'plans') {
  
  
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  # Назначаем шаблон:
  $template = 114;
  }
}


# Дашборд:
if ($_GET['query'] == 'add-city') {
  
  
  # Назначаем шаблон:
  $template = 68;
}

# Дашборд:
if ($_GET['query'] == 'add-country') {
  
  
  # Назначаем шаблон:
  $template = 131;
}

# Дашборд:
if ($_GET['query'] == 'add-repair-type') {
  
  
  # Назначаем шаблон:
  $template = 62;
}

# Дашборд:
if ($_GET['query'] == 'add-repair-type-brand') {
  
  
  # Назначаем шаблон:
  $template = 145;
}


# Дашборд:
if ($_GET['query'] == 'edit-repair' && $_GET['id']) {
  
  
  # Назначаем шаблон:
//$template = 20;
  $template = 2000;
}

# Дашборд:
if ($_GET['query'] == 'edit-categories' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 53;
}

# Дашборд:
if ($_GET['query'] == 'edit-problem' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 60;
}

# Дашборд:
if ($_GET['query'] == 'edit-repair-type' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 59;
}


# Дашборд:
if ($_GET['query'] == 'edit-repair-type-brand' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 146;
}

# Дашборд:
if ($_GET['query'] == 'edit-city' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 69;
}

# Дашборд:
if ($_GET['query'] == 'edit-country' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 130;
}

# Дашборд:
if ($_GET['query'] == 'edit-repair' && $_GET['id'] && $_GET['step']) {
  
  
  # Назначаем шаблон:
  if ($_GET['step'] == 1) {
  $template = 19;
  }
  if ($_GET['step'] == 'software') {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-software.php';
    exit; 
  }
  if ($_GET['step'] == 2) {
  require $_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-repair.php';
  exit; 
    
  if (User::hasRole('slave-admin', 'master')) {

  if ($_COOKIE['in_dev'] == 1) {
  $repair_a = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
  $model_a = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $repair_a['model_id']).'\' LIMIT 1;'));
  $check_brand = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands` WHERE `name` = \''.mysqli_real_escape_string($db, $model_a['brand']).'\' and `parts` = 1 LIMIT 1;'));
  if ($check_brand['COUNT(*)'] > 0) {
  $template = 140;
  } else {
  $template = 127;
  }

  } else {
  $template = 127;
  }


  } else {
  $template = 73;

  }


  }
  if ($_GET['step'] == 22) {
  $template = 73;
  }
  if ($_GET['step'] == 3) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-parts.php';
     exit;
  }
  if ($_GET['step'] == 4) {
    require $_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-photos.php';
    exit;
  }
  if ($_GET['step'] == 5) {
  $template = 56;
  }
  if ($_GET['step'] == 6) {
  $template = 58;
  } 
  /* if ($_GET['step'] == 9) {
    require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-photos.php');
  } */
}


# Дашборд:
if ($_GET['query'] == 'edit-repairman' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 40;
}


# Дашборд:
if ($_GET['query'] == 'edit-price' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 23;
}

# Дашборд:
if ($_GET['query'] == 'edit-provider' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 37;
  }
}

# Дашборд:
if ($_GET['query'] == 'edit-issue' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin')) {
  $template = 65;
  }
}

# Дашборд:
if ($_GET['query'] == 'edit-contrahens' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin')) {
  $template = 134;
  }
}

# Дашборд:
if ($_GET['query'] == 'prices-service' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 55;
  }
}

# Дашборд:
if ($_GET['query'] == 'cats-service' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 103;
  }
}



# Дашборд:
if ($_GET['query'] == 'models-service' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  $template = 106;
  }
}

# Дашборд:
if ($_GET['query'] == 'edit-group' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  # Назначаем шаблон:
  $template = 43;
  }
}

# Дашборд:
if ($_GET['query'] == 'copy-group' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  # Назначаем шаблон:
  $template = 139;
  }
}


# Дашборд:
if ($_GET['query'] == 'del-model' && $_GET['id']) {
  
  
  if (User::hasRole('admin', 'slave-admin')) {
  del('models', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-service-model' && $_GET['id']) {
  
  
  if (User::hasRole('admin', 'slave-admin')) {
  del('models_users', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-personal' && $_GET['id']) {
  
  
  del('users', $_GET['id']);
  mysqli_query('DELETE FROM `plans` where `user_id` = '.$_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}


# Дашборд:
if ($_GET['query'] == 'del-photo' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('photos', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-video' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('videos', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}



# Дашборд:
if ($_GET['query'] == 'del-issue' && $_GET['id']) {
  
  
  if (User::hasRole('admin', 'slave-admin')) {
  del('issues', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-contrahens' && $_GET['id']) {
  
  
  if (User::hasRole('admin', 'slave-admin')) {
  del('contrahens', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'parts-require' && $_GET['id']) {
  
  
  require_parts($_GET['id']);
   header('Location: /dashboard/');
  //header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-problem' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('details_problem', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-city' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del_city('cityfull', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-repair-type-brand' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('repair_type_brand', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-repair-type' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('repair_type', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-repairman' && $_GET['id']) {
  
  
  del('repairmans', $_GET['id'], User::getData('id'));
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-provider' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('providers', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}


# Дашборд:
if ($_GET['query'] == 'del-group' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('groups', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-tickets' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del_ticket($_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-brands' && $_GET['id']) {
  
  
  del('brands', $_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-parts' && $_GET['id']) {
  
  
  Parts::delete($_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-price' && $_GET['id']) {
  
  
  del('prices', $_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-service') {
  
  
  if (User::hasRole('admin')) {

 /* if ($_GET['id'])
  del('requests', $_GET['id']);

  if ($_GET['user_id'])
  del('users', $_GET['user_id']);  */

  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}


# Дашборд:
if ($_GET['query'] == 'del-categories' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  del('cats', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-client' && $_GET['id']) {
  
  
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  del('clients', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-return' && $_GET['id']) {
  
  
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  del_return($_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-combined' && $_GET['id']) {
  
  
  if (User::hasRole('admin', 'slave-admin', 'acct')) {
  del_combined($_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'mod_true' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  true($_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'repair_done' && $_GET['id']) {
  
  
  repair_done($_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'repair_done2' && $_GET['id']) {
  
  
  repair_done($_GET['id']);
  header('Location: /dashboard/');
  exit;
}

# Дашборд:
if ($_GET['query'] == 'repair_personal_done' && $_GET['id']) {
  
  
  repair_personal_done($_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'del-repair' && $_GET['id']) {
  
  
  repair_del($_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'comeback-repair' && $_GET['id']) {
  
  
  repair_comeback($_GET['id']);
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

if ($_GET['query'] == 'mod_false' && $_GET['id']) {
  
  
  if (User::hasRole('admin')) {
  false($_GET['id'], $_POST['comment']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'block-service' && $_GET['id']) {
  if (User::hasRole('admin')) {
  Users::setStatus(Users::IS_BLOCKED, $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'unblock-service' && $_GET['id']) {
  if (User::hasRole('admin')) {
    Users::setStatus(Users::IS_ACTIVE, $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'block-personal' && $_GET['id']) {
  
  
  if (User::hasRole('slave-admin')) {
  block('users', $_GET['id']);

  }

 header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'refresh-sc') {
  
  
  if (User::hasRole('slave-admin')) {
  $template = 117;
  } else {
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
  }

}

# Дашборд:
if ($_GET['query'] == 'unblock-personal' && $_GET['id']) {
  
  
  if (User::hasRole('slave-admin')) {
  unblock('users', $_GET['id']);
  }
  header('Location: '.$_SERVER['HTTP_REFERER']);
  exit;
}

# Дашборд:
if ($_GET['query'] == 'edit-model' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 25;
}

# Дашборд:
if ($_GET['query'] == 'edit-model-service' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 116;
}


# Дашборд:
if ($_GET['query'] == 'edit' && $_GET['service_id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 6;
  } else if (User::hasRole('acct')) {
  $template = 121;
  }
}

# Дашборд:
if ($_GET['query'] == 'my_edit' && $_GET['service_id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 84;
  }
}

# Дашборд:
if ($_GET['query'] == 'documents' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 90;
}

# Дашборд:
if ($_GET['query'] == 'edit_document' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 92;
  }
}
# Дашборд:
if ($_GET['query'] == 'edit-client' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  $template = 98;
  }
}

if ($_GET['query'] == 'return' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin', 'slave-admin', 'taker')) {
  $template = 101;
  }
}

# Дашборд:
if ($_GET['query'] == 'add_document' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 91;
  }
}

# Дашборд:
if ($_GET['query'] == 'add-personal') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('slave-admin')) {
  $template = 109;
  }
}

# Дашборд:
if ($_GET['query'] == 're-edit-repair' && $_GET['id']) {
  # Назначаем шаблон:
  if (User::hasRole('slave-admin', 'taker')) {
    $repair = models\Repair::getRepairByID($_GET['id']);
    mysqli_query($db, 'UPDATE `repairs` SET
    `status_admin` = "В работе",
    `repair_done` = 0 
    WHERE `id` = '.$_GET['id']) or mysqli_error($db);
     models\Log::repair(24, 'Статус "'.$repair['status'].'" изменен на "В работе".', $_GET['id']);
   header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
  }
}

# Дашборд:
if ($_GET['query'] == 'edit-parts' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin') || User::hasRole('master')) {
  $template = 33;
  }
}

# Дашборд:
if ($_GET['query'] == 'edit-personal' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('slave-admin')) {
  $template = 108;
  }
}

# Дашборд:
if ($_GET['query'] == 'edit-brands' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 87;
  }
}

# Дашборд:
if ($_GET['query'] == 'mod-request' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 29;
  }
}

# Дашборд:
if ($_GET['query'] == 'reports') {
  
  
  //if (User::hasRole('admin')) {
  $template = 75;
  //}
}

# Дашборд:
if ($_GET['query'] == 'service-info-full' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin') || User::hasRole('acct')) {
  $template = 35;
  }
}

# Дашборд:
if ($_GET['query'] == 'settings') {
  
  
  # Назначаем шаблон:
  if (!User::hasRole('admin')) {
  $template = 7;
  }
}

# Дашборд:
if ($_GET['query'] == 'personal') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('slave-admin')) {
  $template = 107;
  }
}

# Дашборд:
if ($_GET['query'] == 'notify') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin') || User::hasRole('acct')) {
  $template = 74;
  }
}

# Дашборд:
if ($_GET['query'] == 'manual') {
  
  
  # Назначаем шаблон:
  $template = 70;
}

# Дашборд:
if ($_GET['query'] == 'wait') {
  
  
  # Назначаем шаблон:
  $template = 27;
}

# Дашборд:
if ($_GET['query'] == 'documents' && !$_GET['id']) {
  
  
  # Назначаем шаблон:
  $template = 93;
}

# Дашборд:
if ($_GET['query'] == 'block') {
  
  
  # Назначаем шаблон:
  $template = 50;
}

# Дашборд:
if ($_GET['query'] == 'billing-info') {
  
  
  # Назначаем шаблон:
  $template = 77;
}

# Дашборд:
if ($_GET['query'] == 'billing-info-admin' && $_GET['id']) {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')  || User::hasRole('acct')) {
  $template = 78;
  }
}

# Дашборд:
if ($_GET['query'] == 'login-like' && $_GET['id']) {
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
      $user_log = Users::getUser(['id' => $_GET['id']]);
      User::loginAs($user_log['login']);
      header('Location: /dashboard/');
      exit;
  }
}




# Дашборд:
if ($_GET['query'] == 'payment') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('acct')) {
  $template = 118;
  } else {
  $template = 79;
  }

}

if ($_GET['query'] == 're-repaired') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('slave-admin')) {
  $template = 138;
  }
}

if ($_GET['query'] == 'repeat-models') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('slave-admin')) {
  $template = 1380;
  }
}

# Дашборд:
if ($_GET['query'] == 'payments') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 80;
  }
}

# Дашборд:
if ($_GET['query'] == 'payments-v2') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 102;
  }
}

# Дашборд:
if ($_GET['query'] == 'payments-v3') {
  
  
  # Назначаем шаблон:
  if (User::hasRole('admin')) {
  $template = 105;
  } else if (User::hasRole('acct')) {
  $template = 122;
  }
}

# Дашборд:
if ($_GET['query'] == 'payments-sended') {
  
  
  # Назначаем шаблон:
    if (User::hasRole('acct')) {
  $template = 123;
  }
}

# Дашборд:
if ($_GET['query'] == 'payments-payed') {
  
  
  # Назначаем шаблон:
    if (User::hasRole('acct')) {
  $template = 124;
  }
}

# Ищем ЧПУ новостей:
if ($_GET['query'] == 'news') {
      # Обработчик страницы:
      $news = news_list($_GET['query'], $_GET['page']);
      # Назначаем шаблон:
      $template = 11;
}

# Вызов сравнения товаров:
if (preg_match("/sravnit-/i", $_GET['query'])) {
      # Обработчик страницы:
      $compare = compare();
      # Назначаем шаблон:
      $template = 4;
}

# Вызов сравнения товаров:
if ($_GET['query'] == 'search') {
      # Обработчик страницы:
      $search = search($_POST['search']);
      # Назначаем шаблон:
      $template = 12;
}



# Контакты:
if ($_GET['query'] == 'contacts') {
      # Назначаем шаблон:
      $template = 6;
}


if ($_GET['query'] == 'act-a3') {
       $sql = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = \''.$_GET['return_id'].'\' order by `id` DESC;');
       $archivePath = $_SERVER['DOCUMENT_ROOT'] . '/adm/excel/archive2/';
       while ($row = mysqli_fetch_array($sql)) {
        if ($row['repair_final'] == 1) {
          $techPath = getTechDoc($row['id'], true);
          copy($_SERVER['DOCUMENT_ROOT'] . '/' . $techPath, $archivePath . 'dno/'.$row['id'].'_ato.xlsx');  
        }
        if ($row['repair_final'] == 3) {
          $techPath = getTechDoc($row['id'], true);
          copy($_SERVER['DOCUMENT_ROOT'] . '/' . $techPath, $archivePath . 'ovgr/'.$row['id'].'_ato.xlsx'); 
        }
       /* if (in_array($row['repair_type_id'], array(5, 23))) {
        file_put_contents('adm/excel/archive2/ato/'.$row['id'].'_tech.xlsx', file_get_contents('http://service.harper.ru/get-tech/'.$row['id'].'/'));
        } */
        if (in_array($row['repair_type_id'], array(4))) {
          $rejectPath = getRejectDoc($row['id'], true);
          copy($_SERVER['DOCUMENT_ROOT'] . '/' . $rejectPath, $archivePath . 'anrp/'.$row['id'].'_reject.xlsx'); 
        }
        if ($row['status_admin'] == 'Подтвержден') {
          $actPath = getActDoc($row['id'], true);
          copy($_SERVER['DOCUMENT_ROOT'] . '/' . $actPath, $archivePath . 'avr/'.$row['id'].'_act.xlsx'); 
        }
       }

        $ZipFileName = 'Партия__'.$_GET['return_id'].'_акты.zip';


        @unlink($pathdir.$ZipFileName);
        $nameArhive = $ZipFileName;
        $zip = new ZipArchive;
        $file = $pathdir.$ZipFileName;

        if ($zip -> open($nameArhive, ZipArchive::CREATE) === TRUE){

            $pathdir='adm/excel/archive2/ovgr/';
            $dir = opendir($pathdir);
            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    // echo $pathdir.$file.'<br>';
                    $zip -> addFile($pathdir.$file, 'ovgr/'.$file);

                }

            }
            $pathdir='adm/excel/archive2/dno/';
            $dir = opendir($pathdir);
            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    // echo $pathdir.$file.'<br>';
                    $zip -> addFile($pathdir.$file, 'dno/'.$file);

                }

            }
             $pathdir='adm/excel/archive2/anrp/';
             $dir = opendir($pathdir);
            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    // echo $pathdir.$file.'<br>';
                    $zip -> addFile($pathdir.$file, 'anrp/'.$file);

                }

            }
            $pathdir='adm/excel/archive2/avr/';
            $dir = opendir($pathdir);
            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    // echo $pathdir.$file.'<br>';
                    $zip -> addFile($pathdir.$file, 'avr/'.$file);

                }

            }

            /*$pathdir='adm/excel/archive2/ato/';
            $dir = opendir($pathdir);
            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    // echo $pathdir.$file.'<br>';
                    $zip -> addFile($pathdir.$file, 'ato/'.$file);

                }

            }  */

            $zip -> close();

        }

        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($ZipFileName));
        header("Content-Disposition: attachment; filename=\"".basename($ZipFileName)."\"");
        readfile($ZipFileName);

            foreach (glob('adm/excel/archive2/anrp/*') as $file) {
                unlink($file);
            }
             /*foreach (glob('adm/excel/archive2/ato/*') as $file) {
                unlink($file);
            }  */
            foreach (glob('adm/excel/archive2/avr/*') as $file) {
                unlink($file);
            }
             foreach (glob('adm/excel/archive2/dno/*') as $file) {
                unlink($file);
            }

             foreach (glob('adm/excel/archive2/ovgr/*') as $file) {
                unlink($file);
            }        @unlink($ZipFileName);
        exit;

/*
1
3
/get-act/<?=$_GET['id'];?>/
*/

}

/*if ($_GET['query'] == 'act-a3') {

       
       
       $sql = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = \''.$_GET['return_id'].'\' order by `id` DESC;');
       while ($row = mysqli_fetch_array($sql)) {
        if ($row['repair_final'] == 1) {
        file_put_contents('adm/excel/archive2/notfound/'.$row['id'].'_act.xlsx', file_get_contents('http://service.harper.ru/excel3/'.$_GET['return_id'].'/'));
        }
       }


        exit;



}  */

# Дашборд:
if ($_GET['query'] == 'get-act') {
  getActDoc($_GET['id']);
  exit;
}
function getActDoc($id, $onlyGenerate = false){
  global $db;
  $_GET['id'] = $id;
 // if (User::hasRole('admin')) {
   models\Repair::updateOutDate($_GET['id']);
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['parts_info']['sum'] = 0;
        $content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/ract.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $sheet->setCellValue('A2', 'АКТ ВЫПОЛНЕННЫХ РАБОТ № '.$content['id']);
        if ($content['service_id'] == 33) {
        $sheet->setCellValue('A5', $content['service_info']['name']);
        } else {
        $sheet->setCellValue('A5', $content['service_info']['name_public']);
        }
        $sheet->setCellValue('W8', $content['rsc']);
        $sheet->setCellValue('W9', $content['cat_info']['name']);
        $sheet->setCellValue('W10', $content['model']['name']);
        $sheet->setCellValueExplicit('W11', $content['serial'], PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('W12', date("d.m.Y", strtotime($content['receive_date'])));
        if ($content['client_type'] == 2) {
            $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
            $sheet->setCellValue('A7', 'Принят от:');
            $sheet->setCellValue('W7', $client['name']);
        } else if ($content['client_type'] == 1) {
            $sheet->setCellValue('A7', 'Принят от:');
            $sheet->setCellValue('W7', $content['client']);
        }
        if (!Time::isEmpty($content['sell_date'])) {
        $sheet->setCellValue('W13', date("d.m.Y", strtotime($content['sell_date'])));
        } else {
        $sheet->setCellValue('W13', '');
        }
        $sheet->setCellValue('A14', 'Статус приёма');
        $sheet->setCellValue('W14', 'Клиентский');
        $sheet->setCellValue('W15', $status_array[$content['status_id']]);
        $sheet->setCellValue('W16', $content['client'].', '.$content['address'].', '.$content['phone']);
        $sheet->setCellValue('W18', $content['id']);
        $sheet->setCellValue('W19', implode(', ', explode('|', $content['complex'])));
        $sheet->setCellValue('W20', implode(', ', explode('|', $content['visual'])).' '.$content['visual_comment']);
        $sheet->setCellValue('W21', $content['bugs']);
        $sheet->setCellValue('W22', $content['comment']);
        $sheet->setCellValue('W30', Time::format($content['finish_date']));

        /*$xls->getActiveSheet()->getStyle('A26:A'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('U26:U'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('AT26:AT'.$xls->getActiveSheet()->getHighestRow())
              ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L26:L'.$xls->getActiveSheet()->getHighestRow())
              ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L32:L'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getColumnDimension('L')->setWidth(20);  */
        //$xls->getActiveSheet()->getRowDimension(25)->setRowHeight(strlen(get_content_by_id('repair_type', $content['parts_info']['repair_id'])['name'])/1.2);



        //Ремонт:
        if (count(array_filter($content['parts_info'])) > 0) {
        $id = 26;
        $xls->getActiveSheet()->insertNewRowBefore(27, count($content['parts_info'])-1);
        foreach (array_filter($content['parts_info']) as $parts) {
        $xls->getActiveSheet()->getRowDimension($id)->setRowHeight(40);
        if (count(array_filter($content['parts_info'])) > 1) {
        $xls->getActiveSheet()->mergeCells('A'.$id.':K'.$id);
        $xls->getActiveSheet()->mergeCells('W'.$id.':BA'.$id);
        $xls->getActiveSheet()->mergeCells('BB'.$id.':BH'.$id);
        }

        //$xls->getActiveSheet()->mergeCells('A'.$id.':K'.$id);

        if (is_numeric($parts['name']) && $parts['ordered_flag'] == 1) {
        $part_array = part_by_id($parts['name']);
        $name_part = $part_array['list'];
        } else {
        $name_part = $parts['name'];
        }

        $sheet->setCellValue('A'.$id, get_content_by_id('repair_type', $parts['repair_type_id'])['name']);
        $sheet->setCellValue('W'.$id, $name_part);
        $sheet->setCellValue('BB'.$id, $parts['qty']);
        $sheet->setCellValue('L'.$id, $parts['position']);

        $sheet->getStyle('BB'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $sheet->getStyle('A'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $sheet->getStyle('W'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

       $style = array('borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        ));
        unset($name_part);
        $xls->getActiveSheet()->getStyle('A'.$id.':BH'.$id)->applyFromArray($style);

        $sheet->getStyle('AT'.$id)->getAlignment()->applyFromArray(
            array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $sheet->getStyle('L'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

           $id++;


        }
        }


        //$xls->getActiveSheet()->getColumnDimension('A')->setWidth(strlen($content['works'])/2.5);
        /*$sheet->getStyle('A25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('L25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('U25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);*/


      $date1 = new DateTime($content['begin_date']);
      $date1_ready = $date1->format('d.m.Y');
      
        $nums = [28, 30, 32, 34];

         // Остаток:
        $sheet->setCellValue('W'.($nums[0]+count(array_filter($content['parts_info']))), $date1_ready);
        $date_app_date = @DateTime::createFromFormat('Y.m.d', $content['app_date']);
        if($date_app_date){
          $sheet->setCellValue('W'.($nums[1]+count(array_filter($content['parts_info']))), $date_app_date->format('d.m.Y'));
        }
       

        if ($content['service_id'] == 33) {
        $sheet->setCellValue('L'.($nums[2]+count(array_filter($content['parts_info']))), 'Клюев Александр Александрович');
        } else {
        $sheet->setCellValue('L'.($nums[2]+count(array_filter($content['parts_info']))), $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);
        }

        if ($content['service_id'] == 33) {
        $sheet->setCellValue('A'.($nums[3]+count(array_filter($content['parts_info']))), 'Руководитель АСЦ');
        }

        $sheet->setCellValue('L'.($nums[3]+count(array_filter($content['parts_info']))), $content['service_info']['req_gen_fio']);
   
        //original code...
       /* $titlecolwidth = $sheet->getColumnDimension('A')->getWidth();
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth($titlecolwidth);    */
        //echo $titlecolwidth;
        if(User::hasRole('service')){
          $sheet->getProtection()->setSheet(true);
        }
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);
        if($onlyGenerate){
          return $new_file;
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="act_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        return $new_file;

  //}
}

# Дашборд:
if ($_GET['query'] == 'act-from') {
  
  
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['return_info'] = return_info($_GET['id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/act-from-to.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue('C5', $config['a1_org']);
        $sheet->setCellValue('A14', $config['a1_sender']);
        $sheet->setCellValue('D14', $config['a1_receiver']);
        $sheet->setCellValue('I10', $_GET['id']);
        $sheet->setCellValue('J10', $content['return_info']['date']);

        $sheet->setCellValue('U26', $parts_name);
        $sheet->setCellValue('AT26', $parts_count);
        $sheet->setCellValue('L26', $parts_position);
        //

        $sql = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$_GET['id']);
        $num = mysqli_num_rows($sql);

        if ($num > 0) {
        $sheet->setCellValue('J20', $num);

        if ($num > 1) { $xls->getActiveSheet()->insertNewRowBefore(20,$num-1); }

        $id = 19;
        $id_num = 1;
        while ($row = mysqli_fetch_array($sql)) {
        $row['model'] = model_info($row['model_id']);

        $sheet->setCellValue('A'.$id, $id_num);
        $sheet->setCellValue('B'.$id, $row['rsc']);
        $sheet->setCellValue('C'.$id, $row['model']['name']);
        $sheet->setCellValue('D'.$id, $row['model']['model_id']);
        $sheet->setCellValue('E'.$id, $row['serial']);
        $sheet->setCellValue('F'.$id, $row['bugs']);
        $sheet->setCellValue('G'.$id, implode(', ', array_filter(explode('|', $row['complex']))));
        $sheet->setCellValue('H'.$id, implode(', ', array_filter(explode('|', $row['visual']))).' '.$row['visual_comment']);
        $sheet->setCellValue('I'.$id, $row['client']);
        $sheet->setCellValue('J'.$id, 1);

        $id++;
        $id_num++;
        }
        }


        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="act_from_'.$_GET['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

if ($_GET['query'] == 'show-double') {
  
  

  if (User::hasRole('admin', 'slave-admin', 'taker', 'master')) {
  $template = 135;
  }

}

# Дашборд:
if ($_GET['query'] == 'act-to') {
  
  
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['return_info'] = return_info($_GET['id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/act-from-to2.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue('C5', $config['a2_org']);
        $sheet->setCellValue('A14', $config['a2_sender']);
        $sheet->setCellValue('D14', $config['a2_receiver']);
        $sheet->setCellValue('I10', $_GET['id']);
        $last = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$_GET['id'].' ORDER BY `app_date` DESC LIMIT 1'));

        $sheet->setCellValue('J10', $content['return_info']['date_out']);

        $sheet->setCellValue('U26', $parts_name);
        $sheet->setCellValue('AT26', $parts_count);
        $sheet->setCellValue('L26', $parts_position);
        //

        $sql = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$_GET['id']);
        $num = mysqli_num_rows($sql);

        if ($num > 0) {
        $sheet->setCellValue('J20', $num);

        if ($num > 1) { $xls->getActiveSheet()->insertNewRowBefore(20,$num-1); }



        $id = 19;
        $id_num = 1;
        while ($row = mysqli_fetch_array($sql)) {
        $row['model'] = model_info($row['model_id']);

        $sheet->setCellValue('A'.$id, $id_num);
        $sheet->setCellValue('B'.$id, $row['rsc']);
        $sheet->setCellValue('C'.$id, $row['model']['name']);
        $sheet->setCellValue('D'.$id, $row['model']['model_id']);
        $sheet->setCellValue('E'.$id, $row['serial']);
        $sheet->setCellValue('F'.$id, $row['bugs']);
        $sheet->setCellValue('G'.$id, implode(', ', array_filter(explode('|', $row['complex']))));
        $sheet->setCellValue('H'.$id, implode(', ', array_filter(explode('|', $row['visual']))).' '.$row['visual_comment']);
        $sheet->setCellValue('I'.$id, $row['client']);
        $sheet->setCellValue('J'.$id, 1);

        $id++;
        $id_num++;
        }
        }


        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="act_from_'.$_GET['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-reject') {
  getRejectDoc($_GET['id']);
  exit;
}
  function getRejectDoc($id, $onlyGenerate = false){
    global $db;
    $_GET['id'] = $id;
 // if (User::hasRole('admin')) {
  models\Repair::updateOutDate($_GET['id']);
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));

        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['city'] = get_city($content['service_info']['city']);
        /*if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }*/

        $new_file = 'adm/excel/files/1.xlsx';

        if ($content['model']['brand'] == 'ZARGET' || $content['model']['brand'] == 'FRIO' || $_GET['id'] == 1100) {
        copy('adm/excel/zarget.xlsx', $new_file);
        } else {
        copy('adm/excel/2.xlsx', $new_file);
        }

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();


        // заргет
        if ($content['model']['brand'] == 'ZARGET' || $content['model']['brand'] == 'FRIO') {

         $xls->getActiveSheet()
        ->getStyle('B12')
        ->getNumberFormat()
        ->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_TEXT
        );
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');

        $sheet->setCellValue('B3', $content['id']);
        $sheet->setCellValue('H27', $content['id']);
        $sheet->setCellValue('B4', $content['city']['fcity_name']);
        $sheet->setCellValue('H4', 'СЦ'.$content['service_id']);
        /*if ($content['model']['brand'] == 'TESLER') {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } else {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } */
        $sheet->setCellValue('B5', $content['service_info']['name'].', '.$content['city']['fcity_name'].', '.$content['service_info']['phisical_adress']);
        $sheet->setCellValue('B6', $content['service_info']['phones']);
        $sheet->setCellValue('B13', 1);
        $sheet->setCellValue('B7', $content['service_info']['contact_email']);
        $sheet->setCellValue('E8', $content['cat_info']['name']);
        $sheet->setCellValue('B9', $content['model']['name']);
        $sheet->setCellValue('B10', $content['serial']);
        $sheet->setCellValue('B11', (($content['sell_date'] != '0000-00-00') ? date("d.m.Y", strtotime($content['sell_date'])) : ''));
        $sheet->setCellValue('B12', date("d.m.Y", strtotime($content['receive_date'])));
        $sheet->setCellValue('B14', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B15', $content['bugs']);
        $sheet->setCellValue('B17', $content['client']);
        $sheet->setCellValue('B18', $content['address']);
        $sheet->setCellValue('B19', $content['phone']);
        $sheet->setCellValue('B20', $content['name_shop']);
        $sheet->setCellValue('B21', $content['address_shop']);
        $sheet->setCellValue('B22', $content['phone_shop']);

        if ($content['disease']) {
        $issue = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `issues` WHERE `id` = \''.mysqli_real_escape_string($db, $content['disease']).'\' LIMIT 1;'));
        }
        $sheet->setCellValue('B23', $issue['name']);

        $name_add = ($content['parts_info']['name'] != '') ? '('.$content['parts_info']['name'].')' : '';
        $sheet->setCellValue('B29', get_content_by_id('details_problem', $content['parts_info']['problem_id'])['repair_name'].'. '.preg_replace('/\(.*?\)/', '', get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name']).' '.$name_add);

        $sheet->setCellValue('B16', $content['rsc']);
        $sheet->setCellValue('B18', Time::format($content['sell_date']));
        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B24', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        //$sheet->setCellValue('B29', implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);

        if ($content['service_id'] == 33) {
        $sheet->setCellValue('B32', 'Клюев Александр Александрович');
        } else {
        $sheet->setCellValue('B32', $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);
        }
        $sheet->setCellValue('B34', $content['service_info']['req_gen_fio']);

        $sheet->setCellValue('B27', $content['parts_info']['name']);
        $sheet->setCellValue('F27', date("d.m.Y"));
        $sheet->setCellValue('B36', date("d.m.Y"));


        } else {

        $xls->getActiveSheet()
        ->getStyle('B12')
        ->getNumberFormat()
        ->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_TEXT
        );
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $sheet->setCellValue('A1', 'Акт неремонтопригодности (АНРП) №'.$content['id']);
        /*if ($content['model']['brand'] == 'TESLER') {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } else {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } */
        $sheet->setCellValue('A6', $content['service_info']['name'].', '.$content['city']['fcity_name'].', '.$content['service_info']['phisical_adress'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B8', $content['cat_info']['name']);
        $sheet->setCellValue('B10', $content['model']['name']);
        $sheet->setCellValueExplicit('B12', $content['serial'], PHPExcel_Cell_DataType::TYPE_STRING);


        $sheet->setCellValue('B14', implode(', ', array_filter(array($content['name_shop'], $content['city_shop'], $content['address_shop'], $content['phone_shop']))));
        $sheet->setCellValue('B16', $content['rsc']);
        $sheet->setCellValue('B18', (($content['sell_date'] != '0000-00-00') ? date("d.m.Y", strtotime($content['sell_date'])) : ''));
        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B24', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        $sheet->setCellValue('B27', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B29', implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);
        $sheet->setCellValue('B31', $content['bugs']);

        $name_add = ($content['parts_info']['name'] != '') ? '('.$content['parts_info']['name'].')' : '';
        $problem = get_content_by_id('details_problem', $content['parts_info']['problem_id']);
        $t = $problem['repair_name'].'. '.preg_replace('/\(.*?\)/', '', $problem['name']).' '.$name_add;
        $t = (trim($content['repair_final_cancel'], '- ')) ? $content['repair_final_cancel'] : $t;
        $sheet->setCellValue('B33', $t);

        $sheet->setCellValue('B35', date("d.m.Y", strtotime($content['receive_date'])));
        $sheet->setCellValue('B37', date("d.m.Y"));
        $sheet->setCellValue('B39', date("d.m.Y"));
        if ($content['service_id'] == 33) {
        $sheet->setCellValue('B41', 'Клюев Александр Александрович');

        } else {
        $sheet->setCellValue('B41', $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);

        }
        $sheet->setCellValue('B43', $content['service_info']['req_gen_fio']);

        }
        $params = models\services\Settings::getSettings($content['id'], $content['service_info']['user_id'], $content['service_info']['country']);
        $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        if($content['service_id'] == 33){
            if($content['repair_final'] == 2){
              $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
            }elseif($content['repair_final'] == 1 || $content['repair_final'] == 3){
              $anrpValue = 'Аппарат выдан на руки клиенту';
            }
        }elseif($params && $params['anrp_value'] != 2){
          $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        }else{
          if(!$params && $content['model']['brand'] == 'TESLER'){
            $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
          }else{
            $anrpValue = 'Аппарат выдан на руки клиенту';
          }   
        }
        $sheet->setCellValue('A46', $anrpValue);
        if(User::hasRole('service')){
          $sheet->getProtection()->setSheet(true);
        }
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);
        if($onlyGenerate){
          return $new_file;
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="anrp_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        return $new_file;
}

# Дашборд:
if ($_GET['query'] == 'get-tech') {
  getTechDoc($_GET['id']);
  exit;
}

 // if (User::hasRole('admin')) {
  function getTechDoc($id, $onlyGenerate = false){
    global $db;
    $_GET['id'] = $id;
  models\Repair::updateOutDate($_GET['id']);
  require 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['city'] = get_city($content['service_info']['city']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/3.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
 
        if ($content['parts_info']['problem_id'] == 5) {
            $problem = get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'].'. '.get_content_by_id('repair_type', $content['parts_info']['repair_type_id'])['name'].' '.$content['repair_final_cancel'];
        }elseif (in_array($content['parts_info']['problem_id'], array(3, 14, 15, 16, 18, 19, 23, 43, 41))) {
             if (get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name']) {
            $problem_add = get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'];
            }
            $problem_add = ($content['repair_final_cancel'] != '') ? $content['repair_final_cancel'] : $problem_add;
            $problem = 'В гарантии отказано.';
        }elseif(in_array($content['parts_info']['problem_id'], [35, 57])){
          $problem = get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'];
        }else{
          $problem = $content['repair_final_cancel'];
        }
        $sheet->setCellValue('A1', 'Акт технического заключения, осмотра (АТЗ/О) №'.$content['id']);
        $xls->getActiveSheet()->getStyle('A6')->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->mergeCells('A6:B6');
        $xls->getActiveSheet()->getRowDimension('6')->setRowHeight(50);
        $sheet->setCellValue('A6', $content['service_info']['name'].', '.$content['city']['fcity_name'].', '.$content['service_info']['phisical_adress'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B8', $content['cat_info']['name']);
        $sheet->setCellValue('B10', $content['model']['name']);
        $sheet->setCellValueExplicit('B12', $content['serial'], PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue('B14', implode(', ', array_filter(array($content['name_shop'], $content['city_shop'], $content['address_shop'], $content['phone_shop']))));
        $sheet->setCellValue('B16', $content['rsc']);
        if ($content['sell_date'] != '0000-00-00') {
        $sheet->setCellValue('B18', date("d.m.Y", strtotime($content['sell_date'])));
        } else {
        $sheet->setCellValue('B18', '');
        }
        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B22', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        $sheet->setCellValue('B25', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B27', implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);
        $sheet->setCellValue('B29', $content['bugs']);
        $sheet->setCellValue('B31', $problem.' '.$problem_add);
         unset($problem_add);
        //$sheet->setCellValue('B31', get_content_by_id('details_problem', $content['parts_info']['problem'])['repair_name'].'. '.preg_replace('/\(.*?\)/', '', get_content_by_id('details_problem', $content['parts_info']['problem'])['name']));
        $sheet->setCellValue('B33', date("d.m.Y", strtotime($content['receive_date'])));
        $sheet->setCellValue('B35', date("d.m.Y", strtotime($content['approve_date'])));
        $sheet->setCellValue('B37', date("d.m.Y"));
        if ($content['service_id'] == 33) {
        $sheet->setCellValue('B39', 'Клюев Александр Александрович');
        $sheet->setCellValue('A41', 'Руководитель');
        $sheet->setCellValue('B41', $content['service_info']['req_gen_fio']);
        } else {
        $sheet->setCellValue('B39', $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);
        $sheet->setCellValue('B41', $content['service_info']['req_gen_fio']);
        }
        $params = models\services\Settings::getSettings($content['id'], $content['service_info']['user_id'], $content['service_info']['country']);
        $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        if($problem == 'В гарантии отказано.' || $content['parts_info']['problem_id'] == 5){
          $anrpValue = 'Аппарат выдан на руки клиенту';
        }elseif($content['service_id'] == 33){
            if($content['repair_final'] == 2){
              $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
            }elseif($content['repair_final'] == 1 || $content['repair_final'] == 3){
              $anrpValue = 'Аппарат выдан на руки клиенту';
            }
        }elseif($params && $params['anrp_value'] != 2){
          $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        }else{
          if(!$params && $content['model']['brand'] == 'TESLER'){
            $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
          }else{
            $anrpValue = 'Аппарат выдан на руки клиенту';
          }   
        }
        $sheet->setCellValue('A43', $anrpValue);
        $xls->getActiveSheet()->getPageSetup()->setPrintArea('A1:B44');
        $xls->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);
        $xls->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $xls->getActiveSheet()->getStyle('A29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
       /* $xls->getActiveSheet()->getStyle('A25:A'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L31:L'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        //$xls->getActiveSheet()->getRowDimension(25)->setRowHeight(strlen(get_content_by_id('repair_type', $content['parts_info']['repair_id'])['name'])/1.2);
        $xls->getActiveSheet()->getRowDimension(25)->setRowHeight(28); */

        //$xls->getActiveSheet()->getColumnDimension('A')->setWidth(strlen($content['works'])/2.5);
        /*$sheet->getStyle('A25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('L25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('U25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('AT25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('BC25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); */
         // Остаток:
       /* $sheet->setCellValue('W27', date("d.m.Y", strtotime($content['start_date'])));
        $sheet->setCellValue('W29', date("d.m.Y", strtotime($content['end_date'])));
        $sheet->setCellValue('L31', $content['master_info']['name'].' '.$content['master_info']['surname']);
        $xls->getActiveSheet()->getStyle('L31')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setSize(13);   */
        /*$sheet->setCellValue('AC31', $content['service_info']['req_gen_fio']); */
        //$sheet->setCellValue('X35', $content['service_info']['req_gen_fio']);

        $sheet->calculateColumnWidths();

        //original code...
        $titlecolwidth = $sheet->getColumnDimension('A')->getWidth();
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth($titlecolwidth);
        //echo $titlecolwidth;
        if(User::hasRole('service')){
          $sheet->getProtection()->setSheet(true);
        }
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);
        if($onlyGenerate){
          return $new_file;
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="ato_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        return $new_file;
}


if ($_GET['query'] == 'get-receipt-outside') {
  models\Documents::displayDocument('receipt-outside', App::$URLParams);
  exit;
}


# Дашборд:
if ($_GET['query'] == 'get-label') {
  
  
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('_new-codebase/content/templates/excel/label.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $fail_type = array(1 => 'Деталь или ПО не постваляется', 2 => 'Отказано в гарантии', 3 => 'Клиент от ремонта отказался', 4 => 'Нарушены сроки ремонта', 5 => 'Не ремонтопригоден', 6 => 'Нет технической информации (схем)');

        /*if ($content['parts_info']['problem'] == 5) {
            $problem = get_content_by_id('details_problem', $content['parts_info']['problem'])['name'].'. '.get_content_by_id('repair_type', $content['parts_info']['repair_type'])['name'];
        } else  if (in_array($content['parts_info']['problem'], array(3, 14, 15, 16, 18, 19, 23))) {
            if ($fail_type[$content['parts_info']['repair_type_fail']]) {
            $problem_add = $fail_type[$content['parts_info']['repair_type_fail']];
            } else  if (get_content_by_id('details_problem', $content['parts_info']['problem'])['name']) {
            $problem_add = get_content_by_id('details_problem', $content['parts_info']['problem'])['name'];
            }
            $problem = 'В гарантии отказано. '.$problem_add;
            unset($problem_add);
        }*/


        $sheet->setCellValue('A1', 'Наклейка №'.$content['id']);

        if ($content['anrp_use'] == 1) {
        $sheet->setCellValue('A2', 'АНРП-№'.$content['anrp_number']);
        $sheet->getStyle("A2")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '888888')
                )
            )
        );
        $styleArray = array(
        'font'  => array(
            'name'  => 'Arial'
        ));
        $xls->getActiveSheet()->getStyle('A2')->applyFromArray($styleArray);
        }


        if ($content['client_type'] == 1) {
          $from = $content['client'];
        } else {
        if(!empty($content['client_id'])){
          $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
          $from = $client_info['name'];
        }else{
          $from = $content['name_shop'];
        }
        }
        $sheet->setCellValue('A3', 'Принят от: '.$from);
        $xls->getActiveSheet()->getStyle("A3")->getFont()->setBold(true);
        $xls->getActiveSheet()->getRowDimension(7)->setRowHeight(30);
        $xls->getActiveSheet()->getStyle("A7")->getAlignment()->applyFromArray(array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));


        $sheet->setCellValue("AA3", 'Вн.№: '.$content['rsc']);


        $sheet->setCellValue('A4', 'Модель: '.$content['model']['name']);
        $sheet->setCellValue('A5', 'Владелец: '.(($content['client']) ? $content['client'] : $content['name_shop']));
        $sheet->setCellValue('A6', 'Телефон: '.$content['phone']);
        $sheet->setCellValue('A7', 'Неисправность: '.$content['bugs']);

        $sheet->setCellValue('A9', 'Дата приема: '.Time::format($content['receive_date']));
        $sheet->setCellValue('AA4', 'Сер.№: '.$content['serial']);
        $sheet->setCellValue('AA5', 'Компл-ция: '.implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('AA6', 'Статус рем.: '.$status_array[$content['status_id']]);


        /*$sheet->setCellValue('AN12', 'Наряд №'.$content['id']);
        $sheet->setCellValue('BI12', $content['cat_info']['name']);
        $sheet->setCellValue('AN13', 'Модель: '.$content['model']['name']);
        $sheet->setCellValue('AN14', 'Серийный номер: '.$content['serial']);
        $sheet->setCellValue('AN15', 'Дата приёма в ремонт: '.date("d.m.Y", strtotime(explode(' ', $content['date_get'])['0'])));
        $sheet->setCellValue('AN16', 'Владелец: '.$content['client']);
        $sheet->setCellValue('AN17', 'Адрес: '.$content['address']);

        $sheet->setCellValue('AN19', 'Комплектация: '.implode(', ', array_filter(explode('|', $content['complex']))));

        $sheet->setCellValue('AN21', 'Неисправность со слов владельца: '.$content['bugs']);

        $sheet->setCellValue('AN25', 'Симптом: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN26', 'Поз. обозн.: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN27', 'Причина отказа: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN28', 'Вид ремонта: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN29', 'Мастер: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
                                                                                                                           */

      /* $sheet->setCellValue('B16', $content['rsc']);
        $sheet->setCellValue('B18', date("d.m.Y", strtotime($content['date'])));
        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B22', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        $sheet->setCellValue('B25', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B27', implode(', ', array_filter(explode('|', $content['visual']))));
        $sheet->setCellValue('B29', $content['bugs']);
        //$sheet->setCellValue('B31', $problem);
        $sheet->setCellValue('B31', get_content_by_id('details_problem', $content['parts_info']['problem'])['repair_name'].'. '.preg_replace('/\(.*?\)/', '', get_content_by_id('details_problem', $content['parts_info']['problem'])['name']));
        $sheet->setCellValue('B33', date("d.m.Y", strtotime(explode(' ', $content['date_get'])['0'])));
        $sheet->setCellValue('B35', date("d.m.Y"));
        $sheet->setCellValue('B37', date("d.m.Y"));
        $sheet->setCellValue('B39', $content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('B41', $content['service_info']['req_gen_fio']);   */


        $xls->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);
        $xls->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $xls->getActiveSheet()->getStyle('A29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
       /* $xls->getActiveSheet()->getStyle('A25:A'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L31:L'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        //$xls->getActiveSheet()->getRowDimension(25)->setRowHeight(strlen(get_content_by_id('repair_type', $content['parts_info']['repair_id'])['name'])/1.2);
        $xls->getActiveSheet()->getRowDimension(25)->setRowHeight(28); */

        //$xls->getActiveSheet()->getColumnDimension('A')->setWidth(strlen($content['works'])/2.5);
        /*$sheet->getStyle('A25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('L25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('U25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('AT25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('BC25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); */
         // Остаток:
       /* $sheet->setCellValue('W27', date("d.m.Y", strtotime($content['start_date'])));
        $sheet->setCellValue('W29', date("d.m.Y", strtotime($content['end_date'])));
        $sheet->setCellValue('L31', $content['master_info']['name'].' '.$content['master_info']['surname']);
        $xls->getActiveSheet()->getStyle('L31')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setSize(13);   */
        /*$sheet->setCellValue('AC31', $content['service_info']['req_gen_fio']); */
        //$sheet->setCellValue('X35', $content['service_info']['req_gen_fio']);

        $sheet->calculateColumnWidths();

        //original code...
        $titlecolwidth = $sheet->getColumnDimension('A')->getWidth();
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth($titlecolwidth);
        //echo $titlecolwidth;

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="Nakleyka_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-receipt') {
  
  
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/Kvitanciya_00321283.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        /*$xls->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);   */
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $fail_type = array(1 => 'Деталь или ПО не постваляется', 2 => 'Отказано в гарантии', 3 => 'Клиент от ремонта отказался', 4 => 'Нарушены сроки ремонта', 5 => 'Не ремонтопригоден', 6 => 'Нет технической информации (схем)');

        /*if ($content['parts_info']['problem'] == 5) {
            $problem = get_content_by_id('details_problem', $content['parts_info']['problem'])['name'].'. '.get_content_by_id('repair_type', $content['parts_info']['repair_type'])['name'];
        } else  if (in_array($content['parts_info']['problem'], array(3, 14, 15, 16, 18, 19, 23))) {
            if ($fail_type[$content['parts_info']['repair_type_fail']]) {
            $problem_add = $fail_type[$content['parts_info']['repair_type_fail']];
            } else  if (get_content_by_id('details_problem', $content['parts_info']['problem'])['name']) {
            $problem_add = get_content_by_id('details_problem', $content['parts_info']['problem'])['name'];
            }
            $problem = 'В гарантии отказано. '.$problem_add;
            unset($problem_add);
        }*/
        /*$xls->getActiveSheet()->getStyle('B2:B'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getRowDimension(2)->setRowHeight(30);
        $xls->getActiveSheet()->getStyle('B14:B'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getRowDimension(14)->setRowHeight(30);
        $xls->getActiveSheet()->getStyle('B4:B'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getRowDimension(4)->setRowHeight(20);    */
        /*if ($content['service_id'] == 33) {
        $sheet->setCellValue('B2', $content['service_info']['name_public']);
        } else {  */
        $sheet->setCellValue('A2', $content['service_info']['name']);
        //}
        $sheet->setCellValue('A3', 'Тел.: '.$content['service_info']['phones']);

        if ($content['service_id'] == 33) {
        $sheet->setCellValue('A4', 'Адрес: '.$content['service_info']['phisical_adress']);
        } else {
        $sheet->setCellValue('A4', 'Адрес: '.$content['service_info']['adress']);
        }

       // $sheet->setCellValue('B4', 'Адрес: '.$content['service_info']['adress']);
        if ($content['anrp_use'] == 1) {
        $sheet->setCellValue('A5', 'АНРП-№'.$content['anrp_number']);
        }


        $sheet->setCellValue('A6', 'Квитанция к наряду: №'.$content['id']);
        $sheet->setCellValue('A7', 'Заказ-наряд клиента: '.$content['rsc']);

        if ($content['client_type'] == 1) {
        $sheet->setCellValue('A8', 'Поступил от: '.$content['client']);
        } else {
        $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
      if(!empty($client_info['name'])){
        $sheet->setCellValue('A8', 'Поступил от: '.$client_info['name']);
      }else{
        $sheet->setCellValue('A8', 'Поступил от: '.$content['name_shop']);
      }
        

        }
        $phone = ($content['phone']) ? ', '.$content['phone'] : '';
        $sheet->setCellValue('A9', 'Модель: '.$content['model']['name']);
        $sheet->setCellValue('A10', 'Серийный номер: '.$content['serial']);
        $sheet->setCellValue('A14', 'Владелец: '.$content['client'].$phone);
        $sheet->setCellValue('A11', 'Тип ремонта: '.$status_array[$content['status_id']]);
        $sheet->setCellValue('A12', 'Продавец: '.implode(', ', array_filter(array($content['name_shop'], $content['city_shop'], $content['address_shop'], $content['phone_shop']))));
        $sheet->setCellValue('A13', 'Комплектация: '.implode(', ', array_filter(explode('|', $content['complex']))));

        $sheet->setCellValue('A15', 'Адрес: '.$content['address']);
        $sheet->setCellValue('A16', 'Внешний вид: '.implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);
        $sheet->setCellValue('A17', 'Неисправность со слов владельца: '.$content['bugs']);
        $sheet->setCellValue('A23', 'Клиент: ______________________ /'.$content['client'].' ./');
        $sheet->setCellValue('A30', 'Изделие по данной квитанции получил в той же комплектации и внешнем состоянии, как и сдавал. К качеству выполненных работ претензий не имею. ______________________ /'.$content['client'].' ./');
        $sheet->setCellValue('A31', 'Акт НРП по данной квитанции получил, претензий не имею ______________________ /'.$content['client'].' ./');
        $sheet->setCellValue('A27', 'Дата выдачи: '.Time::format($content['finish_date']).'/');
        $sheet->setCellValue('A20', 'Дата приёма: '.Time::format($content['receive_date']));
        /*$sheet->setCellValue('AN2', 'Талон №'.$content['id']);
        $sheet->setCellValue('AN4', 'Модель: '.$content['model']['name']);
        $sheet->setCellValue('BN4', 'Серийный номер: '.$content['serial']);
        $sheet->setCellValue('AN5', 'Владелец: '.$content['client']);
        $sheet->setCellValue('BN5', 'Комплектация: '.implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('AN7', 'Неисправность со слов владельца: '.$content['bugs']);
        $sheet->setCellValue('AN12', 'Наряд №'.$content['id']);
        $sheet->setCellValue('BI12', $content['cat_info']['name']);
        $sheet->setCellValue('AN13', 'Модель: '.$content['model']['name']);
        $sheet->setCellValue('AN14', 'Серийный номер: '.$content['serial']);
        $sheet->setCellValue('AN15', 'Дата приёма в ремонт: '.date("d.m.Y", strtotime(explode(' ', $content['date_get'])['0'])));
        $sheet->setCellValue('AN16', 'Владелец: '.$content['client']);
        $sheet->setCellValue('AN17', 'Адрес: '.$content['address']);
        $sheet->setCellValue('AN18', 'Телефон: '.$content['phone']);
        $sheet->setCellValue('AN19', 'Комплектация: '.implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('AN20', 'Тип ремонта: '.$status_array[$content['status_id']]);
        $sheet->setCellValue('AN21', 'Неисправность со слов владельца: '.$content['bugs']);

        $sheet->setCellValue('AN25', 'Симптом: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN26', 'Поз. обозн.: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN27', 'Причина отказа: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN28', 'Вид ремонта: '.$content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('AN29', 'Мастер: '.$content['master_info']['name'].' '.$content['master_info']['surname']);



        if (count($content['parts_info']) > 0) {
        foreach ($content['parts_info'] as $parts) {
        $parts_type .= get_content_by_id('repair_type', $parts['repair_type'])['name']."\r\n";
        $parts_name .= $parts['name']."\r\n";
        $parts_count .= $parts['count']."\r\n";
        $parts_position .= $parts['position']."\r\n";
        }
        }

        $sheet->setCellValue('AN32', '');
        $sheet->setCellValue('AV32', '');
        $sheet->setCellValue('BX32', '');
        $sheet->setCellValue('CF32', '');  */

      /* $sheet->setCellValue('B16', $content['rsc']);
        $sheet->setCellValue('B18', date("d.m.Y", strtotime($content['date'])));
        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B22', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        $sheet->setCellValue('B25', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B27', implode(', ', array_filter(explode('|', $content['visual']))));
        $sheet->setCellValue('B29', $content['bugs']);
        //$sheet->setCellValue('B31', $problem);
        $sheet->setCellValue('B31', get_content_by_id('details_problem', $content['parts_info']['problem'])['repair_name'].'. '.preg_replace('/\(.*?\)/', '', get_content_by_id('details_problem', $content['parts_info']['problem'])['name']));
        $sheet->setCellValue('B33', date("d.m.Y", strtotime(explode(' ', $content['date_get'])['0'])));
        $sheet->setCellValue('B35', date("d.m.Y"));
        $sheet->setCellValue('B37', date("d.m.Y"));
        $sheet->setCellValue('B39', $content['master_info']['name'].' '.$content['master_info']['surname']);
        $sheet->setCellValue('B41', $content['service_info']['req_gen_fio']);   */


       /* $xls->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('B9')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);
        $xls->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $xls->getActiveSheet()->getStyle('A29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);   */
       /* $xls->getActiveSheet()->getStyle('A25:A'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L31:L'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        //$xls->getActiveSheet()->getRowDimension(25)->setRowHeight(strlen(get_content_by_id('repair_type', $content['parts_info']['repair_id'])['name'])/1.2);
        $xls->getActiveSheet()->getRowDimension(25)->setRowHeight(28); */

        //$xls->getActiveSheet()->getColumnDimension('A')->setWidth(strlen($content['works'])/2.5);
        /*$sheet->getStyle('A25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('L25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('U25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('AT25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('BC25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); */
         // Остаток:
       /* $sheet->setCellValue('W27', date("d.m.Y", strtotime($content['start_date'])));
        $sheet->setCellValue('W29', date("d.m.Y", strtotime($content['end_date'])));
        $sheet->setCellValue('L31', $content['master_info']['name'].' '.$content['master_info']['surname']);
        $xls->getActiveSheet()->getStyle('L31')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setSize(13);   */
        /*$sheet->setCellValue('AC31', $content['service_info']['req_gen_fio']); */
        //$sheet->setCellValue('X35', $content['service_info']['req_gen_fio']);

       /* $sheet->calculateColumnWidths();

        //original code...
        $titlecolwidth = $sheet->getColumnDimension('A')->getWidth();
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth($titlecolwidth);   */
        //echo $titlecolwidth;

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="Kvitanciya_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-act') {
  getPaymentAct();
  exit();
}

function getPaymentAct($onlyGenerate = false){
  global $db, $_monthsList, $_monthsList2, $config;
// if (User::hasRole('admin')) {
require_once 'adm/excel/vendor/autoload.php';
$userID = User::getData('id');
if (User::hasRole('acct')) {
      $userID = 33;
      $billing_manager = 1;

      /*if ($_GET['year'] >= 2019 && $_GET['month'] >= 10) {
        $no_for_money = ' and `status_id` != 6 ';
        } */

      }
      $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$userID.' '.$no_for_money.' and `deleted` = 0;'));
      $content['billing_log'] = get_payment_info_by_date($userID, $_GET['year'], $_GET['month'], 1);
      $content['service_info'] = service_request_info($userID);
      $content['billing_info'] = service_billing_info($userID);
      //$content['cat_info'] = model_cat_info($content['model']['cat']);
      //$content['parts_info'] = repairs_parts_info_array($content['id']);
      //$content['master_info'] = master_info($content['master_id']);

      if (file_exists('adm/excel/files')) {
          foreach (glob('adm/excel/files/*') as $file) {
              unlink($file);
          }
      }
      $new_file = 'adm/excel/files/1.xlsx';
      copy('adm/excel/payed.xlsx', $new_file);

      $xls = PHPExcel_IOFactory::load($new_file);
      $xls->setActiveSheetIndex(0);
      $sheet = $xls->getActiveSheet();
      if($_GET['tesler'] == 1){
        $sheet->setCellValue('B3', $content['billing_log']['id'] . '-2');
      } else {
        $sheet->setCellValue('B3', $content['billing_log']['id']);
      }
      
      $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
      $sheet->setCellValue('B5', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', Р\С '.$content['billing_info']['sc2'].', '.$content['billing_info']['bank_name'].', БИК '.$content['billing_info']['bik'].', К\С '.$content['billing_info']['sc1'].', тел. '.$content['service_info']['phones']);
      $sheet->setCellValue('B9', $config['billing_info']);

      if ($userID == 33 && $_GET['tesler'] == 1) {
      $sheet->setCellValue('B14', $config['osnovanie_tesler']);
      } else if ($userID == 33 && $_GET['horizont'] == 1) {
      $sheet->setCellValue('B14', $config['osnovanie_horizont']);
      } else if ($userID == 33 && $_GET['sven'] == 1) {
      $sheet->setCellValue('B14', $config['osnovanie_sven']);
      } else if ($userID == 33 && ($_GET['tesler'] != 1 &&  $_GET['horizont'] != 1)) {
      $sheet->setCellValue('B14', $config['osnovanie_other']);
      } else if ($userID == 33 && $_GET['roch'] == 1) {
        $sheet->setCellValue('B14', $config['osnovanie_roch']);
        } else {
      //$sheet->setCellValue('B14', 'Договор организации сервисного обслуживания '.$content['billing_info']['agree']);
      $sheet->setCellValue('B14', 'Договор организации сервисного обслуживания №____ от ___________');
    }


      if ($_GET['tesler'] == 1) {
      $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду Tesler за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
      } else if ($_GET['brand'] == 'SELENGA') {
      $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду Selenga за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
      } else if ($_GET['horizont'] == 1) {
      $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду Горизонт-Союз за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
      } else if ($_GET['sven'] == 1) {
      $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду SVEN за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
      } else {
      $sheet->setCellValue('B17', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');

      }

      if ($_GET['tesler'] == 1) {
      $sheet->setCellValue('G17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager).',00');
      $sheet->setCellValue('H17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager).',00');
      $sheet->setCellValue('H18', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager).',00');
      $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager).',00 руб.');
      $sheet->setCellValue('A21', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager)));
      } else if ($_GET['brand'] == 'SELENGA') {
      $sheet->setCellValue('G17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager).',00');
      $sheet->setCellValue('H17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager).',00');
      $sheet->setCellValue('H18', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager).',00');
      $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager).',00 руб.');
      $sheet->setCellValue('A21', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager)));
      } else if ($_GET['horizont'] == 1) {
      $sheet->setCellValue('G17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT').',00');
      $sheet->setCellValue('H17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT').',00');
      $sheet->setCellValue('H18', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT').',00');
      $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT').',00 руб.');
      $sheet->setCellValue('A21', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT')));
      } else if ($_GET['sven'] == 1) {
      $sheet->setCellValue('G17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN').',00');
      $sheet->setCellValue('H17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN').',00');
      $sheet->setCellValue('H18', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN').',00');
      $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN').',00 руб.');
      $sheet->setCellValue('A21', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN', '')));
      } else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('G17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH').',00');
        $sheet->setCellValue('H17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH').',00');
        $sheet->setCellValue('H18', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH', '')));
      } else {
      $sheet->setCellValue('G17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager).',00');
      $sheet->setCellValue('H17', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager).',00');
      $sheet->setCellValue('H18', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager).',00');
      $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager).',00 руб.');
      $sheet->setCellValue('A21', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager)));
      }



      if ($content['billing_info']['chp'] != 1) {
      $sheet->setCellValue('A28', 'Генеральный директор '.$content['service_info']['name']);
      } else {
      $sheet->setCellValue('A28', $content['service_info']['name']);
      }
      $sheet->setCellValue('A30', $content['service_info']['req_gen_fio']);

      if ($userID == 33) {

      if ($_GET['horizont'] == 1) {
      $sheet->setCellValue('B9', 'ЗАО «Горизонт-Союз», Юр. адрес: 125466, г. Москва, ул. Соколово-Мещерская, д. 29, Почт. адрес: 125466, г. Москва, ул. Соколово-Мещерская, д. 29, Тел./факс: (495) 926-93-30, E-mail: info@horizont.tv, service@tv-service.tv, Сайт: www.tv-service.tv');
      } else if($_GET['tesler'] == 1) {
      $sheet->setCellValue('B9', $config['billing_info_to_tesler']);
      $sheet->setCellValue('E28', $config['billing_info_to_footer_tesler']);
      $sheet->setCellValue('E30', $config['billing_info_to_fio_footer_tesler']);
      }else if($_GET['roch'] == 1) {
        $sheet->setCellValue('B9', $config['billing_info_to_roch']);
        $sheet->setCellValue('E28', $config['billing_info_to_footer_roch']);
        $sheet->setCellValue('E30', $config['billing_info_to_fio_footer_roch']);  
        }else{
        $sheet->setCellValue('B9', $config['billing_info_to']);
        $sheet->setCellValue('E28', 'Генеральный директор '.$config['billing_info_to_footer']);
        $sheet->setCellValue('E30', $config['billing_info_to_fio_footer']);

      }

    
      }

      if ($_GET['sven'] == 1) {
      $xls->getActiveSheet()->getRowDimension(5)->setRowHeight(40);
      $sven_name = '_sven';
      $sheet->setCellValue('E28', 'ООО «РТ-Ф»');
      $sheet->setCellValue('E30', 'Иванов Алексей Сергеевич');
      $sheet->setCellValue('B9', 'ООО «РТ-Ф», Юр. адрес:  105082, г. Москва, ул. Фридриха Энгельса, д.56, стр. 1, этаж 3, помещение 21, тел. (903) 562-56-05, ИНН:7701679880,  КПП: 770101001, Р/С: 40702810338290110709,  Банк: Стромынское ОСБ 5281 Сбербанка России,   К/С: 30101810400000000225, БИК:  044525225');
      }

      if ($_GET['brand'] == 'selenga') {
      $xls->getActiveSheet()->getRowDimension(5)->setRowHeight(40);
      $sven_name = '_selenga';
      $sheet->setCellValue('E28', 'ООО «Селенга»');
      $sheet->setCellValue('E30', 'Дианов Александр Николаевич');
      $sheet->setCellValue('B9', '121351, город Москва, улица Кунцевская, д. 15, оф. ПОМЕЩЕНИЕ IV ОФИС 16');
      }

     $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
      $objWriter->save($new_file);
      if($onlyGenerate){
        return $new_file;
      }
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].$sven_name.'_акт.xlsx"');
      header('Content-Transfer-Encoding: binary');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      header('Content-Length: ' . filesize($new_file));
      ob_clean();
      flush();
      readfile($new_file);
     return $new_file;
//}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-act-optima') {

  
  
 // if (User::hasRole('admin')) {
  $userID = User::getData('id');
         if (User::hasRole('acct')) {
          $userID = 33;
        }
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$userID.' and `deleted` = 0;'));
        $content['billing_log'] = get_payment_info_by_date($userID, $_GET['year'], $_GET['month'], 1);
        $content['service_info'] = service_request_info($userID);
        $content['billing_info'] = service_billing_info($userID);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/33_act.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');

        $sheet->setCellValue('B3', '0'.($_GET['month']+2).'-ТП');
        $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('B17', 'Услуги по технической поддержке продукции за период с 1 '.$_monthsList2[$_GET['month']].' по '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года');
        $xls->getActiveSheet()->getPageSetup()->setPrintArea("A1:H31");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].'_акт.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-bill-optima') {

  
  $userID = User::getData('id');
          if (User::hasRole('acct')) {
            $userID = 33;
        }
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$userID.' and `deleted` = 0;'));
        $content['billing_log'] = get_payment_info_by_date($userID, $_GET['year'], $_GET['month'], 1);
        $content['service_info'] = service_request_info($userID);
        $content['billing_info'] = service_billing_info($userID);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/33_bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $xls->getActiveSheet()
        ->getPageSetup()
        ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $xls->getActiveSheet()
        ->getPageSetup()
        ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');

        $sheet->setCellValue('A8', 'Счёт на оплату № 0'.($_GET['month']+2).'-ТП от '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        //$sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('B20', 'Услуги по технической поддержке продукции за период с 1 '.$_monthsList2[$_GET['month']].' по '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года');

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].'_счет.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-act-optima-pppo') {

  
  $userID = User::getData('id');
          if (User::hasRole('acct')) {
            $userID = 33;
        }
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$userID.' and `deleted` = 0;'));
        $content['billing_log'] = get_payment_info_by_date($userID, $_GET['year'], $_GET['month'], 1);
        $content['service_info'] = service_request_info($userID);
        $content['billing_info'] = service_billing_info($userID);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/33_act.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');

        $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        //$sheet->setCellValue('A8', 'Счёт на оплату № 0'.($_GET['month']+2).'-ТП от '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        $sheet->setCellValue('B3', '00'.($_GET['month']).'-ПППО-ОМ');
        $sheet->setCellValue('B17', 'Услуги по предпродажной подготовке и послепродажному обслуживанию Продукции за период с 1 '.$_monthsList2[$_GET['month']].' по '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года');
        $sheet->setCellValue('B14', 'ДС №3 к договору сервисного обслуживания №30/04/2018 от 09.01.2019');
        $sheet->setCellValue('G17', '157000');
        $sheet->setCellValue('H17', '157000');
        $sheet->setCellValue('H18', '157000');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму 157000 руб.');
        $sheet->setCellValue('A21', 'Сто пятьдесят семь тысяч рублей 00 копеек');
        $xls->getActiveSheet()->getRowDimension(17)->setRowHeight(50);
                $sheet->getStyle('B17')->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $xls->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_ОптимаПППО_'.$_GET['month'].'_'.$_GET['year'].'_акт.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-bill-optima-pppo') {

  
  $userID = User::getData('id');
  if (User::hasRole('acct')) {
    $userID = 33;
}
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$userID.' and `deleted` = 0;'));
        $content['billing_log'] = get_payment_info_by_date($userID, $_GET['year'], $_GET['month'], 1);
        $content['service_info'] = service_request_info($userID);
        $content['billing_info'] = service_billing_info($userID);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/33_bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $xls->getActiveSheet()
        ->getPageSetup()
        ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $xls->getActiveSheet()
        ->getPageSetup()
        ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');

        //$sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('A8', 'Счёт на оплату № 0'.($_GET['month']).'-ПППО-ОМ от '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        //$sheet->setCellValue('B3', '00'.($_GET['month']).'-ПППО-ОМ');
        $sheet->setCellValue('B20', 'Услуги по предпродажной подготовке и послепродажному обслуживанию Продукции за период с 1 '.$_monthsList2[$_GET['month']].' по '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года');
        $sheet->setCellValue('D20', '1.'.$_GET['month'].'.'.$_GET['year']." \n-\n ".date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).'.'.$_GET['month'].'.'.$_GET['year']);
        $sheet->getStyle('D20')->getAlignment()->setWrapText(true);
        $sheet->setCellValue('B17', 'ДС №3 к договору сервисного обслуживания №30/04/2018 от 09.01.2019');
        $sheet->setCellValue('E20', '157000');
        $sheet->setCellValue('F20', '157000');
        $sheet->setCellValue('F22', '157000');
        $sheet->setCellValue('F24', '157000');
        $sheet->setCellValue('E26', '157000');
        $sheet->setCellValue('B27', 'Сто пятьдесят семь тысяч рублей 00 копеек');
                $xls->getActiveSheet()->getRowDimension(20)->setRowHeight(50);
                $sheet->getStyle('B20')->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_ОптимаПППО_'.$_GET['month'].'_'.$_GET['year'].'_счет.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-act-admin') {

  
  
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$_GET['service_id'].' and `deleted` = 0;'));
        $content['billing_log'] = get_payment_info_by_date($_GET['service_id'], $_GET['year'], $_GET['month'], 1);
        $content['service_info'] = service_request_info($_GET['service_id']);
        $content['billing_info'] = service_billing_info($_GET['service_id']);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/payed.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('B3', $content['billing_log']['id'].'-2');
        } else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('B3', $content['billing_log']['id'].'-3');
        } else {
        $sheet->setCellValue('B3', $content['billing_log']['id']);
        }

        $sheet->setCellValue('D3', date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года.');
        $sheet->setCellValue('B5', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', Р\С '.$content['billing_info']['sc2'].', '.$content['billing_info']['bank_name'].', БИК '.$content['billing_info']['bik'].', К\С '.$content['billing_info']['sc1'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B9', $config['billing_info']);
        $sheet->setCellValue('B14', 'Договор организации сервисного обслуживания '.$content['billing_info']['agree']);
        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду Tesler за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        } else if ($_GET['selenga'] == 1) {
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду Selenga за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
		} else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду Roch за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        } else {
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');

        }
        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('G17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER').',00');
        $sheet->setCellValue('H17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER').',00');
        $sheet->setCellValue('H18', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER')));
        } else if ($_GET['selenga'] == 1) {
        $sheet->setCellValue('G17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA').',00');
        $sheet->setCellValue('H17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA').',00');
        $sheet->setCellValue('H18', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA')));
		} else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('G17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH').',00');
        $sheet->setCellValue('H17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH').',00');
        $sheet->setCellValue('H18', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH')));
        } else {
        $sheet->setCellValue('G17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER').',00');
        $sheet->setCellValue('H17', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER').',00');
        $sheet->setCellValue('H18', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER').',00');
        $sheet->setCellValue('A20', 'Всего оказано услуг 1, на сумму '.get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER').',00 руб.');
        $sheet->setCellValue('A21', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER')));
        }


        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('A28', 'Генеральный директор '.$content['service_info']['name']);
        } else {
        $sheet->setCellValue('A28', $content['service_info']['name']);
        }
        $sheet->setCellValue('A30', $content['service_info']['req_gen_fio']);


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

       header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].'_акт.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-bill') {
  getPaymentBill();
  exit;
}
function getPaymentBill($onlyGenerate = false){
  global $db, $config, $_monthsList, $_monthsList2;
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';
  $userID = User::getData('id');
        if (User::hasRole('acct')) {
          $userID = 33;
                 $billing_manager = 1;
         /* if ($_GET['year'] >= 2019 && $_GET['month'] >= 10) {
          $no_for_money = ' and `status_id` != 6 ';
          }  */

        }
        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].'.'.$_GET['month'].'.').'\' and `service_id` = '.$userID.' '.$no_for_money.' and `deleted` = 0;'));
        $content['billing_log'] = get_payment_info_by_date($userID, $_GET['year'], $_GET['month'], 1);
        $content['service_info'] = service_request_info($userID);
        $content['billing_info'] = service_billing_info($userID);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $xls->getActiveSheet()
        ->getPageSetup()
        ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $xls->getActiveSheet()
        ->getPageSetup()
        ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $sheet = $xls->getActiveSheet();
        if ($_GET['horizont'] == 1) {
        $sheet->setCellValue('B2', 'Республиканский Кредитный Альянс');
        $sheet->setCellValue('D2', '044525860');
        $sheet->setCellValue('D3', '30101810945250000860');
        $sheet->setCellValue('D4', '40702810400000000732');
        $sheet->setCellValue('B4', '773301001');
        $sheet->setCellValue('B3', '7730126288');
        } else {
        $sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('D2', $content['billing_info']['bik']);
        $sheet->setCellValue('D3', $content['billing_info']['sc1']);
        $sheet->setCellValue('D4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        }

        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0');

        if ( $userID == 33 && $_GET['tesler'] == 1) {
        $sheet->setCellValue('B14', $config['billing_info_to_tesler']);
        //$sheet->setCellValue('E28', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_footer_tesler'] : $config['billing_info_to_footer']));
        //$sheet->setCellValue('H30', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_fio_footer_tesler'] : $config['billing_info_to_fio_footer']));

        }
        if ( $userID == 33 && $_GET['roch'] == 1) {
          $sheet->setCellValue('B14', $config['billing_info_to_roch']);
          }
        if ( $userID == 33 && $_GET['horizont'] == 1) {
        $sheet->setCellValue('B14', 'ЗАО «Горизонт-Союз», Юр. адрес: 125466, г. Москва, ул. Соколово-Мещерская, д. 29, Почт. адрес: 125466, г. Москва, ул. Соколово-Мещерская, д. 29, Тел./факс: (495) 926-93-30, E-mail: info@horizont.tv, service@tv-service.tv, Сайт: www.tv-service.tv');
        //$sheet->setCellValue('E28', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_footer_tesler'] : $config['billing_info_to_footer']));
        //$sheet->setCellValue('H30', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_fio_footer_tesler'] : $config['billing_info_to_fio_footer']));

        }

        if ($_GET['tesler'] == 1 ) {
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].'-2 от '.date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        } else if ($_GET['tesler'] == 1 ) {
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].'-2 от '.date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        } else {
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].' от '.date("t", strtotime($_GET['year'].'-'.$_GET['month'].'-05')).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        }
        $sheet->setCellValue('B5', $content['service_info']['name']);
        $sheet->setCellValue('B11', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', тел.'.$content['service_info']['phones']);

        if ( $userID == 33 && ($_GET['tesler'] != 1 &&  $_GET['horizont'] != 1)) {
        $sheet->setCellValue('B14', $config['billing_info_to']);
        }  else if ( $userID != 33){
        $sheet->setCellValue('B14', $config['billing_info']);
        }

 if ( $userID == 33 && $_GET['tesler'] == 1) {
        $sheet->setCellValue('B17', $config['osnovanie_tesler']);
        } else if ( $userID == 33 && $_GET['roch'] == 1) {
          $sheet->setCellValue('B17', $config['osnovanie_roch']);
          } else if ( $userID == 33 && $_GET['horizont'] == 1) {
        $sheet->setCellValue('B17', $config['osnovanie_horizont']);
        } else if ( $userID == 33 && $_GET['tesler'] != 1) {
        $sheet->setCellValue('B17', $config['osnovanie_other']);
        } else {
        //$sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору '.$content['billing_info']['agree']);
        $sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору №_____ от ___________'); 
      }   

        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Tesler за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        } else if ($_GET['roch'] == 1) {
          $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Roch за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
          }  else if ($_GET['brand'] == 'SELENGA') {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Selenga за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        }  else if ($_GET['horizont'] == 1) {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Горизонт-Союз за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        } else if ($_GET['sven'] == 1) {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду SVEN за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        } else {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        }
        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('E20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager));
        $sheet->setCellValue('F20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager));
        $sheet->setCellValue('F22', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager));
        $sheet->setCellValue('F24', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager));
        $sheet->setCellValue('E26', get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager).' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'TESLER', '', $billing_manager)));
        } else if ($_GET['brand'] == 'SELENGA') {
        $sheet->setCellValue('E20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager));
        $sheet->setCellValue('F20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager));
        $sheet->setCellValue('F22', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager));
        $sheet->setCellValue('F24', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager));
        $sheet->setCellValue('E26', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager).' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'SELENGA', '', $billing_manager)));
        } else if ($_GET['horizont'] == 1) {
        $sheet->setCellValue('E20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT'));
        $sheet->setCellValue('F20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT'));
        $sheet->setCellValue('F22', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT'));
        $sheet->setCellValue('F24', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT'));
        $sheet->setCellValue('E26', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT').' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'HORIZONT')));
        }  else if ($_GET['sven'] == 1) {
        $sheet->setCellValue('E20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN'));
        $sheet->setCellValue('F20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN'));
        $sheet->setCellValue('F22', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN'));
        $sheet->setCellValue('F24', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN'));
        $sheet->setCellValue('E26', get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN').' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'SVEN')));
		}  else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('E20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('F20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('F22', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('F24', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('E26', get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH').' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'ROCH')));
        } else {
        $sheet->setCellValue('E20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager));
        $sheet->setCellValue('F20', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager));
        $sheet->setCellValue('F22', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager));
        $sheet->setCellValue('F24', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager));
        $sheet->setCellValue('E26', get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager).' руб.');
        $sheet->setCellValue('B27', num2str(get_service_summ($userID, $_GET['month'], $_GET['year'], 'HARPER', '', $billing_manager)));
        }
        $sheet->setCellValue('A32', 'Генеральный директор ' . chr(10) .$content['service_info']['req_gen_fio']);
        if ($content['billing_info']['chp'] != 1 && $userID != 33) {
        $sheet->setCellValue('C32', $content['billing_info']['accountant']);
        } else {
        if ($_GET['sven'] == 1) {
        $sheet->setCellValue('A32', "Индивидуальный предприниматель\nКулиджанов А.А.");
        } else {
        $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        }
        $sheet->setCellValue('F32', '');
        $sheet->setCellValue('E32', '');
        $sheet->setCellValue('D32', '');
        $sheet->getStyle("E32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        $sheet->getStyle("F32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        }

        if ($_GET['sven'] == 1) {
          $xls->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
          $sven_name = '_sven';
        $sheet->setCellValue('B14', 'ООО «РТ-Ф», Юр. адрес:  105082, г. Москва, ул. Фридриха Энгельса, д.56, стр. 1, этаж 3, помещение 21, тел. (903) 562-56-05, ИНН:7701679880,  КПП: 770101001, Р/С: 40702810338290110709,  Банк: Стромынское ОСБ 5281 Сбербанка России,   К/С: 30101810400000000225, БИК:  044525225');
        $sheet->setCellValue('B17', 'Договор сервисного обслуживания № RU 167 / 2019 от 01.04.2019');
        $xls->getActiveSheet()->getRowDimension('14')->setRowHeight(50);
        }

       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);
        if($onlyGenerate){
          return $new_file;
        }
      header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].$sven_name.'_счет.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        return $new_file;
  //}
      }

# Дашборд:
if ($_GET['query'] == 'get-payment-bill-admin') {

  
  
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$_GET['service_id'].' and `deleted` = 0;'));
        $content['billing_log'] = get_payment_info_by_date($_GET['service_id'], $_GET['year'], $_GET['month'], 1);
        $content['service_info'] = service_request_info($_GET['service_id']);
        $content['billing_info'] = service_billing_info($_GET['service_id']);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/bill.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $xls->getActiveSheet()
        ->getPageSetup()
        ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
       if ($_GET['horizont'] == 1) {
        $sheet->setCellValue('B2', 'Республиканский Кредитный Альянс');
        $sheet->setCellValue('D2', '044525860');
        $sheet->setCellValue('D3', '30101810945250000860');
        $sheet->setCellValue('D4', '40702810400000000732');
        $sheet->setCellValue('B4', '773301001');
        $sheet->setCellValue('B3', '7730126288');
        } else {
        $sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('D2', $content['billing_info']['bik']);
        $sheet->setCellValue('D3', $content['billing_info']['sc1']);
        $sheet->setCellValue('D4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        }
        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0');

      if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('B14', $config['billing_info_to_tesler']);
        } else    if ($_GET['roch'] == 1) {
          $sheet->setCellValue('B14', $config['billing_info_to_roch']);
          }else    if ($_GET['horizont'] == 1) {
        $sheet->setCellValue('B14', 'ЗАО «Горизонт-Союз», Юр. адрес: 125466, г. Москва, ул. Соколово-Мещерская, д. 29, Почт. адрес: 125466, г. Москва, ул. Соколово-Мещерская, д. 29, Тел./факс: (495) 926-93-30, E-mail: info@horizont.tv, service@tv-service.tv, Сайт: www.tv-service.tv');
        }

        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].'-2 от '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        } else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].'-3 от '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        } else {
        $sheet->setCellValue('A8', 'Счёт на оплату № '.$content['billing_log']['id'].' от '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' года. ');
        }
        $sheet->setCellValue('B5', $content['service_info']['name']);
        $sheet->setCellValue('B11', $content['service_info']['name'].', ИНН '.$content['service_info']['inn'].', КПП '.$content['service_info']['kpp'].', '.$content['service_info']['adress'].', тел.'.$content['service_info']['phones']);

        $sheet->setCellValue('B14', $config['billing_info']);

        $sheet->setCellValue('B17', 'Вознаграждение за гарантийные, сервисные услуги по договору '.$content['billing_info']['agree']);


        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Tesler за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        } else if ($_GET['selenga'] == 1) {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Selenga за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
		} else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания по бренду Roch за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        }  else {
        $sheet->setCellValue('B20', 'Организация сервисного обслуживания за '.$_monthsList[$_GET['month']].' '.$_GET['year'].' года.');
        }
        if ($_GET['tesler'] == 1) {
        $sheet->setCellValue('E20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER'));
        $sheet->setCellValue('F20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER'));
        $sheet->setCellValue('F22', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER'));
        $sheet->setCellValue('F24', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER'));
        $sheet->setCellValue('F26', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER').' руб.');
        $sheet->setCellValue('A27', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'TESLER')));
        } else if ($_GET['selenga'] == 1) {
        $sheet->setCellValue('E20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA'));
        $sheet->setCellValue('F20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA'));
        $sheet->setCellValue('F22', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA'));
        $sheet->setCellValue('F24', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA'));
        $sheet->setCellValue('F26', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA').' руб.');
        $sheet->setCellValue('A27', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'SELENGA')));
		} else if ($_GET['roch'] == 1) {
        $sheet->setCellValue('E20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('F20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('F22', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('F24', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH'));
        $sheet->setCellValue('F26', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH').' руб.');
        $sheet->setCellValue('A27', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'ROCH')));
        } else {
        $sheet->setCellValue('E20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER'));
        $sheet->setCellValue('F20', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER'));
        $sheet->setCellValue('F22', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER'));
        $sheet->setCellValue('F24', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER'));
        $sheet->setCellValue('F26', get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER').' руб.');
        $sheet->setCellValue('A27', num2str(get_service_summ($_GET['service_id'], $_GET['month'], $_GET['year'], 'HARPER')));
        }

        $sheet->setCellValue('C32', $content['service_info']['req_gen_fio']);

        if ($content['billing_info']['chp'] != 1) {
        $sheet->setCellValue('A32', $content['billing_info']['accountant']);
        } else {
          $sheet->setCellValue('A32', 'Индивидуальный предприниматель');
        $sheet->setCellValue('F32', '');
        $sheet->setCellValue('E32', '');
        $sheet->setCellValue('D32', '');
        $sheet->getStyle("E32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        $sheet->getStyle("F32")->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => 'CCCCCC')
                    )
                )
            )
        );
        }


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].'_счет.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-payment-act-admin-v2') {

  
  
  if (User::hasRole('admin')  || User::hasRole('acct')) {
  require_once 'adm/excel/vendor/autoload.php';


        $combine = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `combine` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['combine_id']).'\';'));
        $date = explode('.', $combine['date']);
        $year = $date['0'];
        $month = $date['1'];
        //$content['service_info'] = service_request_info($_GET['service_id']);
        //$content['billing_info'] = service_billing_info($_GET['service_id']);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/payed2.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        if ($_GET['brand'] == 'TESLER') {
        $sheet->setCellValue('B3', $combine['id'].'-T');
        } else if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('B3', $combine['id'].'-'.$_GET['brand']);
        } else {
        $sheet->setCellValue('B3', $combine['id']);
        }

        $brand_info = brand_info_get($_GET['brand']);

        $sheet->setCellValue('D3', date("t", strtotime($year.'-'.$month.'-05')).' '.$_monthsList2[$month].' '.$year.' года.');

        if ($_GET['brand'] == 'TESLER') {
        $sheet->setCellValue('B5', $config['billing_info_from_tesler']);
        $sheet->setCellValue('B9', $config['billing_info_to_tesler']);
        $sheet->setCellValue('B14', $config['billing_info_agent_tesler']);
        } else if ($_GET['brand'] == 'ROCH') {
          $sheet->setCellValue('B5', $config['billing_info_from_roch']);
          $sheet->setCellValue('B9', $config['billing_info_to_roch']);
          $sheet->setCellValue('B14', $config['billing_info_agent_roch']);
          }
          else if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('B5', $brand_info['billing_info_from']);
        $sheet->setCellValue('B9', $brand_info['billing_info_to']);
        $sheet->setCellValue('B14', $brand_info['billing_info_agent']);

        } else {
        $sheet->setCellValue('B5', (($_GET['brand'] == 'TESLER') ? $config['billing_info_from_tesler'] : $config['billing_info_from']));
        $sheet->setCellValue('B9', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_tesler'] : $config['billing_info_to']));
        $sheet->setCellValue('B14', (($_GET['brand'] == 'TESLER') ? $config['billing_info_agent_tesler'] : $config['billing_info_agent']));
        }

        if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания по бренду '.strtoupper($_GET['brand']).' за '.$_monthsList[$month].' '.$year.' года.');
        } else {
        $sheet->setCellValue('B17', 'Организация сервисного обслуживания за '.$_monthsList[$month].' '.$year.' года.');
        }
        if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('B18', 'Агентское вознаграждение за сервисное обслуживание по бренду '.strtoupper($_GET['brand']).' за '.$_monthsList[$month].' '.$year.' года.');
        } else {
        $sheet->setCellValue('B18', 'Агентское вознаграждение за сервисное обслуживание за '.$_monthsList[$month].' '.$year.' года.');
        }
        $sql2 = mysqli_query($db, 'SELECT * FROM `combine_links` where `combine_id` = '.$combine['id'].' and `type` IN (1,3);');
    
        while ($row2 = mysqli_fetch_array($sql2)) {
          
          $info = get_payment_info($row2['pay_billing_id']);
      $info['month'] = ($info['month'] < 10) ? '0' . $info['month'] : $info['month'];
      $typer = ($info['type'] > 1) ? 'TESLER' : 'HARPER';
      if ($_GET['brand'] == 'TESLER' && $info['type'] > 1) {
        $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $typer);
      } else if ($_GET['brand'] == 'HARPER' && $info['type'] < 3) {
        $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $typer);
      } else if ($_GET['brand'] != 'TESLER' && $_GET['brand'] != 'HARPER' && $info['type'] > 12) {
        $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $_GET['brand']);
      }
    }
		if ($combine['id'] == 139) {
			$summ = $summ - 300;
		}

        if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('G17', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('H17', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('H18', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $sheet->setCellValue('G18', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $summ_full = $summ+$summ*$brand_info['percent'];
        } else {
        $sheet->setCellValue('G17', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('H17', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('H18', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $sheet->setCellValue('G18', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $summ_full = $summ+$summ*$brand_info['percent'];
        }



        $sheet->setCellValue('H19', (preg_match('/\,/', $summ_full)) ? $summ_full : $summ_full.'.00');
        $sheet->setCellValue('A21', 'Всего оказано услуг 2, на сумму '.((preg_match('/\,/', $summ_full)) ? number_format($summ_full, 2, '.', '') : $summ_full.'.00'));
        $sheet->setCellValue('A20', num2str((preg_match('/\,/', $summ_full)) ? $summ_full : $summ_full.'.00'));



        if ($_GET['brand'] == 'TESLER') {
			$sheet->setCellValue('A29', (($_GET['brand'] == 'TESLER') ? $config['billing_info_from_footer_tesler'] : $config['billing_info_from_footer']));
			$sheet->setCellValue('E29', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_footer_tesler'] : $config['billing_info_to_footer']));
			$sheet->setCellValue('B31', (($_GET['brand'] == 'TESLER') ? $config['billing_info_from_fio_footer_tesler'] : $config['billing_info_from_fio_footer']));
			$sheet->setCellValue('G31', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_fio_footer_tesler'] : $config['billing_info_to_fio_footer']));
		}
		else if ($_GET['brand'] == 'ROCH') {
			$sheet->setCellValue('A29', (($_GET['brand'] == 'ROCH') ? $config['billing_info_from_footer_roch'] : $config['billing_info_from_footer']));
			$sheet->setCellValue('E29', (($_GET['brand'] == 'ROCH') ? $config['billing_info_to_footer_roch'] : $config['billing_info_to_footer']));
			$sheet->setCellValue('B31', (($_GET['brand'] == 'ROCH') ? $config['billing_info_from_fio_footer_roch'] : $config['billing_info_from_fio_footer']));
			$sheet->setCellValue('G31', (($_GET['brand'] == 'ROCH') ? $config['billing_info_to_fio_footer_roch'] : $config['billing_info_to_fio_footer']));
		}
		else if ($_GET['brand'] != 'HARPER') {
			$sheet->setCellValue('A29', $brand_info['billing_info_from_footer']);
			if($_GET['brand'] == 'SELENGA'){
				$sheet->setCellValue('E29', 'А. Н. Дианов');
			} else {
				$sheet->setCellValue('E29', $brand_info['billing_info_to_footer']);
			}
			$sheet->setCellValue('B31', $brand_info['billing_info_from_fio_footer']);
			$sheet->setCellValue('G31', $brand_info['billing_info_to_fio_footer']);
        } else {
			$sheet->setCellValue('A29', (($_GET['brand'] == 'TESLER') ? $config['billing_info_from_footer_tesler'] : $config['billing_info_from_footer']));
			$sheet->setCellValue('E29', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_footer_tesler'] : $config['billing_info_to_footer']));
			$sheet->setCellValue('B31', (($_GET['brand'] == 'TESLER') ? $config['billing_info_from_fio_footer_tesler'] : $config['billing_info_from_fio_footer']));
			$sheet->setCellValue('G31', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_fio_footer_tesler'] : $config['billing_info_to_fio_footer']));
        }


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

      header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');

       if ($_GET['brand'] == 'TESLER') {
        header('Content-Disposition: attachment; filename="'.$combine['id'].'-T_'.$month.'_'.$year.'_акт.xlsx"');
        } else if ($_GET['brand'] == 'ROCH') {
        header('Content-Disposition: attachment; filename="'.$combine['id'].'-R_'.$month.'_'.$year.'_акт.xlsx"');
        } else {
        header('Content-Disposition: attachment; filename="'.$combine['id'].'_'.$month.'_'.$year.'_акт.xlsx"');
        }
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  }
}


# Дашборд:
if ($_GET['query'] == 'get-payment-bill-admin-v2') {

  
  
  if (User::hasRole('admin') || User::hasRole('acct')) {
  require_once 'adm/excel/vendor/autoload.php';

        $combine = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `combine` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['combine_id']).'\';'));
        $date = explode('.', $combine['date']);
        $year = $date['0'];
        $month = $date['1'];

        //$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `app_date` REGEXP \''.mysqli_real_escape_string($db, $_GET['year'].$_GET['month'].'.').'\' and `service_id` = '.$_GET['service_id'].' and `deleted` = 0;'));
        //$content['billing_log'] = get_payment_info_by_date($_GET['service_id'], $_GET['year'], $_GET['month'], 1);
        //$content['service_info'] = service_request_info($_GET['service_id']);
        //$content['billing_info'] = service_billing_info($_GET['service_id']);
        //$content['cat_info'] = model_cat_info($content['model']['cat']);
        //$content['parts_info'] = repairs_parts_info_array($content['id']);
        //$content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/bill2.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        /*$sheet->setCellValue('B2', $content['billing_info']['bank_name']);
        $sheet->setCellValue('F2', $content['billing_info']['bik']);
        $sheet->setCellValue('F3', $content['billing_info']['sc1']);
        $sheet->setCellValue('F4', $content['billing_info']['sc2']);
        $sheet->setCellValue('B4', $content['service_info']['kpp']);
        $sheet->setCellValue('B3', $content['service_info']['inn']);
        $sheet->getStyle('B3') ->getNumberFormat() ->setFormatCode('0');
        $sheet->getStyle('B4') ->getNumberFormat() ->setFormatCode('0'); */
        if ($_GET['brand'] == 'TESLER') {
        $sheet->setCellValue('A7', 'Счёт на оплату № '.$combine['id'].'-T от '.date("t", strtotime('05-'.$month.'-'.$year)).' '.$_monthsList2[$month].' '.$year.' года. ');
        } else if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('A7', 'Счёт на оплату № '.$combine['id'].'-'.$_GET['brand'].' от '.date("t", strtotime('05-'.$month.'-'.$year)).' '.$_monthsList2[$month].' '.$year.' года. ');
        } else {
        $sheet->setCellValue('A7', 'Счёт на оплату № '.$combine['id'].' от '.date("t", strtotime('05-'.$month.'-'.$year)).' '.$_monthsList2[$month].' '.$year.' года. ');
        }
        //$sheet->setCellValue('B5', $content['service_info']['name']);

      $brand_info = brand_info_get($_GET['brand']);

        if ($_GET['brand'] == 'TESLER') {
     $sheet->setCellValue('B10', (($_GET['brand'] == 'TESLER') ? $config['billing_info_from_tesler'] : $config['billing_info_from']));
        $sheet->setCellValue('B13', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_tesler'] : $config['billing_info_to']));
        $sheet->setCellValue('B15', (($_GET['brand'] == 'TESLER') ? $config['billing_info_agent_tesler'] : $config['billing_info_agent']));
        } else if ($_GET['brand'] == 'ROCH') {
          $sheet->setCellValue('B10', $config['billing_info_from_roch']);
          $sheet->setCellValue('B13', $config['billing_info_to_roch']);
          $sheet->setCellValue('B15', $config['billing_info_agent_roch']);
        } else if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('B10', $brand_info['billing_info_from']);
        $sheet->setCellValue('B13', $brand_info['billing_info_to']);
        $sheet->setCellValue('B15', $brand_info['billing_info_agent']);

        } else {
     $sheet->setCellValue('B10', (($_GET['brand'] == 'TESLER') ? $config['billing_info_from_tesler'] : $config['billing_info_from']));
        $sheet->setCellValue('B13', (($_GET['brand'] == 'TESLER') ? $config['billing_info_to_tesler'] : $config['billing_info_to']));
        $sheet->setCellValue('B15', (($_GET['brand'] == 'TESLER') ? $config['billing_info_agent_tesler'] : $config['billing_info_agent']));
        }


        if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('B18', 'Организация сервисного обслуживания по бренду '.strtoupper($_GET['brand']).' за '.$_monthsList[$month].' '.$year.' года.');
        } else {
        $sheet->setCellValue('B18', 'Организация сервисного обслуживания за '.$_monthsList[$month].' '.$year.' года.');
        }
        if ($_GET['brand'] != 'HARPER') {
        $sheet->setCellValue('B19', 'Агентское вознаграждение за сервисное обслуживание по бренду '.strtoupper($_GET['brand']).' за '.$_monthsList[$month].' '.$year.' года.');
        } else {
        $sheet->setCellValue('B19', 'Агентское вознаграждение за сервисное обслуживание за '.$_monthsList[$month].' '.$year.' года.');
        }

        $sql2 = mysqli_query($db, 'SELECT * FROM `combine_links` where `combine_id` = '.$combine['id'].' and `type` IN (1,3);');
        while ($row2 = mysqli_fetch_array($sql2)) {
        $info = get_payment_info($row2['pay_billing_id']);
        $info['month'] = ($info['month'] < 10) ? '0'.$info['month'] : $info['month'];
        $typer = ($info['type'] > 1) ? 'TESLER' : 'HARPER';
        if ($_GET['brand'] == 'TESLER' && $info['type'] > 1) {
        $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $typer);
        } else if ($_GET['brand'] == 'HARPER' && $info['type'] < 3) {
        $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $typer);

        } else if ($_GET['brand'] != 'TESLER' && $_GET['brand'] != 'HARPER' && $info['type'] > 12) {
        $summ += get_service_summ($info['service_id'], $info['month'], $info['year'], $_GET['brand']);
        }

        }

		if ($combine['id'] == 139) {
			$summ = $summ - 300;
		}

        if ($_GET['brand'] == 'TESLER') {
        $sheet->setCellValue('E18', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('F18', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('E19', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $sheet->setCellValue('F19', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $summ_full = $summ+$summ*$brand_info['percent'];
        } else {
        $sheet->setCellValue('E18', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('F18', (preg_match('/\,/', $summ)) ? $summ : $summ.'.00');
        $sheet->setCellValue('E19', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $sheet->setCellValue('F19', (preg_match('/\,/', $summ*$brand_info['percent'])) ? $summ*$brand_info['percent'] : $summ*$brand_info['percent'].'.00');
        $summ_full = $summ+$summ*$brand_info['percent'];
        }

        $sheet->setCellValue('F20', (preg_match('/\,/', $summ_full)) ? $summ_full : $summ_full.'.00');
        $sheet->setCellValue('F22', (preg_match('/\,/', $summ_full)) ? $summ_full : $summ_full.'.00');
        $sheet->setCellValue('F24', (preg_match('/\,/', $summ_full)) ? $summ_full : $summ_full.'.00');
        $sheet->setCellValue('A25', num2str((preg_match('/\,/', $summ_full)) ? $summ_full : $summ_full.'.00'));


       $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        if ($_GET['brand'] == 'TESLER') {
        header('Content-Disposition: attachment; filename="'.$combine['id'].'-T_'.$month.'_'.$year.'_счет.xlsx"');
        } else {
        header('Content-Disposition: attachment; filename="'.$combine['id'].'_'.$month.'_'.$year.'_счет.xlsx"');
        }
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  }
}

if ($_GET['query'] == 'get-payment-archive') {
  $year = $_GET['year'];
  $month = $_GET['month'];
  $content['service_info'] = service_request_info(User::getData('id')); 
  $baseName = translit2(preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$month.'_'.$year);
  $archivePath = $_SERVER['DOCUMENT_ROOT'] . '/adm/excel/archive/';
  $actPath = getPaymentAct(true);
  copy($_SERVER['DOCUMENT_ROOT'] . '/' . $actPath, $archivePath . $baseName.'_акт.xlsx');  
  $billPath = getPaymentBill(true);
  copy($_SERVER['DOCUMENT_ROOT'] . '/' . $billPath, $archivePath . $baseName.'_счет.xlsx');  
  $doc = models\Documents::getDocument('detail-report', ['year' => $year, 'month' => $month, 'service-id' => User::getData('id'), 'brand' => ((empty($_GET['tesler'])) ? 'HARPER' : 'TESLER')]);
  $reportPath = $doc->save();
  copy($_SERVER['DOCUMENT_ROOT'] . $reportPath, $archivePath . $baseName.'_отчет.xlsx');
      // gen_act(User::getData('id'), $_GET['month'], $_GET['year']);
     //  gen_bill(User::getData('id'), $_GET['month'], $_GET['year'], ((empty($_GET['tesler'])) ? 'HARPER' : 'TESLER'));
      //  file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_акт.xlsx', file_get_contents('https://crm.r97.ru/get-payment-act/'.$_GET['year'].'/'.$_GET['month'].'/tesler/'));
      //  file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_счет.xlsx', file_get_contents('https://crm.r97.ru/get-payment-bill/'.$_GET['year'].'/'.$_GET['month'].'/tesler/'));
        $ZipFileName = preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].'_акт_и_счет.zip';
        $pathdir='adm/excel/archive/'; 
        @unlink($pathdir.$ZipFileName);
        $nameArhive = $ZipFileName;
        $zip = new ZipArchive;
        $file = $pathdir.$ZipFileName;
        if ($zip -> open($nameArhive, ZipArchive::CREATE) === TRUE){
            $dir = opendir($pathdir);
            while( $file = readdir($dir)){
                if (is_file($pathdir.$file)){
                    $zip -> addFile($pathdir.$file, $file);
                }
            }
            $zip -> close();
        }
        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($ZipFileName));
        header("Content-Disposition: attachment; filename=\"".basename($ZipFileName)."\"");
        readfile($ZipFileName);

        if (file_exists('adm/excel/archive/')) {
            foreach (glob('adm/excel/archive/*') as $file) {
                unlink($file);
            }
        }
        @unlink($ZipFileName);
        exit;
}

if ($_GET['query'] == 'send-to-pay') {

switch ($_GET['brand']) {
    case '':
        $type = 1;
        break;
    case 'tesler':
        $type = 3;
        break;
	case 'selenga':
        $type = 13;
        break;
		/*PG*/
	case 'roch':
        $type = 15;
        break;
}


    mysqli_query($db, 'UPDATE `pay_billing` SET
    `sended` = 1
    WHERE `month` = '.$_GET['month'].' and `year` = '.$_GET['year'].' and `service_id` = '.$_GET['service_id'].' and `type` = '.$type .' LIMIT 1;') or mysqli_error($db);

   header('Location: /payments-sended/');

}

if ($_GET['query'] == 'send-to-repay') {

switch ($_GET['brand']) {
    case '':
    $type = 1;
    $type2 = 2;
        break;
    case 'tesler':
    $type = 3;
    $type2 = 4;
        break;
    case 'selenga':
    $type = 13;
    $type2 = 14;
        break;
}


    mysqli_query($db, 'UPDATE `pay_billing` SET
    `sended` = 0,
    `original` = 0
    WHERE `month` = '.$_GET['month'].' and `year` = '.$_GET['year'].' and `service_id` = '.$_GET['service_id'].' and `type` = '.$type.' LIMIT 1;') or mysqli_error($db);
    mysqli_query($db, 'UPDATE `pay_billing` SET
    `sended` = 0,
    `original` = 0
    WHERE `month` = '.$_GET['month'].' and `year` = '.$_GET['year'].' and `service_id` = '.$_GET['service_id'].' and `type` = '.$type2.' LIMIT 1;') or mysqli_error($db);
   header('Location: /payments-sended/');

}

if ($_GET['query'] == 'get-payment-archive-admin') {

       
       
       $content['service_info'] = service_request_info($_GET['service_id']);
       gen_act($_GET['service_id'], $_GET['month'], $_GET['year'], $_GET['tesler']);
       gen_bill($_GET['service_id'], $_GET['month'], $_GET['year'], $_GET['tesler']);
        //file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_акт.xlsx', file_get_contents('http://service.harper.ru/get-payment-act/2018/01/'));
        //file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_счет.xlsx', file_get_contents('http://service.harper.ru/get-payment-act/2017/12/'));
        $ZipFileName = preg_replace('/[^a-zA-Zа-яА-Я0-9]/ui', '',$content['service_info']['name']).'_'.$_GET['month'].'_'.$_GET['year'].'_акт_и_счет.zip';
      //  echo $ZipFileName;
        $pathdir='adm/excel/archive/';
        @unlink($pathdir.$ZipFileName);
        $nameArhive = $ZipFileName;
        $zip = new ZipArchive;
        $file = $pathdir.$ZipFileName;

        if ($zip -> open($nameArhive, ZipArchive::CREATE) === TRUE){
            $dir = opendir($pathdir);

            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    $zip -> addFile($pathdir.$file, $file);
                }

            }

            $zip -> close();

        }

       header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($ZipFileName));
        header("Content-Disposition: attachment; filename=\"".basename($ZipFileName)."\"");
        readfile($ZipFileName);

        if (file_exists('adm/excel/archive/')) {
            foreach (glob('adm/excel/archive/*') as $file) {
                unlink($file);
            }
        }
        @unlink($ZipFileName);
        exit;




}

if ($_GET['query'] == 'get-payment-archive-full') {
       $date = explode('.', $_POST['date']);
       $_GET['month'] = $date['1'];
       $_GET['year'] = $date['0'];
       
       
      // print_r($_GET);
      $sql = mysqli_query($db, 'SELECT * FROM `repairs` where `app_date` REGEXP \''.$_POST['date'].'\' and `deleted` = 0 and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\')  order by `id` DESC;');
      while ($row = mysqli_fetch_array($sql)) {
      //$info = get_request_info($row['id']);
      //$city = get_city($info['city']);


       gen_act2($row['service_id'], $_GET['month'], $_GET['year']);
       gen_bill2($row['service_id'], $_GET['month'], $_GET['year']);


      }



        //file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_акт.xlsx', file_get_contents('http://service.harper.ru/get-payment-act/2018/01/'));
        //file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_счет.xlsx', file_get_contents('http://service.harper.ru/get-payment-act/2017/12/'));
        $ZipFileName = 'все_сервисы__'.$_GET['month'].'_'.$_GET['year'].'_акт_и_счет.zip';
      //  echo $ZipFileName;
        $pathdir='adm/excel/archive/';
        @unlink($pathdir.$ZipFileName);
        $nameArhive = $ZipFileName;
        $zip = new ZipArchive;
        $file = $pathdir.$ZipFileName;

        if ($zip -> open($nameArhive, ZipArchive::CREATE) === TRUE){
            $dir = opendir($pathdir);

            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    $zip -> addFile($pathdir.$file, $file);
                }

            }

            $zip -> close();

        }

        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($ZipFileName));
        header("Content-Disposition: attachment; filename=\"".basename($ZipFileName)."\"");
        readfile($ZipFileName);

        if (file_exists('adm/excel/archive/')) {
            foreach (glob('adm/excel/archive/*') as $file) {
                unlink($file);
            }
        }
        @unlink($ZipFileName);
        exit;




}


if ($_GET['query'] == 'get-all-bills') {

      

      $combine = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `combine` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['combine_id']).'\';'));
        $date = explode('.', $combine['date']);
        $year = $date['0'];
        $month = $date['1'];

       
      // print_r($_GET);
      $sql = mysqli_query($db, 'SELECT * FROM `combine_links` where `combine_id` = \''.$_GET['id'].'\' and `type` = 0 ;');
      //$tesler = ($_GET['type'] == 'tesler') ? 1 : 0;
      while ($row = mysqli_fetch_array($sql)) {
      $pay = get_payment_info($row['pay_billing_id']);
      $pay['month'] = ($pay['month'] < 10) ? '0'.$pay['month'] : $pay['month'];
      //gen_act2($pay['service_id'], $pay['month'], $pay['year']);

      if ($pay['type'] > 2 && $_GET['type'] == 'tesler') {
      $list[$pay['service_id']]['dates'][$pay['year'].'.'.$pay['month']] = 1;
      } else if ($pay['type'] < 3 && $_GET['type'] == 'harper') {
      $list[$pay['service_id']]['dates'][$pay['year'].'.'.$pay['month']] = 1;
      } else if ($_GET['type'] != 'tesler' && $_GET['type'] != 'harper' && $pay['type'] > 12) {
      $list[$pay['service_id']]['dates'][$pay['year'].'.'.$pay['month']] = 1;
      }

            //gen_bill($pay['service_id'], $pay['month'], $pay['year'], $tesler);

      }

      foreach ($list as $service => $dates) {


      foreach ($dates['dates'] as $date => $no) {

      $date_real = explode('.', $date);
      gen_bill($service, $date_real['1'], $date_real['0'], $_GET['type']);
      //file_put_contents('bill_id.txt', $service.PHP_EOL, FILE_APPEND);

      }

      }


        //file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_акт.xlsx', file_get_contents('http://service.harper.ru/get-payment-act/2018/01/'));
        //file_put_contents('adm/excel/archive/'.$content['service_info']['name'].'_'.$_GET['month'].'_'.$_GET['year'].'_счет.xlsx', file_get_contents('http://service.harper.ru/get-payment-act/2017/12/'));
        $ZipFileName = 'все_счета__'.$_GET['id'].'.zip';
      //  echo $ZipFileName;
        $pathdir='adm/excel/archive/';
        @unlink($pathdir.$ZipFileName);
        $nameArhive = $ZipFileName;
        $zip = new ZipArchive;
        $file = $pathdir.$ZipFileName;

        if ($zip -> open($nameArhive, ZipArchive::CREATE) === TRUE){
            $dir = opendir($pathdir);

            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    $zip -> addFile($pathdir.$file, $file);
                }

            }

            $zip -> close();

        }

        header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".filesize($ZipFileName));
        header("Content-Disposition: attachment; filename=\"".basename($ZipFileName)."\"");
        readfile($ZipFileName);

        if (file_exists('adm/excel/archive/')) {
            foreach (glob('adm/excel/archive/*') as $file) {
                unlink($file);
            }
        }
        @unlink($ZipFileName);
        exit;




}


# Партнеры:
if ($_GET['query'] == 'get-notify') {
  header('Content-Type: application/json');
  
  
  $arrays = gen_notify(User::getData('id'));
  echo json_encode($arrays);
  exit;

}

# Партнеры:
if ($_GET['query'] == 'get-agent') {
  
  

$date_current = new DateTime("01/".$_GET['month']."/".$_GET['year']);
$date_from    = new DateTime("01/10/2019");

$userID = User::getData('id');
        if (User::hasRole('acct')) {
          $userID  = 33;

        if ($date_current >= $date_from) {
          $no_for_money = ' and `status_id` != 6 ';
          }

        }

  if (User::hasRole('admin') || $userID  == 33) {



  if ($userID  == 33) {
  $where_user = ' and `service_id` = 33';
  }

$table .= '<table id="table_content" class="display" cellspacing="0" width="100%" style="border:0.5px solid #000;">
        <thead>
            <tr>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Название сервисной службы</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Номер ремонта</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Номер СЦ в базе</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Бренд</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Категория</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Модель</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Серийный номер</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Количество</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Заявленная неисправность</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Наименование детали (элемента)</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Причина отказа детали</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Вид ремонта</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Количество</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Своя деталь</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Цена</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Сумма</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Выезд</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Демонтаж</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Монтаж</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Стоимость работ</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Общая сумма</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Ф.И.О. Клиента</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Телефон Клиента</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Дата покупки</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Дата начала ремонта</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Дата окончания ремонта</th>

            </tr>

        </thead>
        <tbody>';


  $date = $_GET['year'].'.'.$_GET['month'];

$where_date = 'and `app_date` REGEXP \''.$date.'\' ';

$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `deleted` = 0  '.$where_date.' '.$where_user.' and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') '.$no_for_money.' ;');
$counter = 1;
      while ($row = mysqli_fetch_array($sql)) {
     
        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);

      if ($content['begin_date'] != '0000-00-00') {

      $date1 = new DateTime($content['begin_date']);
      $date1_ready = $date1->format('d.m.Y');      

      }

      if ($content['finish_date'] != '0000-00-00') {

       if (preg_match('|2020|',$content['finish_date']) || preg_match('|2018|',$content['finish_date']) || preg_match('|2019|',$content['finish_date']) || preg_match('|2017|',$content['finish_date'])) {
      $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
      $date2_ready = $date2->format('d.m.Y');
      } else if (preg_match('/2020/',$content['finish_date']) || preg_match('/2019/',$content['finish_date']) || preg_match('/2018/',$content['finish_date']) || preg_match('/2019/',$content['finish_date']) || preg_match('/2017/',$content['finish_date'])) {
      $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
      $date2_ready = $date2->format('d.m.Y');
      } else {

      $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);

      $date2_ready = $date2->format('d.m.Y');
      }

      }


      if ($content['begin_date'] != '0000-00-00' && $content['finish_date'] != '0000-00-00') {

      $date1 = new DateTime($content['begin_date']);

      //$date2 = new DateTime($content['end_date']);
      $diff = $date2->diff($date1)->format("%a");
      }

      if (in_array($content['model']['brand'], explode(',',$_GET['brands']))) {
        $date_current = new DateTime("01/" . $_GET['month'] . "/" . $_GET['year']);
        $date_returns    = new DateTime("01/05/2020");
        $checkReturnsFlag  = ($date_current < $date_returns) ? true : false;
      if (!$checkReturnsFlag || (check_returns_pls($content['return_id']) || $content['return_id'] == 0)) {

        if ($date_current >= $date_from) {

         if ($content['master_user_id'] <= 0) {

         } else {
          $ids[] = $row['id'];
          $totalSum = $content['total_price'] + $content['transport_cost'] + $content['parts_cost'] + $content['install_cost'] + $content['dismant_cost'];
      $table .= '<tr>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['service_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['id'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['service_info']['id'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['model']['brand'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['cat_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['model']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['serial'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">1</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['bugs'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.get_content_by_id('repair_type', $content['parts_info']['repair_type_id'])['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['qty'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.(($content['parts_info']['ordered_flag'] == 1) ? 'Да' : 'Нет').'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['price'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['transport_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['dismant_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['install_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['total_price'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$totalSum.'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['client'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['phone'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.Time::format($content['sell_date']).'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$date1_ready .'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$date2_ready.'</td>
      </tr>';

      $total1 += $totalSum;
      $total2 += $totalSum * 0.35;

      $counter++;
       }

        } else {
$ids[] = $row['id'];
$totalSum = $content['total_price'] + $content['transport_cost'] + $content['parts_cost'] + $content['install_cost'] + $content['dismant_cost'];
      $table .= '<tr>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['service_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['id'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['service_info']['id'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['model']['brand'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['cat_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['model']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['serial'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">1</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['bugs'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.get_content_by_id('repair_type', $content['parts_info']['repair_type_id'])['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['qty'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.(($content['parts_info']['ordered_flag'] == 1) ? 'Да' : 'Нет').'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['price'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['transport_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['dismant_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['install_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['total_price'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$totalSum.'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['client'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['phone'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.Time::format($content['sell_date']).'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$date1_ready .'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$date2_ready.'</td>
      </tr>';

      $total1 += $totalSum;
      $total2 += $totalSum * 0.35;


        }

      }

      }


      }

      $table .= '<tr>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">Итого:</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$counter.'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$total1.'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      </tr>';

        $table .= '</tbody>
</table>';


header("Content-Type: application/vnd.ms-word");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("content-disposition: attachment;filename=Report.doc");

if (in_array('TESLER', explode(',',$_GET['brands'])) || $_GET['brands'] == 'tesler') {
$brand = brand_by_id(4);
} else if ($_GET['brands'] == 'sven') {
$brand = brand_by_id(20);
} else {
$brand = brand_by_id(1);
}

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">

<head>
    <meta http-equiv=Content-Type content="text/html; charset=utf-8">
    <meta name=ProgId content=Word.Document>
    <meta name=Generator content="Microsoft Word 14">
    <meta name=Originator content="Microsoft Word 14">
    <title></title>
    <!--[if gte mso 9]><xml><w:WordDocument><w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel><w:DisplayHorizontalDrawingGridEvery>0</w:DisplayHorizontalDrawingGridEvery><w:DisplayVerticalDrawingGridEvery>2</w:DisplayVerticalDrawingGridEvery><w:DocumentKind>DocumentNotSpecified</w:DocumentKind><w:DrawingGridVerticalSpacing>7.8 磅</w:DrawingGridVerticalSpacing><w:PunctuationKerning></w:PunctuationKerning><w:View>Web</w:View><w:Compatibility><w:DontGrowAutofit/></w:Compatibility><w:Zoom>0</w:Zoom></w:WordDocument></xml><![endif]-->
    <!--[if gte mso 9]><xml><w:LatentStyles DefLockedState="false" DefUnhideWhenUsed="true" DefSemiHidden="true" DefQFormat="false" DefPriority="99" LatentStyleCount="260" > <w:LsdException Locked="false" Priority="0" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Normal" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="heading 1" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 2" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 3" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 4" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 5" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 6" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 7" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 8" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 9" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 6" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 7" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 8" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 9" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 1" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 2" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 3" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 4" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 5" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 6" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 7" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 8" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 9" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Normal Indent" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="footnote text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="annotation text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="header" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="footer" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index heading" ></w:LsdException> <w:LsdException Locked="false" Priority="35" SemiHidden="false" QFormat="true" Name="caption" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="table of figures" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="envelope address" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="envelope return" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="footnote reference" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="annotation reference" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="line number" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="page number" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="endnote reference" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="endnote text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="table of authorities" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="macro" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="toa heading" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 5" ></w:LsdException> <w:LsdException Locked="false" Priority="10" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Title" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Closing" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Signature" ></w:LsdException> <w:LsdException Locked="false" Priority="1" SemiHidden="false" Name="Default Paragraph Font" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text Indent" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Message Header" ></w:LsdException> <w:LsdException Locked="false" Priority="11" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Subtitle" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Salutation" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Date" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text First Indent" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text First Indent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Note Heading" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text Indent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text Indent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Block Text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Hyperlink" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="FollowedHyperlink" ></w:LsdException> <w:LsdException Locked="false" Priority="22" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Strong" ></w:LsdException> <w:LsdException Locked="false" Priority="20" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Emphasis" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Document Map" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Plain Text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="E-mail Signature" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Normal (Web)" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Acronym" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Address" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Cite" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Code" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Definition" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Keyboard" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Preformatted" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Sample" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Typewriter" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Variable" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Normal Table" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="annotation subject" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="No List" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Simple 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Simple 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Simple 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Colorful 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Colorful 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Colorful 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 6" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 7" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 8" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 6" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 7" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 8" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table 3D effects 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table 3D effects 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table 3D effects 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Contemporary" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Elegant" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Professional" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Subtle 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Subtle 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Web 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Web 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Web 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Balloon Text" ></w:LsdException> <w:LsdException Locked="false" Priority="0" SemiHidden="false" UnhideWhenUsed="false" Name="Table Grid" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Theme" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 6" ></w:LsdException> </w:LatentStyles></xml><![endif]-->
    <style>
    body {
    mso-page-orientation: landscape
}
        @font-face{ font-family:"Times New Roman"; } @font-face{ font-family:"宋体"; } @font-face{ font-family:"Calibri"; } @font-face{ font-family:"Calibri"; } @font-face{ font-family:"Wingdings"; } table { font-family:\'Times New Roman\' !important; font-size: 10.0000pt !important; } @list l0:level1{ mso-level-number-format:decimal; mso-level-suffix:tab; mso-level-text:"%1."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:36.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level2{ mso-level-number-format:alpha-lower; mso-level-suffix:tab; mso-level-text:"%2."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:72.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level3{ mso-level-number-format:lower-roman; mso-level-suffix:tab; mso-level-text:"%3."; mso-level-tab-stop:none; mso-level-number-position:right; margin-left:108.0000pt;text-indent:-9.0000pt;font-family:\'Times New Roman\';} @list l0:level4{ mso-level-number-format:decimal; mso-level-suffix:tab; mso-level-text:"%4."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:144.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level5{ mso-level-number-format:alpha-lower; mso-level-suffix:tab; mso-level-text:"%5."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:180.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level6{ mso-level-number-format:lower-roman; mso-level-suffix:tab; mso-level-text:"%6."; mso-level-tab-stop:none; mso-level-number-position:right; margin-left:216.0000pt;text-indent:-9.0000pt;font-family:\'Times New Roman\';} @list l0:level7{ mso-level-number-format:decimal; mso-level-suffix:tab; mso-level-text:"%7."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:252.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level8{ mso-level-number-format:alpha-lower; mso-level-suffix:tab; mso-level-text:"%8."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:288.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level9{ mso-level-number-format:lower-roman; mso-level-suffix:tab; mso-level-text:"%9."; mso-level-tab-stop:none; mso-level-number-position:right; margin-left:324.0000pt;text-indent:-9.0000pt;font-family:\'Times New Roman\';} p.MsoNormal{ mso-style-name:Normal; mso-style-parent:""; margin:0pt; margin-bottom:.0001pt; font-family:Calibri; mso-bidi-font-family:\'Times New Roman\'; font-size:12.0000pt; } span.10{ font-family:Calibri; } p.15{ mso-style-name:"List Paragraph"; margin-left:36.0000pt; mso-add-space:auto; font-family:Calibri; mso-bidi-font-family:\'Times New Roman\'; font-size:12.0000pt; } span.msoIns{ mso-style-type:export-only; mso-style-name:""; text-decoration:underline; text-underline:single; color:blue; } span.msoDel{ mso-style-type:export-only; mso-style-name:""; text-decoration:line-through; color:red; } table.MsoNormalTable{ mso-style-name:"Table Normal"; mso-style-parent:""; mso-style-noshow:yes; mso-tstyle-rowband-size:0; mso-tstyle-colband-size:0; mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt; mso-para-margin:0pt; mso-para-margin-bottom:.0001pt; mso-pagination:widow-orphan; font-family:\'Times New Roman\'; font-size:10.0000pt; mso-ansi-language:#0400; mso-fareast-language:#0400; mso-bidi-language:#0400; } table.MsoTableGrid{ mso-style-name:"Table Grid"; mso-tstyle-rowband-size:0; mso-tstyle-colband-size:0; mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt; mso-border-top-alt:0.5000pt solid windowtext; mso-border-left-alt:0.5000pt solid windowtext; mso-border-bottom-alt:0.5000pt solid windowtext; mso-border-right-alt:0.5000pt solid windowtext; mso-border-insideh:0.5000pt solid windowtext; mso-border-insidev:0.5000pt solid windowtext; mso-para-margin:0pt; mso-para-margin-bottom:.0001pt; mso-pagination:widow-orphan; font-family:\'Times New Roman\'; font-size:10.0000pt; mso-ansi-language:#0400; mso-fareast-language:#0400; mso-bidi-language:#0400; } @page{mso-page-border-surround-header:no; mso-page-border-surround-footer:no;}@page Section0{ margin-top:85.0500pt; margin-bottom:42.5000pt; margin-left:56.7000pt; margin-right:56.7000pt; size:842.0000pt 595.0000pt; layout-grid:18.0000pt; mso-page-orientation:landscape; mso-page-orientation:  landscape} div.Section0{page:Section0;}
    </style>
</head>

<body style="tab-interval:35pt;">
    <!--StartFragment-->
    <div class="Section0" style="layout-grid:18.0000pt;">
        <table class=MsoTableGrid style="border-collapse:collapse;width:495.2500pt;mso-table-layout-alt:fixed;mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
            <tr style="height:16.3000pt;">
                <td width=330 valign=top style="width:247.6000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';font-size:16.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=330 valign=top style="width:247.6500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">'.$brand['title'].'</span> <span style="font-family:\'Times New Roman\';font-size:16.0000pt;"> <o:p></o:p> </span>                        </p>
                </td>
            </tr>
        </table>
        <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" ><o:p> </o:p></span></b></p>
        <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" >КОНСОЛИДИРОВАННЫЙ ОТЧЕТ №'.$_GET['agent_id'].'</span></b><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" ><o:p></o:p></span></b></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" ><o:p> </o:p></span></b></p>
        <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">г. Москва <p class=MsoNormal align=right style="text-align:right;">«'.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).'» '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' г.</p></span>            <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span> </p>
        <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">'.$brand['terms'].'</span></p>
        <p class=15 align=justify style="margin-left:36.0000pt;text-indent:-18.0000pt;text-align:justify;text-justify:inter-ideograph;mso-list:l0 level1 lfo1;">
            <![if !supportLists]><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><span style=\'mso-list:Ignore;\' >1.<span> </span></span>
            </span>
            <![endif]><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">За период с 1 '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' г. по '.date("t", strtotime('05-'.$_GET['month'].'-'.$_GET['year'])).' '.$_monthsList2[$_GET['month']].' '.$_GET['year'].' г. Сервисная служба во исполнение Договора сервисного обслуживания совершила следующие действия:</span>            <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span> </p>
        '.$table.'
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=15 align=justify style="margin-left:36.0000pt;text-indent:-18.0000pt;text-align:justify;text-justify:inter-ideograph;mso-list:l0 level1 lfo1;">
            <![if !supportLists]><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><span style=\'mso-list:Ignore;\' >2.<span> </span></span>
            </span>
            <![endif]><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Стоимость  оказанных Сервисной службой услуг,  подлежащих оплате Заказчиком, составляет '.num2str2($total1).' ('.$total1.') рублей 00 копеек.</span>
            <span
                style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">
                <o:p></o:p>
                </span>
        </p>
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Указанная сумма подтверждена документами, приложенными к Консолидированному отчету.</span> <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>            </p>

        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <table class=MsoNormalTable style="border-collapse:collapse;width:474.7000pt;mso-table-layout-alt:fixed;mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
            <tr style="height:38.9500pt;">
                <td width=316 valign=top style="width:237.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:31.8750pt none rgb(255,255,255);mso-border-right-alt:31.8750pt none rgb(255,255,255);border-top:31.8750pt none rgb(255,255,255);mso-border-top-alt:31.8750pt none rgb(255,255,255);border-bottom:31.8750pt none rgb(255,255,255);mso-border-bottom-alt:31.8750pt none rgb(255,255,255);">
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Отчет сдал:</span><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p></o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Сервисная служба</span><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p></o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">ИП Кулиджанов Андрей Александрович</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">____________________ / А.А. Кулиджанов/</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                </td>
                <td width=316 valign=top style="width:237.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:none;;mso-border-left-alt:none;;border-right:31.8750pt none rgb(255,255,255);mso-border-right-alt:31.8750pt none rgb(255,255,255);border-top:31.8750pt none rgb(255,255,255);mso-border-top-alt:31.8750pt none rgb(255,255,255);border-bottom:31.8750pt none rgb(255,255,255);mso-border-bottom-alt:31.8750pt none rgb(255,255,255);">
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Отчет принял:</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Заказчик</span><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p></o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Генеральный директор</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">'.$brand['footer_general'].'</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">____________________ /'.$brand['footer_general'].'/</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                </td>
            </tr>
        </table>
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
    </div>
    <!--EndFragment-->
</body>

</html>';

  }


 exit;

}

# Партнеры:
if ($_GET['query'] == 'get-all-agent') {
  
  
  if (User::hasRole('admin') || User::hasRole('acct')) {

  if (User::hasRole('acct')) {
  $no_for_money = ' and `status_id` != 6 ';
  } 

$table .= '<table id="table_content" class="display" cellspacing="0" width="100%" style="border:0.5px solid #000;">
        <thead>
            <tr>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Название сервисного центра</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Номер ремонта</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Номер СЦ в базе</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Бренд</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Категория</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Модель</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Серийный номер</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Количество</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Заявленная неисправность</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Комментарии к ремонту</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Наименование детали (элемента)</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Причина отказа детали</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Вид ремонта</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Количество</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Деталь '.(($_GET['type'] == 'TESLER' || $_GET['type'] == 'tesler') ? 'TESLER' : 'HARPER').'</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Цена</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Сумма</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Стоимость выезда</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Стоимость работ</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Общая сумма</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Агентское вознаграждение</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Ф.И.О. Клиента</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Телефон Клиента</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Дата покупки</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Дата начала ремонта</th>
                <th style="width:40px;border:0.5px solid #000;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt" align="left">Дата окончания ремонта</th>
            </tr>
        </thead>
        <tbody>';

      $combine = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `combine` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\';'));

    if ($_GET['type'] == 'tesler') {
      $brands = array('TESLER');
    } else if ($_GET['type'] == 'harper') {
      $brands = array('OLTO', 'HARPER', 'SKYLINE', 'NESONS');
	} else if ($_GET['type'] == 'roch') {
      $brands = array('ROCH');
    } else if ($_GET['type'] != 'harper' && $_GET['type'] != 'tesler') {
      $brands = array($_GET['type']);
    }

      $sql1 = mysqli_query($db, 'SELECT * FROM `combine_links` where `combine_id` = \''.$_GET['id'].'\' and `type` = 0 ;');
      $tesler = ($_GET['type'] == 'tesler') ? 1 : 0;
      $ids = array();
      $counter = 1;
      $total1 = 0;
      $lines = file('ids.txt');
    while ($row1 = mysqli_fetch_array($sql1)) {
      $pay = get_payment_info($row1['pay_billing_id']);
      $pay['month'] = ($pay['month'] < 10) ? '0' . $pay['month'] : $pay['month'];
      if ($pay['type'] > 2 && $_GET['type'] == 'tesler') {
        $list[$pay['service_id']]['dates'][$pay['year'] . '.' . $pay['month']] = 1;
      } else   if ($pay['type'] < 3 && $_GET['type'] == 'harper') {
        $list[$pay['service_id']]['dates'][$pay['year'] . '.' . $pay['month']] = 1;
      } else if ($_GET['type'] != 'tesler' && $_GET['type'] != 'harper' && $pay['type'] > 12) {
        $list[$pay['service_id']]['dates'][$pay['year'] . '.' . $pay['month']] = 1;
      }
    }

foreach ($list as $service => $dates) {

//file_put_contents('a_id.txt', $service.PHP_EOL, FILE_APPEND);
foreach ($dates['dates'] as $date => $no) {

$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `deleted` = 0 and `service_id` = '.$service.' and `app_date` REGEXP \''.$date.'.\' and (`status_admin` = \'Подтвержден\' or `status_admin` = \'Выдан\') '.$no_for_money.'  ;');

while ($row = mysqli_fetch_array($sql)) {
        
        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['parts_info_info'] = parts_price_billing_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);

          if ($content['begin_date'] != '0000-00-00') {
              $date1 = new DateTime($content['begin_date']);
              $date1_ready = $date1->format('d.m.Y');
          }

          if ($content['finish_date'] != '0000-00-00') {
            if (preg_match('/2020/', $content['finish_date']) || preg_match('/2018/', $content['finish_date']) || preg_match('/2017/', $content['finish_date']) || preg_match('/2019/', $content['finish_date'])) {
              $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
              $date2_ready = $date2->format('d.m.Y');
            } else if (preg_match('/2020/', $content['finish_date']) ||  preg_match('/2018/', $content['finish_date']) || preg_match('/2017/', $content['finish_date']) || preg_match('/2019/', $content['finish_date'])) {
              $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
              $date2_ready = $date2->format('d.m.Y');
            } else {
              $date2 = DateTime::createFromFormat('Y-m-d', $content['finish_date']);
              $date2_ready = $date2->format('d.m.Y');
            }
          }

          if ($content['begin_date'] != '0000-00-00' && $content['finish_date'] != '0000-00-00') {
            $date1 = new DateTime($content['begin_date']);
            $date22 = new DateTime($date2_ready);
            if ($date22) {
              $diff_tmp = $date22->diff($date1);
              if ($diff_tmp) {
                $diff = $diff_tmp->format("%a");
              }
            }
          }

       if (in_array(strtolower($content['model']['brand']), array_map("strtolower", $brands))) {

        if($_GET['type'] == 'selenga'){
          $coef = 0.30;
        }else{
          $coef = 0.35;
        }
      //if (in_array($content['model']['brand'],$brands)) {
      $table .= '<tr>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['service_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['id'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['service_info']['id'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['model']['brand'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['cat_info']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['model']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['serial'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">1</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['bugs'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['comment'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['0']['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.get_content_by_id('details_problem', $content['parts_info']['0']['problem_id'])['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.get_content_by_id('repair_type', $content['parts_info']['0']['repair_type_id'])['name'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info']['qty'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.(($content['parts_info']['ordered_flag'] == 1) ? 'Да' : 'Нет').'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['parts_info_info']['price'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$row['parts_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$row['transport_cost'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['total_price'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.($content['total_price'] + $row['transport_cost'] + $row['parts_cost']).'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.round(($content['total_price'] + $row['transport_cost'] + $row['parts_cost'])*$coef).'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['client'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$content['phone'].'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.Time::format($content['sell_date']).'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$date1_ready .'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$date2_ready.'</td>
      </tr>';

      /*if (!in_array($row['id'], $lines)) {
      file_put_contents('errors.txt', $row['id'].PHP_EOL, FILE_APPEND);
      }    */

      $total1 += ($content['total_price'] + $row['transport_cost'] + $row['parts_cost']);

      if ($content['model']['brand'] == 'TESLER') {

      $total2 += ($content['total_price'] + $row['transport_cost'] + $row['parts_cost'])*0.4;
      } else if ($content['model']['brand'] != 'TESLER' && $content['model']['brand'] != 'HARPER' && $content['model']['brand'] != 'OLTO' && $content['model']['brand'] != 'NESONS' && $content['model']['brand'] != 'HORIZONT') {
        $brand_info = brand_info_get($_GET['type']);
      $total2 += ($content['total_price'] + $row['transport_cost'] + $row['parts_cost'])*$brand_info['percent'];
      } else {
     $total2 += ($content['total_price'] + $row['transport_cost'] + $row['parts_cost'])*0.35;

      }
      $counter++;
      }

      }
 
}
}

      $table .= '<tr>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">Итого:</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$counter.'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$total1.'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt">'.$total2.'</td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      <td style="width:40px;border:0.5px solid #000;text-align:left;font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:6.5000pt"></td>
      </tr>';

        $table .= '</tbody>
</table>';

header("Content-Type: application/vnd.ms-word");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("content-disposition: attachment;filename=Report.doc");

if (in_array('TESLER', $brands)) {
$brand = brand_by_id(4);
$t = '-T';
} else if (in_array('HARPER', $brands)) {
$brand = brand_by_id(1);
} else if (in_array('ROCH', $brands)) {
$brand = brand_by_id(56);
} else  {
$brand = $_GET['type'];
$t = $_GET['type'];
}

        $date = explode('.', $combine['date']);

        $year = $date['0'];
        $month = $date['1'];

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">

<head>
    <meta http-equiv=Content-Type content="text/html; charset=utf-8">
    <meta name=ProgId content=Word.Document>
    <meta name=Generator content="Microsoft Word 14">
    <meta name=Originator content="Microsoft Word 14">
    <title></title>
    <!--[if gte mso 9]><xml><w:WordDocument><w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel><w:DisplayHorizontalDrawingGridEvery>0</w:DisplayHorizontalDrawingGridEvery><w:DisplayVerticalDrawingGridEvery>2</w:DisplayVerticalDrawingGridEvery><w:DocumentKind>DocumentNotSpecified</w:DocumentKind><w:DrawingGridVerticalSpacing>7.8 磅</w:DrawingGridVerticalSpacing><w:PunctuationKerning></w:PunctuationKerning><w:View>Web</w:View><w:Compatibility><w:DontGrowAutofit/></w:Compatibility><w:Zoom>0</w:Zoom></w:WordDocument></xml><![endif]-->
    <!--[if gte mso 9]><xml><w:LatentStyles DefLockedState="false" DefUnhideWhenUsed="true" DefSemiHidden="true" DefQFormat="false" DefPriority="99" LatentStyleCount="260" > <w:LsdException Locked="false" Priority="0" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Normal" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="heading 1" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 2" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 3" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 4" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 5" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 6" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 7" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 8" ></w:LsdException> <w:LsdException Locked="false" Priority="9" SemiHidden="false" QFormat="true" Name="heading 9" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 6" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 7" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 8" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index 9" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 1" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 2" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 3" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 4" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 5" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 6" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 7" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 8" ></w:LsdException> <w:LsdException Locked="false" Priority="39" SemiHidden="false" Name="toc 9" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Normal Indent" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="footnote text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="annotation text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="header" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="footer" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="index heading" ></w:LsdException> <w:LsdException Locked="false" Priority="35" SemiHidden="false" QFormat="true" Name="caption" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="table of figures" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="envelope address" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="envelope return" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="footnote reference" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="annotation reference" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="line number" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="page number" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="endnote reference" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="endnote text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="table of authorities" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="macro" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="toa heading" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Bullet 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Number 5" ></w:LsdException> <w:LsdException Locked="false" Priority="10" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Title" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Closing" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Signature" ></w:LsdException> <w:LsdException Locked="false" Priority="1" SemiHidden="false" Name="Default Paragraph Font" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text Indent" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="List Continue 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Message Header" ></w:LsdException> <w:LsdException Locked="false" Priority="11" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Subtitle" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Salutation" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Date" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text First Indent" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text First Indent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Note Heading" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text Indent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Body Text Indent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Block Text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Hyperlink" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="FollowedHyperlink" ></w:LsdException> <w:LsdException Locked="false" Priority="22" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Strong" ></w:LsdException> <w:LsdException Locked="false" Priority="20" SemiHidden="false" UnhideWhenUsed="false" QFormat="true" Name="Emphasis" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Document Map" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Plain Text" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="E-mail Signature" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Normal (Web)" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Acronym" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Address" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Cite" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Code" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Definition" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Keyboard" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Preformatted" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Sample" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Typewriter" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="HTML Variable" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Normal Table" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="annotation subject" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="No List" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Simple 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Simple 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Simple 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Classic 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Colorful 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Colorful 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Colorful 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Columns 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 6" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 7" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Grid 8" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 4" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 5" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 6" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 7" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table List 8" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table 3D effects 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table 3D effects 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table 3D effects 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Contemporary" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Elegant" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Professional" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Subtle 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Subtle 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Web 1" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Web 2" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Web 3" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Balloon Text" ></w:LsdException> <w:LsdException Locked="false" Priority="0" SemiHidden="false" UnhideWhenUsed="false" Name="Table Grid" ></w:LsdException> <w:LsdException Locked="false" Priority="99" SemiHidden="false" Name="Table Theme" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 1" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 2" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 3" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 4" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 5" ></w:LsdException> <w:LsdException Locked="false" Priority="60" SemiHidden="false" UnhideWhenUsed="false" Name="Light Shading Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="61" SemiHidden="false" UnhideWhenUsed="false" Name="Light List Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="62" SemiHidden="false" UnhideWhenUsed="false" Name="Light Grid Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="63" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 1 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="64" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Shading 2 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="65" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 1 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="66" SemiHidden="false" UnhideWhenUsed="false" Name="Medium List 2 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="67" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 1 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="68" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 2 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="69" SemiHidden="false" UnhideWhenUsed="false" Name="Medium Grid 3 Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="70" SemiHidden="false" UnhideWhenUsed="false" Name="Dark List Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="71" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Shading Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="72" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful List Accent 6" ></w:LsdException> <w:LsdException Locked="false" Priority="73" SemiHidden="false" UnhideWhenUsed="false" Name="Colorful Grid Accent 6" ></w:LsdException> </w:LatentStyles></xml><![endif]-->
    <style>
        @font-face{ font-family:"Times New Roman"; } @font-face{ font-family:"宋体"; } @font-face{ font-family:"Calibri"; } @font-face{ font-family:"Calibri"; } @font-face{ font-family:"Wingdings"; } table { font-family:\'Times New Roman\' !important; font-size: 10.0000pt !important; } @list l0:level1{ mso-level-number-format:decimal; mso-level-suffix:tab; mso-level-text:"%1."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:36.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level2{ mso-level-number-format:alpha-lower; mso-level-suffix:tab; mso-level-text:"%2."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:72.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level3{ mso-level-number-format:lower-roman; mso-level-suffix:tab; mso-level-text:"%3."; mso-level-tab-stop:none; mso-level-number-position:right; margin-left:108.0000pt;text-indent:-9.0000pt;font-family:\'Times New Roman\';} @list l0:level4{ mso-level-number-format:decimal; mso-level-suffix:tab; mso-level-text:"%4."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:144.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level5{ mso-level-number-format:alpha-lower; mso-level-suffix:tab; mso-level-text:"%5."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:180.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level6{ mso-level-number-format:lower-roman; mso-level-suffix:tab; mso-level-text:"%6."; mso-level-tab-stop:none; mso-level-number-position:right; margin-left:216.0000pt;text-indent:-9.0000pt;font-family:\'Times New Roman\';} @list l0:level7{ mso-level-number-format:decimal; mso-level-suffix:tab; mso-level-text:"%7."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:252.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level8{ mso-level-number-format:alpha-lower; mso-level-suffix:tab; mso-level-text:"%8."; mso-level-tab-stop:none; mso-level-number-position:left; margin-left:288.0000pt;text-indent:-18.0000pt;font-family:\'Times New Roman\';} @list l0:level9{ mso-level-number-format:lower-roman; mso-level-suffix:tab; mso-level-text:"%9."; mso-level-tab-stop:none; mso-level-number-position:right; margin-left:324.0000pt;text-indent:-9.0000pt;font-family:\'Times New Roman\';} p.MsoNormal{ mso-style-name:Normal; mso-style-parent:""; margin:0pt; margin-bottom:.0001pt; font-family:Calibri; mso-bidi-font-family:\'Times New Roman\'; font-size:12.0000pt; } span.10{ font-family:Calibri; } p.15{ mso-style-name:"List Paragraph"; margin-left:36.0000pt; mso-add-space:auto; font-family:Calibri; mso-bidi-font-family:\'Times New Roman\'; font-size:12.0000pt; } span.msoIns{ mso-style-type:export-only; mso-style-name:""; text-decoration:underline; text-underline:single; color:blue; } span.msoDel{ mso-style-type:export-only; mso-style-name:""; text-decoration:line-through; color:red; } table.MsoNormalTable{ mso-style-name:"Table Normal"; mso-style-parent:""; mso-style-noshow:yes; mso-tstyle-rowband-size:0; mso-tstyle-colband-size:0; mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt; mso-para-margin:0pt; mso-para-margin-bottom:.0001pt; mso-pagination:widow-orphan; font-family:\'Times New Roman\'; font-size:10.0000pt; mso-ansi-language:#0400; mso-fareast-language:#0400; mso-bidi-language:#0400; } table.MsoTableGrid{ mso-style-name:"Table Grid"; mso-tstyle-rowband-size:0; mso-tstyle-colband-size:0; mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt; mso-border-top-alt:0.5000pt solid windowtext; mso-border-left-alt:0.5000pt solid windowtext; mso-border-bottom-alt:0.5000pt solid windowtext; mso-border-right-alt:0.5000pt solid windowtext; mso-border-insideh:0.5000pt solid windowtext; mso-border-insidev:0.5000pt solid windowtext; mso-para-margin:0pt; mso-para-margin-bottom:.0001pt; mso-pagination:widow-orphan; font-family:\'Times New Roman\'; font-size:10.0000pt; mso-ansi-language:#0400; mso-fareast-language:#0400; mso-bidi-language:#0400; } @page{mso-page-border-surround-header:no; mso-page-border-surround-footer:no;}@page Section0{ margin-top:85.0500pt; margin-bottom:42.5000pt; margin-left:56.7000pt; margin-right:56.7000pt; size:842.0000pt 595.0000pt; layout-grid:18.0000pt; mso-page-orientation:landscape; } div.Section0{page:Section0;}
    </style>
</head>

<body style="tab-interval:35pt;">
    <!--StartFragment-->
    <div class="Section0" style="layout-grid:18.0000pt;">
        <table class=MsoTableGrid style="border-collapse:collapse;width:495.2500pt;mso-table-layout-alt:fixed;mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
            <tr style="height:16.3000pt;">
                <td width=330 valign=top style="width:247.6000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';font-size:16.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=330 valign=top style="width:247.6500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">'.$brand['agent_title'].'</span> <span style="font-family:\'Times New Roman\';font-size:16.0000pt;"> <o:p></o:p> </span>                        </p>
                </td>
            </tr>
        </table>
        <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" ><o:p> </o:p></span></b></p>
        <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" >ОТЧЕТ Агента  №'.$_GET['id'].$t.'</span></b><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" ><o:p></o:p></span></b></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-weight:bold;font-size:12.0000pt;" ><o:p> </o:p></span></b></p>
        <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">г. Москва <p class=MsoNormal align=right style="text-align:right;">«'.date("t", strtotime('05-'.$month.'-'.$year)).'» '.$_monthsList2[$month].' '.$year.' г.</p></span>            <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span> </p>
        <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">'.$brand['sc'].', именуемое в дальнейшем «Принципал», в лице генерального директора '.$brand['general'].', действующего на основании Устава, принял, </span>            <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span> </p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Индивидуальный предприниматель Кулиджанов Андрей Александрович, именуемый в дальнейшем «Агент», в лице Кулиджанова А.А., действующего на основании свидетельства о государственной регистрации серия 77 № 015591328 от 01 августа 2013 г. представил  настоящий Отчет об исполнении Агентского соглашения '.$brand['agent_date'].' (далее – Агентское соглашение) о нижеследующем:</span>            <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span> </p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=15 align=justify style="margin-left:36.0000pt;text-indent:-18.0000pt;text-align:justify;text-justify:inter-ideograph;mso-list:l0 level1 lfo1;">
            <![if !supportLists]><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><span style=\'mso-list:Ignore;\' >1.<span> </span></span>
            </span>
            <![endif]><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">За период с 1 '.$_monthsList2[$month].' '.$year.' г. по '.date("t", strtotime('05-'.$month.'-'.$year)).' '.$_monthsList2[$month].' '.$year.' г. Агент во исполнение Агентского соглашения совершил следующие действия:</span>            <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span> </p>
        '.$table.'
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <p class=15 align=justify style="margin-left:36.0000pt;text-indent:-18.0000pt;text-align:justify;text-justify:inter-ideograph;mso-list:l0 level1 lfo1;">
            <![if !supportLists]><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><span style=\'mso-list:Ignore;\' >2.<span> </span></span>
            </span>
            <![endif]><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Сумма расходов Агента, подлежащих возмещению Принципалом, составляет '.num2str2($total1).' ('.$total1.').</span>
            <span
                style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">
                <o:p></o:p>
                </span>
        </p>
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Указанная сумма подтверждена документами, приложенными к Отчету.</span> <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>            </p>
        <p class=15 align=justify style="margin-left:36.0000pt;text-indent:-18.0000pt;text-align:justify;text-justify:inter-ideograph;mso-list:l0 level1 lfo1;">
            <![if !supportLists]><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><span style=\'mso-list:Ignore;\' >3.<span> </span></span>
            </span>
            <![endif]><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Сумма вознаграждения Агента, подлежащая уплате Принципалом, составляет  '.num2str2($total2).' ('.$total2.').</span>            <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span> </p>
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
        <table class=MsoNormalTable style="border-collapse:collapse;width:474.7000pt;mso-table-layout-alt:fixed;mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
            <tr style="height:38.9500pt;">
                <td width=316 valign=top style="width:237.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:31.8750pt none rgb(255,255,255);mso-border-right-alt:31.8750pt none rgb(255,255,255);border-top:31.8750pt none rgb(255,255,255);mso-border-top-alt:31.8750pt none rgb(255,255,255);border-bottom:31.8750pt none rgb(255,255,255);mso-border-bottom-alt:31.8750pt none rgb(255,255,255);">
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Отчет сдал:</span><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p></o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Агент</span><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p></o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">ИП Кулиджанов Андрей Александрович</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">____________________ / А.А. Кулиджанов/</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                </td>
                <td width=316 valign=top style="width:237.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:none;;mso-border-left-alt:none;;border-right:31.8750pt none rgb(255,255,255);mso-border-right-alt:31.8750pt none rgb(255,255,255);border-top:31.8750pt none rgb(255,255,255);mso-border-top-alt:31.8750pt none rgb(255,255,255);border-bottom:31.8750pt none rgb(255,255,255);mso-border-bottom-alt:31.8750pt none rgb(255,255,255);">
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Отчет принял:</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Принципал</span><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p></o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">Генеральный директор</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">'.$brand['footer_general'].'</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                    <p class=MsoNormal><span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
                    <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;">____________________ /'.$brand['footer_general'].'/</span> <span style="font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"> <o:p></o:p> </span>                        </p>
                </td>
            </tr>
        </table>
        <p class=15 align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';color:rgb(0,0,0);letter-spacing:-0.2500pt;font-size:10.0000pt;"><o:p> </o:p></span></p>
    </div>
    <!--EndFragment-->
</body>

</html>';

  }
 exit;
}



# Техническая документация:
if ($_GET['query'] == 'infobase') {
  
  
  require $_SERVER['DOCUMENT_ROOT'] . '/templates/infobase.php';
  exit;
}


# Партнеры:
if ($_GET['query'] == 'update-notify') {
  header('Content-Type: application/json');
  
  
  if (!empty($_POST['update-id']) && !empty($_POST['mark-read'])) {
    $arrays = update_notify(User::getData('id'), $_POST['update-id'], true);
    }
  elseif (!empty($_POST['update-id'])) {
  $arrays = update_notify(User::getData('id'), $_POST['update-id']);
  }
  if ($_POST['update-app-id']) {
  $arrays = update_notify_app(User::getData('id'), $_POST['update-app-id']);
  }
  exit;

}

# Дашборд:
if ($_GET['query'] == 'get-repairs') {
  
  

header("Content-type: text/html; charset=utf-8");
header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-transfer-encoding: binary');
header('Content-Disposition: attachment; filename=list.xls');
header('Content-Type: application/x-unknown');



echo '<table id="table_content" class="display" cellspacing="0" width="100%" style="border:1px solid #000;">
        <thead>
            <tr>
                <th style="border:1px solid #000;" align="left">Статус ремонта</th>
                <th style="border:1px solid #000;" align="left">Название сервисного центра</th>
                <th style="border:1px solid #000;" align="left">Номер ремонта</th>
                <th style="border:1px solid #000;" align="left">Номер СЦ в базе</th>
                <th style="border:1px solid #000;" align="left">Бренд</th>
                <th style="border:1px solid #000;" align="left">Категория</th>
                <th style="border:1px solid #000;" align="left">Модель</th>
                <th style="border:1px solid #000;" align="left">Серийный номер</th>
                <th style="border:1px solid #000;" align="left">Количество</th>
                <th style="border:1px solid #000;" align="left">Заявленная неисправность</th>
                <th style="border:1px solid #000;" align="left">Комментарии к ремонту</th>
                <th style="border:1px solid #000;" style="border:1px solid #000;" align="left">Наименование детали (элемента)</th>
                <th style="border:1px solid #000;" align="left">Позиционное обозначение (на плате/схеме)</th>
                <th style="border:1px solid #000;" align="left">Причина отказа детали</th>
                <th style="border:1px solid #000;" align="left">Вид ремонта</th>
                <th style="border:1px solid #000;" align="left">Количество</th>
                <th style="border:1px solid #000;" align="left">Деталь производителя</th>
                <th style="border:1px solid #000;" align="left">Цена</th>
                <th style="border:1px solid #000;" align="left">Сумма</th>
                <th style="border:1px solid #000;" align="left">Стоимость выезда</th>
                <th style="border:1px solid #000;" align="left">Стоимость работ</th>
                <th style="border:1px solid #000;" align="left">Общая сумма</th>
                <th style="border:1px solid #000;" align="left">Ф.И.О. Клиента</th>
                <th style="border:1px solid #000;" align="left">Телефон Клиента</th>
                <th style="border:1px solid #000;" align="left">Дата покупки</th>
                <th style="border:1px solid #000;" align="left">Дата начала ремонта</th>
                <th style="border:1px solid #000;" align="left">Дата окончания ремонта</th>
                <th style="border:1px solid #000;" align="left">Кол-во дней ремонта</th>
            </tr>

        </thead>
        <tbody>';


$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `status_admin` != \'\';');
      while ($row = mysqli_fetch_array($sql)) {
        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);

      $date1 = new DateTime($content['begin_date']);
      $date2 = new DateTime($content['finish_date']);

      $diff = $date2->diff($date1)->format("%a");

      echo '<tr>
      <td style="border:1px solid #000;text-align:left">'.$content['status_admin'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['service_info']['name'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['id'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['service_info']['id'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['model']['brand'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['cat_info']['name'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['model']['name'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['serial'].'</td>
      <td style="border:1px solid #000;text-align:left">1</td>
      <td style="border:1px solid #000;text-align:left">'.$content['bugs'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['comment'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['parts_info']['name'].'</td>
      <td style="border:1px solid #000;text-align:left">'.get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'].'</td>
      <td style="border:1px solid #000;text-align:left">'.get_content_by_id('repair_type', $content['parts_info']['repair_type_id'])['name'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['parts_info']['qty'].'</td>
      <td style="border:1px solid #000;text-align:left">'.(($content['parts_info']['ordered_flag'] == 1) ? 'Да' : 'Нет').'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['parts_info']['price'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['parts_info']['sum'].'</td>
      <td style="border:1px solid #000;text-align:left">'.(($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type'], $row['service_id']) : '0').'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['total_price'].'</td>
      <td style="border:1px solid #000;text-align:left">'.($content['total_price'] + (($row['status_admin'] != 'Запрос на выезд' && $row['onway'] == 1) ? get_price_transfer($model['cat'], $row['onway_type']) : '0') + $content['parts_info']['sum']).'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['client'].'</td>
      <td style="border:1px solid #000;text-align:left">'.$content['phone'].'</td>
      <td style="border:1px solid #000;text-align:left">'.date("d.m.Y", strtotime($content['sell_date'])).'</td>
      <td style="border:1px solid #000;text-align:left">'.date("d.m.Y", strtotime($content['begin_date'])).'</td>
      <td style="border:1px solid #000;text-align:left">'.date("d.m.Y", strtotime($content['finish_date'])).'</td>
      <td style="border:1px solid #000;text-align:left">'.$diff.'</td>
      </tr>';


      }
        echo '</tbody>
</table>';

exit;

}

if ($_GET['query'] == 'download-returns-report') {

$glob_id = 2;

 require_once 'adm/excel/vendor/autoload.php';
       if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

    $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/blank.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();


$sheet->setCellValue("A1", 'Партия');
$sheet->setCellValue("B1", 'Дата выдачи');
$sheet->setCellValue("C1", 'Стоимость партии возврата');
$sheet->setCellValue("D1", 'Сумма списанной техники');
$sheet->setCellValue("E1", 'Сумма возвращенной техники клиенту');
$sheet->setCellValue("F1", 'Сумма оплаченная мастерам за работу');
$sheet->setCellValue("G1", 'Экономический смысл для сервиса');
$sheet->setCellValue("H1", 'Экономический смысл для клиента');


$sql_returns = mysqli_query($db, 'SELECT * FROM `returns` where DATE(date_out) BETWEEN \''.str_replace('-', '.', $_GET['date1']).'\' AND \''.str_replace('-', '.', $_GET['date2']).'\';');
//echo 'SELECT * FROM `returns` where DATE(date_out) BETWEEN \''.str_replace('-', '.', $_GET['date1']).'\' AND \''.str_replace('-', '.', $_GET['date2']).'\';';

if (mysqli_num_rows($sql_returns) != false) {
 while ($row_returns = mysqli_fetch_array($sql_returns)) {

$sql_check_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row_returns['id']).'\' and `deleted` = 0;'));
$sql_check_count2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row_returns['id']).'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;'));

if ($sql_check_count['COUNT(id)'] == $sql_check_count2['COUNT(id)']) {

if ($_GET['cats'] != '') {
$cat_sql = ' and `cat_id` in ('.$_GET['cats'].') ';
}

$sql = mysqli_query($db, 'SELECT `id`,`repair_final`,`repair_type_id`,`cat_id`,`model_id`,`total_price`,`master_user_id` FROM `repairs` where `return_id` = \''.$row_returns['id'].'\' '.$cat_sql.';');
      while ($row = mysqli_fetch_array($sql)) {

              $model = model_info($row['model_id']);
              if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'SKYLINE' || $model['brand'] == 'NESONS') {
              // Стоимость партии возврата:
              switch($row['repair_final']) {

              case 1:
                $model_price_ato = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              case 2:
                $model_price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              case 3:
                $model_price_ato = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              }

              $return_sum += $row['total_price'];
              unset($price);

              // Сумма техники списанной:
              $return_usd += $model_price;
              unset($model_price);

              if ($row_returns['id'] == 81) {
              //echo $row['id'].' - '.$model_price_ato.'<br>';
              }

              // Сумма техники списанной ато:
              $return_ato_usd += $model_price_ato;
              unset($model_price_ato);

              // Сумма мастерам за работу:
              //$return_master_sum += $row['total_price'];
              $return_master_sum += count_pay_master_funk($row['total_price'], $row['master_user_id']);

             }
      }

$usd = @mysqli_fetch_array(mysqli_query($db, 'SELECT `usd` FROM `returns` where `id` = \''.$row_returns['id'].'\'  ;'))['usd'];
$ffs = $return_ato_usd*$usd-$return_sum;

if ($row_returns['id'] == 81) {
//echo $return_ato_usd;
}

if ($return_sum) {
$sheet->setCellValue("A$glob_id", $row_returns['name']);
$sheet->setCellValue("B$glob_id", $row_returns['date_out']);
$sheet->setCellValue("C$glob_id", $return_sum);
$sheet->setCellValue("D$glob_id", $return_usd);
$sheet->setCellValue("E$glob_id", str_replace(',', '.', $return_ato_usd));
$sheet->setCellValue("F$glob_id", $return_master_sum);
$sheet->setCellValue("G$glob_id", str_replace(',', '.', $return_sum - $return_master_sum));
$sheet->setCellValue("H$glob_id", str_replace(',', '.', $ffs));
 $glob_id++;
}

unset($usd);
unset($ffs);
unset($return_sum);
unset($return_usd);
unset($return_ato_usd);
unset($return_master_sum);



}
}
}

$xls->getDefaultStyle()->getAlignment()->setWrapText(true);
foreach ($xls->getWorksheetIterator() as $worksheet) {

    $xls->setActiveSheetIndex($xls->getIndex($worksheet));

    $sheet = $xls->getActiveSheet();
    $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(true);
    /** @var PHPExcel_Cell $cell */
    foreach ($cellIterator as $cell) {
        $sheet->getColumnDimension($cell->getColumn())->setWidth(15);
            }
}
        $xls->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $xls->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $xls->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $xls->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $xls->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $xls->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $xls->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="report_'.date('d.m.Y').'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);

exit;

}

if ($_GET['query'] == 'create_super_nak') {
$json = json_decode($_GET['value'], true);
$glob_id = 1;
$glob_row = 0;
$glob_row_break = 0;
$glob_row_break_full = 0;
require_once 'adm/excel/vendor/autoload.php';

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/blank.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $fail_type = array(1 => 'Деталь или ПО не постваляется', 2 => 'Отказано в гарантии', 3 => 'Клиент от ремонта отказался', 4 => 'Нарушены сроки ремонта', 5 => 'Не ремонтопригоден', 6 => 'Нет технической информации (схем)');

        $styleArray = array(
        'font'  => array(
            'size'  => 10
        ));
        $xls->getDefaultStyle() ->applyFromArray($styleArray);

        $xls->getActiveSheet()->getColumnDimension('A')->setWidth(0);
        $xls->getActiveSheet()->getColumnDimension('B')->setWidth(31);
        $xls->getActiveSheet()->getColumnDimension('C')->setWidth(32);
        $xls->getActiveSheet()->getColumnDimension('E')->setWidth(32);
        $xls->getActiveSheet()->getColumnDimension('F')->setWidth(31);
        $xls->getActiveSheet()->getColumnDimension('D')->setWidth(2);

foreach ($json as $id) {

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);

        $a1 = 1+$glob_row;
        $b1 = 3+$glob_row;
        $c1 = 6+$glob_row;
        $d1 = 8+$glob_row;
        $act_row = 2+$glob_row;
        $model_row = 4+$glob_row;
        $owner_row = 5+$glob_row;
        $phone_row = 6+$glob_row;
        $problem_row = 7+$glob_row;
        $date_row = 8+$glob_row;
        $break_row = 27+($glob_row*3);
        $serial_row = 4+$glob_row;
        $complex_row = 5+$glob_row;
        $status_row = 6+$glob_row;

        /*left*/
        if ($glob_id == 1) {

        $xls->getActiveSheet()->getStyle("B$b1")->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle("B$b1")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("B$problem_row")->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle("B$a1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $xls->getActiveSheet()->getStyle("C$serial_row")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getRowDimension($problem_row)->setRowHeight(33);
        $xls->getActiveSheet()->getRowDimension($owner_row)->setRowHeight(35);
        $xls->getActiveSheet()->getStyle("B$problem_row")->getAlignment()->applyFromArray(array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
        $xls->getActiveSheet()->getStyle("B$owner_row")->getAlignment()->applyFromArray(array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
        $xls->getActiveSheet()->getStyle("C$complex_row")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("C$problem_row")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("B$owner_row")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("C$owner_row")->getFont()->setSize(10);
         $xls->getActiveSheet()->getStyle("B$problem_row")->getAlignment()->setWrapText(true);
         $xls->getActiveSheet()->getStyle("C$owner_row")->getAlignment()->applyFromArray(array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
        $sheet->getStyle("B".(1+$glob_row).":C".(8+$glob_row)."")->applyFromArray(
            array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000')
                    )
                )
            )
        );
        $xls->getActiveSheet()->mergeCells("B$a1:C$a1");
        //$xls->getActiveSheet()->mergeCells("B$b1:C$b1");
        $xls->getActiveSheet()->mergeCells("B$act_row:C$act_row");
        $xls->getActiveSheet()->mergeCells("B$problem_row:C$problem_row");
        $xls->getActiveSheet()->mergeCells("B$d1:C$d1");

        $sheet->setCellValue("B$a1", 'Наклейка №'.$content['id']);

        if ($content['anrp_use'] == 1) {
        $sheet->setCellValue("B$act_row", 'АНРП-№'.$content['anrp_number']);
        $sheet->getStyle("B$act_row")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '888888')
                )
            )
        );
        $styleArray = array(
        'font'  => array(
            'name'  => 'Arial'
        ));
        $xls->getActiveSheet()->getStyle("B$act_row")->applyFromArray($styleArray);
        }

        if ($content['client_type'] == 1) {
        $sheet->setCellValue("B$b1", $content['client']);
        } else {
        $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
        $sheet->setCellValue("B$b1", $client_info['name']);

        }

        $sheet->setCellValue("C$b1", 'Вн.№: '.$content['rsc']);
        $sheet->setCellValue("B$model_row", 'Модель: '.$content['model']['name']);
        $sheet->setCellValue("B$owner_row", 'Владелец: '.$content['client']);
        $sheet->setCellValue("B$phone_row", 'Телефон: '.$content['phone']);
        $sheet->setCellValue("B$problem_row", 'Неисправность: '.$content['bugs']);
        $sheet->setCellValue("B$date_row", 'Дата приема: '.Time::format($content['receive_date']));
        $sheet->setCellValue("C$serial_row", 'Сер.№: '.$content['serial']);
        $sheet->setCellValue("C$complex_row", 'Компл-ция: '.implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue("C$status_row", 'Статус ремонта: '.$status_array[$content['status_id']]);

        } else

        /*right*/
        if ($glob_id == 2) {

        $xls->getActiveSheet()->getStyle("E$b1")->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle("E$b1")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("E$problem_row")->getFont()->setBold(true);
        $xls->getActiveSheet()->getRowDimension($problem_row)->setRowHeight(33);
        $xls->getActiveSheet()->getRowDimension($owner_row)->setRowHeight(35);
        $xls->getActiveSheet()->getStyle("E$problem_row")->getAlignment()->applyFromArray(array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
        $xls->getActiveSheet()->getStyle("E$a1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $xls->getActiveSheet()->getStyle("F$serial_row")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("F$complex_row")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("E$owner_row")->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle("F$owner_row")->getFont()->setSize(10);
        $xls->getActiveSheet()->getStyle("E$owner_row")->getAlignment()->applyFromArray(array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
        $xls->getActiveSheet()->getStyle("F$owner_row")->getAlignment()->applyFromArray(array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
         $xls->getActiveSheet()->getStyle("E$problem_row")->getAlignment()->setWrapText(true);
        $sheet->getStyle("E".(1+$glob_row).":F".(8+$glob_row)."")->applyFromArray(
            array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000')
                    )
                )
            )
        );
        $xls->getActiveSheet()->mergeCells("E$a1:F$a1");
        //$xls->getActiveSheet()->mergeCells("E$a1:F$a1");
        $xls->getActiveSheet()->mergeCells("E$act_row:F$act_row");
        $xls->getActiveSheet()->mergeCells("E$problem_row:F$problem_row");
        $xls->getActiveSheet()->mergeCells("E$d1:F$d1");

        $sheet->setCellValue("E$a1", 'Наклейка №'.$content['id']);

        if ($content['anrp_use'] == 1) {
        $sheet->setCellValue("E$act_row", 'АНРП-№'.$content['anrp_number']);
        $sheet->getStyle("E$act_row")->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '888888')
                )
            )
        );
        $styleArray = array(
        'font'  => array(
            'name'  => 'Arial'
        ));
        $xls->getActiveSheet()->getStyle("E$act_row")->applyFromArray($styleArray);
        }

        if ($content['client_type'] == 1) {
        $sheet->setCellValue("E$b1", $content['client']);
        } else {
        $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
        $sheet->setCellValue("E$b1", $client_info['name']);

        }
        $sheet->setCellValue("F$b1", 'Вн.№: '.$content['rsc']);
        $sheet->setCellValue("E$model_row", 'Модель: '.$content['model']['name']);
        $sheet->setCellValue("E$owner_row", 'Владелец: '.$content['client']);
        $sheet->setCellValue("E$phone_row", 'Телефон: '.$content['phone']);
        $sheet->setCellValue("E$problem_row", 'Неисправность: '.$content['bugs']);
        $sheet->setCellValue("E$date_row", 'Дата приема: '.Time::format($content['receive_date']));
        $sheet->setCellValue("F$serial_row", 'Сер.№: '.$content['serial']);
        $sheet->setCellValue("F$complex_row", 'Компл-ция: '.implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue("F$status_row", 'Статус рем.: '.$status_array[$content['status_id']]);
        }

        /*$sheet->setCellValue('A2', 'Наклейка №'.$content['id']);
        if ($content['client_type'] == 1) {
        $sheet->setCellValue('A3', $content['client']);
        } else {
        $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
        $sheet->setCellValue('A3', 'Поступил от: '.$client_info['name']);

        }
        $sheet->setCellValue('A4', 'Модель: '.$content['model']['name']);
        $sheet->setCellValue('A5', 'Владелец: '.$content['client']);
        $sheet->setCellValue('A6', 'Телефон: '.$content['phone']);
        $sheet->setCellValue('A7', 'Неисправность со слов владельца: '.$content['bugs']);
        $sheet->setCellValue('A9', 'Дата приема: '.$content['date_get']);
        $sheet->setCellValue('AA4', 'Серийный номер: '.$content['serial']);
        $sheet->setCellValue('AA5', 'Комплектация: '.implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('AA6', 'Статус ремонта: '.$status_array[$content['status_id']]);    */

        /*
        $xls->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);
        $xls->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $xls->getActiveSheet()->getStyle('A29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);  */

       // $sheet->calculateColumnWidths();

        //original code...
        /*$titlecolwidth = $sheet->getColumnDimension('A')->getWidth();
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth($titlecolwidth);   */
        //echo $titlecolwidth;


     $glob_id++;
     $glob_row_break++;
     $glob_row_break_full++;
     if ($glob_id == 3) {$glob_id = 1;$glob_row += 9;  }
     $xls->getActiveSheet()->setBreak( "A$break_row" , PHPExcel_Worksheet::BREAK_ROW );


}

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="Nakleyka_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);


exit;
}

if ($_GET['query'] == 'create_super_kvit') {
$json = json_decode($_GET['value'], true);
$glob_id = 6;
$glob_id_id = 1;

require_once 'adm/excel/vendor/autoload.php';

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/big.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $fail_type = array(1 => 'Деталь или ПО не постваляется', 2 => 'Отказано в гарантии', 3 => 'Клиент от ремонта отказался', 4 => 'Нарушены сроки ремонта', 5 => 'Не ремонтопригоден', 6 => 'Нет технической информации (схем)');
$xls->getActiveSheet()->insertNewRowBefore(7, count($json));
$xls->getActiveSheet()->getStyle('A6:M'.(6+count($json)))->getAlignment()->setWrapText(true);
$xls->getActiveSheet()->getStyle('A6:M'.(6+count($json)))->getAlignment()->setWrapText(true);
$sheet->getStyle('A6:M'.(5+count($json)))->applyFromArray(
    array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    )
);

foreach ($json as $id) {

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $xls->getActiveSheet()->getRowDimension($glob_id)->setRowHeight(-1);
        /*$sheet->setCellValue("B$a1", 'Наклейка №'.$content['id']);
        $sheet->setCellValue("B$act_row", 'АНРП-№'.$content['anrp_number']); */

        if ($content['client_type'] == 1) {
        $sheet->setCellValue("D$glob_id", $content['client']);
        $client_infos = $content['client'];
        } else {
        $client_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
        $sheet->setCellValue("D$glob_id", $client_info['name']);
        $client_infos = $client_info['name'];

        }

        $sheet->setCellValue("A1", $content['service_info']['name']);
        $sheet->setCellValue("A2", $content['service_info']['phone']);
        $sheet->setCellValue("A3", $content['service_info']['req_adress_physic']);

        $sheet->setCellValue("A$glob_id", $glob_id_id);
        $sheet->setCellValue("B$glob_id", '№'.$content['id']);
        $sheet->setCellValue("C$glob_id", $content['rsc']);
        $sheet->setCellValue("E$glob_id", $content['model']['name']);
        $sheet->setCellValue("F$glob_id", $content['serial']);
        $sheet->setCellValue("G$glob_id", $status_array[$content['status_id']]);
        $sheet->setCellValue("H$glob_id", implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue("I$glob_id", implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);
        $sheet->setCellValue("J$glob_id", $content['bugs']);
        $sheet->setCellValue("K$glob_id", implode(', ', array_filter(array($content['name_shop'], $content['city_shop'], $content['address_shop'], $content['phone_shop']))));
        $sheet->setCellValue("L$glob_id", $content['client'].$phone);
        $sheet->setCellValue("M$glob_id", $content['address']);

        $glob_id++;
        $glob_id_id++;

}
        $first = $glob_id+4;
        $first2 = $glob_id+8;
        $second = $glob_id+14;
        $second2 = $glob_id+17;
        $second3 = $glob_id+18;
        $sheet->setCellValue("A$first", 'Дата приема: '.Time::format($content['receive_date']).'______');
        $sheet->setCellValue("A$first2", 'Клиент или его представитель:  ______________________ /'.$client_infos.'/');
        $sheet->setCellValue("A$second", 'Дата выдачи: '.date('d.m.Y').'______');
        $sheet->setCellValue("A$second2", 'Клиент или его представитель:  ______________________ /'.$client_infos.'/');
        $xls->getActiveSheet()->getStyle("A$first2")->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle("A$second2")->getFont()->setBold(true);
        $xls->getActiveSheet()->getPageSetup()->setPrintArea("A1:M$second3");

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="Kvitanciya_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);


exit;
}

# Партнеры:
if ($_GET['query'] == 'excel-services') {
  if (User::hasRole('admin')) {
    models\ServiceManagement::showServicesExcel();
    exit;
  }
}


# Партнеры:
if ($_GET['query'] == 'partneram') {
      # Назначаем шаблон:
      $template = 7;
}

# Поддержка:
if ($_GET['query'] == 'support') {
      $search_techies = ($_POST['search_techies']) ? search_techies($_POST['search_techies']) : '';
      $search_service = ($_POST['search_service']) ? search_service($_POST['search_service']) : '';
      # Назначаем шаблон:
      $template = 8;
}

if ($_GET['query'] == 'del-photo-repair') {
mysqli_query($db, 'DELETE FROM `repairs_photo` WHERE `photo_id` = '.$_GET['photo_id'].' and `repair_id` = '.$_GET['repair_id'].' LIMIT 1');
header('Location: /edit-repair/'.$_GET['repair_id'].'/step/4/');
exit;
}

# Где купить:
if ($_GET['query'] == 'gde-kupit') {
      $shops = shops_list();
      # Назначаем шаблон:
      $template = 9;
}

# Где купить:
if ($_GET['query'] == 'gde-kupit-karta') {
      $shops = shops_list();
      # Назначаем шаблон:
      $template = 17;
}

# Где купить:
if ($_GET['query'] == 'service-doc') {
      # Назначаем шаблон:
      $template = 76;
}

# Где купить:
if ($_GET['query'] == 'show-notify-log') {
  
  
      # Назначаем шаблон:
      $template = 143;
}


# Где купить:
if ($_GET['query'] == 'logs-login') {
  
  
      # Назначаем шаблон:
      $template = 137;
}

# Где купить:
if ($_GET['query'] == 'logs-notify') {
  
  
      # Назначаем шаблон:
      $template = 142;
}

# Где купить:
if ($_GET['query'] == 'add-manual-doc') {
  
  
      # Назначаем шаблон:
      $template = 141;
}

# Где купить:
if ($_GET['query'] == 'brands-tarif') {
  
  
      # Назначаем шаблон:
      $template = 148;
}

# Где купить:
if ($_GET['query'] == 'add-brands-tarif') {
  
  
      # Назначаем шаблон:
      $template = 149;
}

/* Скачать все фото из отчета по браку */
if ($_GET['query'] == 'get-tv-report-photos') {
  require_once 'simple_html_dom.php';
  $html = str_get_html($_POST['table']);
  $path = '/_new-codebase/uploads/temp/reports/Фотоотчет_'.$_POST['provider'];
  $dirRoot = $_SERVER["DOCUMENT_ROOT"] . $path;
  if(!is_dir($dirRoot) && !mkdir($dirRoot, 0777, true)) {
    exit('Dir creation error: ' . $dir);
  }
  $zip = new ZipArchive();
  $zip->open($_SERVER["DOCUMENT_ROOT"] . $path .'.zip', ZipArchive::CREATE);
  foreach($html->find('tr[class=pars_pls]') as $tr) {
    $repairID = $tr->attr['data-repair-id'];
    $model = str_replace([' ', '\'', '"'], ['_', '', ''], trim($tr->find('td', 2)->plaintext));
    $serial = trim($tr->find('td', 3)->plaintext);
    $model = (empty($model)) ? 'нет-названия' : $model;
    $serial = (empty($serial)) ? 'нет-номера' : $serial;
    $dir = $dirRoot . '/' . $model .'/' . $serial;
    if(!is_dir($dir) && !mkdir($dir, 0777, true)) {
      exit('Dir creation error: ' . $dir);
    }
    $photos = \models\Photos::getPhotos($repairID);
    $i = 1;
    foreach($photos as $group){
      foreach($group as $photo) {
        if(empty($photo['url'])){
          continue;
        }
        file_put_contents($dir . '/'.$i.'.jpg', curlData(myUrlEncode($photo['url'])));
        $zip->addFile($dir . '/'.$i.'.jpg', $model .'/' . $serial.'/'.$i.'.jpg');
        $i++;
      }
    }
  }
  $zip->close();
  FS::removeFolder($path); 
  echo json_encode(['file_url' => 'https://'.$_SERVER['HTTP_HOST']. $path .'.zip']);
  exit;
}


# Где купить:
if ($_GET['query'] == 'get-xls-report') {
  //
  //
  require_once('simple_html_dom.php');

$glob_id = 7;
require_once 'adm/excel/vendor/autoload.php';

        if (file_exists('xml_tmp')) {
            foreach (glob('xml_tmp/images/*') as $file) {
                unlink($file);
            }
        }

    $lfcr = chr(10);
        $new_file = 'xml_tmp/report.xlsx';
        copy('adm/excel/Aftersales.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

$sheet->setCellValue("D4", $_POST['date1']);
$sheet->setCellValue("G4", $_POST['date2']);



  $html = str_get_html($_POST['table']);
  $xls->getActiveSheet()->insertNewRowBefore(8, count($html->find('tr[class=pars_pls]'))-1);

  foreach($html->find('tr[class=pars_pls]') as $tr) {

$sheet->setCellValue("B$glob_id", $tr->find('td', 0)->plaintext);
$sheet->setCellValue("C$glob_id", $tr->find('td', 1)->plaintext);
$sheet->setCellValue("D$glob_id", $tr->find('td', 2)->plaintext);
$sheet->setCellValue("E$glob_id", $tr->find('td', 3)->plaintext);
$sheet->setCellValue("F$glob_id", $tr->find('td', 4)->plaintext);
$sheet->setCellValue("G$glob_id", $tr->find('td', 5)->plaintext); // Завод сборщик
$sheet->setCellValue("H$glob_id", $tr->find('td', 6)->plaintext);
$sheet->setCellValue("I$glob_id", $tr->find('td', 7)->plaintext);
$sheet->setCellValue("J$glob_id", $tr->find('td', 8)->plaintext);
$sheet->setCellValue("K$glob_id", $tr->find('td', 9)->plaintext);
$sheet->setCellValue("L$glob_id", $tr->find('td', 10)->plaintext);

    /*echo $tr->find('td.image1', 0)->image_url.'|';
echo $tr->find('td.image2', 0)->image_url.'|';
echo $tr->find('td.image3', 0)->image_url."\n"; */


    $numsToCols = [11 => 'M', 12 => 'N', 13 => 'O', 14 => 'P', 15 => 'Q', 16 => 'R', 17 => 'S', 18 => 'T'];
    for ($i = 11; $i < 19; $i++) {
      if (!$tr->find('td', $i)->image_url) {
        continue;
      }
      $objDrawing = new PHPExcel_Worksheet_Drawing();
      $rand_name = 'xml_tmp/images/' . rand(66999999999999, (int) 999999999999999999999999999999) . '.jpg';
      if (file_put_contents($rand_name, curlData(myUrlEncode('https://crm.r97.ru/resizer.php?src=' . $tr->find('td', $i)->image_url . '&h=600&w=600&zc=4&q=70')))) {
        $objDrawing->setPath('/var/www/service.harper.ru/data/www/service.harper.ru/' . $rand_name);
        $objDrawing->setCoordinates($numsToCols[$i] . $glob_id);
        $objDrawing->setWorksheet($xls->getActiveSheet());
        $objDrawing->setResizeProportional(false);
        $objDrawing->setHeight(200);
        $objDrawing->setWidth(200);
      }
    }

/*$sheet->setCellValue("K$glob_id", );
$sheet->setCellValue("L$glob_id", $tr->find('td', 11)->plaintext);
$sheet->setCellValue("M$glob_id", $tr->find('td', 12)->plaintext); */

   // echo $tr->find('td', 1)->plaintext;

$glob_id++;


  }


if ($bug) {
/*$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setPath('/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
$objDrawing->setCoordinates("J$glob_id");
$objDrawing->setWorksheet($xls->getActiveSheet());
$objDrawing->setResizeProportional(false);
$objDrawing->setHeight(20);
$objDrawing->setWidth(130);*/
//$sheet->setCellValue("J$glob_id", '/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
}






        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

       header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="report_'.date('d.m.Y').'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);

exit;


}


# О компнии:
if ($_GET['query'] == 'about') {
      # Назначаем шаблон:
      $template = 10;
}

if ($query == 'clean-filter') {
      # Проверка авторизации:
      

setcookie('filter_cat_id', null, -1, '/parts');
setcookie('filter_model_id', null, -1, '/parts');
setcookie('filter_name', null, -1, '/parts');
setcookie('filter_code', null, -1, '/parts');

      # Редирект на главную:
      header('Location: /parts/');
      exit;
}


# О компнии:
if ($_GET['query'] == 'support' && $_GET['ticket_id'] != '') {
      $feedback = feedback_info($_GET['ticket_id']);
      $feedback_add = ($_POST['message'] && $feedback['status'] != 'Вопрос закрыт') ? feedback_add($feedback['id']) : '';
      $feedback_close = ($_GET['close'] == 1 && $feedback['status'] != 'Вопрос закрыт') ? feedback_close($feedback['id']) : '';
      $feedback_reopen = ($_GET['reopen'] == 1) ? feedback_reopen($feedback['id']) : '';
      # Назначаем шаблон:
      $template = ($feedback['id']) ? 15 : '';
}

# Получение Hd изображений:
if ($_GET['query'] == 'get-hd') {
download_hd($_GET['page']);
exit;
}



# Выбираем шаблон:
switch ($template) {
    case 4:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/dashboard.php');
        break;
    case 5:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/services.php');
        break;
    case 6:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/service.php');
        break;
    case 7:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/settings.php');
        break;
    case 8:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/support.php');
        break;
    case 9:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/buy.php');
        break;
    case 10:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/about.php');
        break;
    case 11:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/news.php');
        break;
    case 12:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/result.php');
        break;
    case 13:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/pages.php');
        break;
    case 14:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/item.php');
        break;
    case 15:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/support_ticket.php');
        break;
    case 16:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/terms.php');
        break;
    case 17:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/buy_city.php');
        break;
    case 18:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/buy_item.php');
        break;
    case 19:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_repair.php');
        break;

        case 2000:
          require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-acceptance.php');
          break;    
    case 20:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-acceptance-old.php');
        break;
    case 21:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/models.php');
        break;
    case 22:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/prices.php');
        break;
    case 23:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_price.php');
        break;
    case 24:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_price.php');
        break;
    case 25:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_model.php');
        break;
    case 26:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_model.php');
        break;
    case 27:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/wait.php');
        break;
    case 29:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/mod_request.php');
        break;
    case 30:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/requests.php');
        break;
    case 31:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_parts.php');
        break;
    case 32:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/parts.php');
        break;
    case 33:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_parts.php');
        break;
    case 34:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/tickets.php');
        break;
    case 35:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/service_info_full.php');
        break;
    case 36:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/config.php');
        break;
    case 37:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_provider.php');
        break;
    case 38:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_provider.php');
        break;
    case 39:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/providers.php');
        break;
    case 40:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_repairman.php');
        break;
    case 41:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_repairman.php');
        break;
    case 42:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repairmans.php');
        break;
    case 43:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_group.php');
        break;
    case 44:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_group.php');
        break;
    case 45:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/groups.php');
        break;
    case 46:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_repair2.php');
        break;
    case 47:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-parts_old.php');
        break;
    case 48:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-photos-old.php');
        break;
    case 49:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/upload_serials.php');
        break;
    case 50:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/block.php');
        break;
    case 51:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_repair_pre.php');
        break;
    case 52:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_cat.php');
        break;
    case 53:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_cat.php');
        break;
    case 54:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/cats.php');
        break;
    case 55:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/prices_service.php');
        break;
    case 56:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-docs.php');
        break;
    case 57:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_ticket.php');
        break;
    case 58:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-support.php');
        break;

    case 59:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_repair_type.php');
        break;
    case 60:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_problem.php');
        break;
    case 61:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_problem.php');
        break;
    case 62:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_repair_type.php');
        break;
    case 63:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/problems.php');
        break;
    case 64:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair_types.php');
        break;
    case 65:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_issues.php');
        break;
    case 66:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/issues.php');
        break;
    case 67:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/cities.php');
        break;
    case 68:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_city.php');
        break;
    case 69:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_city.php');
        break;
    case 70:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/manual.php');
        break;
    case 71:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_issue.php');
        break;
    case 72:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/transfer.php');
        break;
    case 73:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-repair.php');
        break;
    case 74:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/notify.php');
        break;
    case 75:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/reports.php');
        break;
    case 76:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/service_doc.php');
        break;
    case 77:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/billing_info.php');
        break;
    case 78:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/billing_info_admin.php');
        break;
    case 79:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payment.php');
        break;
    case 80:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments.php');
        break;
    case 81:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/logs_admin.php');
        break;
    case 82:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/services_sc.php');
        break;
    case 83:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_services_sc.php');
        break;
    case 84:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_sc_service.php');
        break;
    case 85:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/dashboard5.php');
        break;
    case 86:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/stat.php');
        break;
    case 87:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_brand.php');
        break;
    case 88:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_brand.php');
        break;
    case 89:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/brands.php');
        break;
    case 90:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/documents.php');
        break;
    case 91:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_document.php');
        break;
    case 92:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_document.php');
        break;
    case 93:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/documents_sc.php');
        break;
    case 94:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/parts_sc.php');
        break;
    case 95:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/parts_history.php');
        break;
    case 96:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/clients.php');
        break;
    case 97:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_client.php');
        break;
    case 98:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_client.php');
        break;
    case 99:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/mass_uploading.php');
        break;
    case 100:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/returns.php');
        break;
    case 101:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/return.php');
        break;
    case 102:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments2.php');
        break;
    case 103:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/cats_services.php');
        break;
    case 104:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/combined.php');
        break;
    case 105:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments3.php');
        break;
    case 106:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/models_services.php');
        break;
    case 107:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/personal.php');
        break;
    case 108:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_personal.php');
        break;
    case 109:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_personal.php');
        break;
    case 110:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/dashboard4.php');
        break;
    case 111:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/dashboard_personal.php');
        break;
    case 112:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/dashboard_sc.php');
        break;
    case 113:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/stat_master.php');
        break;
    case 114:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/plans.php');
        break;
    case 115:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/returns_dashboard.php');
        break;
    case 116:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_model_service.php');
        break;
    case 117:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/refresh_sc.php');
        break;
    case 118:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payment2.php');
        break;
    case 119:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/combined2.php');
        break;
    case 120:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/services2.php');
        break;
    case 121:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/service2.php');
        break;
    case 122:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments3_2.php');
        break;
    case 123:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments_3_2_2.php');
        break;
    case 124:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments_3_2_2.php');
        break;
    case 125:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments_from_combined.php');
        break;
    case 126:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/payments3_2_archive.php');
        break;
    case 127:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair-card-repair.php'); // !SM edit_repair_222 -> repair-card-repair 
        break;
    case 128:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/get_parts_list.php');
        break;
    case 129:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/countries.php');
        break;
    case 130:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_country.php');
        break;
    case 131:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_country.php');
        break;
    case 132:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/contr.php');
        break;
    case 133:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_contr.php');
        break;
     case 134:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_contr.php');
        break;
     case 135:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/double_dashboard.php');
        break;
     case 136:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/return_finance.php');
        break;
     case 137:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/logs_login.php');
        break;
     case 138:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/re_repaired.php');
        break;
        case 1380:
          require_once($_SERVER['DOCUMENT_ROOT'].'/templates/re_repaired-models.php');
          break;    
     case 139:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/copy_group.php');
        break;
     case 140:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_repair2_v4.php');
        break;
     case 141:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_manual_doc.php');
        break;
     case 142:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/logs_notify.php');
        break;
     case 143:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/show_notify_log.php');
        break;
     case 144:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/repair_types_brand.php');
        break;
     case 145:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_repair_type_brand.php');
        break;
     case 146:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/edit_repairtype_brand.php');
        break;
     case 147:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/problems_brand.php');
        break;
     case 148:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/brands_tarif.php');
        break;
     case 149:
        require_once($_SERVER['DOCUMENT_ROOT'].'/templates/add_brand_tarif.php');
        break;    
    default:
        header('Location: /');
        exit;
        break;
}

echo '<!--'.$template.'-->';

# Закрываем коннект к БД:
mysqli_close($db);


function curlData($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HEADER, false);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

function getApiData($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HEADER, false);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}