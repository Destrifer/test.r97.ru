<?php
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/configuration.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/back/autoload.php';

use models\Feedback;
use models\Log;
use models\Repair;
use models\Services;
use models\staff\Staff;
use models\Support;
use models\Tariffs;
use models\User;
use models\Users;
use program\core;
use program\core\Time;

if ($_GET['type'] == 'save-settings') {
  $resp = ['message' => '', 'error_flag' => 0];
  if((empty($_POST['repair_id']) || $_POST['anrp_param'] < 0) || ($_POST['anrp_param'] == $_POST['cur_anrp_param'])){
    echo json_encode($resp);
    exit;
  }
  \models\services\Settings::saveSettings('repair', $_POST['repair_id'], ['anrp_value' => $_POST['anrp_param']]);
  if(User::hasRole('admin') && $_POST['anrp_param'] == 1) { // оповестить, что прибор оставлен СЦ
    $repair = Repair::getRepairByID($_POST['repair_id']);
    if(!$repair || $repair['service_id'] == 33){
      echo json_encode($resp);
      exit;
    }
    $message = 'Данный товар остаётся в вашем сервисе на ответственное хранение. Все исправные детали добавляются на ваш склад Разбор, с которого потом эти детали могут быть использованы для другого ремонта нашей техники.';
    Support::sendMessage(['message' => $message, 'repair_id' => $_POST['repair_id']], User::getData('role_id'));
    \models\Sender::use('bell')->to([$repair['service_id']])->send('Товар оставлен в сервисе', 'Товар оставлен в сервисе на ответственное хранение.', '/edit-repair/'.$_POST['repair_id'].'/step/6/');
    \models\Sender::use('email')->from('robot@crm.r97.ru')->to([\models\Users::getEmail($repair['service_id'])])->send('Товар оставлен в сервисе', ['message' => $message . '<br><a href="https://crm.r97.ru/edit-repair/'.$_POST['repair_id'].'/step/6/">Ремонт #'.$repair['id'].'</a>'], 'notification');
  }
  echo json_encode($resp);
  exit;
}


function count_pay_master_funk($pay, $user_id = '', $old = '') {
$id = ($_GET['master_id'] != '') ? $_GET['master_id'] : $user_id;
$master = Staff::getStaff(['id' => $id]);
$salary = $master['salary'];
$percent = $master['percent'];

if ($old == 1) {

if ($salary < $pay) {
return $salary+(($pay-$salary)*0.3);
} else {
return $salary;
}

} else if ($old == 0 && $old == '') {
return $pay*($percent/100);

}



}

function model_info($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
     // print_r($row);
      }
    return $content;
}

function client_notify($email, $return_id, $status, $client_name = '') {
    global $config, $db;
$content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $return_id).'\' LIMIT 1;'));
$sql_tasks = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $return_id).'\';');

    if (mysqli_num_rows($sql_tasks) != false)
      while ($row = mysqli_fetch_array($sql_tasks)) {

        $model = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $row['model_id']).'\' LIMIT 1;'));

        $date2 = DateTime::createFromFormat('Y.m.d', $row['app_date']);
        if ($date2) {
        $date2_ready = $date2->format('d.m.Y');
        }

        $message_models .= '<tr>
        <td style="padding:7px;border:1px solid #ccc;">'.$model['name'].'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.$row['serial'].'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.core\Time::format($row['receive_date']).'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.$date2_ready.'</td>
        <td style="padding:7px;border:1px solid #ccc;">'.$row['bugs'].'</td>
        </tr>';
        unset($date2_ready);

      }


if ($status == 1) {
$subject = 'Партия получила статус Принята';
// принят
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Партия '.$content['name'].' / Клиент '.$client_name.' получила статус Принята</h3><br>
                      <br>
                      <h4>Информация о партии</h4>
                      <br>
                      <table style="border-collapse:collapse">
                      <tr>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Модель</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Серийный номер</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата приема</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата готовности</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Неисправность</strong></td>
                      </tr>
                      '.$message_models.'
                      </table><br>
                      <br>
                      <br>
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
                      </body>

                    </html>';

}
if ($status == 2) {
  $subject = 'Партия получила статус Подтверждена';
// подтвержден
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Партия '.$content['name'].' / Клиент '.$client_name.' получила статус Подтверждена</h3><br>
                      <br>
                      <h4>Информация о партии</h4>
                      <br>
                      <table style="border-collapse:collapse">
                      <tr>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Модель</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Серийный номер</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата приема</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата готовности</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Неисправность</strong></td>
                      </tr>
                      '.$message_models.'
                      </table><br>
                      <br>
                      <br>
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
                      </body>

                    </html>';

}
if ($status == 3) {
  $subject = 'Партия получила статус Выдана';
// выдан
$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>Партия '.$content['name'].' / Клиент '.$client_name.' получила статус Выдана</h3><br>
                      <br>
                      <h4>Информация о партии</h4>
                      <br>
                      <table style="border-collapse:collapse">
                      <tr>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Модель</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Серийный номер</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата приема</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Дата готовности</strong></td>
                      <td style="padding:7px;border:1px solid #ccc;"><strong>Неисправность</strong></td>
                      </tr>
                      '.$message_models.'
                      </table><br>
                      <br>
                      <br>
                      '.$config['email_footer'].'
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
                      </body>

                    </html>';

}

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');

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
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = $subject;
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
//$mail->MailerDebug = true;

if(!$mail->send()) {
    //echo 'Message could not be sent.';
   //echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
   // echo 'Message has been sent';
}


}

