<?php

use program\core;

if (!empty(core\App::$URLParams['action'])) {
  switch (core\App::$URLParams['action']) {
    case 'save-settings':
      models\services\Settings::saveSettings('service', $_POST['service_id'], $_POST['settings']);
      header('Location: /service/' . $_POST['service_id'] . '/edit/');
      exit;
      break;
  }
}

function content_service($id)
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `users` WHERE `id` = ' . $id . ' LIMIT 1;');
  while ($row = mysqli_fetch_array($sql)) {
    $content = $row;
    //$content['services_child']

    $sql2 = mysqli_query($db, 'SELECT * FROM `services_link` WHERE `service_parent` = ' . $id);
    if (mysqli_num_rows($sql2) != false) {
      while ($row2 = mysqli_fetch_array($sql2)) {

        $content['services_child'] .= '<div class="value2"><select name="services_child[]"><option value="">Выберите сервис</option>' . services_select($row2['service_child']) . '</select> <a href="#" class="remove_field del"></a></div>';
      }
    }
  }
  return $content;
}


if ($_GET['service_id']) {
  $content = content_service($_GET['service_id']);
  $content['service_name'] = htmlspecialchars($content['service_name']);
}

function services_select($cat_id = '')
{
  global $db;
  $sql = mysqli_query($db, 'SELECT * FROM `requests` where `name` != \'\';');
  while ($row = mysqli_fetch_array($sql)) {
    if ($cat_id == $row['user_id']) {
      $content .= '<option selected value="' . $row['user_id'] . '">' . htmlspecialchars($row['name']) . '</option>';
    } else {
      $content .= '<option value="' . $row['user_id'] . '">' . htmlspecialchars($row['name']) . '</option>';
    }
  }
  return $content;
}

?>
<!doctype html>
<html>

<head>
  <meta charset=utf-8>
  <title>Редактирование СЦ - Панель управления</title>
  <link href="/css/fonts.css" rel="stylesheet" />
  <link href="/css/style.css" rel="stylesheet" />
  <script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"></script>
  <script src="/js/jquery-ui.min.js"></script>
  <script src="/js/jquery.placeholder.min.js"></script>
  <script src="/js/jquery.formstyler.min.js"></script>
  <script src="/js/main.js"></script>

  <script src="/notifier/js/index.js"></script>
  <link rel="stylesheet" href="/notifier/css/style.css">
  <link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
  <script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
  <script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
  <link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

  <script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="/css/datatables.css">
  <link href="/_new-codebase/front/templates/main/css/settings.css" rel="stylesheet" />

  <!-- New codebase -->
  <link href="/_new-codebase/front/templates/main/css/table.css" rel="stylesheet">

  <script >
    // Таблица
    $(document).ready(function() {
      $('#table_content').dataTable({
        "pageLength": 30,
        "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>',
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

      var max_fields2 = 50; //maximum input boxes allowed
      var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
      var add_button2 = $(".add_field_button2"); //Add button ID

      var x2 = 1; //initlal text box count
      $(add_button2).click(function(e) { //on add input button click
        e.preventDefault();
        if (x2 < max_fields2) { //max input box allowed
          x2++; //text box increment
          $(wrapper2).append('<div class="value2"><select name="services_child[]"><option value="">Выберите сервис</option><?= services_select(); ?></select> <a href="#" class="remove_field del"></a></div>'); //add input box
          $('select:not(.nomenu)').selectmenu({
            open: function() {
              $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
            }
          }).addClass("selected_menu");
        }
      });

      $(wrapper2).on("click", ".remove_field", function(e) { //user click on remove text
        e.preventDefault();
        $(this).parent('div').remove();
        x--;
      });

    });
  </script>
</head>

<body>

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

    <div class="wrapper">

      <?= top_menu_admin(); ?>

      <div class="adm-tab">

        <?= menu_dash(); ?>

      </div><!-- .adm-tab -->
      <br> <br>
      <div class="add">
        <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/service-info-full/<?= $content['id']; ?>/" class="button">Анкета сервиса</a>
        <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/prices-service/<?= $content['id']; ?>/" class="button">Тарифы сервиса</a>
        <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/billing-info/<?= $content['id']; ?>/" class="button">Платежная информация</a>
        <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/documents/<?= $content['id']; ?>/" class="button">Документы</a>
        <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/cats-service/<?= $content['id']; ?>/" class="button">Обслуживаемые категории</a>
        <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/models-service/<?= $content['id']; ?>/" class="button">Обслуживаемые модели</a>
        <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/transport-rate/<?= $content['id']; ?>/" class="button">Тарифы транспорт</a>
      </div>


      <br>
      <h2 style="text-align: center;">Редактирование СЦ</h2>
      <br>
      <?php if ($answer) {
        echo '<h2><b>' . $answer . '</b></h2>';
      } ?>

      <p><a href="/user/?id=<?= $content['id']; ?>" target="_blank">Открыть настройки пользователя СЦ</a></p>


      <br> <br>
      <hr> <br>
      <h2>Настройки:</h2>

      <form action="?action=save-settings" method="POST">

        <?php
        $settings = models\services\Settings::getSettingsByServiceID($_GET['service_id']);
        ?>
        <section class="param-section">
          <table class="table">
            <tbody>

              <tr>
                <td style="width: 30%; vertical-align: middle">
                  АНРП:
                </td>
                <td>
                  <select class="nomenu" name="settings[anrp_value]">
                    <option value="0">Не выбрано</option>
                    <option value="1" <?= (($settings['anrp_value'] == 1) ? 'selected' : ''); ?>>Оставлен на ответственное хранение</option>
                    <option value="2" <?= (($settings['anrp_value'] == 2) ? 'selected' : ''); ?>>Выдан на руки клиенту</option>
                  </select>
                </td>
              </tr>

              <tr>
                <td style="vertical-align: middle">
                  Автоматически одобрять
                  выездные ремонты и демонтаж:
                </td>
                <td>
                  <select class="nomenu" name="settings[auto_approve_out_dismant]">
                    <option value="0">Нет</option>
                    <option value="1" <?= ((!empty($settings['auto_approve_out_dismant'])) ? 'selected' : ''); ?>>Да</option>
                  </select>
                </td>

              </tr>
            </tbody>
          </table>

        </section>


        <div class="adm-finish">
          <div class="save">
            <button type="submit">Сохранить</button>
          </div>
        </div>

        <input type="hidden" name="service_id" value="<?= $_GET['service_id']; ?>">
      </form>

      <br>
      <hr> <br>
      <form method="POST">
        <h2>Дочерние сервисы:</h2>

        <div class="field input_fields_wrap2" style="margin-top: 20px;">
          <?= $content['services_child']; ?>
        </div>

        <div class="add adm-add">
          <a href="#" class="add_field_button2"><u>Добавить еще</u></a>
        </div>

        <div class="adm-finish">
          <div class="save">
            <input type="hidden" name="send" value="1" />
            <button type="submit">Сохранить</button>
          </div>
        </div>
    </div>

    </form>
    <br>
    <hr> <br>
  </div>
  </div>
  </div>
</body>

</html>