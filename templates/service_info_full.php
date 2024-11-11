<?php

$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
$content['cats'] = explode('|', $content['cat']);

# Сохраняем:
if ($_POST['send'] == 1) {

$test = mysqli_query($db, 'UPDATE `requests` SET
`name` = \''.mysqli_real_escape_string($db, $_POST['name']).'\',
`name_public` = \''.mysqli_real_escape_string($db, $_POST['name_public']).'\',
`type` = \''.mysqli_real_escape_string($db, $_POST['type']).'\',
`year` = \''.mysqli_real_escape_string($db, $_POST['year']).'\',
`country` = \''.mysqli_real_escape_string($db, $_POST['country']).'\',
`city` = \''.mysqli_real_escape_string($db, $_POST['city']).'\',
`adress` = \''.mysqli_real_escape_string($db, $_POST['adress']).'\',
`phisical_adress` = \''.mysqli_real_escape_string($db, $_POST['phisical_adress']).'\',
`phones` = \''.mysqli_real_escape_string($db, $_POST['phones']).'\',
`fax` = \''.mysqli_real_escape_string($db, $_POST['fax']).'\',
`post` = \''.mysqli_real_escape_string($db, $_POST['post']).'\',
`filials` = \''.mysqli_real_escape_string($db, $_POST['filials']).'\',
`position` = \''.mysqli_real_escape_string($db, $_POST['position']).'\',
`land` = \''.mysqli_real_escape_string($db, $_POST['land']).'\',
`general_fio` = \''.mysqli_real_escape_string($db, $_POST['general_fio']).'\',
`general_phone` = \''.mysqli_real_escape_string($db, $_POST['general_phone']).'\',
`general_email` = \''.mysqli_real_escape_string($db, $_POST['general_email']).'\',
`contact_fio` = \''.mysqli_real_escape_string($db, $_POST['contact_fio']).'\',
`contact_phone` = \''.mysqli_real_escape_string($db, $_POST['contact_phone']).'\',
`contact_email` = \''.mysqli_real_escape_string($db, $_POST['contact_email']).'\',
`priem` = \''.mysqli_real_escape_string($db, $_POST['priem']).'\',
`size` = \''.mysqli_real_escape_string($db, $_POST['size']).'\',
`repair_size` = \''.mysqli_real_escape_string($db, $_POST['repair_size']).'\',
`sklad_size` = \''.mysqli_real_escape_string($db, $_POST['sklad_size']).'\',
`peoples` = \''.mysqli_real_escape_string($db, $_POST['peoples']).'\',
`admins` = \''.mysqli_real_escape_string($db, $_POST['admins']).'\',
`engineers` = \''.mysqli_real_escape_string($db, $_POST['engineers']).'\',
`cat` = \''.mysqli_real_escape_string($db, implode('|', $_POST['cat'])).'\',
`cars` = \''.mysqli_real_escape_string($db, $_POST['cars']).'\',
`marks` = \''.mysqli_real_escape_string($db, $_POST['marks']).'\',
`marks_second` = \''.mysqli_real_escape_string($db, $_POST['marks_second']).'\',
`marks_no` = \''.mysqli_real_escape_string($db, $_POST['marks_no']).'\',
`war_repairs` = \''.mysqli_real_escape_string($db, $_POST['war_repairs']).'\',
`war_presale` = \''.mysqli_real_escape_string($db, $_POST['war_presale']).'\',
`nowar_repairs` = \''.mysqli_real_escape_string($db, $_POST['nowar_repairs']).'\',
`comments` = \''.mysqli_real_escape_string($db, $_POST['comments']).'\',
`req_name` = \''.mysqli_real_escape_string($db, $_POST['req_name']).'\',
`inn` = \''.mysqli_real_escape_string($db, $_POST['inn']).'\',
`kpp` = \''.mysqli_real_escape_string($db, $_POST['kpp']).'\',
`req_adress` = \''.mysqli_real_escape_string($db, $_POST['req_adress']).'\',
`req_adress_physic` = \''.mysqli_real_escape_string($db, $_POST['req_adress_physic']).'\',
`req_phones` = \''.mysqli_real_escape_string($db, $_POST['req_phones']).'\',
`req_fax` = \''.mysqli_real_escape_string($db, $_POST['req_fax']).'\',
`req_email` = \''.mysqli_real_escape_string($db, $_POST['req_email']).'\',
`req_gen_fio` = \''.mysqli_real_escape_string($db, $_POST['req_gen_fio']).'\',
`req_phone_gen` = \''.mysqli_real_escape_string($db, $_POST['req_phone_gen']).'\',
`req_gen_email`  = \''.mysqli_real_escape_string($db, $_POST['req_gen_email']).'\'
WHERE `user_id` = '.$_GET['id'].'
;') or mysqli_error($db);


admin_log_add('Обновлена анкета сервиса #'.$_GET['id']);

/*mysqli_query($db, 'UPDATE `users` SET
`active` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $content['user_id']).'\' LIMIT 1
;') or mysqli_error($db);

mysqli_query($db, 'UPDATE `requests` SET
`mod` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1
;') or mysqli_error($db);
*/
header('Location: /service/'.$_GET['id'].'/edit/');
}

function cities($cat_id, $country) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `cityfull` where `fcity_country` = '.$country.';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['fcity_id']) {
      $content .= '<option selected value="'.$row['fcity_id'].'">'.$row['fcity_name'].'</option>';
      } else {
       $content .= '<option value="'.$row['fcity_id'].'">'.$row['fcity_name'].'</option>';
      }
      }
    return $content;
}