function get_user_info2($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `users` WHERE `id` = '.$id));
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

function notice_add($subject, $text, $user_id, $link = '') {
    global $config, $db;
require($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');

mysqli_query($db, 'INSERT INTO `notification` (
`subject`,
`text`,
`user_id`,
`link`
) VALUES (
\''.mysqli_real_escape_string($db, $subject).'\',
\''.mysqli_real_escape_string($db, $text).'\',
\''.mysqli_real_escape_string($db, $user_id).'\',
\''.mysqli_real_escape_string($db, $link).'\'
);') or mysqli_error($db);



$userinfo = get_user_info2($user_id);

if ($user_id == 1) {
  $toEmail = 'service3@harper.ru';
}else{
  $toEmail = $userinfo['email'];
}

$mes = '<html>
                      <body bgcolor="#DCEEFC">
                      <h3>У вас новое уведомление</h3><br>
                      '.$subject.'
                      <br>
                      <br>
                      '.$text.'
                      <br>
                      '.(($link) ? '<a href="'.$link.'">Подробнее</a>' : '').'
                      <br>
                      <br>
                      '.$config['email_footer'].'
- -  <br>
С уважением, <br>
Служба поддержки HARPER, OLTO, SKYLINE и TESLER <br>
e-mail: <a href="mailto:kan@r97.ru">kan@r97.ru</a>; <a href="mailto:service2@harper.ru">service2@harper.ru</a>
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
$mail->addAddress($toEmail);
$mail->isHTML(true);
$mail->Subject = $subject;
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
//$mail->MailerDebug = true;

if(!$mail->send()) {
    //echo 'Message could not be sent.';
   //echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
   // echo 'Message has been sent';
}
   




/*$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.mail.ru';
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = 'robot@harper.ru';
$mail->Password = '!@qwASzx';
$mail->Timeout       =  10;
$mail->setFrom('robot@harper.ru', 'Harper.ru');
$mail->Subject = "Новое уведомление от HARPER.RU";
$mail->CharSet = 'UTF-8';
$mail->msgHTML($body);


foreach ($result as $row) { //This iterator syntax only works in PHP 5.4+
    $mail->addAddress($row['email'], $row['full_name']);
    if (!empty($row['photo'])) {
        $mail->addStringAttachment($row['photo'], 'YourPhoto.jpg'); //Assumes the image data is stored in the DB
    }

    if (!$mail->send()) {
        echo "Mailer Error (" . str_replace("@", "&#64;", $row["email"]) . ') ' . $mail->ErrorInfo . '<br />';
        break; //Abandon sending
    } else {
        echo "Message sent to :" . $row['full_name'] . ' (' . str_replace("@", "&#64;", $row['email']) . ')<br />';
        //Mark it as sent in the DB
        mysqli_query(
            $mysql,
            "UPDATE mailinglist SET sent = true WHERE email = '" .
            mysqli_real_escape_string($mysql, $row['email']) . "'"
        );
    }

    $mail->clearAddresses();
    $mail->clearAttachments();
}
     */

}

function admin_log_add($name) {
    global $db;

mysqli_query($db, 'INSERT INTO `admin_logs` (
`name`
) VALUES (
\''.mysqli_real_escape_string($db, $name).'\'
);') or mysqli_error($db);


}

function get_modelid_by_name($name) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` WHERE `name` = \''.mysqli_real_escape_string($db, $name).'\'');
return mysqli_fetch_array($sql);
}

function get_model_by_id($name) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $name).'\'');
return mysqli_fetch_array($sql);
}

function get_provider_name($id)  {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `providers` WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\'');
return mysqli_fetch_array($sql)['name'];
}

function group_info($name) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `groups` WHERE `name` = \''.mysqli_real_escape_string($db, $name).'\'');
return mysqli_fetch_array($sql);
}

function group_by_name($name) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `groups` WHERE `cat` = \''.mysqli_real_escape_string($db, $name).'\'');
      while ($row = mysqli_fetch_array($sql)) {
      $content .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
      }
    return $content;
}

function check_serial($serial, $modelID) {
  return \models\Serials::isValid($serial, $modelID);
}


function doubl($serial, $modelID, $id)
{
  global $db;
  $content = '';
  $repestRepairs = \models\Repair::getRepeatedRepairs($id, $serial, $modelID);
  if(!$repestRepairs){
    return '';
  }
  $content .= '
  <div style="color:red">Внимание! Данная техника поступила повторно. Подробности в сводке ниже, либо обратитесь к администратору программы.</div>
  <table style="background: #fff; border: 1px solid #ccc; font-size: 15px;width: 100%;">';
  $content .= '<tr>
  <th style="padding:5px;text-align:left">№ Карточки</th>
  <th style="padding:5px;text-align:left">СЦ</th>
  <th style="padding:5px;text-align:left">Город</th>
  <th style="padding:5px;text-align:left">Дата продажи</th>
  <th style="padding:5px;text-align:left">Дата приема</th>
  <th style="padding:5px;text-align:left">Проблема</th>
  <th style="padding:5px;text-align:left">Итоги ремонта</th>';
  if (\models\User::hasRole('slave-admin', 'admin', 'master')) {
    $content .= '<th style="padding:5px;text-align:left">Мастер</th>';
  } 
  $content .= '<th style="padding:5px;text-align:left">Статус</th>
  </tr>';
  foreach ($repestRepairs as $row) {
    if ($row['service_id'] == 33 && $row['master_user_id']) {
      $master = Staff::getStaff(['id' => $row['master_user_id']]);
    } else if ($row['service_id'] != 33 && $row['master_id']) {
      $master = mysqli_fetch_array(mysqli_query($db, 'SELECT `name`, `surname`, `third_name` FROM `repairmans` WHERE `id` = \'' . mysqli_real_escape_string($db, $row['master_id']) . '\''));
    }
    $service = get_service_info2($row['service_id']);
    
    $link = (\models\User::hasRole('admin', 'slave-admin', 'taker') || User::getData('id') == $row['service_id'] || ($row['service_id'] == 33 && \models\User::hasRole('master'))) ? '<a target="_blank" href="/edit-repair/' . $row['id'] . '/">' . $row['id'] . '</a>' : $row['id'];
    $masterCol = '';
    if (\models\User::hasRole('slave-admin', 'admin', 'master')) {
      $masterCol = '<td style="padding:5px;">' . $master['surname'] . ' ' . $master['name'] . ' ' . $master['thirdname'] . '</td>';
    } 
      $content .= '<tr>
      <td style="padding:5px;">' . $link . '</td>
      <td style="padding:5px;">' . $service['name'] . '</td>
      <td style="padding:5px;">' . $service['city_name'] . '</td>
      <td style="padding:5px;">' . core\Time::format($row['sell_date']) . '</td>
      <td style="padding:5px;">' . core\Time::format($row['receive_date']) . '</td>
      <td style="padding:5px;">' . $row['bugs'] . '</td>
      <td style="padding:5px;">' . $row['repair_name'] . '</td>
      '.$masterCol.'
      <td style="padding:5px;">' . $row['status_admin'] . '</td>
      </tr>';
  }
  $content .= '</table>';
  return $content;
}


function models($cat_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `cat` = \''.$cat_id.'\';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['model_id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['model_id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function serials($cat_id, $model_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `parts` where `cat` = \''.$cat_id.'\' and `model_id` = \''.$model_id.'\';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['serial'].'">'.$row['serial'].'</option>';
      } else {
       $content .= '<option value="'.$row['serial'].'">'.$row['serial'].'</option>';
      }
      }
    return $content;
}

function groups($model_id, $serial) {
  global $db;
$sql = mysqli_query($db, 'SELECT DISTINCT(`group`) FROM `parts` where `model_id` = \''.$model_id.'\' and `serial` = \''.$serial.'\';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['group'].'">'.$row['group'].'</option>';
      } else {
       $content .= '<option value="'.$row['group'].'">'.$row['group'].'</option>';
      }
      }
    return $content;
}

function groups_all($model_id, $serial) {
  global $db;
$sql = mysqli_query($db, 'SELECT DISTINCT(`group`) FROM `parts` order by `group` ASC;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']) {
      $content .= '<option selected value="'.$row['group'].'">'.$row['group'].'</option>';
      } else {
       $content .= '<option value="'.$row['group'].'">'.$row['group'].'</option>';
      }
      }
    return $content;
}

function groups_by_cat_id($cat) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `groups` where `name` != \'\' and `cat` = '.$cat.';');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['name']) {
      $content .= '<option selected value="'.$row['name'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}
function parts($cat_id, $model_id, $serial, $group) {
  global $db;
  $content = '';
//$serial_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `serials` WHERE `serial` = \''.mysqli_real_escape_string($db, $serial).'\' and `model_id` = '.$model_id));
 //
$sql = mysqli_query($db, 'SELECT `id`, `list` FROM `parts` where `parent_id` = 0 and `cat` = \''.$cat_id.'\' and `group` = \''.$group.'\';');//and count > 0
  while ($row = mysqli_fetch_array($sql)) {
    $selected = ($cat_id == $row['id']) ? 'selected' : '';
    $content .= '<option '.$selected.' value="' . $row['id'] . '">' . $row['list'] . '</option>';
  }
return $content;
}

function parts_all($group = '', $model_id = '', $part_id = '') {
  global $db;

if ($group != '') {
$where = 'and `group` = \''.trim($group).'\'';
}

if ($model_id != '') {
$where .= 'and `model_id` = \''.trim($model_id).'\'';
}

$sql = mysqli_query($db, 'SELECT * FROM `parts` where `parent_id` = 0 '.$where.' and `count` > 0 order by `list` desc');
if (mysqli_num_rows($sql) > 0) {
      while ($row = mysqli_fetch_array($sql)) {
    $content2 .= '<option value="'.$row['id'].'">'.str_replace('\'', '', htmlspecialchars(stripslashes($row['list']))).'</option>';

}
}

if ($part_id != '') {
$part_where = ' id = '.$part_id.' or ';
}

/*дочки*/
$sql = mysqli_query($db, 'SELECT * FROM `parts` WHERE '.$part_where.' id IN ( SELECT MAX(id) FROM parts where `count` > 0 '.$where.' AND `parent_id` != 0 AND `parent_id` != \'\' GROUP BY parent_id ) ORDER BY id desc');
//$sql = mysqli_query($db, 'SELECT * FROM `parts` where `parent_id` != \'\' and `parent_id` != 0 '.$where.' and `count` > 0 order by id desc');

if (mysqli_num_rows($sql) > 0) {
      while ($row = mysqli_fetch_array($sql)) {
    $content2 .= '<option value="'.$row['id'].'">'.str_replace('\'', '', htmlspecialchars(stripslashes($row['list']))).'</option>';

}
}

return $content2;
}


function cat_by_id($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `cats` where `id` = \''.$id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;
      }
    return $content;
}

function categories_by_model($model_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = \''.$model_id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content['body'] .= '<option selected value="'.$row['cat'].'">'.cat_by_id($row['cat'])['name'].'</option>';
      $content['id'] = $row['id'];
      }
    return $content;
}

function groups_by_cat($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = \''.$id.'\' LIMIT 1;');
      $content .= group_by_name(51);
      while ($row = mysqli_fetch_array($sql)) {
      $sql2 = mysqli_query($db, 'SELECT * FROM `groups` where `cat` = \''.$row['cat'].'\' order by `name` ASC;');
            while ($row2 = mysqli_fetch_array($sql2)) {
             $content .= '<option value="'.$row2['name'].'">'.$row2['name'].'</option>';
            }

      }
    return '<option value="">Выберите вариант</option>'.$content;
}

function repair_type_name($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT `name` FROM `repair_type` where `id` = \''.$id.'\' LIMIT 1;');
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row['name'];
      }
    return $content;
}

function get_problem_price($id, $cat, $service_id) {
  global $db;
  $table = Tariffs::getServiceTariffTable($service_id);
  $sql = mysqli_fetch_array(mysqli_query($db, 'SELECT `type` FROM `re` where `user_id` = '.$service_id));
$sql = mysqli_query($db, 'SELECT `type` FROM `details_problem` where `id` = \''.$id.'\' ;');
            while ($row = mysqli_fetch_array($sql)) {

              switch($row['type']) {

              case 'Всегда блочный ремонт':

                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `block` FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['block'];
                //echo 'SELECT `block` FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;';
               } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `block` FROM `'.$table.'` where `cat_id` = \''.$cat.'\';'))['block'];
                }
                break;
              case 'АНРП':

                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['anrp'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `'.$table.'` where `cat_id` = \''.$cat.'\';'))['anrp'];
                }
                break;
              case 'АТО':

                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `ato` FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['ato'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `ato` FROM `'.$table.'` where `cat_id` = \''.$cat.'\';'))['ato'];
                }
                break;
              case 'Всегда компонентный ремонт':

                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `component` FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['component'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `element` FROM `'.$table.'` where `cat_id` = \''.$cat.'\';'))['element'];
                }
                break;
              case 'Замена аксессуаров':
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `access` FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['access'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `acess` FROM `'.$table.'` where `cat_id` = \''.$cat.'\';'))['acess'];
                }
                break;
              case 'Просто ремонт':
                if (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['COUNT(*)'] > 0) {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `simple` FROM `prices_service` where `cat_id` = \''.$cat.'\' and `service_id` = \''.$service_id.'\' ;'))['simple'];
                } else {
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `simple` FROM `'.$table.'` where `cat_id` = \''.$cat.'\';'))['simple'];
                }
                break;
              }



            }

    return $price;
}

function get_problem_type($id, $cat) {
  global $db;
$sql = mysqli_query($db, 'SELECT `type` FROM `details_problem` where `id` = \''.$id.'\' ;');
            while ($row = mysqli_fetch_array($sql)) {

              switch($row['type']) {
              case 'Всегда блочный ремонт':
                $price = 1;
                break;
              case 'АНРП':
                $price = '4';
                break;
              case 'АТО':
                $price = 5;
                break;
              case 'Всегда компонентный ремонт':
                $price = 2;
                break;
              case 'Замена аксессуаров':
                $price = 3;
                break;
              }


            }

    return $price;
}

function get_problem_value($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `details_problem` where `id` = \''.$id.'\' ;');
            while ($row = mysqli_fetch_array($sql)) {
        
            if ($row['repair_end'] == 1) {
            $price = 1;
            }
            if ($row['repair_end2'] == 1) {
            $price = 2;
            }
            if ($row['repair_end3'] == 1) {
            $price = 3;
            }

            }

    return $price;
}

function problem_links($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `problem_link` where `problem_id` = \''.$id.'\' ;');
            while ($row = mysqli_fetch_array($sql)) {

              $content .= '<option value="'.$row['repair_type'].'">'.repair_type_name($row['repair_type']).'</option>';

            }

    return $content;
}

function get_service_info2($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT r.*, c.`fcity_name` AS city_name FROM `requests` r
 LEFT JOIN `cityfull` c ON r.`city` = c.`fcity_id` 
 WHERE r.`user_id` = '.$id));
}


function serials_select($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `serials` where `model_id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $order = ($row['order']) ? ', '.$row['order'] : '';
      if ($id == $row['id']) {
      $content .= '<option selected value="'.$row['serial'].'">'.$row['serial'].'('.get_provider_name($row['provider_id']).''.$order.')</option>';
      } else {
       $content .= '<option value="'.$row['serial'].'">'.$row['serial'].' ('.get_provider_name($row['provider_id']).''.$order.')</option>';
      }
      }
    return $content;
}

