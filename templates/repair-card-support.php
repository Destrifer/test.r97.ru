<?php

require '_new-codebase/front/templates/main/parts/repair-card/repair-card.php';
require '_new-codebase/front/templates/main/parts/repair-card/attention.php';
require '_new-codebase/front/templates/main/parts/dashboard/ui.php';

use program\core;
use program\adapters;
use models;
use models\User;
use models\dashboard\UI;
use models\Log;
use models\Repair;
use models\repaircard\Support;
use models\staff\Staff;

if (!empty(core\App::$URLParams['ajax'])) {
  switch (core\App::$URLParams['ajax']) {
    case 'del-photo':
      $feedback_info = mysqli_fetch_assoc(mysqli_query($db, 'SELECT `id`, `imgs` FROM `feedback_admin` WHERE `repair_id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\';'));
      $imgs_gen = json_decode($feedback_info['imgs']);
      for ($i = 0, $cnt = count($imgs_gen); $i < $cnt; $i++) {
        if ($imgs_gen[$i] == core\App::$URLParams['path']) {
          unset($imgs_gen[$i]);
          break;
        }
      }
      $imgs_gen = array_values($imgs_gen);
      $json = ($imgs_gen) ? json_encode($imgs_gen) : '';
      mysqli_query($db, 'UPDATE `feedback_admin` SET 
          `imgs` = \'' . mysqli_real_escape_string($db, $json) . '\' 
          WHERE `id` = \'' . mysqli_real_escape_string($db, $feedback_info['id']) . '\'
          ;') or mysqli_error($db);
      break;
    default:
      $res = ['error' => 'Неверный запрос.'];
      break;
  }
  exit(json_encode($res));
}

if (\models\User::hasRole('admin', 'service', 'taker', 'master')) {
  $count = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\';'));
} else {
  $count = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' and `service_id` = ' . \models\User::getData('id') . ';'));
}

if(!$count){
  $backURL = '/dashboard/';
  if (!empty($_COOKIE['dashboard:tab'])) {
    $backURL = '/dashboard/?tab=' . $_COOKIE['dashboard:tab'];
  }
  header('Location: ' . $backURL);
}


  if (User::hasRole('admin', 'service', 'taker', 'master', 'slave-admin')) {
    $content = mysqli_fetch_assoc(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' LIMIT 1;'));
    $repairStatus = $content['status_admin'];
    $content['status_admin'] = 'Есть вопросы';
    $content['repair_done'] = 0;
  } else {
    $content = mysqli_fetch_assoc(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' and `service_id` = ' . \models\User::getData('id') . ' LIMIT 1;'));
    $repairStatus = $content['status_admin'];
  }
  if (empty($_POST['send'])) {
  if(User::hasRole('service')) {
    Repair::setReadStatus($_GET['id'], ['service' => true]);
  } else {
    Repair::setReadStatus($_GET['id'], ['admin' => true]);
  } 
}
  
  disable_notice('/edit-repair/' . $_GET['id'] . '/step/6/', \models\User::getData('id'));

  if (check_feedback($content['id'])) {

    $feedback_info = mysqli_fetch_assoc(mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `repair_id` = \'' . mysqli_real_escape_string($db, $_GET['id']) . '\' LIMIT 1;'));

    if ($feedback_info['imgs']) {
      $imgs_gen = json_decode($feedback_info['imgs']);
      foreach ($imgs_gen as $img) {
        $content['img_uploaded'] .= '<li class="adm-media-item">
          <div class="img ">
            <span style="background: #fff;"><a href="' . rtrim($img) . '" data-fancybox="group"><img  style="max-height:100px;max-width: 150px;" src="' . rtrim($img) . '" alt=""/></a></span>
          </div>
          <a class="del remove_preview"></a><input type="hidden" data-file-path name="files_preview[]" value="' . rtrim($img) . '">
        </li>';
      }
    }
  }

  $content['complexs'] = explode('|', $content['complex']);
  $content['visuals'] = explode('|', $content['visual']);
  $content['model'] = model($content['model_id']);

  $sql = mysqli_query($db, 'SELECT * FROM `repairs_parts` where `repair_id` = ' . $content['id']);
  while ($row = mysqli_fetch_array($sql)) {
    $part_info = part_info($row['part_id']);

    $content['parts'] .= '<div class="part"><div class="item"><div class="level">Группы запчастей</div><div class="value"><select name="groups_parts" ><option value="" disabled selected>Выберите вариант</option>' . groups($content['model']['cat'], $part_info['group']) . '</select></div></div><div class="item"><div class="level">Запчасть</div><div class="value"><select name="parts_parts[]" ><option value="" disabled selected>' . parts($content['model']['cat'], $content['model']['id'], $content['serial'], $part_info['group'], $row['part_id']) . '</option></select></div></div></div>';
  }


