<?php
require($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');
header("Content-type: text/html; charset=utf-8");
error_reporting(1);

use models\User;
use program\adapters;
# Получаем
function content_feedback($id) {
  global $db, $config;

if (\models\User::hasRole('admin')) {
$sql = mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `id` = '.$id.' LIMIT 1;');
disable_notice('https://crm.r97.ru/tickets/'.$_GET['id'].'/', 1);
} else {
$sql = mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `id` = '.$id.' and `user_id` = '.User::getData('id').' LIMIT 1;');
disable_notice('https://crm.r97.ru/tickets/'.$_GET['id'].'/', User::getData('id'));
}
     if (mysqli_num_rows($sql) == 0) {
     header('Location: '.$config['url'].'/tickets/');
     exit;
     } else {
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      $content['service_info'] = service_request_info($content['user_id']);
      //$content['repair_info'] = repair_info($content['repair_id']);
      //$content['item_info'] = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'items` WHERE `id` = \''.mysqli_real_escape_string($db, $row['item_id']).'\' LIMIT 1;'));
      }

   $content['repair_link'] = ($content['repair_id'] != 0) ? ', <a target="_blank" href="https://crm.r97.ru/edit-repair/'.$content['repair_id'].'/">Ремонт '.$content['repair_id'].'</a>' : '';

    mysqli_query($db, 'UPDATE `feedback_messages` SET `read` = 1 WHERE `feedback_id` = '.$id.' ;');
    return $content;
    }
}

function sections($type) {
  global $db;
$array = array('Вопрос открыт', 'Ждем уточнения', 'Вопрос закрыт');
foreach ($array as $key)  {
      if ($type == $key) {
      $content .= '<option selected value="'.$key.'">'.$key.'</option>';
      } else {
       $content .= '<option value="'.$key.'">'.$key.'</option>';
      }
}

    return $content;
}

function content_model($id = '') {
  global $db;
  if ($id != '') {
$sql = mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'items` WHERE `id` = '.$id.' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
} else {
return '';
}
}

function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

