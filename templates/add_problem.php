<?php
# Сохраняем:
if ($_POST['send'] == 1) {

  

  $typeID = 0;
  switch ($_POST['type']) {
    case 'Всегда блочный ремонт':
      $typeID = 1;
      break;
    case 'Всегда компонентный ремонт':
      $typeID = 2;
      break;
    case 'Замена аксессуаров':
      $typeID = 3;
      break;
    case 'АНРП':
      $typeID = 4;
      break;
    case 'АТО':
      $typeID = 5;
      break;
  }

mysqli_query($db, 'INSERT INTO `details_problem` (
`name`,
`type`,
`type_id`,
`work_type`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['name']).'\',
\''.mysqli_real_escape_string($db, $_POST['type']).'\',
'.$typeID.',
\''.mysqli_real_escape_string($db, $_POST['work_type']).'\'
);') or mysqli_error($db);
$id = mysqli_insert_id($db);

admin_log_add('Добавлена новая проблема - '.$_POST['name']);  

if ($_POST['problem_link']) {

foreach ($_POST['problem_link'] as $link)  {
    mysqli_query($db, 'INSERT INTO `problem_link` (
    `problem_id`,
    `repair_type`
    ) VALUES (
    \''.mysqli_real_escape_string($db, $id).'\',
    \''.mysqli_real_escape_string($db, $link).'\'
    );') or mysqli_error($db);
}

}

header('Location: '.$config['url'].'problems/');
}

?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">

<script >
// Таблица
$(document).ready(function() {
    $('#table_content').dataTable({
      stateSave:false,
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

  $('ul.tabs li').click(function(){
    var tab_id = $(this).attr('data-tab');

    $('ul.tabs li').removeClass('current');
    $('.tab-content').removeClass('current');

    $(this).addClass('current');
    $("#"+tab_id).addClass('current');
  })

} );

</script>
<style>
ul.tabs{
      margin: 0px;
      padding: 0px;
      list-style: none;
    }
    ul.tabs li{
      background: none;
      color: #222;
      display: inline-block;
      padding: 10px 15px;
      cursor: pointer;
    }

    ul.tabs li.current{
      background: #ededed;
      color: #222;
    }

    .tab-content{
      display: none;
      background: #ededed;
      padding: 15px;
    }

    .tab-content.current{
      display: inherit;
    }
</style>
</head>

<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="/i/logo.png" alt=""/></a>
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
           <br>
           <h2>Добавление причины отказа детали</h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">


                  <div class="item">
              <div class="level">Название:</div>
              <div class="value">
                <input type="text" name="name" style="    width: 400px;"  value="<?=$content['name'];?>"  />
              </div>
            </div>
            <br><br>
       <div class="item">
              <div class="level">Тип ремонта:</div>
              <div class="value">
              <select name="type">
               <option value="" selected>Выберите вариант</option>
               <option value="АТО" >АТО</option>
               <option value="АНРП" >АНРП</option>
               <option value="Всегда блочный ремонт" >Всегда блочный ремонт</option>
               <option value="Всегда компонентный ремонт" >Всегда компонентный ремонт</option>
               <option value="Замена аксессуаров" >Замена аксессуаров</option>
              </select>
              </div>
            </div>

            <div class="item">
              <div class="level">Вид работы:</div>
              <div class="value">
              <select name="work_type">
               <option value="repair">Ремонт</option>
               <option value="nonrepair">Без ремонта</option>
               <option value="diag">Тестирование</option>
              </select>
              </div>
            </div>

<div class="adm-finish">
<h3>Вид ремонта:</h3>
            <ul>
              <?php
              $sql2 = mysqli_query($db, 'SELECT * FROM `repair_type` ;');
              if (mysqli_num_rows($sql2) != false) {
                    while ($row2 = mysqli_fetch_array($sql2)) {
                     echo '<li style="padding:5px 0px;"><label><input type="checkbox" name="problem_link[]" value="'.$row2['id'].'" '.$check.'/>'.$row2['name'].'</label></li>';
                    }
              }

              ?>
            </ul>
            </div>

                <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="submit" >Сохранить</button>
            </div>
            </div>
        </div>

      </form>




        </div>
  </div>
</div>
</body>
</html>