function get_payment_info($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `pay_billing` WHERE `id` = '.$id.';');
return mysqli_fetch_array($sql);
}

$_monthsList = array(
"1"=>"Январь","2"=>"Февраль","3"=>"Март",
"4"=>"Апрель","5"=>"Май", "6"=>"Июнь",
"7"=>"Июль","8"=>"Август","9"=>"Сентябрь",
"10"=>"Октябрь","11"=>"Ноябрь","12"=>"Декабрь");

$_monthsList2 = array(
"01"=>"Января","02"=>"Февраля","03"=>"Марта",
"04"=>"Апреля","05"=>"Мая", "06"=>"Июня",
"07"=>"Июля","08"=>"Августа","09"=>"Сентября",
"10"=>"Октября","11"=>"Ноября","12"=>"Декабря");

if ($_GET['type'] == 'update_repair_status') {
  if ($_GET['value'] == 'oncheck') {
    $_GET['value'] = 'На проверке';
  }
  if ($_GET['value'] == 'onway') {
    $_GET['value'] = 'Запчасти в пути';
  }
  if ($_GET['value'] == 'questions') {
    $_GET['value'] = 'Есть вопросы';
  }
  if ($_GET['value'] == 'approve') {
    $_GET['value'] = 'Подтвержден';
  }
  if ($_GET['value'] == 'inwork') {
    $_GET['value'] = 'В работе';
  }
  if ($_GET['value'] == 'На проверке') {
    \models\Repair::changeRepairFinal($_GET['id']);
  }
  if ($_GET['value'] == 'Подтвержден') {
    \models\Repair::changeRepairFinal($_GET['id']);
    $res = \models\Repair::moveSavedPartsToService($_GET['id']);
    if($res['error_flag']){
      echo json_encode($res);
      exit;
    }
    $res = \models\Repair::updateInstallCost($_GET['id']);
    if($res['error_flag']){
      echo json_encode($res);
      exit;
    }
    if (User::hasRole('master')) {
      $res = \models\Repair::applyPartsPrice($_GET['id']);
      if ($res['error_flag']) {
        echo json_encode($res);
        exit;
      }
    }
  }
  $repair = \models\Repair::getRepairByID($_GET['id']);
  if($repair['status'] != $_GET['value']){
    \models\Repair::changeStatus($_GET['id'], $_GET['value']);
    \models\Log::repair(1, '"'.$repair['status'].'" на "'.$_GET['value'].'".', $repair['id']);
  }
  if (in_array($_GET['value'], ['Подтвержден', 'Выдан'])) {
    $ids = array_filter([244, 307], function ($id) {
      return !\models\Users::isBlocked($id);
    });
    if($repair['client_type'] == 1){
      \models\Sender::use('bell')->to($ids)->send('Ремонт завершен', 'Ремонт №'.$_GET['id'].' от Потребителя завершен.', '/edit-repair/'.$_GET['id'].'/');
    }
    if ($repair['return_id'] && \models\Returns::getBatchProgress($repair['return_id']) == 100) {
      $returns = \models\Returns::getBatchByID($repair['return_id']);
      $client = \models\Clients::getClientByID($returns['client_id']);
      $message = 'Клиент: ' . (($client) ? $client['name'] : '- не найден -') . ', дата завершения: ' . date('d.m.Y') . '.';
      $mails = [];
      foreach ($ids as $id) {
        $mails[] = \models\Users::getEmail($id);
      }
      if($repair['service_id'] != 33){
        \models\Sender::use('bell')->to([$repair['service_id']])->send('Карточка №'.$_GET['id'].' подтверждена', 'Подробности по ссылке.', '/edit-repair/'.$_GET['id'].'/');
      }
      \models\Sender::use('bell')->to($ids)->send('Партия завершена', $message, '/returns/?search=' . $returns['name']);
      \models\Sender::use('email')->from('robot@crm.r97.ru')->to($mails)->send('Партия завершена', ['message' => $message . '<br><a href="https://crm.r97.ru/returns/?search=' . $returns['name'] . '">Перейти к партии</a>'], 'notification');
    }
  }

$content = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
$sql_check_count = @mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $content['return_id']).'\' and `deleted` = 0;'));
$sql_check_count2 = @mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $content['return_id']).'\' and `status_admin` = \'Подтвержден\' and `deleted` = 0;'));

if (($sql_check_count['COUNT(*)'] == $sql_check_count2['COUNT(*)']) && ($sql_check_count2['COUNT(*)'] != 0 && $sql_check_count['COUNT(*)'] != 0)) {
mysqli_query($db, 'UPDATE `returns` SET `light` = 1 where `id` = '.$content['return_id']);
}

echo json_encode(['message' => '', 'error_flag' => 0]);
exit;
}

if ($_GET['type'] == 'returns_out') {
//$date_out = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `return_id` = '.$_GET['id'].' ORDER BY `repairs`.`app_date` DESC LIMIT 1'))['app_date'];
$usd = str_replace(',', '.', json_decode(@file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js'))->Valute->USD->Value);
mysqli_query($db, 'UPDATE `returns` SET `out` = 1, `usd` = \''.$usd.'\',  `light` = 0, `date_farewell` = \''.date('Y.m.d').'\' where `id` = '.$_GET['id']);
mysqli_query($db, 'UPDATE `repairs` SET `status_admin` = "Выдан", `out_date` = "'.date('Y-m-d').'" WHERE `return_id` = '.$_GET['id']);


$content = mysqli_fetch_array(mysqli_query($db, 'SELECT client_id FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
$content_client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));

if ($content_client['manager_notify'] == 1) {
client_notify($content_client['manager_email'], $_GET['id'], 3, $content_client['name']);
}
if ($content_client['manager_contact_notify'] == 1) {
client_notify($content_client['contacts_phone'], $_GET['id'], 3, $content_client['name']);
}

}

if ($_GET['type'] == 'returns_out_minus') {
mysqli_query($db, 'UPDATE `returns` SET `out` = 0, `light` = 0, `date_farewell` = \'\' where `id` = '.$_GET['id']);
mysqli_query($db, 'UPDATE `repairs` SET `status_admin` = \'Подтвержден\' where `return_id` = '.$_GET['id']);
}

if ($_GET['type'] == 'update_user_cats') {


mysqli_query($db, 'UPDATE `cats_users` SET
`service` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'add_master') {

$status = ($_GET['value'] == 0) ? '`status_admin` = \'Принят\', ' : '`status_admin` = \'В работе\', ';
mysqli_query($db, 'UPDATE `repairs` SET
`master_user_id` = \''.mysqli_real_escape_string($db, $_GET['value']).'\',
'.$status.' 
`begin_date` = \''.mysqli_real_escape_string($db, date('Y-m-d')).'\' 
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'update_user_models') {
mysqli_query($db, 'UPDATE `models_users` SET
`service` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
exit;
}

if ($_GET['type'] == 'update_document_status') {

mysqli_query($db, 'UPDATE `services_documents` SET
`status` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);


admin_log_add('Обновлен статус документа #'.$_GET['id']);

}


if ($_GET['type'] == 'update_repair_appdate') {

$content = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));

$return_info = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $content['return_id']).'\' LIMIT 1;'));

if ($return_info['out'] != 1) {
mysqli_query($db, 'UPDATE `repairs` SET
`app_date` = \''.mysqli_real_escape_string($db, $_GET['value']).'\', 
`approve_date` = "'.str_replace('.', '-', $_GET['value']).'" 
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);


admin_log_add('Обновлена дата подтверждения ремонта #'.$_GET['id'].' пользователем '.$_SESSION['login'].' из '.$content['app_date']);

} else {
mysqli_query($db, 'UPDATE `repairs` SET
`app_date` = \''.mysqli_real_escape_string($db, $_GET['value']).'\', 
`approve_date` = "'.str_replace('.', '-', $_GET['value']).'" 
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);


admin_log_add('Обновлена дата подтверждения ремонта #'.$_GET['id'].' пользователем '.$_SESSION['login'].' из '.$content['app_date']);
}
}