function check_important() {
  global $db;

if (\models\User::hasRole('admin')) {
$where = 'where `status` != \'Вопрос закрыт\' and `repair_id` = 0 and ((`status` != \'Уведомление\') or (`status` = \'Уведомление\' and `need_answer` = 1))';
} else {
$where = 'where `user_id` = '.User::getData('id').' and `status` != \'Вопрос закрыт\'';
}
$count = 0;
$sql = mysqli_query($db, 'SELECT * FROM `feedback_admin` '.$where.' ;');
      while ($row = mysqli_fetch_array($sql)) {

      $check = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_messages` where `feedback_id` = '.$row['id'].' and `read` = 0 GROUP by `feedback_id`;'))['COUNT(*)'];
      if ($check > 0) {
      $count++;
      }
      }
    return $count;
}

function get_important() {
  global $db;

if (\models\User::hasRole('admin')) {
$where = '';
} else {
$where = 'and `user_id` = '.User::getData('id');
}

$sql = mysqli_query($db, 'SELECT `feedback_id` FROM `feedback_messages` WHERE `read` = 0 and '.$where.' GROUP BY `feedback_id`');
      while ($row = mysqli_fetch_array($sql)) {
      $content['ids'][] = $row['feedback_id'];
      }

return '('.implode(',', $content['ids']).')';
}

# Получаем список материалов
function content_list() {
  global $db;
require_once($_SERVER['DOCUMENT_ROOT'].'/adm/pagination.php');

if (\models\User::hasRole('admin')) {
$where = 'WHERE `repair_id` = 0 and ((`status` != \'Уведомление\') or (`status` = \'Уведомление\' and `need_answer` = 1)) ';
} else {
$where = 'WHERE `user_id` = '.User::getData('id').' and `repair_id` = 0';
}

if ($_GET['status']) {
$where .= ' and `status` = \''.$_GET['status'].'\' ';
}

//echo 'SELECT * FROM `feedback_admin` '.$where.' ORDER by `id` DESC';
# Считаем количество записей:

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_admin` '.$where.' ORDER by `id` DESC;'));
$countElements = $count['COUNT(*)'];
if ($countElements == 0) {
  if (\models\User::hasRole('admin')) {
   $content_list['body'] = '<tr><td colspan="9" style="text-align:center">Обращений с такими условиями нет!</td></tr>';
   } else {
   $content_list['body'] = '<tr><td colspan="9" style="text-align:center">Обращений нет!</td></tr>';
   }


} else {
    # Вызываем класс:
    $params = array('pageSize' => 20, 'maxPage' => '20000',  'css' => '', 'title' => '', 'litag' => '', 'tag' => '', 'arr' => 'true', 'line' => '5');
    $pager = new goPaginator($countElements, $params);
    $sql = mysqli_query($db, 'SELECT * FROM `feedback_admin` '.$where.' ORDER by `id` DESC LIMIT '.$pager->getSqlLimits());
    $number = mysqli_num_rows($sql);
    $content_list['bad'] = check_important();
    $i = 0;
    if ($number != false) {
        while ($row = mysqli_fetch_array($sql)) {


          $class .= ($i == 0) ? 'odd ' : 'even ';
          $i++;
          if ($i == 2) {
          $i = 0;
          }
          $day = date("Y-m-d H:i:s", strtotime("-1 day"));
          if ($row['date'] <= $day && $row['answer'] == '' && $row['status'] != 'Вопрос закрыт') {
          $class .= 'red';
          }

          $count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `'.$_COOKIE['lang'].'feedback_messages` WHERE `read` = 0 and `feedback_id` = '.$row['id'].';'));
          if ($count['COUNT(*)'] > 0) {
          $class .= 'green';

          }

          $last_message = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'feedback_messages` WHERE `feedback_id` = '.$row['id'].' and `user_type` = 2 ORDER by `id` DESC LIMIT 1;'));

          //$model = content_model($row['model_id']);
          $pub = ($row['answer'] != '') ? '<strong>+</strong>' : '<strong>-</strong>';
          if ($row['vote'] > 0) {
          $row['vote'] = '<font style="color:#33CC33">+'.$row['vote'].'</font>';
          } else if ($row['vote'] == 0) {
          $row['vote'] = '<font>'.$row['vote'].'</font>';
          } else {
          $row['vote'] = '<font style="color:#FF3300">-'.$row['vote'].'</font>';
          }
          $row['service_info'] = service_request_info($row['user_id']);
          if ($_GET['important'] == 'true') {
            if (\models\User::hasRole('admin')) {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$row['service_info']['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td>'.strip_tags($row['message']).'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a>  <a class="t-5" style="float:" onclick="return confirm(\'Подтвердите удаление запроса\');" href="/del-tickets/'.$row['id'].'/"></a></td></tr>';
            } else {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$row['service_info']['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td>'.cutString(strip_tags($row['message']), 300).'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a></td></tr>';
            }
          } else if ($_GET['important'] != 'true') {
            if (\models\User::hasRole('admin')) {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$row['service_info']['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td style="width:200px; word-break:break-all;">'.strip_tags($row['message']).'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a>  <a class="t-5" style="float:" onclick="return confirm(\'Подтвердите удаление запроса\');" href="/del-tickets/'.$row['id'].'/"></a> </td></tr>';
            } else {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$row['service_info']['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td style="width:200px; word-break:break-all;">'.cutString(strip_tags($row['message']), 300).'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a> </td></tr>';
            }
         }

          unset($class);
          unset($last_message);
        }

      if ($pager != '')
            $content_list['pager'] = $pager;
      }
      }
return  $content_list;
}

function categories($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'categories`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function models($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'items`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return '<option value="0">Выберите модель</option>'.$content;
}

function models_index($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `'.$_COOKIE['lang'].'items`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return '<option value="">Выберите модель</option>'.$content;
}



function check_similar($message, $id, $model_id = '') {
  global $db;

/*$exp = explode(' ', $message);

$i = 1;
foreach ($exp as $query) {

if ($i != 2) {
$query_sql .= ' '.$query;
echo $query_sql.'|';
} else {

//echo $query_sql.'<br>';  */

/*$sql = mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `id` != '.$id.' `message` LIKE \'%'.$query_sql.'%\';');
      while ($row = mysqli_fetch_array($sql)) {
      $answer[$row['id']] = $row['message'];
      }      */

$sql = mysqli_query($db, 'SELECT * FROM `feedback_admin` WHERE `model_id` = '.$model_id.' ;');
      while ($row = mysqli_fetch_array($sql)) {

      $sql3 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_messages` WHERE `feedback_id` = '.$row['id'].' ;'));
      if ($sql3['COUNT(*)'] > 0) {

      echo '<div style="background:rgba(128, 189, 3, 0.13);margin:10px 10px 10px 0px;padding:10px;">'.$row['message'].' <a href="#" class="show_messages">Показать ответы</a>';

      $sql2 = mysqli_query($db, 'SELECT * FROM `feedback_messages` WHERE `feedback_id` = '.$row['id'].' and `user_type` = 2;');
      while ($row2 = mysqli_fetch_array($sql2)) {
       if ($row2['message']) {
      echo '<div class="answerz" style="display:none;margin-left:80px;background:#fff;padding:10px;"><span>'.$row2['message'].'</span> <a href="#" class="copy_messages">Копировать в ответ</a>';
      echo '</div>';
      }
      }

      echo '</div>';
      }
      }
/*$query_sql = $query;
$i = 0;
}

$i++;
}

$answer = array_filter($answer);
foreach ($answer as $ans) {

echo $ans.'<br><hr><br>';
}
          */
}


# Сохраняем 2:
if ($_POST['comment']) {

mysqli_query($db, 'UPDATE `'.$_COOKIE['lang'].'feedback_admin` SET
`comment` = \''.mysqli_real_escape_string($db, $_POST['comment']).'\'
WHERE `id` = \''.$_GET['id'].'\' LIMIT 1') or mysqli_error($db);

//header('Location: '.$config['url'].'adm/tickets.php');
}

# Сохраняем 2:
if ($_POST['model_id']) {

mysqli_query($db, 'UPDATE `'.$_COOKIE['lang'].'feedback_admin` SET
`model_id` = \''.mysqli_real_escape_string($db, $_POST['model_id']).'\'
WHERE `id` = \''.$_GET['id'].'\' LIMIT 1') or mysqli_error($db);

//header('Location: '.$config['url'].'adm/tickets.php');
}

# Сохраняем 2:
if ($_POST['status']) {

if (\models\User::hasRole('admin')) {
mysqli_query($db, 'UPDATE `'.$_COOKIE['lang'].'feedback_admin` SET
`status` = \''.mysqli_real_escape_string($db, $_POST['status']).'\'
WHERE `id` = \''.$_GET['id'].'\' LIMIT 1') or mysqli_error($db);
} else {
mysqli_query($db, 'UPDATE `'.$_COOKIE['lang'].'feedback_admin` SET
`status` = \''.mysqli_real_escape_string($db, $_POST['status']).'\'
WHERE `id` = \''.$_GET['id'].'\' and `user_id` = '.User::getData('id').' LIMIT 1') or mysqli_error($db);
}

//header('Location: '.$config['url'].'adm/tickets.php');
}

# Сохраняем 3:
if ($_POST['status_change']) {
if (\models\User::hasRole('admin')) {
mysqli_query($db, 'UPDATE `feedback_admin` SET
`status` = \''.mysqli_real_escape_string($db, str_replace('+',' ', $_POST['status_change'])).'\'
WHERE `id` = '.$_GET['id'].' LIMIT 1') or mysqli_error($db);
} else {
mysqli_query($db, 'UPDATE `feedback_admin` SET
`status` = \''.mysqli_real_escape_string($db, str_replace('+',' ', $_POST['status_change'])).'\'
WHERE `id` = '.$_GET['id'].' and `user_id` = '.User::getData('id').' LIMIT 1') or mysqli_error($db);
}

//header('Location: '.$config['url'].'adm/tickets.php');
}

# Сохраняем 3:
if ($_POST['del_id']) {
foreach ($_POST['del_id'] as $id) {
mysqli_query($db, 'DELETE FROM `feedback_admin` WHERE `id` = '.$id);
}
//header('Location: '.$config['url'].'adm/tickets.php');
}



if ($_GET['id']) {
$content = stripslashes_array(content_feedback($_GET['id']));
}

# Сохраняем:
if ($_POST['send'] == 1) {

/*Новый ответ службы поддержки:
                      <a href="http://harper.ru/support/'.$content['md5'].'/">http://harper.ru/support/'.$content['md5'].'/</a>*/

/*$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Уважаемый '.$content['name'].'.</h3><br>
                      На нашем сайте service.harper.ru вы задали вопрос:<br>
                      '.$content['message'].'
                      <br>
                      <br>
                      <br>
                      Получен новый ответ от службы подержки.
                      <br>
- -  <br>
<b>Пожалуйста, при ответе сохраняйте переписку.<br>
С уважением,  <br>
Служба поддержки HARPER   <br>
<img src="http://harper.ru//img/Picture1.jpg" height="50px"><br>
e-mail: service2@harper.ru</b>
                      </body>

                    </html>';

$mail = new PHPMailer;
$mail->isSMTP();
//$mail->SMTPDebug = 1;
$mail->Host = 'smtp.mail.ru';
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = 'robot@harper.ru';
$mail->Password = '!@qwASzx';
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->setFrom('robot@harper.ru', 'Harper.ru');
$mail->addAddress($content['email'], $content['name']);
$mail->isHTML(true);
$mail->Subject = "Служба поддержки HARPER.RU [Q".numberFormat($_GET['id'], 4)."]";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;

if(!$mail->send()) {
    //echo 'Message could not be sent.';
   //echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
   // echo 'Message has been sent';
}
         */


if (\models\User::hasRole('admin')) {
mysqli_query($db, 'INSERT INTO `feedback_messages` (
`feedback_id`,
`message`,
`user_type`,
`date`,
`read`
) VALUES (
\''.mysqli_real_escape_string($db, $_GET['id']).'\',
\''.mysqli_real_escape_string($db, $_POST['answer']).'\',
\'2\',
\''.mysqli_real_escape_string($db, date("Y-m-d H:i:s")).'\',
\'1\'
);') or mysqli_error($db);

notice_add('Новый ответ от поддержки', 'Поступил новый ответ от службы поддержки. Пожалуйста, ознакомьтесь.', $content['user_id'], 'https://crm.r97.ru/tickets/'.$_GET['id'].'/');

} else {
mysqli_query($db, 'INSERT INTO `feedback_messages` (
`feedback_id`,
`message`,
`user_type`,
`date`,
`read`
) VALUES (
\''.mysqli_real_escape_string($db, $_GET['id']).'\',
\''.mysqli_real_escape_string($db, $_POST['answer']).'\',
\'1\',
\''.mysqli_real_escape_string($db, date("Y-m-d H:i:s")).'\',
\'0\'
);') or mysqli_error($db);

notice_add('Новый ответ в сообщениях', 'Поступил новый ответ в службу поддержки. Пожалуйста, ознакомьтесь.', 1, 'https://crm.r97.ru/tickets/'.$_GET['id'].'/');

}


if ($_FILES['filenamez']) {
  for ($i = 0; $i < 1000; $i++) {
    if (empty($_FILES['filenamez']['name'][$i])) {
      break;
    }
    try {
      $url = adapters\DigitalOcean::upload('filenamez', 'uploads/photos/tickets/', $i);
      if (empty($url)) {
        continue;
      }
    } catch (\Exception $e) {
      exit('Ошибка при загрузке изображения. Пожалуйста, попробуйте еще раз, либо обратитесь к администратору. ' . $e->getMessage());
    }
    mysqli_query($db, 'INSERT INTO `feedback_photos` (`feedback_id`, `url`) VALUES (' . $_GET['id'] . ', "' . $url . '");') or mysqli_error($db);
  }
}

if ($_FILES['filenamez_video']) {
  for ($i = 0; $i < 1000; $i++) {
    if (empty($_FILES['filenamez_video']['name'][$i])) {
      break;
    }
    try {
      $url = adapters\DigitalOcean::upload('filenamez_video', 'uploads/video/tickets/', $i);
      if (empty($url)) {
        continue;
      }
    } catch (\Exception $e) {
      exit('Ошибка при загрузке видео. Пожалуйста, попробуйте еще раз, либо обратитесь к администратору. ' . $e->getMessage());
    }
    mysqli_query($db, 'INSERT INTO `feedback_videos` (`feedback_id`, `url`) 
                  VALUES (
                ' . $_GET['id'] . ',
                "' . mysqli_real_escape_string($db, $url) . '"
                );') or mysqli_error($db);
  }
}
header('Location: /tickets/' . $_GET['id'] . '/');
exit;
}



$content_list = content_list();
?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Сообщения - Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="/date/jquery.datetimepicker.css"/>
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal-default-theme.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal.css" />
<link rel="stylesheet" href="/redactor/redactor.css" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
<script src="/_new-codebase/front/vendor/remodal/remodal.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="/js/datatables.css">
<link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
<script src="/js/moment.min.js"></script>
<script src="/js/jquery.daterangepicker.js"></script>
<link href="/_new-codebase/front/vendor/jqueryui-editable/jqueryui-editable.css" rel="stylesheet"/>
<script src="/_new-codebase/front/vendor/jqueryui-editable/jqueryui-editable.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/fancybox/3.2.5/jquery.fancybox.min.css" />
<script src="/_new-codebase/front/vendor/fancybox/3.2.5/jquery.fancybox.min.js"></script>
<script >
// Таблица
$(document).ready(function() {

<?php if (\models\User::hasRole('admin')) { ?>



$( ".edit_message" ).click(function() {
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

       $.post("/ajax.php?type=edit_message_by_id", {id: id, value: value}, function(data){ current_span.html(value);   });

    });

$( ".delete_message" ).click(function() {
  var id = $(this).data('id');
  var current_div = $(this).parent().parent();
  current_div.hide();

  $.get( "/ajax.php?type=del_message_by_id&id="+id, function( data ) {});

  return false;

});

<?php } ?>

    $('#impo').change(function() {
        if($(this).is(":checked")) {
        window.location.href = "?important=true";
        } else {
        window.location.href = "/tickets/";
        }

    });



<?php if (!$_GET['id']) { ?>
  $('#two-inputs').dateRangePicker(
  {
    separator : ' to ',
    getValue: function()
    {
      if ($('#date-range200').val() && $('#date-range201').val() )
        return $('#date-range200').val() + ' to ' + $('#date-range201').val();
      else
        return '';
    },
    setValue: function(s,s1,s2)
    {
      $('#date-range200').val(s1);
      $('#date-range201').val(s2);
    }
  });
<?php } ?>

    var max_fields      = 50; //maximum input boxes allowed
    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $(".add_field_button"); //Add button ID

    var x = 1; //initlal text box count
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
            x++; //text box increment
            $(wrapper).append('<div class="part"><div class="item" style="vertical-align: top;  margin-top: 20px;"><div class="level">Фото</div><div class="value"><input type="file" accept="image/*" id="capture" capture="camera" name="filenamez[]"></div></div></div>');
        }

    });

    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    });

    var max_fields2      = 50; //maximum input boxes allowed
    var wrapper2         = $(".input_fields_wrap2"); //Fields wrapper
    var add_button2      = $(".add_field_button2"); //Add button ID

    var x = 1; //initlal text box count
    $(add_button2).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields2){ //max input box allowed
            x++; //text box increment
            $(wrapper2).append('<div class="part"><div class="item" style="vertical-align: top;  margin-top: 20px;"><div class="level">Видео</div><div class="value"><input type="file" accept=".mp4" id="capture" capture="camera" name="filenamez_video[]"><br><span style="color:red">Только mp4!</span></div></div></div>');
        }

    });

    $(wrapper2).on("click",".remove_field2", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    });

