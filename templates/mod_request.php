<?php

use models\Users;

require($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `requests` WHERE `mod` = 0 and `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\';'));
if ($count['COUNT(*)'] > 0) {
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `mod` = 0 and `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
$content['cats'] = explode('|', $content['cat']);
$content['user_info'] = get_user_info2($content['user_id']);
} else {
header('Location: '.$config['url'].'requests/');
}

# Сохраняем:
if ($_POST['send'] == 1) {

mysqli_query($db, 'UPDATE `'.Users::TABLE.'` SET
`status_id` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $content['user_id']).'\' LIMIT 1
;') or mysqli_error($db);

mysqli_query($db, 'UPDATE `requests` SET
`mod` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1
;') or mysqli_error($db);

$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Уважаемый '.$content['user_info']['email'].'.</h3><br>
                      Ваша заявка на подключение одорбена.<br>
                      <strong>Ваш логин</strong>: '.$content['user_info']['email'].'<br>
                      <strong>Ваш пароль</strong>: '.$content['user_info']['password'].'

                      <br>
                      <br>
                      '.$config['email_footer'].'
                      <br>
- -  <br>
<b>Пожалуйста, при ответе сохраняйте переписку.<br>
С уважением,  <br>
Служба поддержки SERVICE.HARPER   <br>
<img src="http://harper.ru//img/Picture1.jpg" height="50px"><br>
e-mail: service2@harper.ru</b>
                      </body>

                    </html>';

$mail = new PHPMailer;
$mail->isSMTP();
//$mail->SMTPDebug = 1;
$mail->Host = $config['mail_host'];
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = $config['mail_username'];
$mail->Password = $config['mail_password'];
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom($config['mail_username'], $config['mail_from']);
$mail->addAddress($content['user_info']['email']);
$mail->isHTML(true);
$mail->Subject = "Ваша заявка на SERVICE.R97.RU";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;

if(!$mail->send()) {
}


admin_log_add('Обработана анкета СЦ #'.$_GET['id']);

header('Location: '.$config['url'].'requests/');
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

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/css/datatables.css">

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

<!-- New codebase -->
<link href="/_new-codebase/front/vendor/intl-tel-input/css/intlTelInput.css" rel="stylesheet">
<body>

<div class="viewport-wrapper">

<div class="site-header">
  <div class="wrapper">

    <div class="logo">
      <a href="/dashboard/"><img src="/i/logo.png" alt=""/></a>
      <span>Сервис</span>
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
                <input type="text" name="phones" data-input-filter="phone" data-intl-tel-input value="<?=$content['phones'];?>"  />
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
                <input type="text" name="filials" data-input-filter="phone" data-intl-tel-input value="<?=$content['filials'];?>"  />
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
                <input type="text" data-input-filter="phone" data-intl-tel-input name="general_phone" value="<?=$content['general_phone'];?>"  />
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
                <input type="text" data-input-filter="phone" data-intl-tel-input name="contact_phone" value="<?=$content['contact_phone'];?>"  />
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


            <br>
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
            </div>

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
                <input type="text" data-input-filter="phone" data-intl-tel-input name="req_phones" value="<?=$content['req_phones'];?>"  />
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
                <input type="text" data-input-filter="phone" data-intl-tel-input name="req_phone_gen" value="<?=$content['req_phone_gen'];?>"  />
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

              <button type="submit" >Одобрить</button>

              <a style="background: #F44336;width:150px;" href="/mod-false/<?=$content['id'];?>/" class="button">Отклонить</a>


            </div>
            </div>
        </div>

      </form>




        </div>
  </div>

  
  <!-- New codebase -->
  <script src="/_new-codebase/front/components/input-filter.js"></script>
  <script src="/_new-codebase/front/vendor/intl-tel-input/js/intlTelInput.js"></script>
  <script src="/_new-codebase/front/modules/intl-settings-requests.js"></script>
</body>
</html>