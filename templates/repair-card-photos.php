<?php

require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/spaces/spaces.php';
require '_new-codebase/front/templates/main/parts/dashboard/ui.php';

use program\core;
use models;
use models\User;
use models\dashboard\UI;
use models\Log;
use models\Repair;
use models\repaircard\Photos;
use program\adapters\S3;

$S3AvailFlag = S3::isAvailable();

$extraPhotos = [];
$sql = mysqli_query($db, 'SELECT * FROM `photos` where `repair_id` = ' . $_GET['id']);
if (mysqli_num_rows($sql) != false) {
  while ($row = mysqli_fetch_array($sql)) {
    $extraPhotos[] = $row;
  }
}
$extraVideos = [];
$sql = mysqli_query($db, 'SELECT * FROM `videos` where `repair_id` = ' . $_GET['id']);
if (mysqli_num_rows($sql) != false) {
  while ($row = mysqli_fetch_array($sql)) {
    $extraVideos[] = $row;
  }
}


if (isset(core\App::$URLParams['ajax'])) {
  $space = new SpacesConnect(core\App::$config['digocean_key'], core\App::$config['digocean_secret'], core\App::$config['digocean_name'], core\App::$config['digocean_region']);
  switch (core\App::$URLParams['ajax']) {
    case 'save-comment':
      $table = (core\App::$URLParams['type'] == 'extra') ? 'photos' : 'videos';
      mysqli_query($db, 'UPDATE `' . $table . '` SET
      `comment` = "' . $_POST['comment'] . '"  
      WHERE `id` = ' . core\App::$URLParams['id'] . ';') or mysqli_error($db);
      exit;
    case 'rotate':
      $table = (core\App::$URLParams['type'] == 'extra') ? 'photos' : 'repairs_photo';
      $photo = mysqli_fetch_assoc(mysqli_query($db, 'SELECT * FROM `' . $table . '` WHERE `id` = ' . core\App::$URLParams['id'] . ';'));
      $newPhoto = rotatePhoto($photo['url'], core\App::$URLParams['direct']);
      $newURL = '';
      if ($newPhoto) {
        try {
          $fileName =  date('d_H-i_s') . rand(1, 99999999);
          $up = $space->UploadFile(ltrim($newPhoto['path'], '/'), 'public', 'uploads/photos/repairs/' . date('mY') . '/' . $fileName . '.' . $newPhoto['ext']);
        } catch (Exception $e) {
          exit('Ошибка при загрузке изображения. Пожалуйста, попробуйте еще раз, либо обратитесь к администратору. ' . $e->getMessage());
        }
      }
      if (!empty($up['ObjectURL'])) {
        mysqli_query($db, 'UPDATE `' . $table . '` SET
        `url` = "' . $up['ObjectURL'] . '"  
        WHERE `id` = ' . core\App::$URLParams['id'] . ';') or mysqli_error($db);
        $newURL = $up['ObjectURL'];
        $p = parse_url($photo['url']);
        $space->DeleteObject(ltrim($p['path'], '/'));
      }
      echo json_encode(['url' => $newURL]);
      exit;
    case 'del-photo':
      switch (core\App::$URLParams['type']) {
        case 'extra':
          $table = 'photos';
          $log = 'Дополнительное фото.';
          break;
        case 'video':
          $table = 'videos';
          $log = 'Дополнительное видео.';
          break;
        default:
          $table = 'repairs_photo';
          $log = '';
      }
      $photo = deletePhoto(core\App::$URLParams['id'], $table);
      if($photo){
        $log = ($log) ? $log : Photos::$types[$photo['photo_id']].'.';
        Log::repair(4, $log, $_GET['id']);
      }
      exit;
    case 'set-no-photo-flag':
      @mysqli_query($db, 'DELETE FROM `repairs_photo` WHERE `photo_id` = ' . core\App::$URLParams['type'] . ' AND `repair_id` = ' . $_GET['id']);
      if ($_POST['flag']) {
        mysqli_query($db, 'INSERT INTO `repairs_photo` (`repair_id`, `photo_id`, `no_photo_flag`) 
        VALUES 
        ("' . mysqli_real_escape_string($db, $_GET['id']) . '",
        "' . core\App::$URLParams['type'] . '",
         "' . $_POST['flag'] . '");') or mysqli_error($db);
         Log::repair(5, Photos::$types[core\App::$URLParams['type']].'.', $_GET['id']);
      }
      exit;
    case 'upload-photo':
      try {
        if (core\App::$URLParams['type'] == 'extra') {
          $url = uploadPhotoToDigOcean(0);
          mysqli_query($db, 'INSERT INTO `photos` (`repair_id`, `url`) 
        VALUES 
        ("' . mysqli_real_escape_string($db, $_GET['id']) . '",
         "' . $url . '");') or mysqli_error($db);
         $log = 'Дополнительное фото.';
         $cacheKey = 'cache_photo_notif_' . $_GET['id'];
         if(empty($_SESSION[$cacheKey]) || (time() - $_SESSION[$cacheKey]) > 300) { // 5 мин.
          if(User::hasRole('master', 'taker')) {
            $sendToID = 33;
          } else {
            $sendToID = 1;
          }
          models\Sender::use('bell')->to([$sendToID])->send('Сервис загрузил фото', 'Ремонт #' . $_GET['id'], '/edit-repair/'.$_GET['id'].'/step/4/');
          $_SESSION[$cacheKey] = time();
        }
        } elseif (core\App::$URLParams['type'] == 'video') {
          $url = uploadVideoToDigOcean(0);
          mysqli_query($db, 'INSERT INTO `videos` (`repair_id`, `url`) 
        VALUES 
        ("' . mysqli_real_escape_string($db, $_GET['id']) . '",
         "' . $url . '");') or mysqli_error($db);
         $log = 'Дополнительное видео.';
        } else {
          deletePhoto(core\App::$URLParams['id'], 'repairs_photo');
          $url = uploadPhotoToDigOcean(0);
          mysqli_query($db, 'INSERT INTO `repairs_photo` (`repair_id`, `photo_id`, `url`) 
        VALUES 
        ("' . mysqli_real_escape_string($db, $_GET['id']) . '",
         "' . core\App::$URLParams['type'] . '",
         "' . $url . '");') or mysqli_error($db);
         $log = '';
        }
      } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
      }
      $log = ($log) ? $log : 'Фото "'.Photos::$types[core\App::$URLParams['type']].'".';
      Log::repair(6, $log, $_GET['id']);
      $id = mysqli_insert_id($db);
      echo json_encode(['id' => $id, 'url' => $url]);
      exit;
  }
}


if (\models\User::hasRole('admin', 'service', 'taker', 'master')) {
  $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\';'));
} else {
  $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' and `service_id` = ' . \models\User::getData('id') . ';'));
}


if ($count['COUNT(*)'] > 0) {
  if (\models\User::hasRole('admin', 'service', 'taker', 'master')) {
    $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = "' . mysqli_real_escape_string($db, $_GET['id']) . '"'));
    $repairStatus = $content['status_admin'];
	$repairStatus = $basestatus;
	$newStatus = $content['status_admin'];
    $content['status_admin'] = 'Есть вопросы';
    $content['repair_done'] = 0;
  } else {
    $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = "' . mysqli_real_escape_string($db, $_GET['id']) . '" and `service_id` = ' . \models\User::getData('id')));
    $repairStatus = $content['status_admin'];
    if ($content['status_admin'] == 'Есть вопросы' || $content['status_admin'] == 'Нужны запчасти') {
      $content['repair_done'] = 0;
    }
  }
} else {
  $backURL = '/dashboard/';
    if(!empty($_COOKIE['dashboard:tab'])){
      $backURL = '/dashboard/?tab=' . $_COOKIE['dashboard:tab'];
    }
    header('Location: ' . $backURL );
}


$activeFlag = ' style="display: none" ';
if (models\User::hasRole('admin', 'master', 'slave-admin', 'taker') || in_array($content['status_admin'], ['Принят', 'В работе', 'Есть вопросы', 'Запчасти в пути', 'Выезд подтвержден'])) {
    $activeFlag = '';
}

disable_notice('/edit-repair/' . $_GET['id'] . '/step/4/', \models\User::getData('id'));

function uploadPhotoToDigOcean($filesKey)
{
  /* https://github.com/SociallyDev/Spaces-API/issues/6 */
  global $space;
  $file = new core\File('', $filesKey);
  if (!$file->hasExt('png', 'jpg', 'jpeg')) {
    throw new Exception('Допустимые форматы фото: jpg, png.');
  }
  $fileName =  date('d_H-i_s') . rand(1, 99999999);
  $file->setPath(core\App::$config['dir_uploads'] . '/temp', $fileName);
  if (!$file->exists()) {
    throw new Exception('Не удалось загрузить файл.');
  }
  resize($file->path);
  $up = $space->UploadFile(ltrim($file->path, '/'), 'public', 'uploads/photos/repairs/' . date('mY') . '/' . $fileName . '.' . strtolower($file->ext));
  return $up['ObjectURL'];
}


function resize($path)
{
  $img = new Imagick($_SERVER['DOCUMENT_ROOT'] . $path);
  $img->thumbnailImage(2000, 2000, true);
  $img->writeImage($_SERVER['DOCUMENT_ROOT'] . $path);
  $img->clear();
  $img->destroy();
}


function uploadVideoToDigOcean($filesKey, $filesIndex = -1)
{
  global $space;
  $fileName =  date('d_H-i_s') . rand(1, 99999999);
  try {
    $file = new core\File('', $filesKey, $filesIndex);
    $fileName =  date('d_H-i_s') . rand(1, 99999999);
    $file->setPath(core\App::$config['dir_uploads'] . '/temp', $fileName);
    $up = $space->UploadFile(ltrim($file->path, '/'), 'public', 'uploads/videos/repairs/' . date('mY') . '/' . $fileName . '.' . strtolower($file->ext));
  } catch (Exception $e) {
    exit('Ошибка при загрузке видео. Пожалуйста, попробуйте еще раз, либо обратитесь к администратору. ' . $e->getMessage());
  }
  return $up['ObjectURL'];
}



function model($id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = ' . $id);
  while ($row = mysqli_fetch_array($sql)) {
    $content = $row;
    // print_r($row);
  }
  return $content;
}


function get_last_photo($repair_id, $type)
{
  global $db;
  return mysqli_fetch_assoc(mysqli_query($db, 'SELECT * FROM `repairs_photo` where `photo_id` = ' . $type . ' and `repair_id` = ' . $repair_id . ' order by id desc LIMIT 1'));
}


function check_complete($id)
{
  global $db;
  $req = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE
`model_id` != \'\' and
`disease` != \'\' and
`repair_type_id` != \'\' and
`id` = ' . $id));
  if ($req['COUNT(*)'] == 0) {
    return false;
  } else {
    return true;
  }
}


$saleDate = '';
if ($content['sell_date'] != '0000-00-00') {
  $saleDate = '<li class="photos__tag">Дата продажи: ' . core\Time::format($content['sell_date']) . '</li>';
}

$wrongSerial = '';
if (!empty($content['serial']) && !empty($content['model_id'])) {
  $model = get_model_by_id($content['model_id']);
  if (in_array($model['brand'], ['HARPER', 'OLTO', 'NESONS', 'SKYLINE']) && !models\Serials::isValid($content['serial'], $content['model_id'])) {
    $wrongSerial = '<li class="photos__tag photos__tag_alert">Некорректный номер</li>';
  }
}

$photosTypes = models\repaircard\Photos::getPhotosTypes();
if(!Repair::hasOwnParts($content['id'])){
  unset($photosTypes[11]);
  unset($photosTypes[12]);
}
?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Фото и видео - Карточка ремонта</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style-without-forms.css" rel="stylesheet" />
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>

  <script src="/notifier/js/index.js"></script>
  <link rel="stylesheet" type="text/css" href="/notifier/css/style.css?v=1.00">
  <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
  <script src="/js/jquery.dialogx.js"></script>
  <link rel="stylesheet" href="/js/jquery.dialogx.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
  <script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
  <script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
  <script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
  <link href="/_new-codebase/front/templates/main/css/repair-card/save-parts-window.css" rel="stylesheet">

  <style>
    .tab-content {
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current {
      display: inherit;
    }

    label.error {
      display: block;
      color: red;
    }

    .sale-date {
      text-align: left;
      border: solid 1px #fbfbfb;
      background-color: #f7f7f7;
      padding: 5px;
    }

    .wrong-serial {
      text-align: left;
      background-color: crimson;
      padding: 5px;
      color: #fff;
    }

    .error-message {
      width: 500px;
      margin: 16px auto;
      border-radius: 7px;
      background-color: crimson;
      padding: 7px;
      color: #fff;
    }
  </style>
  <!-- New codebase -->
  <style>
    * {
      box-sizing: border-box;
    }
  </style>
  <link href="/_new-codebase/front/modules/dashboard/css/ui.css" rel="stylesheet" />
  <link href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/grid.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/repair-card/repair-card.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/repair-card/photos/photos.css" rel="stylesheet">
  <!-- Aside controls -->
  <link href="/_new-codebase/front/components/aside-controls/css/aside-controls.css" rel="stylesheet">
</head>

<body>
  <?php
  if (!$S3AvailFlag) {
    echo '<div class="top-message top-message_alert" style="text-align:center">Загрузка и просмотр фото временно недоступны. Приносим извинения за неудобства!</div>';
  }else if ($repairStatus == 'Есть вопросы' && models\User::hasRole('service')) {
    echo '<div class="top-message top-message_alert" style="text-align:center">Пожалуйста, внесите исправления в карточку и отправьте на проверку.</div>';
  }
  ?>
  <div class="viewport-wrapper">

    <div class="site-header">
      <div class="wrapper">

        <div class="logo">
          <a href="/dashboard/"><img src="<?= $config['url']; ?>i/logo.png" alt="" /></a>
          <span>Сервис</span>
        </div>

        <div class="not-container">
          <button style="position:relative;    margin-left: 120px;   margin-top: 15px;" type="button" class="button-default show-notifications js-show-notifications animated swing">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="30" height="32" viewBox="0 0 30 32">
              <defs>
                <g id="icon-bell">
                  <path class="path1" d="M15.143 30.286q0-0.286-0.286-0.286-1.054 0-1.813-0.759t-0.759-1.813q0-0.286-0.286-0.286t-0.286 0.286q0 1.304 0.92 2.223t2.223 0.92q0.286 0 0.286-0.286zM3.268 25.143h23.179q-2.929-3.232-4.402-7.348t-1.473-8.652q0-4.571-5.714-4.571t-5.714 4.571q0 4.536-1.473 8.652t-4.402 7.348zM29.714 25.143q0 0.929-0.679 1.607t-1.607 0.679h-8q0 1.893-1.339 3.232t-3.232 1.339-3.232-1.339-1.339-3.232h-8q-0.929 0-1.607-0.679t-0.679-1.607q3.393-2.875 5.125-7.098t1.732-8.902q0-2.946 1.714-4.679t4.714-2.089q-0.143-0.321-0.143-0.661 0-0.714 0.5-1.214t1.214-0.5 1.214 0.5 0.5 1.214q0 0.339-0.143 0.661 3 0.357 4.714 2.089t1.714 4.679q0 4.679 1.732 8.902t5.125 7.098z" />
                </g>
              </defs>
              <g fill="#000000">
                <use xlink:href="#icon-bell" transform="translate(0 0)"></use>
              </g>
            </svg>

            <div class="notifications-count js-count"></div>

          </button>
        </div>



        <div class="logout">

          <a href="/logout/">Выйти, <?= \models\User::getData('login'); ?></a>
        </div>

      </div>
    </div><!-- .site-header -->

    <div class="wrapper" style="max-width: 1280px">

      <?= top_menu_admin(); ?>

      <div class="adm-tab">

        <?= getSummaryHTML(models\RepairCard::getSummary($content['id'])); ?>

        <?php if (\models\User::getData('id') == 33 && $content['master_id'] != 0 && $content['status_admin'] != 'Подтвержден'  && check_complete($content['id'])  && $_GET['readonly'] != 1) { ?>

          <script>
            $(document).ready(function() {


              if ($('select[name="master_id"]').val() != '') {
                $('.show_faster').show();
              }


            });
          </script>


        <?php } ?>

        <?= menu_dash(); ?>

      </div><!-- .adm-tab -->
      <br>
        <!-- Меню вкладок -->
        <section class="layout__mb_md">
            <?= getTabsHTML(UI::getTabs(User::getData('role'))); ?>
        </section>
      <h2>Процессинг</h2>

      <?php
      $stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav($content['id'], 'photos'));
      echo $stepsNavHTML;
      ?>

      <form id="send" method="POST" enctype="multipart/form-data" class="repair-card">
        <div class="container gutters">
          <div class="row">
          <a name="error"></a>
          <div class="col-12">
          <p style="text-align: center;color:red;font-weight:600">Если у вас не дефект LCD при списании ТВ (Без ремонта), то нам не нужны все фото. Если база их запросит, то отметьте галочкой их отсутствие в окошке "Отсутствует". Только при списании ТВ нам требуются ВСЕ фото!</p>
          </div>
            
            <?php
            $photoErr = \models\repair\Check::hasPhotoErrors($content['id']); 
            if ($photoErr['error_flag']) {
              echo '<div class="col-12"><div class="error-message"><p style="margin-bottom: 8px">Необходимо добавить фото:</p><p style="margin-left: 16px">' . implode('<br>', $photoErr['errors']) . '</p></div></div>';
            }
            ?>

            <div class="col-12">
              <h3 class="form__title">Основные фото</h3>
            </div>

            <?php
            foreach ($photosTypes as $typeID => $typeName) {
              echo getPhotoHTML(get_last_photo($content['id'], $typeID), $typeName, '', $typeID);
            }
            ?>

          </div>

          <div class="row" id="photos-container">
            <div class="col-12">
              <h3 class="form__title">Дополнительные фото</h3>
            </div>

            <?php
            foreach ($extraPhotos as $photo) {
              echo getPhotoHTML($photo, $photo['comment'], '', 'extra');
            }
            ?>
          </div>

          <div class="row">
            <?= getPhotoHTML(['id' => '', 'url' => ''], '', 'photo-template', 'extra'); ?>
            <div class="col-12">
              <button id="add-photo-trig" class="add-trig" <?= $activeFlag; ?>>Добавить</button>
            </div>
          </div>

          <div class="row" id="videos-container">
            <div class="col-12">
              <h3 class="form__title">Видео</h3>
            </div>

            <?php
            foreach ($extraVideos as $video) {
              echo getPhotoHTML($video, $video['comment'], '', 'video');
            }
            ?>
          </div>

          <div class="row">
            <?= getPhotoHTML(['id' => '', 'url' => ''], '', 'video-template', 'video'); ?>
            <div class="col-12">
              <button id="add-video-trig" class="add-trig" <?= $activeFlag; ?>>Добавить</button>
            </div>
          </div>
        </div>
      </form>


      <?= $stepsNavHTML; ?>

    </div>
  </div>
  </div>
  <!-- New codebase -->
  <script src='/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js'></script>
  <script src='/_new-codebase/front/components/repair-card/repair-card.js?v=1.02'></script>
  <script src="/_new-codebase/front/components/status/status.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let processFlag = false;
      let $photoTpl = $('[data-photo-block=photo-template]'),
        $videoTpl = $('[data-photo-block=video-template]'),
        $photosContainer = $('#photos-container'),
        $videosContainer = $('#videos-container');
      $photoTpl.detach();
      $videoTpl.detach();

      $('#add-photo-trig').on('click', function() {
        event.preventDefault();
        $photosContainer.append($photoTpl.clone());
      });

      $('#add-video-trig').on('click', function() {
        event.preventDefault();
        $videosContainer.append($videoTpl.clone());
      });

      $('body').on('click', '[data-rotate-trig]', function(event) {
        if (processFlag) {
          return false;
        }
        let $this = $(this);
        let $block = $this.closest('[data-photo-block]');
        processFlag = true;
        $block.addClass('waiting');
        let params = {
          type: 'POST',
          dataType: 'json',
          url: '?ajax=rotate&type=' + $block.data('type') + '&direct=' + $this.data('rotate-trig') + '&id=' + $block.data('id'),
          cache: false,
          success: function(resp) {
            setTimeout(function() {
              $block.removeClass('waiting');
              processFlag = false;
            }, 3000);
            if (resp.url) {
              $('[data-link] img', $block).prop('src', resp.url);
              $('[data-link]', $block).prop('href', resp.url);
              $('[data-open-image-trigger]', $block).prop('href', resp.url);
            } else {
              alert('К сожалению, данное изображение нельзя вращать.');
            }
          },
          error: function(jqXHR) {
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
          }
        };
        $.ajax(params);
      });

      $('body').on('change', '[data-no-photo-flag]', function(event) {
        let $this = $(this);
        let $block = $this.closest('[data-photo-block]');
        let val;
        if ($this.prop('checked')) {
          $('[data-photo-file-input]', $block).hide(10);
          val = 1;
        } else {
          $('[data-photo-file-input]', $block).show(10);
          val = 0;
        }
        let params = {
          type: 'POST',
          dataType: 'json',
          data: 'flag=' + val,
          url: '?ajax=set-no-photo-flag&type=' + $block.data('type'),
          cache: false,
          error: function(jqXHR) {
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
          }
        };
        $.ajax(params);
      });

      $('body').on('change', '[data-photo-file-input]', function(event) {
        let $this = $(this);
        let $block = $this.closest('[data-photo-block]');
        var data = new FormData();
        if ($block.hasClass('waiting')) {
          return false;
        }
        $.each(this.files, function(key, value) {
          data.append(key, value);
        });
        $block.addClass('waiting');
        this.value = '';
        params = {
          type: 'POST',
          dataType: 'json',
          data: data,
          url: '?ajax=upload-photo&id=' + $block.data('id') + '&type=' + $block.data('type'),
          processData: false,
          contentType: false,
          cache: false,
          error: function(jqXHR) {
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
          }
        };
        params.success = function(resp) {
          $block.removeClass('waiting');
          if (resp.error) {
            alert(resp.error);
          }
          if (!resp.url) {
            return;
          }
          if ($block.data('type') == 'video') {
            $('[data-link]', $block).addClass('photos__photo_play-video');
          } else {
            $('[data-link] img', $block).prop('src', resp.url);
          }
          $('[data-link]', $block).prop('href', resp.url).show(10).fancybox();
          $('[data-open-image-trigger]', $block).prop('href', resp.url).show(10);
          $('[data-photo-placeholder]', $block).hide(10);
          $('[data-no-photo-label]', $block).hide(10);
          $('[data-rotate-trig]', $block).show(10);
          $block.data('id', resp.id);
          $('[data-del-trig]', $block).show(10);
          let $capt = $('[data-photo-capt]');
          if ($capt.length) {
            $capt.removeProp('readonly');
          }
        };
        $.ajax(params);
      });

      $('body').on('change', '[data-photo-capt]', function(event) {
        let $this = $(this);
        let $block = $this.closest('[data-photo-block]');
        let params = {
          type: 'POST',
          dataType: 'json',
          data: 'comment=' + $this.val(),
          url: '?ajax=save-comment&id=' + $block.data('id') + '&type=' + $block.data('type'),
          cache: false,
          error: function(jqXHR) {
            console.log('Ошибка сервера');
            console.log(jqXHR.responseText);
          }
        };
        $.ajax(params);
      });

      $('body').on('click', '[data-del-trig]', function(event) {
        event.preventDefault();
        if (!confirm('Удалить файл?')) {
          return false;
        }
        let $this = $(this);
        let $block = $this.closest('[data-photo-block]');
        let id = $block.data('id');
        $this.hide(10);
        if (id) {
          $.ajax({
            type: 'POST',
            dataType: 'json',
            cache: false,
            url: '?ajax=del-photo&id=' + id + '&type=' + $block.data('type'),
            error: function(jqXHR) {
              console.log('Ошибка сервера');
              console.log(jqXHR.responseText);
            }
          });
        }
        if ($block.data('type') == 'extra' || $block.data('type') == 'video') {
          $block.slideUp(400, function() {
            $(this).remove();
          });
        } else {
          $('[data-no-photo-label]', $block).show(10);
          $('[data-photo-placeholder]', $block).show(10);
          $('[data-link]', $block).hide(10);
          $('[data-rotate-trig]', $block).hide(10);
          $('[data-open-image-trigger]', $block).hide(10);
        }
      });

      $('body').on('click', '[data-open-image-trigger]', function(event) {
        event.preventDefault();
        let newWin = window.open('about:blank');
        newWin.document.write(
          "<html><head><link rel='shortcut icon' href='/favicon.ico'></head><body><img src='" + this.href + "'></body></html>"
        );
      });
    });
  </script>

  <div id="user-data-json" style="display: none"><?= json_encode(['id' => models\User::getData('id'), 'role' => models\User::getData('role')]); ?></div>

  <!-- Aside controls -->
  <script src="/_new-codebase/front/components/request.js"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/confirm-approve-window.js"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/save-parts-window.js"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/aside-controls.js"></script>
  <!-- / Aside controls -->
  <div id="aside-controls-json" style="display: none"><?= json_encode(models\RepairCard::getAsideControls($content['id'])); ?></div>
  <div id="repair-data-json" style="display: none"><?= json_encode(['id' => $content['id'], 'model_id' => $content['model_id']]); ?></div>