if ($_GET['type'] == 'update_combine_date') {

mysqli_query($db, 'UPDATE `pay_billing` SET
`date_pay` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'update_combine_number') {

mysqli_query($db, 'UPDATE `pay_billing` SET
`number` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'update_repair_outdate') {

mysqli_query($db, 'UPDATE `returns` SET
`date_out` = \''.mysqli_real_escape_string($db, $_GET['value']).'\',
`light` = 0
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

$sql = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$_GET['id'].'');
      while ($row = mysqli_fetch_array($sql)) {


      //$date1 = DateTime::createFromFormat('Y.m.d', $row['app_date']);
      //$date2 = DateTime::createFromFormat('d.m.Y', $_GET['value']);
      //$date2 = DateTime::createFromFormat('Y.m.d', $_GET['value']);
      //$date2_ready = $date2->format('Y.m.d');

      //if ($date1 > $date2) {

if ($return_info['out'] == 1) {

} else {
mysqli_query($db, 'UPDATE `repairs` SET
`app_date` = \''.mysqli_real_escape_string($db, $_GET['value']).'\', 
`approve_date` = "'.date('Y-m-d', strtotime($_GET['value'])).'" 
WHERE `id` = \''.mysqli_real_escape_string($db, $row['id']).'\' LIMIT 1') or mysqli_error($db);

     // }

      } 

admin_log_add('Обновлена дата выдачи возврата  #'.$_GET['id']);
}
}

if ($_GET['type'] == 'get_cities') {



echo json_encode(array('html'=> cities('', $_GET['country'])));

}

if ($_GET['type'] == 'update_pay_status') {


mysqli_query($db, 'UPDATE `pay_billing` SET
`status` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
$payment = get_payment_info($_GET['id']);

if ($_GET['value'] == 1 && $payment['service_id'] != 33) {
notice_add('Платеж отправлен.', 'Вам отправлены денежные средства по счету #'.$_GET['id'].' за '.$_monthsList[$payment['month']].' '.$payment['year'].' года.', $payment['service_id'], '/payment/');
}

admin_log_add('Обновлен статус платежа #'.$_GET['id']);

}

if ($_GET['type'] == 'update_pay_status_custom') {
mysqli_query($db, 'UPDATE `manual_docs` SET
`status` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
}

if ($_GET['type'] == 'update_part_to_model') {

$part_info = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$_GET['id']));
//if ($part_info['model_id'] != $_GET['value']) {
//$part_info['parent_id'] != 0 ||
if ($part_info['model_id'] != $_GET['value']) {
$_GET['id'] = ($part_info['parent_id'] != 0) ? $part_info['parent_id'] : $_GET['id'];
$part_info = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$_GET['id']));

if ($_GET['serial']) {
$serial_info = \models\Serials::getSerial($_GET['serial'], $part_info['model_id']);
$serial = ' \''.$serial_info['serial'].'\' ';
} else {
$serial = ' serial ';
}

mysqli_query($db, 'INSERT INTO parts
    (cat, model_id, serial, `group`, codepre, `list`, `desc`, photo, type, weight, price, part, brand, `count`, parent_id, imgs, place)
SELECT
    cat, \''.$_GET['value'].'\', '.$serial.', `group`, codepre, `list`, `desc`, photo, type, weight, price, part, brand, `count`, \''.$_GET['id'].'\', imgs, place
FROM
    parts
WHERE
    `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);



$last_id = mysqli_insert_id($db);
echo json_encode(array('new_id'=> $last_id));

} else if ($part_info['model_id'] == $_GET['value'] && $part_info['parent_id'] != 0) {

$part_info_minus = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$part_info['parent_id']));

if ($_GET['count'] > 0) {
if ($_GET['no_parts'] != 1) {
mysqli_query($db, 'UPDATE `parts` SET
`count` = count - '.$_GET['count'].'
WHERE `id` = \''.mysqli_real_escape_string($db, $part_info['parent_id']).'\' LIMIT 1') or mysqli_error($db);
}
$part_info_minus = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$part_info['parent_id']));
if ($_GET['no_parts'] != 1) {
mysqli_query($db, 'UPDATE `parts` SET
`count` = '.$part_info_minus['count'].'
WHERE `parent_id` = \''.mysqli_real_escape_string($db, $part_info['parent_id']).'\' LIMIT 1') or mysqli_error($db);
}
//admin_log_add('Запчасть #'.$id.' списана.');
//admin_log_add('Запчасть #'.$id.' отправлена.');
}

}

if ($part_info['parent_id'] == 0) {

$part_info_minus = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$part_info['id']));

if ($_GET['count'] > 0) {
  if ($_GET['no_parts'] != 1) {
mysqli_query($db, 'UPDATE `parts` SET
`count` = count - '.$_GET['count'].'
WHERE `id` = \''.mysqli_real_escape_string($db, $part_info['id']).'\' and count > 0 LIMIT 1') or mysqli_error($db);
}
$part_info_minus = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$part_info['id']));

if ($_GET['no_parts'] != 1) {
mysqli_query($db, 'UPDATE `parts` SET
`count` = '.$part_info_minus['count'].'
WHERE `parent_id` = \''.mysqli_real_escape_string($db, $part_info['id']).'\'') or mysqli_error($db);

admin_log_add('Запчасть #'.$id.' списана.');
admin_log_add('Запчасть #'.$id.' отправлена.');
}
}

}

//}

/*mysqli_query($db, 'UPDATE `parts` SET
`model_id` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db); */
echo json_encode([]);
exit;
}

if ($_GET['type'] == 'update_act_status') {


mysqli_query($db, 'UPDATE `pay_billing` SET
`original` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);


admin_log_add('Обновлен статус получения оригиналов акта #'.$_GET['id']);

}

if ($_GET['type'] == 'update_act_status_custom') {

mysqli_query($db, 'UPDATE `manual_docs` SET
`original` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'update_bill_status_custom') {

mysqli_query($db, 'UPDATE `manual_docs` SET
`original_bill` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'get_returns_report') {

$sql_returns = mysqli_query($db, 'SELECT * FROM `returns` where DATE(date_out) BETWEEN \''.str_replace('-', '.', $_GET['date1']).'\' AND \''.str_replace('-', '.', $_GET['date2']).'\';');
//echo 'SELECT * FROM `returns` where DATE(date_out) BETWEEN \''.str_replace('-', '.', $_GET['date1']).'\' AND \''.str_replace('-', '.', $_GET['date2']).'\';';
if (mysqli_num_rows($sql_returns) != false) {
 while ($row_returns = mysqli_fetch_array($sql_returns)) {

$sql_check_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row_returns['id']).'\' and `deleted` = 0;'));
$sql_check_count2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row_returns['id']).'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;'));

if ($sql_check_count['COUNT(id)'] == $sql_check_count2['COUNT(id)']) {

if ($_GET['cats'] != '') {
$cat_sql = ' and `cat_id` in ('.$_GET['cats'].') ';
}

$sql = mysqli_query($db, 'SELECT `id`,`repair_final`,`repair_type_id`,`cat_id`,`model_id`,`total_price`,`master_user_id` FROM `repairs` where `return_id` = \''.$row_returns['id'].'\' '.$cat_sql.' ;');
      while ($row = mysqli_fetch_array($sql)) {

              $model = model_info($row['model_id']);
              //echo $model['brand']."\n";
              if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO'  || $model['brand'] == 'NESONS' || $model['brand'] == 'SKYLINE') {

              // Стоимость партии возврата:
              switch($row['repair_final']) {

              case 1:
                $model_price_ato = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              case 2:
                $model_price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              case 3:
                $model_price_ato = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              }

              $return_sum += $row['total_price'];
              $return_sum_total += $row['total_price'];
              unset($price);

              // Сумма техники списанной:
              $return_usd += $model_price;
              unset($model_price);

              // Сумма техники списанной ато:
              $return_ato_usd += $model_price_ato;
              $return_ato_usd_total += $model_price_ato;
              unset($model_price_ato);

              // Сумма мастерам за работу:
              //$return_master_sum += $row['total_price'];
              $return_master_sum += count_pay_master_funk($row['total_price'], $row['master_user_id']);

              }

      }

$usd = @mysqli_fetch_array(mysqli_query($db, 'SELECT `usd` FROM `returns` where `id` = \''.$row_returns['id'].'\'  ;'))['usd'];
$ffs += $return_ato_usd*$usd-$return_sum;
unset($return_sum);
unset($return_ato_usd);

}
}
}
$color_ffs = ($ffs < 0) ? 'color:rgba(204, 0, 0, 1);' : 'color:green;';
$body = '<br><h2 style="text-align:center;font-size: 22px;">Финансовая статистика за период: '.$_GET['date1'].' &mdash; '.$_GET['date2'].' <a href="/download-returns-report/'.$_GET['date1'].'/'.$_GET['date2'].'/'.$_GET['cats'].'/" title="Скачать расширенный отчет в XLS"><img  src="https://icon-library.net/images/download-png-icon/download-png-icon-8.jpg" style="max-width:20px;"></a></h2>
  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

                  <div class="item">
              <div class="level" style="font-size: 17px;">Стоимость партии возврата:</div>
              <div class="value">
                <input type="text" style="width:200px;" name="model_id" value="'.$return_sum_total.'" style="cursor:pointer"  readonly/> ₽
              </div>
            </div>

                  <div class="item">
              <div class="level" style="font-size: 17px;">Сумма списанной техники:</div>
              <div class="value">
                <input type="text" style="width:200px;" name="model_id" value="'.$return_usd.'" style="cursor:pointer" readonly /> $
              </div>
            </div>
                  <div class="item">
              <div class="level" style="font-size: 17px;">Сумма возвращенной техники клиенту:</div>
              <div class="value">
                <input type="text" style="width:200px;" name="model_id" value="'.$return_ato_usd_total.'" style="cursor:pointer" readonly/> $
              </div>
            </div>


                  <div class="item">
              <div class="level" style="font-size: 17px;">Сумма оплаченная мастерам за работу:</div>
              <div class="value">
                <input type="text" style="width:200px;" name="model_id" value="'.$return_master_sum.'" style="cursor:pointer" readonly/> ₽
              </div>
            </div>

                   <div class="item">
              <div class="level" style="font-size: 17px;">Экономический смысл для сервиса:</div>
              <div class="value">
                <input type="text"  name="model_id" value="'.($return_sum_total - $return_master_sum).'" style="width:200px;'.((($return_sum_total - $return_master_sum) < 0) ? 'color:red;' : 'color:green;').'cursor:pointer" readonly /> ₽
              </div>
            </div>

                    <div class="item">
              <div class="level" style="font-size: 17px;">Экономический смысл для клиента:</div>
              <div class="value">
                <input type="text" name="model_id" value="'.$ffs.'"  style="width:200px;cursor:pointer;'.$color_ffs.'" readonly /> ₽
              </div>
            </div>


        </div>

      </form>';

echo json_encode(array('body'=> $body));

}

if ($_GET['type'] == 'get_returns_report_cli') {

$sql_returns = mysqli_query($db, 'SELECT * FROM `returns` where DATE(date_out) BETWEEN \''.str_replace('-', '.', $_GET['date1']).'\' AND \''.str_replace('-', '.', $_GET['date2']).'\';');
//echo 'SELECT * FROM `returns` where DATE(date_out) BETWEEN \''.str_replace('-', '.', $_GET['date1']).'\' AND \''.str_replace('-', '.', $_GET['date2']).'\';';
if (mysqli_num_rows($sql_returns) != false) {
 while ($row_returns = mysqli_fetch_array($sql_returns)) {

$sql_check_count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row_returns['id']).'\' and `deleted` = 0;'));
$sql_check_count2 = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(id) FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $row_returns['id']).'\' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\') and `deleted` = 0;'));

if ($sql_check_count['COUNT(id)'] == $sql_check_count2['COUNT(id)']) {

if ($_GET['cats'] != '') {
$cat_sql = ' and `cat_id` in ('.$_GET['cats'].') ';
}

$sql = mysqli_query($db, 'SELECT `id`,`repair_final`,`repair_type_id`,`cat_id`,`model_id`,`total_price`,`master_user_id` FROM `repairs` where `return_id` = \''.$row_returns['id'].'\' '.$cat_sql.' ;');
      while ($row = mysqli_fetch_array($sql)) {

              $model = model_info($row['model_id']);
              //echo $model['brand']."\n";
              if ($model['brand'] == 'HARPER' || $model['brand'] == 'OLTO' || $model['brand'] == 'NESONS' || $model['brand'] == 'SKYLINE') {

              // Стоимость партии возврата:
              switch($row['repair_final']) {

              case 1:
                $model_price_ato = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                $array_result[$row_returns['client_id']]['ato_sum'] += $model_price_ato;
                $array_result[$row_returns['client_id']]['ato_counter'] += 1;
                break;
              case 2:
                $model_price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              case 3:
                $model_price_ato = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                $array_result[$row_returns['client_id']]['ato_summ'] += $model_price_ato;
                $array_result[$row_returns['client_id']]['ato_counter'] += 1;
                break;
              }

              $model_price_total = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
              $array_result[$row_returns['client_id']]['total_counter'] += 1;
              $array_result[$row_returns['client_id']]['total_sum'] += $model_price_total;

              $return_sum += $row['total_price'];
              $return_sum_total += $row['total_price'];
              unset($price);

              // Сумма техники списанной:
              $return_usd += $model_price;
              unset($model_price);

              // Сумма техники списанной ато:
              //$return_ato_usd += $model_price_ato;


              unset($model_price_ato);

              // Сумма мастерам за работу:
              //$return_master_sum += $row['total_price'];
              $return_master_sum += count_pay_master_funk($row['total_price'], $row['master_user_id']);

              }

      }

$usd = @mysqli_fetch_array(mysqli_query($db, 'SELECT `usd` FROM `returns` where `id` = \''.$row_returns['id'].'\'  ;'))['usd'];
$ffs += $return_ato_usd*$usd-$return_sum;
unset($return_sum);
unset($return_ato_usd);

}
}
}
$color_ffs = ($ffs < 0) ? 'color:rgba(204, 0, 0, 1);' : 'color:green;';

$clients_body .= '<tr><td style="text-align:left;">Клиент</td><td>Возвраты от клиента</td><td>Сумма возвратов($)</td><td>Вернули обратно</td><td> Вернули обратно ($)</td><td>Процент возвратов</td></tr>';

foreach ($array_result as $client_id => $client_stat) {
$percent = round(($client_stat['ato_counter']/$client_stat['total_counter'])*100);
$sorter_array[$client_id] = $percent;
}
arsort($sorter_array);

foreach ($sorter_array as $client_id => $percent) {
$content_client = mysqli_fetch_array(mysqli_query($db, 'SELECT `name` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $client_id).'\' LIMIT 1;'));
$clients_body .= '<tr><td style="text-align:left;padding:5px;">'.$content_client['name'].'</td><td>'.$array_result[$client_id]['total_counter'].'</td><td>'.$array_result[$client_id]['total_sum'].'</td><td>'.$array_result[$client_id]['ato_counter'].'</td><td>'.$array_result[$client_id]['ato_sum'].'</td><td>'.$percent.'%</td></tr>';
}

/*foreach ($array_result as $client_id => $client_stat) {
$percent = round(($client_stat['ato_counter']/$client_stat['total_counter'])*100);
$content_client = mysqli_fetch_array(mysqli_query($db, 'SELECT `name` FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $client_id).'\' LIMIT 1;'));
$clients_body .= '<tr><td style="text-align:left;padding:5px;">'.$content_client['name'].'</td><td>'.$client_stat['total_counter'].'</td><td>'.$client_stat['total_sum'].'</td><td>'.$client_stat['ato_counter'].'</td><td>'.$client_stat['ato_sum'].'</td><td>'.$percent.'%</td></tr>';
}    */

$body = '<br><h2 style="text-align:center;font-size: 22px;">Финансовая статистика за период: '.$_GET['date1'].' &mdash; '.$_GET['date2'].' <a style="display:none; href="/download-returns-report/'.$_GET['date1'].'/'.$_GET['date2'].'/'.$_GET['cats'].'/" title="Скачать расширенный отчет в XLS"><img  src="https://icon-library.net/images/download-png-icon/download-png-icon-8.jpg" style="max-width:20px;"></a></h2>
<stylE>
.client_row_table td{
padding: 5px;
border: 1px solid #80bd03;
vertical-align: middle;
}
.client_row_table{
border-collapse:collapse;
margin-top:15px;
    font-size: 16px;
}
.remodal {
    max-width: 900px !important;
}
</style>

<form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">
                 <table class="client_row_table">
                 '.$clients_body.'
                 </table>
        </div>

      </form>';

echo json_encode(array('body'=> $body));

//echo json_encode(array('body'=> ' Неа :( '));

}

if ($_GET['type'] == 'update_bill_status') {


mysqli_query($db, 'UPDATE `pay_billing` SET
`original` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);


admin_log_add('Обновлен статус получения оригиналов счета #'.$_GET['id']);

}

if ($_GET['type'] == 'update_bill_status_combine') {

if ($_GET['tesler'] == 1) {
mysqli_query($db, 'UPDATE `combine` SET
`bill_tesler` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
} else if ($_GET['extra'] != '') {
mysqli_query($db, 'UPDATE `combine_docs` SET
`bill` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `combine_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `brand` = \''.$_GET['extra'].'\' LIMIT 1') or mysqli_error($db);
} else {
mysqli_query($db, 'UPDATE `combine` SET
`bill` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
}

}

if ($_GET['type'] == 'update_act_status_combine') {

if ($_GET['tesler'] == 1) {
mysqli_query($db, 'UPDATE `combine` SET
`act_tesler` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
} else if ($_GET['extra'] != '') {
mysqli_query($db, 'UPDATE `combine_docs` SET
`act` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `combine_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `brand` = \''.$_GET['extra'].'\' LIMIT 1') or mysqli_error($db);
} else {
mysqli_query($db, 'UPDATE `combine` SET
`act` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
}

}

if ($_GET['type'] == 'update_pay_status_combine') {

if ($_GET['tesler'] == 1) {
mysqli_query($db, 'UPDATE `combine` SET
`payed_tesler` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
} else if ($_GET['extra'] != '') {
mysqli_query($db, 'UPDATE `combine_docs` SET
`payed` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `combine_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `brand` = \''.$_GET['extra'].'\' LIMIT 1') or mysqli_error($db);
} else {
mysqli_query($db, 'UPDATE `combine` SET
`payed` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
}


}

if ($_GET['type'] == 'get_namesparts') {

function get_names($cat_id) {
  global $db;
  $content = '';
$sql = mysqli_query($db, 'SELECT `id`, `name` FROM `parts2` WHERE `id` IN (SELECT `part_id` FROM `parts2_models` WHERE `model_cat_id` = '.$cat_id.')');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
              $content .= '<option value="'.$row['name'].'">'.$row['name'].'</option>';
      }
}

return '<option value="">Выберите название</option>'.$content;

}

$content = get_names($_GET['cat_id']);

echo json_encode(array('html'=> $content));
}

if ($_GET['type'] == 'get_namesparts_by_model') {

function get_names($cat_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `parts` where model_id = '.$cat_id.' and `parent_id` = \'\' and `list` != \'\' group by list order by list asc;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
          if ($current == $row['list']) {
              $content .= '<option selected value="'.$row['list'].'">'.$row['list'].'</option>';
          } else {
              $content .= '<option value="'.$row['list'].'">'.$row['list'].'</option>';
          }
      }
}

return '<option value="">Выберите название</option>'.$content;

}