$('.mailing img').each(function (){
 var currentImage = $(this);
 currentImage.wrap("<a class='fancybox' href='" + currentImage.attr("src") + "'</a>"); });

$(".fancybox").fancybox();

} );

function popupWindow(mypage, myname, w, h, scroll) {
var winl = (screen.width - w) / 2;
var wint = (screen.height - h) / 2;
winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
win = window.open(mypage, myname, winprops)
if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
}
</script>
<style>
.ui-selectmenu-button {
width: 230px !important;
}
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
#question td{
padding: 5px;
border: 1px solid #fff;
}
#question2 {
background: #FFFFEB;
width: 100%;
}
#question2 td{
padding: 5px;
border: 1px solid #fff;
}
#answer {
background: #F1FFE5;
width: 100%;
}
#answer td{
padding: 5px;
border: 1px solid #fff;
}

.red.odd{
background: rgba(255, 124, 92, 0.08) !important;
}
.red.even td {
background: rgba(255, 124, 92, 0.08) !important;
}
.red.even{
background: rgba(255, 124, 92, 0.08) !important;
}
.red.odd td {
background: rgba(255, 124, 92, 0.08) !important;
}
.green.odd{
background: rgba(255, 184, 112, 1) !important;
}
.green.even td {
background: rgba(255, 184, 112, 1) !important;
}
.green.even{
background: rgba(255, 184, 112, 1) !important;
}
.green.odd td {
background: rgba(255, 184, 112, 1) !important;
}
.sorting_desc:after {
display:none !important;
}
</style>
<style>
.remodal-cancel, .remodal-confirm {
      min-width: 160px !important;
}

