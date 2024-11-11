<?php

use models\User;

$content = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `billing` WHERE `service_id` = \''.mysqli_real_escape_string($db, User::getData('id')).'\' LIMIT 1;'));

# Сохраняем:
if ($_POST['send'] == 1) {

if ($content) {
$test = mysqli_query($db, 'UPDATE `billing` SET
`bank_name` = \''.mysqli_real_escape_string($db, $_POST['bank_name']).'\',
`bik` = \''.mysqli_real_escape_string($db, $_POST['bik']).'\',
`sc1` = \''.mysqli_real_escape_string($db, $_POST['sc1']).'\',
`sc2` = \''.mysqli_real_escape_string($db, $_POST['sc2']).'\',
`accountant` = \''.mysqli_real_escape_string($db, $_POST['accountant']).'\',
`agree` = \''.mysqli_real_escape_string($db, $_POST['agree']).'\',
`chp` = \''.mysqli_real_escape_string($db, $_POST['chp']).'\'
WHERE `service_id` = '.User::getData('id').'
;') or mysqli_error($db);
} else {

$test = mysqli_query($db, 'INSERT INTO `billing` (
`service_id`,
`bank_name`,
`bik`,
`sc1`,
`sc2`,
`accountant`,
`agree`,
`chp`
) VALUES (
\''.mysqli_real_escape_string($db, User::getData('id')).'\',
\''.mysqli_real_escape_string($db, $_POST['bank_name']).'\',
\''.mysqli_real_escape_string($db, $_POST['bik']).'\',
\''.mysqli_real_escape_string($db, $_POST['sc1']).'\',
\''.mysqli_real_escape_string($db, $_POST['sc2']).'\',
\''.mysqli_real_escape_string($db, $_POST['accountant']).'\',
\''.mysqli_real_escape_string($db, $_POST['agree']).'\',
\''.mysqli_real_escape_string($db, $_POST['chp']).'\'
);') or mysqli_error($db);

notice_add('Запрос на проверку платежных данных.', 'Поступил новый запрос на проверку платежных данных. ', 2, 'https://crm.r97.ru/billing-info/'.User::getData('id').'/');
}


header('Location: '.$_SERVER['HTTP_REFERER']);
exit;
}



function cities($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `cityfull`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['fcity_id']) {
      $content .= '<option selected value="'.$row['fcity_id'].'">'.$row['fcity_name'].'</option>';
      } else {
       $content .= '<option value="'.$row['fcity_id'].'">'.$row['fcity_name'].'</option>';
      }
      }
    return $content;
}

$content = stripslashes_array($content);

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

  <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>

<script >
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
        }});

  $('ul.tabs li').click(function(){
    var tab_id = $(this).attr('data-tab');

    $('ul.tabs li').removeClass('current');
    $('.tab-content').removeClass('current');

    $(this).addClass('current');
    $("#"+tab_id).addClass('current');
  })


$('input[name="chp"]').change(function () {
    if ($(this).prop("checked")) {
        //do the stuff that you would do when 'checked'
        $('input[name="accountant"]').prop( "disabled", true );
        return;
    }
    $('input[name="accountant"]').prop( "disabled", false);
});

 $('.select2').select2();

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

           <br>
           <h2>Платежная информация <?=($content['aproved'] == 1) ? '<img style="    margin-top: 7px;" src="/img/true.png" title="Подтверждено администратором">' : '';?></h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

                  <div class="item">
              <div class="level">Полное название банка:</div>
              <div class="value">
                <input type="text" style="width: 80% !important;" name="bank_name" value="<?=$content['bank_name'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">БИК:</div>
              <div class="value">
                <input type="text" style="width: 80% !important;" name="bik" value="<?=$content['bik'];?>"  />
              </div>
            </div>


                  <div class="item">
              <div class="level">Кор.счет банка:</div>
              <div class="value">
                <input type="text" style="width: 80% !important;" name="sc1" value="<?=$content['sc1'];?>" placeholder="Только для ООО, ЗАО, ОАО" />
              </div>
            </div>

                  <div class="item">
              <div class="level">Номер счета в банке:</div>
              <div class="value">
                <input type="text" style="width: 80% !important;" name="sc2" value="<?=$content['sc2'];?>"  />
              </div>
            </div>


                  <div class="item">
              <div class="level">ФИО бухгалтера:</div>
              <div class="value">
                <input type="text" style="width: 80% !important;" <?=($content['chp'] == 1) ? 'disabled' : ''?> name="accountant" value="<?=$content['accountant'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">№ и дата договора <br>(в формате: №1 от 15 июля 2017):</div>
              <div class="value">
                <input type="text" style="width: 80% !important;"  placeholder="Заполняют сотрудники Harper"  name="agree" value="<?=$content['agree'];?>"  />
              </div>
            </div>

              <div class="item item-media">
      <div class="check" style="   display: inline-block;">
            <label><input type="checkbox" name="chp" value="1" <?=($content['chp'] == 1) ? 'checked' : ''?>/></label> &nbsp;&nbsp;&nbsp;&nbsp;
          </div><div class="level" style="    margin: 0 auto;   display: inline-block;">Я - индивидуальный предприниматель</div>



</div><!-- .item-media -->

             <?php if ($content['aproved'] != 1) { ?>
             <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <div class="error_valid" style="color:red;display:none;">Вы не заполнили все обязательные поля, просмотрите анкету внимательно еще раз!</div>
              <button type="submit" >Сохранить</button>
            </div>
            </div>
            <?php } ?>

        </div>

      </form>




        </div>
  </div>
</body>
</html>