$content = get_names($_GET['cat_id']);

echo json_encode(array('html'=> $content));
}

if ($_GET['type'] == 'get_namesparts_by_codemodel') {

function get_names($model_id, $code) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `parts` where model_id = '.$model_id.' and codepre = \''.$code.'\' and `parent_id` = \'\' and `list` != \'\' group by list order by list asc;');
//echo 'SELECT * FROM `parts` where model_id = '.$model_id.' and codepre = '.$code.' and `parent_id` = \'\' and `list` != \'\' group by list order by list asc;';
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
          if ($current == $row['list']) {
              $content .= '<option selected value="'.$row['list'].'">'.$row['list'].'</option>';
          } else {
              $content .= '<option value="'.$row['list'].'">'.$row['list'].'</option>';
          }
      }
}

return '<option value="">Выберите название</option>'.$content;

}

$content = get_names($_GET['model_id'], $_GET['cat_id']);

echo json_encode(array('html'=> $content));
}

if ($_GET['type'] == 'get_codeparts') {

function get_codes($cat_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `parts` where cat = '.$cat_id.' and `parent_id` = \'\' and `codepre` != \'\' group by codepre order by codepre asc ;');
if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
          if ($current == $row['codepre']) {
              $content .= '<option selected value="'.$row['codepre'].'">'.$row['codepre'].'</option>';
          } else {
              $content .= '<option value="'.$row['codepre'].'">'.$row['codepre'].'</option>';
          }
      }
}