function countries($country_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `countries` ;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($country_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
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

  $('ul.tabs li').click(function(){
    var tab_id = $(this).attr('data-tab');

    $('ul.tabs li').removeClass('current');
    $('.tab-content').removeClass('current');

    $(this).addClass('current');
    $("#"+tab_id).addClass('current');
  })

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

           <br>
           <h2>Анкета сервисного центра</h2>

  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

                  <div class="item">
              <div class="level">Название организации (полное юридическое):</div>
              <div class="value">
                <input type="text" name="name" value="<?=$content['name'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Название, под которым СЦ известен конечному потребителю:</div>
              <div class="value">
                <input type="text" name="name_public" value="<?=$content['name_public'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Тип СЦ:</div>
              <div class="value">
                <select name="type">
               <option>Выберите вариант</option>
               <option value="Независимый" <?php if ($content['type'] == 'Независимый') { echo 'selected';}?>>Независимый</option>
               <option value="Дилерский" <?php if ($content['type'] == 'Дилерский') { echo 'selected';}?>>Дилерский</option>
              </select>
              </div>
            </div>

                  <div class="item">
              <div class="level">Год основания:</div>
              <div class="value">
                <input type="text" name="year" value="<?=$content['year'];?>"  />
              </div>
            </div>


             <div class="item">
              <div class="level">Страна:</div>
              <div class="value">
              <select name="country">
               <option>Выберите вариант</option>
              <?=countries($content['country']);?>
              </select>
              </div>
            </div>

               <div class="item">
              <div class="level">Город:</div>
              <div class="value">
                <select name="city" class="select2 nomenu">
               <option value="">Выберите вариант</option>
               <?=cities($content['city'], $content['country']);?>
              </select>
              </div>
            </div>

                  <div class="item">
              <div class="level">Юридический адрес:</div>
              <div class="value">
                <input type="text" name="adress" value="<?=$content['adress'];?>"  />
              </div>
            </div>


                  <div class="item">
              <div class="level">Фактический адрес:</div>
              <div class="value">
                <input type="text" name="phisical_adress" value="<?=$content['phisical_adress'];?>"  />
              </div>
            </div>


                  <div class="item">
              <div class="level">Телефоны (с кодом города):</div>
              <div class="value">
                <input type="text" name="phones" value="<?=$content['phones'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Факс (с кодом города):</div>
              <div class="value">
                <input type="text" name="fax" value="<?=$content['fax'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Полный почтовый адрес (для ЗЧ и корреспонденции):</div>
              <div class="value">
                <input type="text" name="post" value="<?=$content['post'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Наличие филиалов (приёмок):  название, город, адрес, телефоны:</div>
              <div class="value">
                <input type="text" name="filials" value="<?=$content['filials'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Место расположение СЦ:</div>
              <div class="value">
              <select name="position">
               <option>Выберите вариант</option>
               <option value="Центр" <?php if ($content['position'] == 'Центр') { echo 'selected';}?>>Центр</option>
               <option value="Пром.зона" <?php if ($content['position'] == 'Пром.зона') { echo 'selected';}?>>Пром.зона</option>
               <option value="Окраина/Спальн.р-он" <?php if ($content['position'] == 'Окраина/Спальн.р-он') { echo 'selected';}?>>Окраина/Спальн.р-он</option>
               <option value="Иное" <?php if ($content['position'] == 'Иное') { echo 'selected';}?>>Иное</option>
              </select>
              </div>
            </div>

                  <div class="item">
              <div class="level">Форма собственности:</div>
              <div class="value">
              <select name="land">
               <option>Выберите вариант</option>
               <option value="Аренда" <?php if ($content['land'] == 'Аренда') { echo 'selected';}?>>Аренда</option>
               <option value="Собственность" <?php if ($content['land'] == 'Собственность') { echo 'selected';}?>>Собственность</option>
               <option value="Иное" <?php if ($content['land'] == 'Иное') { echo 'selected';}?>>Иное</option>
              </select>
              </div>
            </div>

            <br>
            <br>
            <hr>
            <br>
            <br>

                   <h3>Генеральный директор</h3>

                    <div class="item">
              <div class="level">Ф.И.О.:</div>
              <div class="value">
                <input type="text" name="general_fio" value="<?=$content['general_fio'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Телефон:</div>
              <div class="value">
                <input type="text" name="general_phone" value="<?=$content['general_phone'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">E-mail:</div>
              <div class="value">
                <input type="text" name="general_email" value="<?=$content['general_email'];?>"  />
              </div>
            </div>

            <br>
            <br>
            <hr>
            <br>
            <br>

                  <h3>Контактное лицо (заказ ЗЧ, отчёты)</h3>
                    <div class="item">
              <div class="level">Ф.И.О.:</div>
              <div class="value">
                <input type="text" name="contact_fio" value="<?=$content['contact_fio'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Телефон:</div>
              <div class="value">
                <input type="text" name="contact_phone" value="<?=$content['contact_phone'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">E-mail:</div>
              <div class="value">
                <input type="text" name="contact_email" value="<?=$content['contact_email'];?>"  />
              </div>
            </div>

            <br>
            <br>
            <hr>
            <br>
            <br>

                  <h3>Помещения</h3>
                    <div class="item">
              <div class="level">Наличие приемного помещения с отдельным  входом, его адрес:</div>
              <div class="value">
                <input type="text" name="priem" value="<?=$content['priem'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Общая площадь помещений, в т.ч.:</div>
              <div class="value">
                <input type="text" name="size" value="<?=$content['size'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Площадь ремонтных помещений:</div>
              <div class="value">
                <input type="text" name="repair_size" value="<?=$content['repair_size'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Площадь склада:</div>
              <div class="value">
                <input type="text" name="sklad_size" value="<?=$content['sklad_size'];?>"  />
              </div>
            </div>

            <br>
            <br>
            <hr>
            <br>
            <br>

                  <h3>Штат, кол-во чел.</h3>
                    <div class="item">
              <div class="level">Общее количество:</div>
              <div class="value">
                <input type="text" name="peoples" value="<?=$content['peoples'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Административный персонал:</div>
              <div class="value">
                <input type="text" name="admins" value="<?=$content['admins'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Инженеры (общее количество):</div>
              <div class="value">
                <input type="text" name="engineers" value="<?=$content['engineers'];?>"  />
              </div>
            </div>


            <!--<br>
            <br>
            <hr>
            <br>
            <br>

            <h3>Категории техники, обслуживаемые в Вашем СЦ:</h3>
            <div class="adm-finish">
            <ul>
              <?php
              $sql2 = mysqli_query($db, 'SELECT * FROM `cats` where `service` = 1 ;');
              if (mysqli_num_rows($sql2) != false) {
                    while ($row2 = mysqli_fetch_array($sql2)) {
                     $checked = (in_array($row2['name'], $content['cats'])) ? 'checked' : '';
                     echo '<li style="padding:5px 0px;"><label><input type="checkbox" name="cat[]" value="'.$row2['name'].'" '.$checked.'/>'.$row2['name'].'</label></li>';
                    }
              }

              ?>
               </ul>
            </div>-->

            <br>
            <br>
            <hr>
            <br>
            <br>

                    <div class="item">
              <div class="level">Наличие транспорта для выездных ремонтов (кол-во):</div>
              <div class="value">
                <input type="text" name="cars" value="<?=$content['cars'];?>"  />
              </div>
            </div>


                    <div class="item">
              <div class="level">Обслуживаемые торговые марки по прямым авторизованным Сервисным Соглашениям (перечислить):</div>
              <div class="value">
                <input type="text" name="marks" value="<?=$content['marks'];?>"  />
              </div>
            </div>


                    <div class="item">
              <div class="level">Обслуживаемые торговые марки по вторичным авторизованным Сервисным Соглашениям (перечислить):</div>
              <div class="value">
                <input type="text" name="marks_second" value="<?=$content['marks_second'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Обслуживаемые торговые марки без авторизованных Сервисных Соглашений (перечислить):</div>
              <div class="value">
                <input type="text" name="marks_no" value="<?=$content['marks_no'];?>"  />
              </div>
            </div>

            <br>
            <br>
            <hr>
            <br>
            <br>

            <h3>Сервис:</h3>

                    <div class="item">
              <div class="level">Среднее количество гарантийных ремонтов в месяц:</div>
              <div class="value">
                <input type="text" name="war_repairs" value="<?=$content['war_repairs'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Среднее количество предпродажных ремонтов в месяц:</div>
              <div class="value">
                <input type="text" name="war_presale" value="<?=$content['war_presale'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Среднее количество негарантийных ремонтов в месяц:</div>
              <div class="value">
                <input type="text" name="nowar_repairs" value="<?=$content['nowar_repairs'];?>"  />
              </div>
            </div>


                    <div class="item">
              <div class="level">Примечания:</div>
              <div class="value">
                <input type="text" name="comments" value="<?=$content['comments'];?>"  />
              </div>
            </div>

            <br>
            <br>
            <hr>
            <br>
            <br>

            <h2>Реквизиты сервисного центра:</h2>

                    <div class="item">
              <div class="level">Название организации (полное юридическое):</div>
              <div class="value">
                <input type="text" name="req_name" value="<?=$content['req_name'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">ИНН:</div>
              <div class="value">
                <input type="text" name="inn" value="<?=$content['inn'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">КПП:</div>
              <div class="value">
                <input type="text" name="kpp" value="<?=$content['kpp'];?>"  />
              </div>
            </div>


                    <div class="item">
              <div class="level">Полный юридический адрес (с указанием индекса):</div>
              <div class="value">
                <input type="text" name="req_adress" value="<?=$content['req_adress'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Фактический адрес приемки:</div>
              <div class="value">
                <input type="text" name="req_adress_physic" value="<?=$content['req_adress_physic'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Телефоны (с кодом города):</div>
              <div class="value">
                <input type="text" name="req_phones" value="<?=$content['req_phones'];?>"  />
              </div>
            </div>


                    <div class="item">
              <div class="level">Факс (с кодом города):</div>
              <div class="value">
                <input type="text" name="req_fax" value="<?=$content['req_fax'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">E-mail:</div>
              <div class="value">
                <input type="text" name="req_email" value="<?=$content['req_email'];?>"  />
              </div>
            </div>
            <br><br>
            <h3>Генеральный директор:</h3>

                    <div class="item">
              <div class="level">Ф.И.О. (полностью):</div>
              <div class="value">
                <input type="text" name="req_gen_fio" value="<?=$content['req_gen_fio'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Телефон:</div>
              <div class="value">
                <input type="text" name="req_phone_gen" value="<?=$content['req_phone_gen'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Email:</div>
              <div class="value">
                <input type="text" name="req_gen_email" value="<?=$content['req_gen_email'];?>"  />
              </div>
            </div>

             <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <div class="error_valid" style="color:red;display:none;">Вы не заполнили все обязательные поля, просмотрите анкету внимательно еще раз!</div>
              <button type="submit" >Сохранить</button>
            </div>
            </div>

        </div>

      </form>




        </div>
  </div>
</body>
</html>