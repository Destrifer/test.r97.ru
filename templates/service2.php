<?php

function content_service($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `users` WHERE `id` = '.$id.' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      //$content['services_child']

      $sql2 = mysqli_query($db, 'SELECT * FROM `services_link` WHERE `service_parent` = '.$id);
      if (mysqli_num_rows($sql2) != false) {
      while ($row2 = mysqli_fetch_array($sql2)) {

      $content['services_child'] .= '<div class="value2"><select name="services_child[]"><option value="">Выберите сервис</option>'.services_select($row2['service_child']).'</select> <a href="#" class="remove_field del"></a></div>';

      }
      }


      }
    return $content;
}

# Сохраняем:
if ($_POST['send'] == 1) {

mysqli_query($db, 'UPDATE `users` SET
`email` = "'.mysqli_real_escape_string($db, $_POST['email']).'",
`phone` = "'.mysqli_real_escape_string($db, $_POST['tel']).'" 
WHERE `id` = \''.$_GET['service_id'].'\' LIMIT 1') or mysqli_error($db);


admin_log_add('Обновлен сервис #'.$_GET['id']);

mysqli_query($db, 'DELETE FROM `services_link` WHERE `service_parent` = \''.mysqli_real_escape_string($db, $_GET['service_id']).'\';') or mysqli_error($db);

foreach ($_POST['services_child'] as $service) {

mysqli_query($db, 'INSERT INTO `services_link` (
`service_child`,
`service_parent`
) VALUES (
\''.mysqli_real_escape_string($db, $service).'\',
\''.mysqli_real_escape_string($db, $_GET['service_id']).'\'
);') or mysqli_error($db);

}

header('Location: '.$config['url'].'dashboard/services/');
}

if ($_POST['send'] == 2) {
$answer = changepassadmin($_POST['new'], $_GET['service_id']);
}

if ($_GET['service_id']) {
$content = content_service($_GET['service_id']);
$content['service_name'] = htmlspecialchars($content['service_name']);
}

function services_select($cat_id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `requests` where `name` != \'\';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['user_id']) {
      $content .= '<option selected value="'.$row['user_id'].'">'.htmlspecialchars($row['name']).'</option>';
      } else {
       $content .= '<option value="'.$row['user_id'].'">'.htmlspecialchars($row['name']).'</option>';
      }
      }
    return $content;
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
<link href="<?=$config['url'];?>css/fonts.css" rel="stylesheet" />
<link href="<?=$config['url'];?>css/style.css" rel="stylesheet" />
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
        }});

    var max_fields2      = 50; //maximum input boxes allowed
    var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
    var add_button2      = $(".add_field_button2"); //Add button ID

    var x2 = 1; //initlal text box count
    $(add_button2).click(function(e){ //on add input button click
        e.preventDefault();
        if(x2 < max_fields2){ //max input box allowed
            x2++; //text box increment
            $(wrapper2).append('<div class="value2"><select name="services_child[]"><option value="">Выберите сервис</option><?=services_select();?></select> <a href="#" class="remove_field del"></a></div>'); //add input box
            $('select:not(.nomenu)').selectmenu({
            open: function(){
              $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
            }}).addClass("selected_menu");
        }
    });

    $(wrapper2).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    });

} );

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

  </div><!-- .adm-tab -->
     <br> <br>
   <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/service-info-full/<?=$content['id'];?>/" class="button">Анкета сервиса</a>
 <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/billing-info/<?=$content['id'];?>/" class="button">Платежная информация</a>
 <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/documents/<?=$content['id'];?>/" class="button">Документы</a>
     </div>


           <br>
           <h2 style="text-align: center;">Редактирование С/Ц</h2>
           <br>
           <?php if ($answer) {
           echo '<h2><b>'.$answer.'</b></h2>';
           }?>



        <form id="send" method="POST">
            <div class="adm-form">

            <div class="item">
              <div class="level">Тип СЦ:</div>
              <div class="value">
              <select name="type_id">
               <option>Выберите список СЦ</option>
               <option value="2" <?php if ($content['type_id'] == 2) { echo 'selected';}?>>ФСЦ</option>
               <option value="3" <?php if ($content['type_id'] == 3) { echo 'selected';}?>>СЦ</option>
              </select>
              </div>
            </div>

                    <div class="item">
              <div class="level">Email:</div>
              <div class="value">
                <input type="text" readonly name="email" value="<?=$content['email'];?>"  />
              </div>
            </div>

                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
        </div>

      </form><br>


        </div>
  </div>
</div>
</body>
</html>