return '<option value="">Выберите код</option>'.$content;

}

$content = get_codes($_GET['cat_id']);

echo json_encode(array('html'=> $content));
}

if ($_GET['type'] == 'get_codeparts_by_model') {
  function get_codes()
  {
    global $db;
    $content = '';
    $sql = mysqli_query($db, 'SELECT DISTINCT `code` FROM `parts2_groups` ORDER BY `code`');
    while ($row = mysqli_fetch_array($sql)) {
      $content .= '<option value="' . $row['code'] . '">' . $row['code'] . '</option>';
    }
    return '<option value="">Выберите код</option>' . $content;
  }
  $content = get_codes();
  echo json_encode(array('html' => $content));
  exit;
}

if ($_GET['type'] == 'get_modelsparts') {
$content = '<option value="">Выберите модель</option>';
$catID = $_GET['cat_id'];
$sql = mysqli_query($db, 'SELECT `id`, `name` FROM `models` WHERE `cat` = '.$catID.' AND `model_id` != ""GROUP BY `model_id` ORDER BY `name`');
while ($row = mysqli_fetch_array($sql)) {
  $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
}
echo json_encode(array('html'=> $content));
exit;
}

if ($_GET['type'] == 'get_warranty_by_model') {
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT `warranty` FROM `models` WHERE `id` = '.$_GET['id']));
echo json_encode(array('warranty'=> $req['warranty']));
}

if ($_GET['type'] == 'get_warranty_answer') {

$date = DateTime::createFromFormat('d.m.Y', $_GET['date']);
$date2_row = DateTime::createFromFormat('d.m.Y', $_GET['date'])->modify('+'.$_GET['warranty'].' days');
$date3 = new DateTime();
$date3_row = DateTime::createFromFormat('Y-m-d', $_GET['receive_date']);

if ($date3_row <= $date2_row) {
$answer = 1;
} else {
$answer = 0;
}

echo json_encode(array('answer'=> $answer));

}

if ($_GET['type'] == 'get_repair_type' && $_GET['problem_id']) {
$pre = '<option value="">Выберите вариант</option>'.problem_links($_GET['problem_id']);
echo json_encode(array('html'=> $pre, 'price' => get_problem_price($_GET['problem_id'], $_GET['cat'], $_GET['service_id']), 'type' => get_problem_type($_GET['problem_id'], $_GET['cat']), 'default' => get_problem_value($_GET['problem_id'])));
}

/* Небольшая проверка пароля перед изменением тарифов */
if ($_GET['type'] == 'check-password') {
  if ($_POST['password'] == '2308') {
    $_SESSION['pass_protect'] = 1;
    echo json_encode(['error_flag' => 0, 'message' => '']);
  } else {
    echo json_encode(['error_flag' => 1, 'message' => 'Неверный пароль.']);
  }
  exit;
}

