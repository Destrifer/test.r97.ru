<?php

# Получаем
function content_config($id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'configuration` WHERE `id` = '.$id.' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

# Получаем список материалов
function content_list() {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'configuration`;');
      while ($row = mysqli_fetch_array($sql)) {
      $content_list .= '<tr><td>'.$row['id'].'</td><td>'.$row['desc'].'</td><td class="linkz" align="center"><a class="t-3" href="/config/'.$row['id'].'/" ></a></td>
      </tr>';
      }
    return $content_list;
}


if ($_GET['id']) {
$content = stripslashes_array(content_config($_GET['id']));
}


# Сохраняем:
if ($_POST['send'] == 1) {

if ($_FILES['filename']['size'] != 0){
copy($_FILES["filename"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'].'/files/tech/'.$_FILES['filename']['name']);
$file = $config['url'].'files/tech/'.$_FILES['filename']['name'];
} else if ($_POST['img'] != '') {
$file = $_POST['img'];
}

if ($file) {
mysqli_query($db, 'UPDATE `'.$_COOKIE['lang'].'configuration` SET
`value` = \''.mysqli_real_escape_string($db, $file).'\'
WHERE `id` = \''.$_GET['id'].'\' LIMIT 1') or mysqli_error($db);
} else {
mysqli_query($db, 'UPDATE `'.$_COOKIE['lang'].'configuration` SET
`value` = \''.mysqli_real_escape_string($db, $_POST['value']).'\'
WHERE `id` = \''.$_GET['id'].'\' LIMIT 1') or mysqli_error($db);
}

admin_log_add('Обновлен конфиг #'.$_GET['id']);

header('Location: '.$config['url'].'config/');
}


?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />
<link rel="stylesheet" href="<?=$config['url'];?>redactor/redactor.css" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="<?=$config['url'];?>js/jquery-ui.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.placeholder.min.js"></script>
<script src="<?=$config['url'];?>js/jquery.formstyler.min.js"></script>
<script src="<?=$config['url'];?>js/main.js"></script>

<script src="<?=$config['url'];?>notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$config['url'];?>css/datatables.css">
<style>
.redactor-editor {
  text-align:left;
  }
</style>
<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
      "dom": '<"top"flp<"clear">>rt<"bottom"ip<"clear">>', 
      "pageLength": <?=$config['page_limit'];?>,
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
        }});
} );

</script>
<script src="<?=$config['url'];?>redactor/redactor.min.js"></script>

<script src="<?=$config['url'];?>redactor/lang/ru.js"></script>
  <script >
  $(document).ready(
    function()		{
    $('#redactor_text').redactor({minHeight: 200, lang: 'ru', imageUpload: '/image_upload.php'});
    }

  );
  </script>
</head>

<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="<?=$config['url'];?>i/logo.png" alt=""/></a>
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

      <a href="/logout/">Выйти, <?=\models\User::getData('login');?></a>
    </div>

  </div>
</div><!-- .site-header -->

<div class="wrapper">

<?=top_menu_admin();?>

  <div class="adm-tab">

<?=menu_dash();?>

  </div></div><!-- .adm-tab --><br>
            <?php if ($_GET['id']) { ?>    <br><h1>Редактирование конфига &laquo;<?=$content['desc'];?>&raquo;:</h1>  <?php } ?>
       <?php if ($_GET['id']) { ?>
             <form id="send" method="POST" enctype="multipart/form-data">
            <div class="adm-form">

            <?php if ($content['type'] == 'file') { ?>


            <div class="item" style="width: 100%;    display: block;">
              <div class="level" style="text-align: left;">Файл:</div>
              <div class="value">
                <input style="width: 100%;" type="text" name="img" value="<?=$content['value'];?>"  />
              </div>
            </div>


            <div class="item" style="width: 100%;    display: block;">
              <div class="level" style="text-align: left;">Или загрузите новый файл:</div>
              <div class="value">
                <input style="width: 100%;" type="file" name="filename" />
              </div>
            </div>

            <?php } else if ($content['type'] == 'text') { ?>

                 <div class="item" style="width: 100%;    display: block;">
              <div class="level" style="text-align: left;">Значение:</div>
              <div class="value">
                <div class="adm-w-text" style="border:0px;">
                  <textarea name="value" cols="180" style="height:300px"><?=$content['value'];?></textarea>
                </div>
              </div>
            </div>

             <?php } else { ?>

                    <div class="item" style="display: block;  width: 100%;">
              <div class="level" style="display: block;  width: 100%;">Текст:</div>
              <div class="value" style="display: block;  width: 100%;">
                <div class="adm-w-text" style="border:0px;">
                 <textarea name="value" rows="5" id="redactor_text" width="100%" style="text-align:left;"><?=$content['value'];?></textarea>
                </div>
              </div>
            </div>

              <?php } ?>
                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
        </div>

      </form>

            <?php } else { ?>

  <h2>Конфигурации и блоки</h2>

  <div style="margin-top: 24px;">
              <a href="/dashboard-settings/" class="button" style="padding: 0 16px;">Настройки дашборда</a>
  </div>


  <div class="adm-catalog">


       <br>
  <table id="table_content" class="display" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left">ID</th>
                <th align="left">Название</th>
                <th>Операции</th>
            </tr>
        </thead>

        <tbody>
        <?=content_list();?>
        </tbody>
</table>
           </div>

            <?php } ?>



        </div>
       </div>
</body>
</html>