# Сохраняем:
if ($_POST['send'] == 1) {
  $messageID = Support::sendMessage($_POST['answer'], $content['id']);
  if (!$messageID) {
    echo 'Во время отправки произошла ошибка. Пожалуйста, обратитесь к администратору.';
    exit;
  }
  Log::repair(7, '#' . $messageID . ', с вкладки "Поддержка".', $content['id']);
  if (User::hasRole('admin')) {
    Repair::setReadStatus($content['id'], ['admin' => true, 'service' => false]);
    $serviceInfo = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `users` WHERE `id` = \'' . $content['service_id'] . '\';'));
    sendEmailNotification($serviceInfo, $content['id']);
  } else {
    Repair::setReadStatus($content['id'], ['admin' => false, 'service' => true]);
    notice_add('Новый запрос в ремонте №' . $content['id'], 'Поступил новый запрос в службу поддержки. ', 1, 'https://crm.r97.ru/edit-repair/' . $_GET['id'] . '/step/6/', $_POST['answer']);
  }
  $threadID = Support::getThreadID($content['id']);
  if ($_POST['files_preview']) {
    mysqli_query($db, 'UPDATE `feedback_admin` SET
      `imgs` = \'' . mysqli_real_escape_string($db, uploadPreviews($_POST['files_preview'])) . '\'
      WHERE `id` = \'' . mysqli_real_escape_string($db, $threadID) . '\' LIMIT 1
      ;') or mysqli_error($db);
  }
  if (User::hasRole('service')) {
    if($repairStatus == 'Принят') {
      models\Repair::changeStatus($content['id'], 'В работе');
      models\Log::repair(1, '"Принят" на "В работе", при отправке сообщения.', $content['id']);
    }
    if(!$content['has_questions'] && Repair::setHasQuestions($content['id'], 1)){
      \models\Log::repair(23, 'Статус установлен при обращении в поддержку.', $repairID);
    }
  }
  if (\models\User::hasRole('admin')) {
    if ($content['service_id'] != 33) {
      $master_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairmans` WHERE `id` = ' . $content['master_id']));
      $master_string = ', ' . $master_info['surname'] . ' ' . $master_info['name'];
    } else {
      $master_info = Staff::getStaff(['id' => $content['master_user_id']]);
      $master_string = ', ' . $master_info['surname'] . ' ' . $master_info['name'];
    }
    notice_add('Новый запрос в ремонте #' . $_GET['id'] . $master_string, 'Поступил новый запрос в ремонте от службы поддержки. Пожалуйста, ознакомьтесь.', $content['service_id'], 'https://crm.r97.ru/edit-repair/' . $_GET['id'] . '/step/6/', $_POST['answer']);
    //admin_log_add('Обновлена дата подтверждения ремонта #'.$_GET['id'].' пользователем '.$_SESSION['login'].' из '.$content['app_date']);
  }
  header('Location: /edit-repair/' . $_GET['id'] . '/step/6/');
  exit;
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


function models($cat_id)
{
  global $db;
  $content = array();
  $sql = mysqli_query($db, 'SELECT * FROM `models`;');
  while ($row = mysqli_fetch_array($sql)) {
    if ($cat_id == $row['id']) {
      $content .= '<option selected value="' . $row['model_id'] . '">' . $row['name'] . '</option>';
    } else {
      $content .= '<option value="' . $row['model_id'] . '">' . $row['name'] . '</option>';
    }
  }
  return $content;
}

function get_last_photo($repair_id, $type)
{
  global $db;

  $sql = mysqli_query($db, 'SELECT * FROM `repairs_photo` where `photo_id` = ' . $type . ' and `repair_id` = ' . $repair_id);
  if (mysqli_num_rows($sql) != false) {
    while ($row = mysqli_fetch_array($sql)) {
      $content = ($row['url_do'] != '') ? $row['url_do'] : $row['url'];
    }
  }
  return $content;
}

function part_info($id)
{
  global $db;
  $content = array();
  $sql = mysqli_query($db, 'SELECT * FROM `parts` where `id` = ' . $id);
  while ($row = mysqli_fetch_array($sql)) {
    $content = $row;
  }
  return $content;
}

function groups($cat, $group = '')
{
  global $db;

  $sql = mysqli_query($db, 'SELECT * FROM `groups` where `cat` = \'' . $cat . '\';');
  while ($row = mysqli_fetch_array($sql)) {

    if ($group == $row['name']) {
      $content .= '<option value="' . $row['name'] . '" selected>' . $row['name'] . '</option>';
    } else {
      $content .= '<option value="' . $row['name'] . '">' . $row['name'] . '</option>';
    }
  }
  return $content;
}

function parts($cat_id, $model_id, $serial, $group, $id = '')
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `parts` where `cat` = \'' . $cat_id . '\' and `group` = \'' . $group . '\' and `model_id` = \'' . $model_id . '\' and `serial` = \'' . $serial . '\';');
  //echo 'SELECT * FROM `parts` where `cat` = \''.$cat_id.'\' and `group` = \''.$group.'\' and `model_id` = \''.$model_id.'\' and `serial` = \''.$serial.'\'';
  while ($row = mysqli_fetch_array($sql)) {
    if ($id == $row['id']) {
      $content .= '<option selected value="' . $row['id'] . '">' . $row['list'] . '</option>';
    } else {
      $content .= '<option value="' . $row['id'] . '">' . $row['list'] . '</option>';
    }
  }
  return $content;
}

function check_feedback($id)
{
  global $db;
  $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_admin` WHERE `repair_id` = \'' . mysqli_real_escape_string($db, $id) . '\' ;'));
  if ($count['COUNT(*)'] > 0) {
    return true;
  } else {
    return false;
  }
}