if ($_GET['type'] == 'update_service_price') {


  if (empty($_SESSION['pass_protect'])) {
    exit;
  }
$rawData = json_decode($_POST['data'], true);
foreach ($rawData as $data) {
  $sql_service = mysqli_query($db, 'SELECT COUNT(*) AS cnt FROM `prices_service` WHERE `service_id` = "'.mysqli_real_escape_string($db, $data['service_id']).'" AND `cat_id` = "'.mysqli_real_escape_string($db, $data['cat_id']).'"');
$count_service = mysqli_fetch_array($sql_service)['cnt'];
if ($count_service > 0) {
  mysqli_query($db, 'UPDATE `prices_service` SET 
  `'.$data['field'].'` = "'.mysqli_real_escape_string($db, $data['value']).'" 
  WHERE `service_id` = '.$data['service_id'] . ' AND `cat_id` = "'.mysqli_real_escape_string($db, $data['cat_id']).'"') or mysqli_error($db);
  admin_log_add('Обновлена цена для сервиса #'.$data['service_id']);
}else{
  mysqli_query($db, 'INSERT INTO `prices_service` (
    `service_id`,
    `cat_id`,
    `'.$data['field'].'`
    ) VALUES (
    "'.mysqli_real_escape_string($db, $data['service_id']).'",
    "'.mysqli_real_escape_string($db, $data['cat_id']).'",
    "'.mysqli_real_escape_string($db, $data['value']).'" 
    );') or mysqli_error($db);
    admin_log_add('Обновлена цена для сервиса #'.$data['service_id']);
}
}
echo json_encode(['message' => '', 'error_flag' => 0]);
exit;
}

if ($_GET['type'] == 'update_brand_price') {

if ($_GET['paid'] == 1) {
$type = 'value_paid';
} else {
$type = 'value';
}

mysqli_query($db, 'UPDATE `brands_tarif_values` SET
`'.$type.'` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `brand_id` = \''.mysqli_real_escape_string($db, $_GET['brand_id']).'\' and `cat_id` = \''.mysqli_real_escape_string($db, $_GET['cat_id']).'\' and `plan_id` = \''.mysqli_real_escape_string($db, $_GET['plan_id']).'\' LIMIT 1') or mysqli_error($db);


}

if ($_GET['type'] == 'update_brand_plan_name') {


mysqli_query($db, 'UPDATE `brands_tarif_plans` SET
`plan_name` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\'  LIMIT 1') or mysqli_error($db);


}

if ($_GET['type'] == 'remove_brand_plan') {

mysqli_query($db, 'DELETE FROM `brands_tarif_plans` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\'  LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'update_price_total_2023') {
  if (empty($_SESSION['pass_protect'])) {
    exit;
  }
$rawData = json_decode($_POST['data'], true);
foreach ($rawData as $data) {
  mysqli_query($db, 'UPDATE `prices-2023` SET 
  `'.$data['field'].'` = \''.mysqli_real_escape_string($db, $data['value']).'\'
  WHERE `id` = '.$data['id']) or mysqli_error($db);
  admin_log_add('Обновлена цена #'.$id);
}
echo json_encode(['message' => '', 'error_flag' => 0]);
exit;
}

if ($_GET['type'] == 'update_price_total') {
  if (empty($_SESSION['pass_protect'])) {
    exit;
  }
$rawData = json_decode($_POST['data'], true);
foreach ($rawData as $data) {
  mysqli_query($db, 'UPDATE `prices` SET 
  `'.$data['field'].'` = \''.mysqli_real_escape_string($db, $data['value']).'\'
  WHERE `id` = '.$data['id']) or mysqli_error($db);
  admin_log_add('Обновлена цена #'.$id);
}
echo json_encode(['message' => '', 'error_flag' => 0]);
exit;
}


if ($_GET['type'] == 'update_price_total2') {
  if (empty($_SESSION['pass_protect'])) {
    exit;
  }
  $rawData = json_decode($_POST['data'], true);
  foreach ($rawData as $data) {
    mysqli_query($db, 'UPDATE `prices_2` SET 
    `'.$data['field'].'` = \''.mysqli_real_escape_string($db, $data['value']).'\'
    WHERE `id` = '.$data['id']) or mysqli_error($db);
    admin_log_add('Обновлена цена (тариф 2018) #'.$id);
  }
  echo json_encode(['message' => '', 'error_flag' => 0]);
  exit;
  }



if ($_GET['type'] == 'update_price_model' && $_GET['id']) {

mysqli_query($db, 'UPDATE `models` SET
`price_usd` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

admin_log_add('Обновлена цена модели #'.$_GET['id']);

}

if ($_GET['type'] == 'update_plan' && $_GET['plan_id'] && $_GET['value']) {

mysqli_query($db, 'UPDATE `plans` SET
`plan` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['plan_id']).'\' LIMIT 1') or mysqli_error($db);

//admin_log_add('Обновлена цена #'.$_GET['service_id']);

}

if ($_GET['type'] == 'edit_message_by_id' && $_POST['id'] && $_POST['value']) {
Feedback::editMessage($_POST['id'], $_POST['value']);
$message = Feedback::getMessage($_POST['id']);
$talk = Feedback::getTalk($message['talk_id']);
Log::repair(9, '#'.$_POST['id'], $talk['repair_id']);
}

if ($_GET['type'] == 'del_part_by_id' && $_GET['id']) {

mysqli_query($db, 'DELETE from `parts_files` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

admin_log_add('Удален запрос запчасти с фото #'.$_GET['id']);

}

if ($_GET['type'] == 'del_part_by_id_ok' && $_GET['id']) {

mysqli_query($db, 'DELETE from `repairs_parts` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
admin_log_add('Удален запрос запчасти #'.$_GET['id']);
}

if ($_GET['type'] == 'del_message_by_id' && $_GET['id']) {
  $message = Feedback::getMessage($_GET['id']);
  $talk = Feedback::getTalk($message['talk_id']);
  if(Feedback::deleteMessage($_GET['id'])){
    Log::repair(8, '#'.$_GET['id'], $talk['repair_id']);
  }
}

if ($_GET['type'] == 'update_comment_by_id' && $_GET['value'] && $_GET['id']) {

mysqli_query($db, 'UPDATE `parts_files` SET
`comment` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);
admin_log_add('Обновлен комментарий к фото запчасти #'.$_GET['id']);
}

if ($_GET['type'] == 'send_parts' && $_GET['value'] && $_GET['id']) {

$repair = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = '.$_GET['value']));
mysqli_query($db, 'INSERT INTO `parts_sc` (
`part_id`,
`repair_id`,
`service_id`,
`count`,
`date_get`
) VALUES (
\''.mysqli_real_escape_string($db, $_GET['id']).'\',
\''.mysqli_real_escape_string($db, $_GET['value']).'\',
\''.mysqli_real_escape_string($db, $repair['service_id']).'\',
\''.mysqli_real_escape_string($db, '1').'\',
NOW()
);') or mysqli_error($db);

mysqli_query($db, 'INSERT INTO `parts_sc_log` (
`part_id`,
`repair_id`,
`service_id`,
`count`,
`date_get`
) VALUES (
\''.mysqli_real_escape_string($db, $_GET['id']).'\',
\''.mysqli_real_escape_string($db, $_GET['value']).'\',
\''.mysqli_real_escape_string($db, $repair['service_id']).'\',
\''.mysqli_real_escape_string($db, '1').'\',
NOW()
);') or mysqli_error($db);

mysqli_query($db, 'UPDATE `repairs_work` SET
`name` = \''.$_GET['id'].'\'
WHERE `repair_id` = \''.mysqli_real_escape_string($db, $_GET['value']).'\' and `name` = \'\' ORDER by id ASC LIMIT 1') or mysqli_error($db);

$part = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$_GET['id']));
$id = ($part['parent_id'] > 0) ? $part['parent_id'] : $_GET['id'];
mysqli_query($db, 'UPDATE `parts` SET
`count` = count - 1
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' LIMIT 1') or mysqli_error($db);
admin_log_add('Запчасть #'.$id.' списана.');
admin_log_add('Запчасть #'.$id.' отправлена.');

}

if ($_GET['type'] == 'create_combine') {
  // Создание combine
$json = json_decode($_POST['value'], true);
foreach ($json['0'] as $bill) {

echo $bill.'<br>';

}

//0 = bills
//1 = act
//2 = date

mysqli_query($db, 'INSERT INTO `combine` (
`date`,
`create_date`
) VALUES (
\''.mysqli_real_escape_string($db, $json['2']).'\',
"'.str_replace('.', '-', mysqli_real_escape_string($db, $json['2'])).'-01"
);') or mysqli_error($db);

$combine_id = mysqli_insert_id($db);
if(!$combine_id){
  exit;
}
foreach ($json['0'] as $bill) {
mysqli_query($db, 'INSERT INTO `combine_links` (
`combine_id`,
`pay_billing_id`,
`type`
) VALUES (
\''.mysqli_real_escape_string($db, $combine_id).'\',
\''.mysqli_real_escape_string($db, $bill).'\',
\''.mysqli_real_escape_string($db, 0).'\'
);') or mysqli_error($db);

mysqli_query($db, 'UPDATE `pay_billing` SET
`status` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $bill).'\' LIMIT 1') or mysqli_error($db);

$usr_info = Users::getUser(['login' => $_SESSION['login']]);

//if ($usr_info['id'] == 2) {
mysqli_query($db, 'UPDATE `pay_billing` SET
`payed` = 1
WHERE `id` = \''.mysqli_real_escape_string($db, $bill).'\' LIMIT 1') or mysqli_error($db);

//}

}
foreach ($json['1'] as $act) {
mysqli_query($db, 'INSERT INTO `combine_links` (
`combine_id`,
`pay_billing_id`,
`type`
) VALUES (
\''.mysqli_real_escape_string($db, $combine_id).'\',
\''.mysqli_real_escape_string($db, $act).'\',
\''.mysqli_real_escape_string($db, 1).'\'
);') or mysqli_error($db);
}
exit;
}

if ($_GET['type'] == 'update_place' && $_GET['value'] && $_GET['id']) {

mysqli_query($db, 'UPDATE `parts2_balance` SET
`place` = "'.mysqli_real_escape_string($db, $_GET['value']).'" 
WHERE `part_id` = "'.mysqli_real_escape_string($db, $_GET['id']).'" AND `depot_id` = 1') or mysqli_error($db);
admin_log_add('Обновлен место хранения запчасти #'.$_GET['id']);
}

if ($_GET['type'] == 'update_place_service' && $_GET['value'] && $_GET['id']) {

mysqli_query($db, 'UPDATE `parts_sc` SET
`place` = \''.mysqli_real_escape_string($db, $_GET['value']).'\'
WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1') or mysqli_error($db);

}

if ($_GET['type'] == 'remove_loan') {

mysqli_query($db, 'UPDATE `pay_billing` SET `custom_loan` = 0 where `service_id` = '.$_GET['value'].';') or mysqli_error($db);

}

if ($_GET['type'] == 'remove_document') {

mysqli_query($db, 'UPDATE `services_documents` SET `deleted` = 1 where `id` = '.$_GET['value'].';') or mysqli_error($db);

}


if ($_GET['type'] == 'update_travel_total') {
  if (empty($_SESSION['pass_protect'])) {
    exit;
  }
$rawData = json_decode($_POST['data'], true);
foreach ($rawData as $data) {
  mysqli_query($db, 'UPDATE `transfer` SET 
  `'.$data['field'].'` = \''.mysqli_real_escape_string($db, $data['value']).'\'
  WHERE `id` = '.$data['id']) or mysqli_error($db);
  admin_log_add('Обновлена цена доставки (2022) #'.$data['id']);
}
echo json_encode(['message' => '', 'error_flag' => 0]);
exit;
}

if ($_GET['type'] == 'update_travel_total2') {
  if (empty($_SESSION['pass_protect'])) {
    exit;
  }
$rawData = json_decode($_POST['data'], true);
foreach ($rawData as $data) {
  mysqli_query($db, 'UPDATE `transfer_2` SET 
  `'.$data['field'].'` = \''.mysqli_real_escape_string($db, $data['value']).'\'
  WHERE `id` = '.$data['id']) or mysqli_error($db);
  admin_log_add('Обновлена цена доставки (2018) #'.$data['id']);
}
echo json_encode(['message' => '', 'error_flag' => 0]);
exit;
}

if ($_GET['type'] == 'update_travel_total3') {
  if (empty($_SESSION['pass_protect'])) {
    exit;
  }
$rawData = json_decode($_POST['data'], true);
foreach ($rawData as $data) {
  mysqli_query($db, 'UPDATE `transfer_3` SET 
  `'.$data['field'].'` = \''.mysqli_real_escape_string($db, $data['value']).'\'
  WHERE `id` = '.$data['id']) or mysqli_error($db);
  admin_log_add('Обновлена цена доставки (2024) #'.$data['id']);
}
echo json_encode(['message' => '', 'error_flag' => 0]);
exit;
}

if ($_GET['type'] == 'get_models' && $_GET['id']) {

$html = '<option>Выберите вариант</option>'.models($_GET['id']);
echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get-all-models') {
  echo json_encode(\models\Models::getModelsList());
  exit;
}

if ($_GET['type'] == 'check-model-service') {
  $models = \models\Models::searchModel($_POST['model']);
  if (!$models) {
    echo json_encode(['service_flag' => 0, 'message' => 'Модель "' . $_POST['model'] . '" не найдена.']);
  } else {
    $userID = User::getData('id');
    if(User::hasRole('taker', 'master')){
      $userID = 33;
    }
    $res = (int)\models\Models::inService($models[0]['id'], $userID);
    echo json_encode(['service_flag' => $res, 'message' => 'Модель "' . $models[0]['name'] . '" ' . (($res) ? 'обслуживается' : 'не обслуживается') . '.']);
  }
  exit;
}

if ($_GET['type'] == 'check-serial') {
  $sql = mysqli_query($db, 'SELECT `id`, `model_name`, `receive_date`, `serial`, `sell_date`, `ready_date`, `repair_type_id`, `service_id`   
  FROM `repairs` WHERE `serial` LIKE "%' . mysqli_real_escape_string($db, trim($_POST['serial'])) . '%" AND `deleted` = 0');
  $result = [];
  while($repair = mysqli_fetch_assoc($sql)) {
    $service = Services::getServiceByID($repair['service_id']);
    $type = Repair::getRepairType($repair['repair_type_id']);
    $res = Repair::getRepairResult($repair['id']);
    $result[] = [
      'id' => $repair['id'],
      'url' => '/edit-repair/' . $repair['id'] . '/',
      'model' => $repair['model_name'],
      'service' => $service['name'] ?? '',
      'master' => Repair::getMaster($repair['id']),
      'type' => ($type ? $type : '(не установлен)'),
      'result' => ($res ? $res : '(не установлен)'),
      'receive_date' => Time::format($repair['receive_date']),
      'serial' => $repair['serial'],
      'sell_date' => Time::format($repair['sell_date']),
      'ready_date' => Time::format('d.m.Y', $repair['ready_date'])
  ];
  }
  echo json_encode($result);
  exit;
}

if ($_GET['type'] == 'check_model1' && $_GET['id']) {
  
echo json_encode(['answer'=> (int)\models\Models::inService($_GET['id'], User::getData('id'))]);
exit;
}

if ($_GET['type'] == 'check_serials' && $_GET['model_name'] && $_GET['serial']) {

$model = get_model_by_id($_GET['model_name']);
if (check_serial($_GET['serial'], $_GET['model_name'])) {
$answer = 1;
} else {
$answer = 0;
}
$table = doubl($_GET['serial'], $_GET['model_name'], $_GET['id']);
echo json_encode(['answer'=> $answer, 'model' => $model['id'], 'table' => $table, 'repeated_flag' => (int)\models\Repair::isRepeated($_GET['id'])]);
}

if ($_GET['type'] == 'get_serials' && $_GET['model_id']  && $_GET['cat']) {

$html = '<option>Выберите вариант</option>'.serials($_GET['cat'], $_GET['model_id']);
echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get_group' && $_GET['model_id']   && $_GET['serial']) {

$html = '<option>Выберите вариант</option>'.groups($_GET['model_id'], $_GET['serial']);
echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get_group_by_cat' && $_GET['cat']) {

$html = '<option value="">Выберите вариант</option>'.groups_by_cat_id($_GET['cat']);
echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get_cat' && $_GET['id'] ) {

$html = categories_by_model($_GET['id']);
$html2 = serials_select($_GET['id']);
$html3 = groups_by_cat($_GET['id']);
echo json_encode(array('html'=> $html['body'], 'html2'=> $html2, 'html3'=> $html3));

}

if ($_GET['type'] == 'get_cat_brand') {



$brand = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `brands` WHERE `name` = \''.mysqli_real_escape_string($db, $_GET['id']).'\''));
$sql = mysqli_query($db, 'SELECT * FROM `cats` ;');
      while ($row = mysqli_fetch_array($sql)) {

      $selected = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `cats_to_brand` WHERE `cat_id` = \''.mysqli_real_escape_string($db, $row['id']).'\' and `brand_id` = '.$brand['id'].' LIMIT 1;'))['COUNT(*)'] > 0) ? '1' : '';
      if ($selected > 0) {
      $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }

      }

/*if (!$content) {
$sql = mysqli_query($db, 'SELECT * FROM `cats` ;');
      while ($row = mysqli_fetch_array($sql)) {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
}   */


echo json_encode(array('html'=> $content, 'service' => ($brand['service'] == 1) ? 'Да' : 'Нет'));
}

if ($_GET['type'] == 'get_pre' && $_GET['id'] ) {

$pre = group_info($_GET['id']);
echo json_encode(array('pre'=> $pre['pre']));

}

if ($_GET['type'] == 'get_parts_showall' && $_GET['model_id']  && $_GET['cat'] && $_GET['serial'] && $_GET['group']) {

$html = '<option>Выберите вариант</option>'.parts_all($_GET['group']);

echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get_parts' && $_GET['model_id']  && $_GET['cat'] && $_GET['serial'] && $_GET['group']) {

$html = '<option>Выберите вариант</option>'.parts($_GET['cat'], $_GET['model_id'], $_GET['serial'], $_GET['group']);

echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get_parts_all') {

$html = '<option>Выберите вариант</option>'.parts_all($_GET['group']);

echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get_parts_all_all') {

$html = '<option>Выберите вариант</option>'.parts_all('', $_GET['model_id'], $_GET['part_id_check']);

echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'get_parts_groups') {

$html = '<option>Выберите вариант</option>'.groups_all();

echo json_encode(array('html'=> $html));

}

if ($_GET['type'] == 'mass_update') {
$json = json_decode($_GET['value'], true);
$statusSQL = (!empty($_GET['status_admin'])) ? '`status_admin` = "'.$_GET['status_admin'].'",' : '';
$masterSQL = (!empty($_GET['master_id'])) ? '`master_user_id` = "'.$_GET['master_id'].'",' : '';
foreach ($json as $id) {
mysqli_query($db, 'UPDATE `repairs` SET
'.$statusSQL.'
'.$masterSQL.'
`begin_date` = \''.mysqli_real_escape_string($db, date('Y-m-d')).'\' 
WHERE `id` = \''.mysqli_real_escape_string($db, $id).'\' AND `status_admin` NOT IN ("Выдан", "Подтвержден")') or mysqli_error($db);
}
exit;
}

if ($_GET['type'] == 'get_part_info' && $_GET['part_id']) {
$part_info = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `parts` WHERE `id` = '.$_GET['part_id']));
if ($part_info['imgs']) {
$imgs_gen = json_decode($part_info['imgs']);
        foreach ($imgs_gen as $img) {
            $html .= '<li class="adm-media-item">
          <div class="img remove_preview">
            <span style="background: #fff;"><a href="'.rtrim($img).'" data-fancybox="group'.$part_info['id'].'">
  <img src="'.rtrim($img).'" alt="" style="max-height:100px;max-width: 150px;" />
</a></span>
          </div>
        </li>';
        }
}
echo json_encode(array('html'=> $html));
}

exit;