</style>
<?php if ($_GET['id']) { ?>
<script src="<?=$config['url'];?>redactor/redactor.min.js"></script>

<script src="<?=$config['url'];?>redactor/lang/ru.js"></script>
  <script >
  $(document).ready(
    function()		{
    $('#redactor_text').redactor({minHeight: 200, lang: 'ru', imageUpload: '/image_upload.php'});
    $('#redactor_text2').redactor({ minHeight: 200, lang: 'ru', imageUpload: '/image_upload.php'});
    $('#select_model').select2();
    }



  );
  </script>
<?php } ?>
</head>

<body>

<div class="remodal" data-remodal-id="modal">
  <button data-remodal-action="close" class="remodal-close"></button>
  <h1 style="float:none !important">Нужно подтверждение действий</h1>
   <br />
  <table style="    margin: 0 auto;">
  <tr>
  <Td>
  <p>Выберите статус:</p>
  </Td>
  </tr>
  <tr>
  <Td>
    <select class="nomenu" name="status_change" style="padding: 5px">
    <?=sections($content['status']);?>
    </select>
  </Td>
   </tr>
  <tr>
  <td>
      <p>
    Вы уверены, что хотите отправить ответ?
    </p>
  </td>
  </tr>
  </table>

  <button style="    width: 30%;"  data-remodal-action="cancel" class="remodal-cancel">Ответ не отправлять</button>
  <button style="    width: 30%;" data-remodal-action="confirm" class="remodal-confirm">Все верно, отправить</button>