?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Поддержка - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style.css?v=1.00" rel="stylesheet" />
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
  <script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
  <script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
  <script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
  <script src="/js/jquery.dialogx.js"></script>
  <link rel="stylesheet" href="/js/jquery.dialogx.css" />
  <script src="/notifier/js/index.js"></script>
  <link rel="stylesheet" href="/notifier/css/style.css">
  <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />
  <script src="/js/main.js"></script>

  <link href="/_new-codebase/front/templates/main/css/repair-card/save-parts-window.css" rel="stylesheet">
  <script src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="/css/datatables.css">

  <script>
    // Таблица
    $(document).ready(function() {
      $('#table_content').dataTable({
        "pageLength": 30,
        stateSave: true,
        "oLanguage": {
          "sLengthMenu": "Показывать _MENU_ записей на страницу",
          "sZeroRecords": "Записей нет.",
          "sInfo": "Показано от _START_ до _END_ из _TOTAL_ записей",
          "sInfoEmpty": "Записей нет.",

          "oPaginate": {
            "sFirst": "Первая",
            "sLast": "Последняя",
            "sNext": "Следующая",
            "sPrevious": "Предыдущая",
          },
          "sSearch": "Поиск",
          "sInfoFiltered": "(отфильтровано из _MAX_ записи/(ей)"
        }
      });
      $("[data-fancybox]").fancybox({
        // Options will go here
      });
      $('ul.tabs li').click(function() {
        var tab_id = $(this).attr('data-tab');

        $('ul.tabs li').removeClass('current');
        $('.tab-content').removeClass('current');

        $(this).addClass('current');
        $("#" + tab_id).addClass('current');
      })

      $(document).on('selectmenuchange', 'select[name=groups_parts]', function() {
        var value = $(this).val();
        var this_parent = $(this).parent().parent().parent();
        var cat = $('input[name="cat_parts_hidden"]').val();
        var model = $('input[name="model_id_parts_hidden"]').val();
        var serial = $('input[name="serial_parts_hidden"]').val();
        if (value) {

          $.get("/ajax.php?type=get_parts&group=" + value + "&serial=<?= $content['serial']; ?>&model_id=<?= $content['model']['id']; ?>&cat=<?= $content['model']['cat']; ?>", function(data) {
            /*var obj = jQuery.parseJSON(data);
            $('input[name=title]').val(obj.title);  */
            this_parent.find($('select[name="parts_parts[]"]')).html(data.html).selectmenu("refresh");
            $('input[name="serial_parts_hidden"]').val(value);
            $('.add_to_list').show();

          });

        }


        return false;
      });



      $('input[type="text"]').tooltipster({
        trigger: 'custom',
        position: 'bottom',
        animation: 'grow',
        timer: 6000,
        theme: 'tooltipster-shadow'
      });
      $('select').tooltipster({
        trigger: 'custom',
        position: 'bottom',
        animation: 'grow',
        timer: 6000,
        theme: 'tooltipster-shadow'
      });
      $.validator.setDefaults({
        ignore: ""
      });

      jQuery.extend(jQuery.validator.messages, {
        required: "Обязательно к заполнению!"
      });


      <?php if (\models\User::hasRole('admin')) { ?>

        $(".edit_message").click(function() {
          var id = $(this).data('id');
          var current_div = $(this).parent().parent();

          current_div.find('[name="message_edit"]').toggle();
          current_div.find('span').toggle();

          return false;

        });

        $('[name="message_edit"]').change(function() {
          var id = $(this).data('id');
          var value = $(this).val();
          var current_span = $(this).parent().find('span');

          $.post("/ajax.php?type=edit_message_by_id", {
            id: id,
            value: value
          }, function(data) {
            current_span.html(value);
          });

        });

        $(".delete_message").click(function() {
          var id = $(this).data('id');
          var current_div = $(this).parent().parent();
          current_div.hide();

          $.get("/ajax.php?type=del_message_by_id&id=" + id, function(data) {});

          return false;

        });

      <?php } ?>


    });
  </script>
  <script src="/js/ajaxupload.3.5.dev.js"></script>

  <script>
    $(document).ready(function() {

      let uploadedPhotoFlag = false;
      let formBlockedFlag = false;

      $('#send').on('submit', function() {
        if (formBlockedFlag) {
          return false;
        }
        formBlockedFlag = true;
        let textInput = this.querySelector('[name="answer"]');
        if (!textInput.value.length && !uploadedPhotoFlag) {
          alert('Пожалуйста, введите сообщение или прикрепите фото.');
          formBlockedFlag = false;
          return false;
        }
      });

      var maxPhotos = 50;

      $('body').on('click', ".remove_preview", function(e) {
        const $btn = $(this);
        $.get("?ajax=del-photo&path=" + $btn.parent().find('[data-file-path]').val());
        $(this).parent().remove();
        var total = $("#files li").length;
        if (total >= maxPhotos) {
          $("#upload").fadeOut();
        } else {
          $("#upload").fadeIn();
        }
        return false;
      });
      var btnUpload = $('#upload');
      var status = $('#status');
      new AjaxUpload(btnUpload, {
        action: '/js/upload-file-dev.php',
        name: 'uploadfile[]',
        onSubmit: function(file, ext) {
          /*if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){
            // extension is not allowed
            status.text('Можно загружать только JPG, PNG или GIF файлы');
            return false;
          }*/
          status.html('<div><img src="350.gif" style="vertical-align: middle; border: 0px; margin: 0 2px 0 0;"/><span style="vertical-align: middlebackground: #fff;">Загрузка...Подождите пока файл/ы будет загружен.</span></div>');
        },
        onComplete: function(file, response) {
          //On completion clear the status
          status.text('');
          //Add uploaded file to list
          if (response !== "error") {

            var resp = $.parseJSON(response);
            var fl = 0;
            for (a = 0; a < resp.length; a++) {
              if (resp[a] == 'false') {
                fl++;
              }
            }
            if (fl == resp.length) {
              status.text('Можно загружать только JPG, PNG или GIF файлы');
              return false;
            }
            if (fl > 0) {
              status.text('Можно загружать только JPG, PNG или GIF файлы. Один или несколько файлов не соответсвуют формату. Эти файлы не были загружены.');
            }

            var total = $("#files li").length + resp.length;
            if (total >= maxPhotos) {
              $("#upload").fadeOut();
            } else {
              $("#upload").fadeIn();
            }

            for (a = 0; a < resp.length - fl; a++) {
              if (resp[a] != 'false') {
                $('<li class="adm-media-item"></li>').appendTo('#files').html('<div class="img"><span style="background: #fff;"><img style="max-height:100px;max-width: 150px;" src="' + resp[a] + '" alt=""/></span></div><a href="' + resp[a] + '" class="del  remove_preview"></a><input type="hidden" data-file-path name="files_preview[]" value="' + resp[a] + '" />').addClass('success');
                uploadedPhotoFlag = true;
              }
            }
            const $mess = $('[name="answer"]');
            if (!$mess.val()) {
              $mess.val('Добавлено фото');
            }
            status.html('<span style="color:red">Фото отправляется...</span>');
            $('#send').submit();
          } else {
            $('<li></li>').appendTo('#files').text("Ошибка").addClass('error').fadeOut(5000);
          }
        }
      });

      if (window.location.hash == '#focus') {
        setTimeout(function() {
          $('textarea[name="answer"]').focus();
        }, 0);
      }

      $(document).on('change selectmenuchange', 'input, textarea, select', function() {
        if (this.id == 'summary-status-select') {
          return;
        }
      });


    });
  </script>
  <style>
    ul.tabs {
      margin: 0px;
      padding: 0px;
      list-style: none;
    }

    ul.tabs li {
      background: none;
      color: #222;
      display: inline-block;
      padding: 10px 15px;
      cursor: pointer;
    }

    ul.tabs li.current {
      background: #ededed;
      color: #222;
    }

    .tab-content {
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current {
      display: inherit;
    }
  </style>
  <style>
    .date-picker-wrapper .drp_top-bar .apply-btn.disabled {
      width: auto !important;
    }

    .date-picker-wrapper .drp_top-bar .apply-btn {
      width: auto !important;
    }

    .redactor-editor {
      text-align: left;
    }

    .dataTables_filter input {
      background: none !important;
      box-shadow: none !important;
      padding: 2px !important;
      width: auto !important;
    }

    #question {
      background: #FFF1EB;
      width: 100%;
    }

    #question td {
      padding: 5px;
      border: 1px solid #fff;
    }

    #question2 {
      background: #FFFFEB;
      width: 100%;
    }

    #question2 td {
      padding: 5px;
      border: 1px solid #fff;
    }

    #answer {
      background: #F1FFE5;
      width: 100%;
    }

    #answer td {
      padding: 5px;
      border: 1px solid #fff;
    }

    .red.odd {
      background: rgba(255, 124, 92, 0.08) !important;
    }

    .red.even td {
      background: rgba(255, 124, 92, 0.08) !important;
    }

    .red.even {
      background: rgba(255, 124, 92, 0.08) !important;
    }

    .red.odd td {
      background: rgba(255, 124, 92, 0.08) !important;
    }

    .green.odd {
      background: rgba(255, 184, 112, 1) !important;
    }

    .green.even td {
      background: rgba(255, 184, 112, 1) !important;
    }

    .green.even {
      background: rgba(255, 184, 112, 1) !important;
    }

    .green.odd td {
      background: rgba(255, 184, 112, 1) !important;
    }

    .sorting_desc:after {
      display: none !important;
    }
  </style>
  <style>
    .remodal-cancel,
    .remodal-confirm {
      min-width: 160px !important;
    }
  </style>
  <!-- New codebase -->
  <link href="/_new-codebase/front/modules/dashboard/css/ui.css" rel="stylesheet" />
  <link href="/_new-codebase/front/templates/main/css/repair-card/repair-card.css" rel="stylesheet">
  <link href="/_new-codebase/front/templates/main/css/form.css" rel="stylesheet">
  <link href="/_new-codebase/front/modules/repair-card/support/attention.css" rel="stylesheet">
  <!-- Aside controls -->
  <link href="/_new-codebase/front/components/aside-controls/css/aside-controls.css" rel="stylesheet">