</body>

</html>

<?php


function get_model_by_id($name)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT `name`, `brand` FROM `models` WHERE `id` = \'' . mysqli_real_escape_string($db, $name) . '\'');
  return mysqli_fetch_array($sql);
}

function getPhotoHTML($photo, $capt, $name = '', $type)
{
  global $activeFlag, $wrongSerial, $saleDate;
  $serialTag = ($type == 1) ? $wrongSerial : '';
  $dateTag = (in_array($type, [6, 7])) ? $saleDate : '';
  $videoFlag = ($type == 'video') ? 'photos__photo_video' : '';
  $delTrig = '<li class="photos__del-trig" data-del-trig style="display:none">Удалить</li>';
  $link = '<a href="" target="_blank" class="photos__photo" data-link style="display:none">
              <img src="" alt="">
          </a>';
  $placeholder = '<div class="photos__photo ' . $videoFlag . '" data-photo-placeholder></div>';
  $newWindowLink = '';
  $rotateTrig = 'style="display:none"';
  if (!empty($photo['url'])) {
    $delTrig = '<li class="photos__del-trig" data-del-trig>Удалить</li>';
    $playFlag = ($type == 'video') ? 'photos__photo_play-video' : '';
    $link = '<a href="' . $photo['url'] . '" data-caption="' . $capt . '" data-fancybox="gallery" target="_blank" class="photos__photo ' . $playFlag . '" data-link>';
    if ($type != 'video') {
      $rotateTrig = '';
      $link .= '<img src="' . $photo['url'] . '" alt="' . $capt . '">';
    }
    $link .= '</a>';
    if($type == 'video'){
      $newWindowLink = '<a href="' . $photo['url'] . '" target="_blank" class="photos__new-window-link">Скачать</a>';
    }else{
      $newWindowLink = '<a href="' . $photo['url'] . '" target="_blank" data-open-image-trigger class="photos__new-window-link">Открыть</a>';
    }
    $placeholder = '<div class="photos__photo ' . $videoFlag . '" style="display:none" data-photo-placeholder></div>';
  }
  if ($type == 'extra' || $type == 'video') {
    if ($name != 'photo-template' && $name != 'video-template') {
      $capt = '<input type="text" data-photo-capt value="' . $capt . '" placeholder="Введите название...">';
    } else {
      $capt = '<input type="text" data-photo-capt readonly placeholder="Введите название...">';
    }
  }
  global $newStatus;
  $noPhotoFlag = (models\User::hasRole('slave-admin', 'master') || (empty($photo['url']) && in_array($type, [2, 9, 10]))) ? '<label data-no-photo-label><input type="checkbox" ' . ((!empty($photo['no_photo_flag'])) ? 'checked' : '') . ' data-no-photo-flag> Отсутствует</label>' : '';
  $fileInput = '<input type="file" accept="image/jpeg, image/png, video/*" data-photo-file-input>';
  if (!($newStatus === 'Принят' || $newStatus === 'В работе' || $newStatus === 'Одобрен акт')) {
	if(models\User::hasRole('service') && !empty($photo['url'])){
		$fileInput = '';
		$delTrig = '';
	}
  }

  return '<div class="col-6" data-photo-block="' . $name . '" data-type="' . $type . '" data-id="' . $photo['id'] . '">
  <figure class="photos__cell">
    <ul class="photos__controls" ' . $activeFlag . '>
      <li>' . $fileInput . '</li>
      <li>' . $noPhotoFlag . '</li>
      <li>
        <img class="photos__rotate-trig" data-rotate-trig="left" ' . $rotateTrig . ' style="margin-right: 8px" src="/_new-codebase/front/templates/main/images/rotate-ic-left.png">
        <img class="photos__rotate-trig" data-rotate-trig="right" ' . $rotateTrig . ' src="/_new-codebase/front/templates/main/images/rotate-ic-right.png">
       </li>
      ' . $delTrig . '
    </ul>
    <ul class="photos__tags">
    ' . $serialTag . '
    ' . $dateTag . '
    </ul>
    ' . $placeholder . '
    ' . $link . '
    <figcaption class="photos__capt">' . $capt . $newWindowLink . '</figcaption>
  </figure>
</div>';
}