</div>
<?php if (\models\User::hasRole('admin')) { ?>

<script>
$(document).ready(function() {

$( ".save_ans" ).click(function(event) {
  $('[data-remodal-id=modal]').remodal().open();
  event.preventDefault();
});

$( ".remodal-confirm" ).click(function() {
  var vale = $('select[name="status_change"]').val();
  $.post(
    "/tickets/<?=$_GET['id'];?>/", {
      status_change: vale
    }
  ).done(function(msg){ $('[data-remodal-id=modal]').remodal().close();
  $(".sendform")[0].submit(); });
});


    $('.show_messages').click(function() {
    $(this).parent().find('.answerz').show();
    return false;
    });

    $('.copy_messages').click(function() {
    $('#redactor_text').redactor('code.set', $(this).parent().find('span').html());
        $("html, body").animate({
        scrollTop: $('#add_form').offset().top
    }, 2000);
    return false;
    });


});
</script>
<?php } else {  ?>

<script>
$(document).ready(function() {

$( ".save_ans" ).click(function(event) {
  $(".sendform")[0].submit();
});



});
</script>

<?php } ?>


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

  </div><!-- .adm-tab -->  <br>
            <?php
            if ($_GET['id']) {
              if ($content['status'] == 'Уведомление' && $content['need_answer'] != 1) {
                $title = 'Уведомление';
              } else {
                $title = 'Запрос';
              }
              if (!empty($content['subject'])) {
                $title .= ' [' . $content['subject'] . ']';
              } else {
                $title .= ' [Q' . numberFormat($_GET['id'], 4) . ']';
              }
              if (!empty($content['repair_link'])) {
                $title .= ': ' . $content['repair_link'];
              }
              echo '<h2>' . $title . '</h2>';
            } 
             
             if ($_GET['id']) { ?>
<br>
<style>
.ui-selectmenu-button {
    vertical-align: initial;
}
.ui-selectmenu-button:after {
    right: 10px;
}
.select2-container .select2-selection--single {
margin-top: -5px;
height: 55px;
border: 1px solid #cfcfcf;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
line-height: 58px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    top: 10px;
}

.mailing img {
max-width:500px;
}

</style><?php if (\models\User::hasRole('admin')) { ?>
<?php if ($content['status'] != 'Уведомление' || ($content['need_answer'] == 1 && $content['status'] == 'Уведомление')) { ?>
<form method="POST" enctype="multipart/form-data">
            <h3 style="    padding-left: 5px;">Служебная информация (никуда не отправляется):</h3>
            <table id="question2">
            <tr>
            <td style="background:#FFF8EB;">Статус:</td>
            <td colspan="2" style="background:#FFF8EB;">Комментарий</td>
            </tr>

            <tr>
            <td>
            <select name="status" style="padding: 5px">
            <?=sections($content['status']);?>
            </select>
            </td>

            <td>
            <textarea style="width: 650px; height: 55px;      overflow: hidden;    border: 1px solid #cfcfcf;" name="comment"><?=$content['comment'];?></textarea>
            </td>

            <td>
            <div style="height: 100%;"><input class="green_button" style="margin-bottom: 6px;height: 55px;vertical-align: bottom;" type="submit" value=" Сохранить " /> </div>
            </td>

            </tr>

            </table>
</form>
<?php } ?>
       <?php } ?>
         <?php if ($content['status'] != 'Уведомление' || ($content['need_answer'] == 1 && $content['status'] == 'Уведомление')) { ?>
         <form id="send" class="sendform" method="POST" enctype="multipart/form-data">
            <div class="adm-form">
                <br>
<h3 style="padding-left: 5px;">Общая информация:</h3><br>
            <table id="question2">
            <tr>
            <td style="background:#FFF8EB;">СЦ</td>
            <td style="background:#FFF8EB;">Дата запроса</td>
            <td style="background:#FFF8EB;">ФИО</td>
            <td style="background:#FFF8EB;">Email</td>
            <td style="background:#FFF8EB;">Телефон</td>
            </tr>
            <tr>
            <td><?=$content['service_info']['name'];?></td>
            <td><?=$content['date'];?></td>
            <td><?=$content['service_info']['contact_fio'];?></td>
            <td><?=$content['service_info']['contact_email'];?></td>
            <td><?=$content['service_info']['contact_phone'];?></td>
            </tr>
            </table>   <br>
            <h3 style="padding-left: 5px;">Переписка:</h3><br>

<?php

if ($_GET['id']) {
  if (\models\User::hasRole('admin')) {
  $feedback = mysqli_query($db, 'SELECT * FROM `feedback_messages` WHERE `feedback_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\';');
  if (mysqli_num_rows($feedback) != false) {
    while ($row = mysqli_fetch_array($feedback)) {
      if ($row['user_type'] == 1) {
      echo '
            <table id="question">
            <tr>
            <td width="100px">СЦ:</td>
            <td width="200px">'.$row['date'].'</td>
             <td style="text-align:left;" class="message_orig mailing"><textarea style="display:none;height: 250px;font-size: inherit;" name="message_edit" data-id="'.$row['id'].'">'.$row['message'].'</textarea><span>'.$row['message'].'</span></td>
             <td class="editable linkz" style="width: 40px;background: #fff;"><a class="t-3 edit_message" data-id="'.$row['id'].'" href="#" ></a><a class="t-5 delete_message" data-id="'.$row['id'].'" style="float:" onclick="return confirm(\'Подтвердите удаление сообщения\');" href="#"></a></td>
           </tr>
            </table>
            <br />';
      } else {
      echo '
            <table id="answer">
            <tr>
            <td width="100px">Поддержка</td>
            <td width="200px">'.$row['date'].'</td>
            <td style="text-align:left;" class="message_orig mailing"><textarea style="display:none;height: 250px;font-size: inherit;" name="message_edit" data-id="'.$row['id'].'">'.$row['message'].'</textarea><span>'.$row['message'].'</span></td>
             <td class="editable linkz" style="width: 40px;background: #fff;"><a class="t-3 edit_message" data-id="'.$row['id'].'" href="#" ></a><a class="t-5 delete_message" data-id="'.$row['id'].'" style="float:" onclick="return confirm(\'Подтвердите удаление сообщения\');" href="#"></a></td>
            </tr>
            </table><br>';
      }

    }
  }
  } else {
  $feedback = mysqli_query($db, 'SELECT * FROM `feedback_messages` WHERE `feedback_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\';');
  if (mysqli_num_rows($feedback) != false) {
    while ($row = mysqli_fetch_array($feedback)) {
      if ($row['user_type'] == 1) {
      echo '
            <table id="question">
            <tr>
            <td width="100px">СЦ:</td>
            <td width="200px">'.$row['date'].'</td>
            <td style="text-align:left;" class="mailing">'.$row['message'].'</td>
            </tr>
            </table>
            <br />';
      } else {
      echo '
            <table id="answer">
            <tr>
            <td width="100px">Поддержка</td>
            <td width="200px">'.$row['date'].'</td>
            <td style="text-align:left;" class="mailing">'.$row['message'].'</td>
            </tr>
            </table><br>';
      }

    }
  }
  }
}

 $sql2 = mysqli_query($db, 'SELECT * FROM `feedback_photos` where `feedback_id` = '.$content['id']);
      if (mysqli_num_rows($sql2) != false) {
      while ($row2 = mysqli_fetch_array($sql2)) {
       $content['parts_pics'] .= '<div class="part"><div class="item" style="width:100%"><div class="level">Фото</div><div class="value"><a href="'.$row2['url'].'" target="_blank"><img style="max-width:300px;" src="'.$row2['url'].'"></a></div></div></div>';
      }
      }

$sql3 = mysqli_query($db, 'SELECT * FROM `feedback_videos` where `feedback_id` = '.$content['id']);
      if (mysqli_num_rows($sql3) != false) {
      while ($row3 = mysqli_fetch_array($sql3)) {
       $content['parts_videos'] .= '<div class="part"><div class="item" style="width:100%" ><div class="level" >Видео</div><div class="value"><video controls class="video" width="720px" height="auto" style="position:relative;display:block;margin: 0 auto;"><source src="'.$row3['url'].'"></video></div></div></div>';
      }
      }

            ?>

          <?php } else { ?>
            <h3 style="padding-left: 5px;">Сообщение:</h3><br>

<?php

if ($_GET['id']) {
  $feedback = mysqli_query($db, 'SELECT * FROM `feedback_messages` WHERE `feedback_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\';');
  if (mysqli_num_rows($feedback) != false) {
    while ($row = mysqli_fetch_array($feedback)) {
      if ($row['user_type'] == 1) {
      echo $row['date'].'<br><div style="width:100%" class="mailing">'.$row['message'].'</div>';
      } else {
      echo $row['date'].'<br><div style="width:100%" class="mailing">'.$row['message'].'</div>';
      }

    }
  }
}

 $sql2 = mysqli_query($db, 'SELECT * FROM `feedback_photos` where `feedback_id` = '.$content['id']);
      if (mysqli_num_rows($sql2) != false) {
      while ($row2 = mysqli_fetch_array($sql2)) {
       $content['parts_pics'] .= '<div class="part" ><div class="item" style="width:100%"><div class="level">Фото</div><div class="value"><a href="'.$row2['url'].'" target="_blank"><img style="max-width:300px;" src="'.$row2['url'].'"></a></div></div></div>';
      }
      }

$sql3 = mysqli_query($db, 'SELECT * FROM `feedback_videos` where `feedback_id` = '.$content['id']);
      if (mysqli_num_rows($sql3) != false) {
      while ($row3 = mysqli_fetch_array($sql3)) {
       $content['parts_videos'] .= '<div class="part"><div class="item" style="width:100%" ><div class="level" >Видео</div><div class="value"><video controls class="video" width="720px" height="auto" style="position:relative;display:block;margin: 0 auto;"><source src="'.$row3['url'].'"></video></div></div></div>';
      }
      }

            ?>

<?php } ?>


             <?php if ($content['status'] != 'Уведомление' || ($content['need_answer'] == 1 && $content['status'] == 'Уведомление')) { ?>
            <div id="add_form">

            <div class="item" style="display: block;  width: 100%;">
              <div class="level" style="display: block;  width: 100%;">Ответ:</div>
              <div class="value" style="display: block;  width: 100%;">
                <div class="adm-w-text" style="border:0px;">
                  <textarea id="redactor_text" name="answer" rows="5"></textarea>
                </div>
              </div>
            </div>

    <div class="field input_fields_wrap">

    </div>
    <div class="add adm-add add_to_list" >
      <a href="#" class="add_field_button"><u>Добавить еще фото</u></a>
    </div><br>

    <div class="field input_fields_wrap2">

    </div>
    <div class="add adm-add add_to_list" >
      <a href="#" class="add_field_button2"><u>Добавить еще видео</u></a>
    </div><br>
     <?php } ?>
    <hr><br>
    <?php if ($content['parts_pics']) { ?>
    <div class="field">
       <?=$content['parts_pics'];?>
    </div> <br>
    <?php } ?>
    <?php if ($content['parts_videos']) { ?>
    <div class="field">
       <?=$content['parts_videos'];?>
    </div>
    <?php } ?>

          <?php if ($content['status'] != 'Уведомление' || ($content['need_answer'] == 1 && $content['status'] == 'Уведомление')) { ?>

            <div class="adm-finish">
            <div class="save">
              <input type="hidden" name="send" value="1" />
              <button type="button" class="save_ans submitko" >Отправить</button>
            </div>
            </div>


            </div>
             <?php } ?>

        </div>

      </form>

            <?php } else { ?>

  <h2>Сообщения</h2>
<br>

  <div class="adm-catalog">
     <?php if (!User::hasRole('admin')) { ?>
     <div class="add">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/add-ticket/" class="button">Добавить запрос</a>
    </div>
    <?php } else { ?>
      <div class="add" style="padding-top:0px;display:inline-block;">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/notify/" class="button">Рассылка</a>
    </div>
      <div class="add" style="padding-top:0px; display:inline-block;">
      <a style="width: auto;padding-left: 7px;padding-right: 7px;" href="/logs-notify/" class="button">История рассылк</a>
    </div>
    <br> <br>
    <?php } ?>

<?php if (\models\User::hasRole('admin')) { ?>
<form method="GET">
<table width="100%">
 <tr>
<td colspan="5">
<font style="color:#FF9900"><input id="impo" type="checkbox" name="important" value="1"  <?php if ($_GET['important'] == 'true') { echo 'checked'; } ?>/> Требуют внимания (<?=check_important();?>)</font>
<br><br></td>

 </tr>
<tr>
<td>Фильтр запросов:</td>   
<td><span id="two-inputs">От <input type="text" id="date-range200" name="date1" style="width: 100px;height: 20px;padding:0;" value="<?=($_GET['date1'] ? $_GET['date1'] : '')?>"/> До <input name="date2"  type="text" id="date-range201" style="width: 100px;height: 20px;padding:0;" value="<?=($_GET['date2'] ? $_GET['date2'] : '')?>"/></span></td>
<td><select name="status">

  <option value="">Выберите статус</option>
  <option value="Вопрос открыт" <?php if ($_GET['status'] == 'Вопрос открыт') { echo 'selected';}?>>Вопрос открыт</option>
  <option value="Вопрос закрыт" <?php if ($_GET['status'] == 'Вопрос закрыт') { echo 'selected';}?>>Вопрос закрыт</option>
  <option value="Ждем уточнения" <?php if ($_GET['status'] == 'Ждем уточнения') { echo 'selected';}?>>Ждем уточнения</option>
</select></td>
<td><input class="green_button" type="submit" value="Применить" /></td>
<td align="right"><a href="excel.php?<?=($_GET['date1'] ? 'date1='.$_GET['date1'] : '')?><?=($_GET['date1'] ? '&date2='.$_GET['date2'] : '')?><?=($_GET['date1'] ? '&model_id='.$_GET['model_id'] : '')?>" class="add_link"><b>Экспорт в xls</b></a></td>
</tr>
</table>
</form>
<?php } ?>

       <br>
       <form method="POST">
         <div id="table_content_wrapper" class="dataTables_wrapper no-footer">
  <table id="table_content" class="display dataTable no-footer" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th align="left" class="sorting_desc">id</th>
                <th align="left" class="sorting_desc" width="120px">СЦ</th>
                <th align="left" width="120px" class="sorting_desc">Дата запроса</th>
                <th align="left" width="120px" class="sorting_desc">Дата ответа</th>
                <th align="left" width="100px" class="sorting_desc">Статус</th>
                <th align="left" width="200px" class="sorting_desc">Запрос</th>
                <th align="center" class="sorting_desc">Обработан</th>
                <th class="sorting_desc">Операции</th>
            </tr>

        </thead>

        <tbody>
        <?=$content_list['body'];?>
        </tbody>
</table>
<div class="dataTables_paginate paging_simple_numbers" id="table_content_paginate">
<?=$content_list['pager'];?>
</div>
 </div>
</form>

  </div><!-- .adm-catalog -->            <?php } ?>

</div><!-- .wrapper -->

</div><!-- .viewport-wrapper -->

</body>
</html>