</head>

<body>
  <?php
  if ($repairStatus == 'Есть вопросы' && models\User::hasRole('service')) {
    echo '<div class="top-message top-message_alert" style="text-align:center">Пожалуйста, внесите исправления в карточку и отправьте на проверку.</div>';
  }
  ?>
  <div class="viewport-wrapper">

    <div class="site-header">
      <div class="wrapper">

        <div class="logo">
          <a href="/dashboard/"><img src="<?= $config['url']; ?>i/logo.png?v=1" alt="" /></a>
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

    <div class="wrapper" style="max-width: 1170px">

      <?= top_menu_admin(); ?>

      <div class="adm-tab">

        <?= getSummaryHTML(models\RepairCard::getSummary($content['id'])); ?>

        <?= menu_dash(); ?>

      </div><!-- .adm-tab -->
      <br>
      <!-- Меню вкладок -->
      <section class="layout__mb_md">
        <?= getTabsHTML(UI::getTabs(User::getData('role'))); ?>
      </section>
      <h2>Процессинг</h2>

      <?php
      $stepsNavHTML = getStepsNavHTML(\models\RepairCard::getStepsNav($content['id'], 'support'));
      echo $stepsNavHTML;
      ?>


      <div class="adm-form" style="padding-top:0;">

        <!--  <div class="item">
              <div class="level">Номер квитанции РСЦ:</div>
              <div class="value">
                <input type="text" name="rsc" value="<?= $content['rsc']; ?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Заказ-наряд клиента:</div>
              <div class="value">
                <input type="text" name="zakaz_client" value="<?= $content['zakaz_client']; ?>"  />
              </div>
            </div>
   <br><br>  -->


        <div class="tab-content current" style="    padding-bottom: 60px;">

          <?php
          if (!User::hasRole('service')) {
            attentionHTML(models\repair\Attention::get($content['model_id'], $content['serial']), User::hasRole('admin'));
          }
          ?>
          <?php if (User::hasRole('admin')) : ?>
            <p style="margin: 16px 0;"><a href="/log/?object=<?= $content['id']; ?>" target="_blank">Открыть логи ремонта</a></p>
          <?php endif; ?>
          <form id="send" class="sendform" method="POST">
            <div class="adm-form">
              <div id="support-container">
                <br>

                <?php

                if (check_feedback($content['id'])) {
                  $feedback_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `repair_id` = \'' . mysqli_real_escape_string($db, $content['id']) . '\' LIMIT 1;'));

                  if (\models\User::hasRole('admin')) {
                    mysqli_query($db, 'UPDATE `feedback_admin` SET
`read_admin` = 1
WHERE `id` = \'' . mysqli_real_escape_string($db, $feedback_info['id']) . '\' LIMIT 1
;') or mysqli_error($db);
                  } else {
                    mysqli_query($db, 'UPDATE `feedback_admin` SET
`read` = 1
WHERE `id` = \'' . mysqli_real_escape_string($db, $feedback_info['id']) . '\' LIMIT 1
;') or mysqli_error($db);
                  }

                  $feedback = mysqli_query($db, 'SELECT * FROM `feedback_messages` WHERE `feedback_id` = \'' . mysqli_real_escape_string($db, $feedback_info['id']) . '\';');
                  if (mysqli_num_rows($feedback) != false) {

                    if (\models\User::hasRole('admin')) {


                      while ($row = mysqli_fetch_array($feedback)) {
                        if ($row['user_type'] == 1) {
                          echo '
            <table id="question">
            <tr>
            <td width="100px">СЦ:</td>
            <td width="200px">' . date('d.m.Y H:i', strtotime($row['date'])) . '</td>
             <td style="text-align:left;" class="message_orig"><textarea style="display:none;width: 100%;height: 200px;font-size: inherit;" name="message_edit" data-id="' . $row['id'] . '">' . $row['message'] . '</textarea><span>' . $row['message'] . '</span></td>
             <td class="editable linkz" style="width: 40px;background: #ededed;    border: 0px;"><a class="t-3 edit_message" data-id="' . $row['id'] . '" href="#" ></a><a class="t-5 delete_message" data-id="' . $row['id'] . '" style="float:" onclick="return confirm(\'Подтвердите удаление сообщения\');" href="#"></a></td>
           </tr>
            </table>
            <br />';
                        } else {
                          echo '
            <table id="answer">
            <tr>
            <td width="100px">Поддержка</td>
            <td width="200px">' . date('d.m.Y H:i', strtotime($row['date'])) . '</td>
            <td style="text-align:left;" class="message_orig"><textarea style="display:none;width: 100%;height: 200px;font-size: inherit;" name="message_edit" data-id="' . $row['id'] . '">' . $row['message'] . '</textarea><span>' . $row['message'] . '</span></td>
             <td class="editable linkz" style="width: 40px;background: #ededed;   border: 0px;"><a class="t-3 edit_message" data-id="' . $row['id'] . '" href="#" ></a><a class="t-5 delete_message" data-id="' . $row['id'] . '" style="float:" onclick="return confirm(\'Подтвердите удаление сообщения\');" href="#"></a></td>
            </tr>
            </table><br>';
                        }
                      }
                    } else {


                      while ($row = mysqli_fetch_array($feedback)) {
                        if ($row['user_type'] == 1) {
                          echo '
            <table id="question">
            <tr>
            <td width="100px">СЦ:</td>
            <td width="200px">' . date('d.m.Y H:i', strtotime($row['date'])) . '</td>
            <td style="text-align:left;">' . $row['message'] . '</td>
            </tr>
            </table>
            <br />';
                        } else {
                          echo '
            <table id="answer">
            <tr>
            <td width="100px">Поддержка</td>
            <td width="200px">' . date('d.m.Y H:i', strtotime($row['date'])) . '</td>
            <td style="text-align:left;">' . $row['message'] . '</td>
            </tr>
            </table><br>';
                        }
                      }
                    }
                  }
                }


                ?>
              </div>

              <?php if (in_array($repairStatus, ["Отклонен", "Подтвержден", "Выдан"])) : ?>
                <div style="padding: 8px;font-weight: 600;color: red;">Вопросы и пожелания можно направлять по адресам: <a href="mailto:kan@r97.ru" target="_blank">kan@r97.ru</a>, <a href="mailto:service2@harper.ru" target="_blank">service2@harper.ru</a></div>
              <?php endif; ?>

              <?php
              $acceptanceFilledFlag = getAcceptanceFilledFlag($content);
              if (\models\User::hasRole('admin', 'slave-admin') || (!in_array($repairStatus, ["Оплачен", "Подтвержден", "Отклонен", "Выдан"]) && $acceptanceFilledFlag)) :
              ?>
                <div class="item item-media" style="width: 100%;">
                  <div class="adm-add">
                    <div id="upload"><a href=""><u>Добавить изображение</u></a></div>
                  </div>
                  <span id="status"></span>

                  <ul id="files">
                    <?= $content['img_uploaded']; ?>
                  </ul>

                </div>



                <div id="add_form">

                  <div class="item" style="display: block;  width: 100%;">
                    <div class="level" style="display: block;  width: 100%;">Запрос в службу поддержки:</div>
                    <div class="value" style="display: block;  width: 100%;">
                      <div class="adm-w-text" style="border:0px;">
                        <textarea id="redactor_text" name="answer" rows="5"></textarea>
                      </div>
                    </div>
                  </div>


                  <div class="adm-finish">
                    <div class="save">
                      <input type="hidden" name="send" value="1" />
                      <input type="hidden" name="changed" value="" />
                      <button type="submit" class="save_ans submitko">Отправить</button>
                    </div>
                  </div>


                </div>

              <?php elseif (!$acceptanceFilledFlag) : ?>

                <div>Пожалуйста, заполните информацию на вкладке «<a href="/edit-repair/<?= $content['id']; ?>/">Приемка</a>» перед тем, как отправить сообщение.</div>

              <?php endif; ?>

            </div>

          </form>

        </div>

      </div>

      <?= $stepsNavHTML; ?>

    </div>
  </div>
  <!-- New codebase -->
  <script src="/_new-codebase/front/vendor/fancybox/jquery.fancybox.min.js"></script>
  <script src='/_new-codebase/front/components/repair-card/repair-card.js?v=1.02'></script>
  <script src="/_new-codebase/front/components/status/status.js"></script>
  <script src="/_new-codebase/front/modules/repair-card/support/attention.js"></script>
  <!-- Aside controls -->
  <script src="/_new-codebase/front/components/request.js"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/confirm-approve-window.js"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/save-parts-window.js"></script>
  <script src="/_new-codebase/front/components/aside-controls/js/aside-controls.js"></script>
  <!-- / Aside controls -->
  <div id="aside-controls-json" style="display: none"><?= json_encode(models\RepairCard::getAsideControls($content['id'])); ?></div>
  <div id="repair-data-json" style="display: none"><?= json_encode(['id' => $content['id'], 'model_id' => $content['model_id']]); ?></div>
  <div id="user-data-json" style="display: none"><?= json_encode(['id' => models\User::getData('id'), 'role' => models\User::getData('role')]); ?></div>
</body>

</html>

<?php


function sendEmailNotification(array $serviceData, $repairID)
{
  global $config;
  $mes = '<html>
        <body>
        <p>Здравствуйте!</p>
        <p>Вам пришло сообщение от администратора: <a href="https://' . $_SERVER["SERVER_NAME"] . '/edit-repair/' . $repairID . '/step/6/">ремонт №' . $repairID . '</a></p>
       <p>' . $config['email_footer'] . '</p>
        </body>

      </html>';
  $subject = "Новое сообщение по ремонту №" . $repairID;
  $headers  = "From: robot@" . str_replace('www.', '', $_SERVER["SERVER_NAME"]) . "\r\n";
  $headers .= "Content-type: text/html; charset=utf-8 \r\n";
  $email = $serviceData['email'];
  mail($email, $subject, $mes, $headers);
}


function uploadPreviews(array $previews)
{
  $res = [];
  foreach ($previews as $preview) {
    if (strpos($preview, 'digitalocean') !== false) {
      $res[] = $preview;
      continue;
    }
    try {
      $file = new core\File($preview);
      if (!$file->exists()) {
        continue;
      }
      $url = adapters\DigitalOcean::uploadFile($preview, 'uploads/photos/support/' . date('mY') . '/' . adapters\DigitalOcean::makeFilename() . '.' . strtolower($file->ext));
      if (empty($url)) {
        continue;
      }
      $res[] = $url;
    } catch (Exception $e) {
      continue;
    }
  }
  if (!$res) {
    return '';
  }
  return json_encode($res);
}

function getAcceptanceFilledFlag(array $repair)
{
  if ((empty($repair['serial']) && empty($repair['no_serial'])) && empty($repair['bugs'])) {
    return false;
  }
  return true;
}