function deletePhoto($id, $table)
{
  global $db, $space;
  $photo = mysqli_fetch_assoc(mysqli_query($db, 'SELECT * FROM `' . $table . '` WHERE `id` = ' . $id . ';'));
  if (!$photo) {
    return [];
  }
  mysqli_query($db, 'DELETE FROM `' . $table . '` WHERE `id` = ' . $id);
  $p = parse_url($photo['url']);
  $space->DeleteObject(ltrim($p['path'], '/'));
  return $photo;
}


function rotatePhoto($url, $direct = 'left')
{
  $parts = pathinfo($url);
  $ext = strtolower($parts['extension']);
  switch ($ext) {
    case 'png':
      $fn = 'imagecreatefrompng';
      $type = 1;
      break;
    case 'jpeg':
    case 'jpg':
      $fn = 'imagecreatefromjpeg';
      $type = 2;
      break;
    default:
      return [];
  }
  $deg = ($direct == 'left') ? 90 : -90;
  $path = '/_new-codebase/uploads/temp/' . md5(time()) . '.' . $ext;
  $pathNew = '/_new-codebase/uploads/temp/' . md5(time()) . '-new.' . $ext;
  $f = fopen($_SERVER['DOCUMENT_ROOT'] . $path, 'w');
  fwrite($f, GetImageFromUrl($url));
  fclose($f);
  $source = $fn($_SERVER['DOCUMENT_ROOT'] . $path);
  if(!$source){
    clearExif($path); // ошибка с exif в некоторых файлах
    $source = $fn($_SERVER['DOCUMENT_ROOT'] . $path);
    if(!$source){
      return [];
    }
  }
  $rotate = imagerotate($source, $deg, 0);
  $f = fopen($_SERVER['DOCUMENT_ROOT'] . $pathNew, 'w');
  if ($type == 1) {
    imagepng($rotate, $f, 9);
  } else {
    imagejpeg($rotate, $f, 100);
  }
  imagedestroy($source);
  imagedestroy($rotate);
  if (is_file($_SERVER['DOCUMENT_ROOT'] . $pathNew)) {
    return ['path' => $pathNew, 'ext' => $ext];
  }
  return [];
}

function clearExif($path)
{
  $img = new Imagick($_SERVER['DOCUMENT_ROOT'] . $path);
  $img->stripImage();
  $img->writeImage($_SERVER['DOCUMENT_ROOT'] . $path);
  $img->clear();
  $img->destroy();
}

function GetImageFromUrl($link)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_POST, 0);
  curl_setopt($ch, CURLOPT_URL, $link);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}
