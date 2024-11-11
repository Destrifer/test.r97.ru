<?php

use models\User;

if(!User::isAuth()){
  header('Location: /login/');
  exit;
}
if(User::getData('service_id')){
  User::logout();
  header('Location: /login/');
  exit;
}

require($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');

# Сохраняем:
if ($_POST['send'] == 1) {

$test = mysqli_query($db, 'INSERT INTO `requests` (
`id`,
`user_id`,
`name`,
`name_public`,
`type`,
`year`,
`country`,
`city`,
`adress`,
`phisical_adress`,
`phones`,
`fax`,
`post`,
`filials`,
`position`,
`land`,
`general_fio`,
`general_phone`,
`general_email`,
`contact_fio`,
`contact_phone`,
`contact_email`,
`priem`,
`size`,
`repair_size`,
`sklad_size`,
`peoples`,
`admins`,
`engineers`,
`cat`,
`cars`,
`marks`,
`marks_second`,
`marks_no`,
`war_repairs`,
`war_presale`,
`nowar_repairs`,
`comments`,
`req_name`,
`inn`,
`kpp`,
`req_adress`,
`req_adress_physic`,
`req_phones`,
`req_fax`,
`req_email`,
`req_gen_fio`,
`req_phone_gen`,
`req_gen_email`
) VALUES (
\''.mysqli_real_escape_string($db, $_POST['id']).'\',
\''.mysqli_real_escape_string($db, User::getData('id')).'\',
\''.mysqli_real_escape_string($db, $_POST['name']).'\',
\''.mysqli_real_escape_string($db, $_POST['name_public']).'\',
\''.mysqli_real_escape_string($db, $_POST['type']).'\',
\''.mysqli_real_escape_string($db, $_POST['year']).'\',
\''.mysqli_real_escape_string($db, $_POST['country']).'\',
\''.mysqli_real_escape_string($db, $_POST['city']).'\',
\''.mysqli_real_escape_string($db, $_POST['adress']).'\',
\''.mysqli_real_escape_string($db, $_POST['phisical_adress']).'\',
\''.mysqli_real_escape_string($db, $_POST['phones']).'\',
\''.mysqli_real_escape_string($db, $_POST['fax']).'\',
\''.mysqli_real_escape_string($db, $_POST['post']).'\',
\''.mysqli_real_escape_string($db, $_POST['filials']).'\',
\''.mysqli_real_escape_string($db, $_POST['position']).'\',
\''.mysqli_real_escape_string($db, $_POST['land']).'\',
\''.mysqli_real_escape_string($db, $_POST['general_fio']).'\',
\''.mysqli_real_escape_string($db, $_POST['general_phone']).'\',
\''.mysqli_real_escape_string($db, $_POST['general_email']).'\',
\''.mysqli_real_escape_string($db, $_POST['contact_fio']).'\',
\''.mysqli_real_escape_string($db, $_POST['contact_phone']).'\',
\''.mysqli_real_escape_string($db, $_POST['contact_email']).'\',
\''.mysqli_real_escape_string($db, $_POST['priem']).'\',
\''.mysqli_real_escape_string($db, $_POST['size']).'\',
\''.mysqli_real_escape_string($db, $_POST['repair_size']).'\',
\''.mysqli_real_escape_string($db, $_POST['sklad_size']).'\',
\''.mysqli_real_escape_string($db, $_POST['peoples']).'\',
\''.mysqli_real_escape_string($db, $_POST['admins']).'\',
\''.mysqli_real_escape_string($db, $_POST['engineers']).'\',
\''.mysqli_real_escape_string($db, implode('|', $_POST['cat'])).'\',
\''.mysqli_real_escape_string($db, $_POST['cars']).'\',
\''.mysqli_real_escape_string($db, $_POST['marks']).'\',
\''.mysqli_real_escape_string($db, $_POST['marks_second']).'\',
\''.mysqli_real_escape_string($db, $_POST['marks_no']).'\',
\''.mysqli_real_escape_string($db, $_POST['war_repairs']).'\',
\''.mysqli_real_escape_string($db, $_POST['war_presale']).'\',
\''.mysqli_real_escape_string($db, $_POST['nowar_repairs']).'\',
\''.mysqli_real_escape_string($db, $_POST['comments']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_name']).'\',
\''.mysqli_real_escape_string($db, $_POST['inn']).'\',
\''.mysqli_real_escape_string($db, $_POST['kpp']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_adress']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_adress_physic']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_phones']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_fax']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_email']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_gen_fio']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_phone_gen']).'\',
\''.mysqli_real_escape_string($db, $_POST['req_gen_email']).'\'
);') or mysqli_error($db);

if ($test) {
  $serviceID = mysqli_insert_id($db);
mysqli_query($db, 'UPDATE `users` SET 
`service_id` = "'.$serviceID.'"   
WHERE `id` = '.User::getData('id').' LIMIT 1
;') or mysqli_error($db);

$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Уважаемый '.\models\User::getData('login').'.</h3><br>
                      Ваша заявка на подключение оформлена. Ожидайте результата её обработки.<br>

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

$emailTo = \models\User::getData('email');
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
$mail->addAddress($emailTo);
$mail->isHTML(true);
$mail->Subject = "Ваша заявка на SERVICE.R97.RU";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;

$mail->send();


}

  User::logout();
  header('Location: /login/');
  exit;
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


function cities($cat_id, $country = 1) {
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


?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Анкета сервисного центра - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/jquery.validate.min.js"></script>
<script src="/_new-codebase/front/vendor/jquery-validation/additional-methods.min.js"></script>
<script src="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster.bundle.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/tooltipster/tooltipster-sideTip-shadow.min.css" />
  <link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script >
// Таблица
$(document).ready(function() {

  $('.select2').select2();

                          $('input[type="text"]').tooltipster({
                              trigger: 'custom',
                              position: 'bottom',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });
                          $('select').tooltipster({
                              trigger: 'custom',
                              position: 'bottom',
                              animation: 'grow',
                              theme: 'tooltipster-shadow'
                          });
$.validator.setDefaults({
    ignore: ""
});

jQuery.extend(jQuery.validator.messages, {
    required: "Обязательно к заполнению!"
});

$(".validate_form").validate({
        ignore: "",
  rules: {
      name: {
      required: true
      },
      name_public: {
      required: true
      },
      type: {
      required: true
      },
      adress: {
      required: true
      },
      phisical_adress: {
      required: true
      },
      phones: {
      required: true
      },
      post: {
      required: true
      },
      position: {
      required: true
      },
      land: {
      required: true
      },
      general_fio: {
      required: true
      },
      general_phone: {
      required: true
      },
      general_email: {
      required: true
      },
      contact_fio: {
      required: true
      },
      contact_phone: {
      required: true
      },
      contact_email: {
      required: true
      },
      inn: {
      required: true
      },
      req_name: {
      required: true
      },
      kpp: {
      required: true
      },
      req_adress: {
      required: true
      },
      req_adress_physic: {
      required: true
      },
      req_phones: {
      required: true
      },
      req_email: {
      required: true
      },
      req_gen_fio: {
      required: true
      },
      req_phone_gen: {
      required: true
      },
      req_gen_email: {
      required: true
      }
  },
  highlight: function (element, errorClass) {
            $(element).addClass("input-validation-error");
  },
  errorClass: "field-validation-error",
  errorPlacement: function(error, element) {
      var ele = $(element),
      err = $(error),
      msg = err.text();
      if (msg != null && msg !== "") {
      ele.tooltipster('content', msg);
      ele.tooltipster('open'); //open only if the error message is not blank. By default jquery-validate will return a label with no actual text in it so we have to check the innerHTML.
      $('.error_valid').show();
      }
  },
  unhighlight: function(element, errorClass, validClass) {
      $(element).removeClass(errorClass).addClass(validClass).tooltipster('close');
      $(element).removeClass("input-validation-error");
      $('.error_valid').hide();
  }
});


    $(document).on('selectmenuchange', 'select[name="country"]', function() {
       var this_parent = $(this).parent().parent().parent();
        var value = $(this).val();
              if (value) {

                  $.get( "/ajax.php?type=get_cities&country="+value, function( data ) {
                  this_parent.find($('select[name="city"]')).html(data.html);
                  });

         }



        return false;
    });


$("input[name='name']").keyup(function() {
    $("input[name='req_name']").val( this.value );
});

$("input[name='phisical_adress']").keyup(function() {
    $("input[name='req_adress']").val( this.value );
    $("input[name='req_adress_physic']").val( this.value );
});
$("input[name='phones']").keyup(function() {
    $("input[name='req_phones']").val( this.value );
});
$("input[name='fax']").keyup(function() {
    $("input[name='req_fax']").val( this.value );
});
$("input[name='general_fio']").keyup(function() {
    $("input[name='req_gen_fio']").val( this.value );
});
$("input[name='general_phone']").keyup(function() {
    $("input[name='req_phone_gen']").val( this.value );
});
$("input[name='general_email']").keyup(function() {
    $("input[name='req_gen_email']").val( this.value );
});



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

<!-- New codebase -->
<link href="/_new-codebase/front/vendor/intl-tel-input/css/intlTelInput.css" rel="stylesheet">
</head>

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
           <h2>Анкета сервисного центра</h2><br>
           <h4 style="    font-weight: 200;      color: #578200;"><?=$config['text_anketa'];?></h4>
  <form id="send" class="validate_form" method="POST">
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
               <option value="">Выберите вариант</option>
               <option value="Независимый" <?php if ($content['service'] == 'Независимый') { echo 'selected';}?>>Независимый</option>
               <option value="Дилерский" <?php if ($content['service'] == 'Дилерский') { echo 'selected';}?>>Дилерский</option>
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
               <?=cities(0);?>
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
                <input type="text" name="filials" value="<?=$content['filials'];?>"  />
              </div>
            </div>

                  <div class="item">
              <div class="level">Место расположение СЦ:</div>
              <div class="value">
              <select name="position">
               <option value="">Выберите вариант</option>
               <option value="Центр" <?php if ($content['service'] == 'Центр') { echo 'selected';}?>>Центр</option>
               <option value="Пром.зона" <?php if ($content['service'] == 'Пром.зона') { echo 'selected';}?>>Пром.зона</option>
               <option value="Окраина/Спальн.р-он" <?php if ($content['service'] == 'Окраина/Спальн.р-он') { echo 'selected';}?>>Окраина/Спальн.р-он</option>
               <option value="Иное" <?php if ($content['service'] == 'Иное') { echo 'selected';}?>>Иное</option>
              </select>
              </div>
            </div>

                  <div class="item">
              <div class="level">Форма собственности:</div>
              <div class="value">
              <select name="land">
               <option value="">Выберите вариант</option>
               <option value="Аренда" <?php if ($content['service'] == 'Аренда') { echo 'selected';}?>>Аренда</option>
               <option value="Собственность" <?php if ($content['service'] == 'Собственность') { echo 'selected';}?>>Собственность</option>
               <option value="Иное" <?php if ($content['service'] == 'Иное') { echo 'selected';}?>>Иное</option>
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
                <input type="text" name="general_phone" data-input-filter="phone" data-intl-tel-input value="<?=$content['general_phone'];?>"  />
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
                <input type="text" name="contact_phone" data-input-filter="phone" data-intl-tel-input value="<?=$content['contact_phone'];?>"  />
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
              <div class="level">Общая площадь помещений, в т.ч. (м<sup>2</sup>):</div>
              <div class="value">
                <input type="text" name="size" value="<?=$content['size'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Площадь ремонтных помещений (м<sup>2</sup>):</div>
              <div class="value">
                <input type="text" name="repair_size" value="<?=$content['repair_size'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Площадь склада (м<sup>2</sup>):</div>
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
                     echo '<li style="padding:5px 0px;"><label><input type="checkbox" name="cat[]" value="'.$row2['name'].'" />'.$row2['name'].'</label></li>';
                    }
              }

              ?>
            </ul>
            </div>

                    <div class="item">
              <div class="level">Если вы ремонтируете что то кроме как из списка - укажите это:</div>
              <div class="value">
                <input type="text" name="cat[]" value=""  />
              </div>
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
                <input type="text" name="req_phones"  value="<?=$content['req_phones'];?>"  />
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
                <input type="text" name="req_phone_gen" data-input-filter="phone" data-intl-tel-input value="<?=$content['req_phone_gen'];?>"  />
              </div>
            </div>

                    <div class="item">
              <div class="level">Email:</div>
              <div class="value">
                <input type="text" name="req_gen_email" value="<?=$content['req_gen_email'];?>"  />
              </div>
            </div>

            <br>
            <br>
            <hr>
            <br>
            <br>

            <div class="item">
              <div class="level">Email для получения уведомлений:</div>
              <div class="value">
                <input type="text" name="notif_email" value="<?=$content['notif_email'];?>"  />
              </div>
            </div>

                     <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <div class="error_valid" style="color:red;display:none;">Вы не заполнили все обязательные поля, просмотрите анкету внимательно еще раз!</div>
              <button type="submit" >Отправить</button>
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