<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/back/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/spaces/spaces.php';

use models\Parts;
use models\parts\Balance;
use models\parts\Batches;
use models\Repair;
use models\Serials;
use models\Tariffs;
use program\core;
use program\adapters;
use program\adapters\DigitalOcean;
use program\adapters\Excel;
use program\core\Time;

core\App::$config = $config;
core\App::run();
$db = new core\DB($config['db_host'], $config['db_name'], $config['db_user'], $config['db_pass'], [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES `utf8`']);


exit;
echo '<pre>';
print_r(getApiData1('https://crm.r97.ru/get-reject/243147/'));
echo '</pre>';

function getApiData1($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }

exit;
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');

$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPDebug = 1;
$mail->Host = 'smtp.mail.ru';
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = 'robot2@r97.ru';
$mail->Password = 'G3ZbuLtpCYWcahLRJaar';
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->addReplyTo('kan@r97.ru', 'kan@r97.ru');
$mail->setFrom('robot2@r97.ru', 'R97.RU');
$mail->addAddress('destrifer@yandex.ru');
$mail->isHTML(true);
$mail->Subject = "TEST";
$mail->CharSet = 'UTF-8';
$mail->Body    = 'test';
$mail->send(); 

exit;
$rows = $db->exec('SELECT `id`, `status_admin` FROM `repairs` WHERE `id` IN (SELECT `object_id` FROM `log` WHERE message like "%при возврате запчасти%") AND `status_admin` != "Подтвержден"');
echo '<pre>';
print_r($rows);
echo '</pre>';
exit;
$rows = $db->exec('SELECT `id` FROM `brands` WHERE `is_deleted` = 1');
foreach($rows as $row){
    $r = $db->exec('UPDATE `cats` SET `is_deleted` = 1 WHERE `brand_id` = ?', [$row['id']]);
}
exit;
$rows = $db->exec('SELECT `id`, `brand` FROM `models`');
foreach($rows as $row){
    $brand = trim($row['brand']);
    if(!$brand){
        continue;
    }
    $rows2 = $db->exec('SELECT `id` FROM `repairs` WHERE `model_id` = '.$row['id'] . ' AND `deleted` = 0 LIMIT 1');
    if($rows2){
        $brands[$brand] = 1;
        $r = $db->exec('UPDATE `brands` SET `is_deleted` = 0 WHERE `name` = ?', [$brand]);
    }
}

echo 'exist:<pre>';
print_r($brands);
echo '</pre>';

exit;
$url = DigitalOcean::uploadFile('/_new-codebase/uploads/temp/mstarupgrade_no_tvcertificatetvconfig_20220601.zip', 'infobase/0000014/6592/firmware/mstarupgrade_no_tvcertificatetvconfig_20220601.zip');
echo '<pre>';
print_r($url);
echo '</pre>';
 
exit;
$xls = adapters\Excel::create();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setCellValue('A1', 'Шаблон');
        $sheet->setCellValue('B1', 'Реальное наименование');
        $sheet->setCellValue('C1', 'Код (менять нельзя)');
        $rows = $db->exec('SELECT * FROM `parts2` ORDER BY `name`');
$rowNum = 2;
foreach($rows as $row){
    $sheet->setCellValue('A'.$rowNum, '');
    $sheet->setCellValue('B'.$rowNum, $row['name']);
    $sheet->setCellValue('C'.$rowNum, $row['id']);
    $rowNum++;
}
 $cols = range('A', 'C');
        foreach ($cols as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        } 
        adapters\Excel::display($xls, 'Запчасти-с-шаблоном.xlsx');
exit;
echo '<table border=1>
<thead>
<tr>
    <th>Запчасть</th>
    <th>Имя ru</th>
    <th>Имя en</th>
</tr>
</thead><tbody>
';
$rows = $db->exec('SELECT * FROM `parts2_names`');
foreach($rows as $row) {
    $rows2 = $db->exec('SELECT `id`, `name` FROM `parts2` WHERE `name` LIKE ?', [$row['ru'] . '%']);
    foreach($rows2 as $row2) {
        echo '<tr>
                <td style="padding: 6px"><a href="/part/?id='.$row2['id'].'" target="_blank">'.$row2['name'].'</a></td>
                <td style="padding: 6px">'.$row['ru'].'</td>
                <td style="padding: 6px">'.$row['en'].'</td>
            </tr>';
    }
}
echo '</tbody></table>';

exit;
$rows = $db->exec('SELECT `id`, `name`, `name_1s` FROM `parts2` WHERE `name` LIKE "%Кабель сигнальный к ЖК панели%"');
foreach($rows as $row) {
    $name = trim(str_replace('Кабель сигнальный к ЖК панели', 'Кабель видеосигнала к ЖК панели', $row['name']));
    $name1s = trim(str_replace('Кабель сигнальный к ЖК панели', 'Кабель видеосигнала к ЖК панели', $row['name_1s']));
    $r = $db->exec('UPDATE `parts2` SET `name` = ?, `name_1s` = ? WHERE `id` = ?', [$name, $name1s, $row['id']]);
    echo '<p>'.$name.' - '.$r.'</p>';
}

exit;
$rows = $db->exec('SELECT * FROM `users_old2411`');
foreach($rows as $row){
    $statusID = 0;
    if($row['block'] == 1){
        $statusID = 2;
    }elseif($row['active'] == 1){
        $statusID = 1;
    }
    echo '<pre>';
    print_r($statusID);
    echo '</pre>';
    $db->exec('UPDATE `users` SET `status_id` = ? WHERE `id` = ?', [$statusID, $row['id']]); 
}
exit;
$rows = $db->exec('SELECT * FROM `staff`');
foreach($rows as $row){
    $name = trim($row['name'] . ' ' . $row['surname']);
    echo '<pre>';
    print_r($name);
    echo '</pre>';
    $db->exec('UPDATE `users` SET `nickname` = ? WHERE `id` = ?', [$name, $row['user_id']]); 
}
exit;
$rows = $db->exec('SELECT * FROM `users`');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `requests` WHERE `user_id` = ? ORDER BY `id` DESC LIMIT 1', [$row['id']]);
    if(!$rows2){
        continue;
    }
    echo '<pre>';
    print_r($rows2[0]);
    echo '</pre>';
    $db->exec('UPDATE `users` SET `service_id` = ? WHERE `id` = ?', [$rows2[0]['id'], $row['id']]); 
}
exit;
$rows = $db->exec('SELECT * FROM `users` WHERE `surname` != "" AND `type_id` = 3');
foreach($rows as $row){
    $name = trim(trim($row['name']) . ' ' . trim($row['surname']));
    echo '<pre>';
    print_r($name);
    echo '</pre>';
    $db->exec('UPDATE `users_new` SET `nickname` = ? WHERE `id` = ?', [$name, $row['id']]); 
}
exit;
$rows = $db->exec('SELECT * FROM `users`');
foreach($rows as $row){
    $phone = ($row['tel']) ? mb_strtolower(trim($row['tel'])) : '';
    echo '<pre>';
    print_r($row['tel']);
    echo '</pre>';
    $db->exec('UPDATE `users_new` SET `phone` = ? WHERE `id` = ?', [$phone, $row['id']]); 
}
exit;
$rows = $db->exec('SELECT * FROM `users` WHERE `hide_on_site_flag` = 1');
foreach($rows as $row){
    echo '<pre>';
    print_r($row['email']);
    echo '</pre>';
    $db->exec('UPDATE `requests` SET `is_hidden_on_site` = 1 WHERE `user_id` = ?', [$row['id']]); 
}
exit;
$rows = $db->exec('SELECT * FROM `users_old`');
foreach($rows as $row){
    echo '<pre>';
    print_r($row['email']);
    echo '</pre>';
    $db->exec('UPDATE `users` SET `login` = ? WHERE `id` = ?', [trim($row['email']), $row['id']]); 
}
/* foreach($rows as $row){
    $hash = password_hash($row['password'] . '7R4#m3m@d!F6hc5m4%w7&@Ot^$jo', PASSWORD_DEFAULT);
    echo '<pre>';
    print_r($hash);
    echo '</pre>';
    $db->exec('UPDATE `users` SET `password` = ? WHERE `id` = ?', [$hash, $row['id']]);
} */
exit;
$xls = adapters\Excel::create();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setCellValue('A1', '№ карточки');
        $sheet->setCellValue('B1', 'Наименование СЦ');
        $sheet->setCellValue('C1', 'Модель');
        $sheet->setCellValue('D1', 'Дата подтверждения');
        $rows = $db->exec('SELECT r.`id`, r.`approve_date`, m.`name` AS model, rq.`name` FROM `repairs` r 
        LEFT JOIN `requests` rq ON rq.`user_id` = r.`service_id`  
        LEFT JOIN `models` m ON m.`id` = r.`model_id`  
 WHERE r.`deleted` = 0 AND r.`onway` != 0 AND r.`app_date` != "" AND r.`transport_cost` = 0 ORDER BY r.`approve_date` DESC');
$rowNum = 2;
foreach($rows as $row){
    $sheet->setCellValue('A'.$rowNum, $row['id']);
    $sheet->setCellValue('B'.$rowNum, $row['name']);
    $sheet->setCellValue('C'.$rowNum, $row['model']);
    $sheet->setCellValue('D'.$rowNum, date('d.m.Y', strtotime($row['approve_date'])));
    $rowNum++;
}
 $cols = range('A', 'C');
        foreach ($cols as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        } 
        adapters\Excel::display($xls, 'Выездной-ремонт.xlsx');
exit;
$rows = $db->exec('SELECT `part_id`, `depot_id`, `qty` FROM `parts2_balance` WHERE `depot_id` != 1 AND `qty` != 0');
foreach($rows as $row){
    $r = Batches::add($row['part_id'], $row['qty'], $row['depot_id']);
    if(!$r){
        echo $db->getErrorInfo();
        exit;
    }
}
echo 'done ' . count($rows);
exit;
$rows = $db->exec('SELECT `id`, `photos` FROM `parts2` ORDER BY `id`');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `parts2` WHERE `photos` = ? AND `id` != ?', [$row['photos'], $row['id']]);
    if($rows2){
        echo '<p>'.$row['id'].' -> '.$rows2[0]['id'].'</p>';
    }
}

exit;
$rows = $db->exec('SELECT `id` FROM `parts2` WHERE `attr_id` = 1');
foreach($rows as $row){
    $db->exec('DELETE FROM `parts2_standard` WHERE `part_id` = ?', [$row['id']]);
}
count($rows);
exit;
$rows = $db->exec('SELECT `id` FROM `parts2` WHERE `attr_id` = 2');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `parts2_balance` WHERE `part_id` = ? AND `depot_id` = 1', [$row['id']]);
    if($rows2){
        continue;
    }
    $r = $db->exec('INSERT INTO `parts2_balance` (`part_id`, `depot_id`, `qty`, `place`) 
    VALUES (?, ?, ?, ?)', [$row['id'], 1, 0, '']);
    if($r){
        echo '<p>ok: '.$row['id'].'</p>';
    }else{
        echo '<p style="color:red">fail: '.$row['id'].'</p>'; 
    }
}
echo '<pre>';
print_r(count($rows));
echo '</pre>';

exit;
foreach($rows as $row){
    $rows2 = $db->exec('SELECT COUNT(*) AS cnt FROM `orders_parts` WHERE `order_id` = ?', [$row['id']]);
    if(!$rows2[0]['cnt']){
        $db->exec('DELETE FROM `orders` WHERE `id` = ?', [$row['id']]);
        echo '<p>empty: '.$row['id'].'</p>';
    }
}
exit;
$rows = $db->exec('SELECT * FROM `orders`');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT COUNT(*) AS cnt FROM `orders_parts` WHERE `order_id` = ?', [$row['id']]);
    if(!$rows2[0]['cnt']){
        $db->exec('DELETE FROM `orders` WHERE `id` = ?', [$row['id']]);
        echo '<p>empty: '.$row['id'].'</p>';
    }
}
exit;
$rows = $db->exec('SELECT * FROM `parts2_log` WHERE `h` = 1 AND `object2_id` > 220000 AND `event_id` = 23 LIMIT 1000');

foreach($rows as $row){
    $db->exec('UPDATE `parts2_log` SET `h` = 1 WHERE `id` = ?', [$row['id']]);
    try{
        $repair = Repair::getRepairByID($row['object2_id']);
        $serial = $repair['serial'];
       /*  $model = reset(Parts::getModels($row['part_id']));
        $serial = (!empty($model['serials'])) ? reset($model['serials'])['model_serial'] : '';
        $modelID = (!empty($model['serials'])) ? reset($model['serials'])['model_id'] : 0;  */ 
        if(empty($serial)){
            continue;
        } 
    }catch(Exception $e){
        continue;
    }
    $db->exec('UPDATE `parts2_log` SET `serial` = ? WHERE `id` = ?', [$serial, $row['id']]);
}

if(count($rows) > 0){
    echo '<script>setTimeout(function(){document.location.reload();}, 1000)</script>';
}else{
    echo '<h1>Загрузка завершена</h1>';
} 
exit;
$rows = $db->exec('SELECT `repair_id` FROM `feedback_admin` WHERE `read_admin` = 0 AND `repair_id` != 0 ORDER BY `id` DESC');
echo '<ol>';
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `repairs` WHERE `id` = ? and `deleted` = 0', [$row['repair_id']]);
    if($rows2){
        continue;
    }
    echo '<li style="margin-bottom: 24px"><a href="/edit-repair/'.$row['repair_id'].'/step/6/" target="_blank">№ '.$row['repair_id'].'</a></li>';
}
echo '</ol>';
exit;
$rows = $db->exec('SELECT `id`, `model_id`, `no_serial`, `serial` FROM `repairs` WHERE `h` = 0 LIMIT 5000');
foreach($rows as $row){
    if(empty(trim($row['serial']))){
        $db->exec('UPDATE `repairs` SET `h` = 1, `no_serial` = 1, `serial_invalid_flag` = 0 WHERE `id` = ?', [$row['id']]);
        continue;
    }
    $serialInvalidFlag = (empty($row['no_serial']) && !\models\Serials::isValid($row['serial'], $row['model_id'])) ? 1 : 0;
    $db->exec('UPDATE `repairs` SET `h` = 1, `serial_invalid_flag` = ? WHERE `id` = ?', [$serialInvalidFlag, $row['id']]);
}

if(count($rows) > 0){
    echo '<script>setTimeout(function(){document.location.reload();}, 1000)</script>';
}else{
    echo '<h1>Загрузка завершена</h1>';
} 

exit;
$delCnt = 0;
$rows = $db->exec('SELECT `id`, `repair_id` FROM `feedback_admin` WHERE `h` = 0 AND `repair_id` != 0 LIMIT 1000');
foreach($rows as $row){
    if(empty($row['repair_id'])){
        continue;
    }
    $rows2 = $db->exec('SELECT `id` FROM `repairs` WHERE `id` = ?', [$row['repair_id']]); 
    if($rows2){
        $db->exec('UPDATE `feedback_admin` SET `h` = 1 WHERE `id` = ?', [$row['id']]);
        continue;
    }
    $db->exec('DELETE FROM `feedback_admin` WHERE `id` = ?', [$row['id']]);
    $db->exec('DELETE FROM `feedback_messages` WHERE `feedback_id` = ?', [$row['id']]);
    $db->exec('DELETE FROM `feedback_photos` WHERE `feedback_id` = ?', [$row['id']]);
    $db->exec('DELETE FROM `feedback_videos` WHERE `feedback_id` = ?', [$row['id']]);
    $delCnt++;
}
echo '<p>Del: '.$delCnt.'</p>';
if(count($rows) > 0){
    echo '<script>setTimeout(function(){document.location.reload();}, 1000)</script>';
}else{
    echo '<h1>Загрузка завершена.</h1>';
}
exit;
$delCnt = 0;
$rows = $db->exec('SELECT `id` FROM `repairs` WHERE `status_admin` IN("Принят", "Отклонен") 
                    AND `client_id` = 0 AND `client_type` = 0 AND `address` = "" AND `name_shop` = "" 
                    AND `phone` = "" AND `city_shop` = "" AND `address_shop` = "" AND `phone_shop` = "" 
                    AND `serial` = "" AND `sell_date` = "0000-00-00" AND `begin_date` = "0000-00-00" 
                    AND `repair_type_id` = 0 AND `disease` = "0" AND `total_price` = 0 
                    AND `anrp_number` = "" AND `return_id` = 0 AND `imported` = 0 AND `onway` = 0 
                    AND `master_id` = 0 AND `out_date` = "0000-00-00" AND `receive_date` = "0000-00-00"
                     AND `works` ="" AND `component` = "" AND `recommend` = "" AND `repair_done` = 0 
                     AND `repair_final` = 0 AND `ready_date` = "0000-00-00" 
                     AND `problem_id` = 0 AND `doubled` = 0 AND `create_date` < "2022-09-07 12:00:00"');
foreach($rows as $row){
     $rows2 = $db->exec('SELECT `id` FROM `repairs_work` WHERE `repair_id`='. $row['id']);
    if($rows2){
        continue;
    } 
   $rows2 = $db->exec('SELECT `id` FROM `orders` WHERE `repair_id`='. $row['id']);
    if($rows2){
        continue;
    } 
    $db->exec('DELETE FROM `repairs` WHERE `id` = ?', [$row['id']]);
    $db->exec('DELETE FROM `log` WHERE `object_id` = ?', [$row['id']]);
    $delCnt++;
}
echo '<p>Total: '.count($rows).'</p>';
echo '<p>Del: '.$delCnt.'</p>';
exit;
/* $rows = $db->exec('SELECT `id`, `name` FROM `parts2` WHERE `name` LIKE "%№%" AND `h` = 0 LIMIT 500');
foreach($rows as $row){
    // По номеру ремонта и новодельной запчасти находим базовую запчасть 
    $p = explode('№', $row['name']);
    $initPartID = $row['id'];
    $repairID = trim($p[1]);
    $rowsRep = $db->exec('SELECT `id` FROM `repairs` WHERE `id` = ?', [$repairID]);
    $db->exec('UPDATE `parts2` SET `h` = 1 WHERE `id` = ?', [$initPartID]);
    if(!$rowsRep){
        echo '<p style="color:red">error, repair not found, $initPartID: '.$initPartID.', name: '.$row['name'].'</p>';
        continue; // возможно, не новодельная запчасть
    }
    $rows2 = $db->exec('SELECT * FROM `parts2` WHERE `name` = ? AND `attr_id` = 2', [trim($p[0])]);
    if(!$rows2){
        echo '<p style="color:red">error, base not found, $initPartID: '.$initPartID.'</p>';
        continue;
    }    
     // На основе базовой делаем новую 
    $partID = $rows2[0]['id'];
    $newPartID = \models\Parts::createOriginalPart($partID, $repairID);
    if(!$newPartID){
        echo '<p style="color:red">New part error: $partID: '.$partID.', $repairID: '.$repairID.'</p>';
        continue;
    }
    // Отмечаем старую на удаление, новую на замену 
    $rowsNew = $db->exec('SELECT `id`, `name` FROM `parts2` WHERE `id` = ?', [$newPartID]);
    $db->exec('INSERT INTO `parts-repl` (`old`, `new`) VALUES (?, ?)', [$initPartID, $newPartID]);
    $db->exec('UPDATE `parts2` SET `to_del` = 1 WHERE `id` = ?', [$initPartID]);
    echo '<hr>new part<pre>';
    print_r($rowsNew[0]);
    echo '</pre>'; 
    echo 'old part<pre>';
    print_r($row);
    echo '</pre><hr>'; 
}

exit; */ 

/*  $rows = $db->exec('SELECT * FROM `parts-repl` WHERE `h` = 0 LIMIT 100');
foreach($rows as $row){
    $db->exec('UPDATE `repairs_work` SET `part_id` = ? WHERE `part_id` = ?', [$row['new'], $row['old']]);
    $db->exec('UPDATE `parts2_balance` SET `part_id` = ? WHERE `part_id` = ?', [$row['new'], $row['old']]);
    $db->exec('UPDATE `parts2_log` SET `part_id` = ? WHERE `part_id` = ?', [$row['new'], $row['old']]);
    $db->exec('UPDATE `parts2_models` SET `to_del` = 1 WHERE `part_id` = ?', [$row['old']]);
    $db->exec('UPDATE `orders_extra` SET `part_id` = ? WHERE `part_id` = ?', [$row['new'], $row['old']]);
    $db->exec('UPDATE `orders_parts` SET `part_id` = ? WHERE `part_id` = ? AND `origin_id` = 1', [$row['new'], $row['old']]);
    $rowsRep = $db->exec('SELECT `id`, `saved_parts` FROM `repairs` WHERE `saved_parts` != ""');
    foreach($rowsRep as $rowRep){
        if(empty(trim($rowRep['saved_parts']))){
            continue;
        }
        $newData = [];
        $data = json_decode($rowRep['saved_parts'], true);
        foreach($data as $partID => $num){
            if($partID == $row['old']){
                $newData[$row['new']] = $num;
            }else{
                $newData[$partID] = $num;
            }
        }
        $db->exec('UPDATE `repairs` SET `saved_parts` = ? WHERE `id` = ?', [(($newData) ? json_encode($newData) : ''), $rowRep['id']]);
    }
    $db->exec('UPDATE `parts-repl` SET `h` = 1 WHERE `id` = ?', [$row['id']]);
}
echo '<p>Rows count: '.count($rows).'</p>';
exit;  */

$rows = $db->exec('SELECT * FROM `cats`');
foreach($rows as $row){
    echo '<p>old: '.$row['name'].' - new: <b>'.getName($row['name']).'</b></p>';
}
function getName($name){
    $name = trim(preg_replace('/[^a-zа-яё\d\s-,<>]/ui', '', $name));
    $name = str_replace(['Harper', 'ZARGET', 'HARPER', 'Olto', 'TESLER', 'Body Craft', 'Compak', 'Maxima', 'Redmond'], '', $name);
    $p = explode(',', $name);
    return $p[0];
}

exit;
$rows = $db->exec('SELECT `id`, `total_price` FROM `repairs` where `repair_type_id` = 4 AND `create_date` BETWEEN "2022-08-01 00:00:00" AND "2022-08-31 23:59:59"');

foreach($rows as $row){
    echo '<p><a href="/edit-repair/'.$row['id'].'/step/2/">'.$row['id'].' - '.$row['total_price'].'</a></p>';
    Repair::rejectRepair($row['id']);
}
exit;
$rows = $db->exec('SELECT * FROM `repairs_attention`');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `approve_date` FROM `repairs` WHERE `model_id` = ? AND `serial` = ? AND `approve_date` != "0000-00-00" ORDER BY `id` DESC LIMIT 1', [$row['model_id'], $row['serial']]);
    if(!$rows2){
        echo '<p style="color:red">fail</p>';
    }else{
        $date = $rows2[0]['approve_date'] . ' 12:00:00';
        $messages = explode(';', $row['message']);
        foreach($messages as $message){
            $db->exec('INSERT INTO `repairs_attention_messages` (`message`, `add_date`, `attention_id`) VALUES (?, ?, ?)', [trim($message), $date, $row['id']]);
        }
    }
}

exit;
$rows = $db->exec('SELECT * FROM `parts2_log` WHERE `event_id` = 13');
foreach($rows as $row){
    $repair = Repair::getRepairByID($row['object_id']);
    if(empty($repair['model_id'])){
        continue;
    }
    $db->exec('UPDATE `parts2_log` SET `object2_id` = ? WHERE `id` = ?', [$repair['model_id'], $row['id']]);
}
exit;
$rows = $db->exec('SELECT * FROM `repairs_work` WHERE `h` = 0 LIMIT 10000');

$i = 0;
foreach($rows as $work){
    $ownFlag = ($work['price'] > 0) ? 1 : 0;
    if($work['part_id'] != 0){
        $i++;
        $db->exec('UPDATE `repairs_work` SET `h` = 1, `own_flag` = '.$ownFlag.' WHERE `id` = ' . $work['id']);
        continue;
    }
    $position = trim($work['position']);
    if($work['name']){
        $position = $position . ' ('.trim($work['name']).')';
    }
    $db->exec('UPDATE `repairs_work` SET `h` = 1, `own_flag` = '.$ownFlag.', `position` = "'.trim($position).'" WHERE `id` = ' . $work['id']);
    $i++;
}
echo $i;




exit;
$rows = $db->exec('SELECT p.`id`, p.`name_1s`, v.`name` AS vendor FROM `parts2` p LEFT JOIN `parts2_vendors_0108` v ON v.`id` = p.`vendor_id`');

foreach($rows as $row){
    if(empty($row['vendor'])){
     continue;   
    }
    $rows2 = $db->exec('SELECT * FROM `providers` WHERE `name` = ?', [$row['vendor']]);
    if(!$rows2){
        echo '<p>['.$row['name_1s'].'](https://crm.r97.ru/part/?id='.$row['id'].') - введено: '.$row['vendor'].'</p>';
        $r = $db->exec('UPDATE `parts2` SET `vendor_id` = 1 WHERE `id` = ?', [$row['id']]);
    }else{
        $r = $db->exec('UPDATE `parts2` SET `vendor_id` = ? WHERE `id` = ?', [$rows2[0]['id'], $row['id']]);
        if(!$r){
            echo '<p style="color:red">error: '.$db->getErrorInfo().'</p>';
        }
    }
    
}

exit;
$rows = $db->exec('SELECT * FROM `orders_parts` WHERE `origin_id` = 1');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `parts2` WHERE `id` = '.$row['part_id']);
    if($rows2){
        continue;
    }
    $rows3 = $db->exec('SELECT `id`, `parent_id` FROM `parts` WHERE `id` = '.$row['part_id']);
    if(empty($rows3[0]['parent_id'])){
        echo '<p style="color:red">'.$row['part_id'].'</p>';
    }else{
        echo '<p style="color:green">'.$row['part_id'].', parent id: '.$rows3[0]['parent_id'].'</p>';
        $db->exec('UPDATE `orders_parts` SET `part_id` = '.$rows3[0]['parent_id'].' WHERE `id` = '.$row['id']);
    }
    
}
exit;
$repairIDs = [];

foreach($repairIDs as $repairID){
   $rows = $db->exec('SELECT `status_admin`, `service_id`, `create_date` FROM `repairs` WHERE `deleted` = 0 AND `id` = '.$repairID);
   if(empty($rows[0]['service_id'])){
        echo '<p style="color:blue">no '.$repairID.'</p>';
        continue;
   }
   $repair = $rows[0];
   $manuals = $store = [];
   $partsManual = $db->exec('SELECT * FROM `parts_files` WHERE `repair_id` = '.$repairID);
   foreach($partsManual as $p){
        if(empty($p['img'])){
            continue;
        }
        $manuals[] = $db->exec('INSERT INTO `orders_manual` (`repair_id`, `photo_path`, `comment`) VALUES (?, ?, ?)', [$repairID, trim($p['img']), trim($p['comment'])]);
   }
   $partsStore = $db->exec('SELECT * FROM `repairs_parts` WHERE `repair_id` = '.$repairID);
   foreach($partsStore as $p){
        if(empty($p['part_id'])){
            continue;
        }
        $store[] = $p['part_id'];
   }
   if(!$manuals && !$store){
    continue;
   }
   $params = getParams($repair);
   $orderID = $db->exec('INSERT INTO `orders` (`service_id`, `repair_id`, `status_id`, `create_date`, `approve_date`, `send_date`, `cancel_date`, `receive_date`) 
   VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$repair['service_id'], $repairID, $params['status_id'], $params['create_date'], $params['approve_date'], $params['send_date'], $params['cancel_date'], $params['receive_date']]); 
   if(!$orderID){
    continue;
   }
   if($manuals){
    foreach($manuals as $manID){
        $db->exec('INSERT INTO `orders_parts` (`order_id`, `part_id`, `origin_id`, `depot_id`, `qty`, `cancel_flag`) 
        VALUES (?, ?, ?, ?, ?, ?)', [$orderID, $manID, 2, 1, 1, 0]);
    }
   }
   if($store){
    foreach($store as $storeID){
        $db->exec('INSERT INTO `orders_parts` (`order_id`, `part_id`, `origin_id`, `depot_id`, `qty`, `cancel_flag`) 
        VALUES (?, ?, ?, ?, ?, ?)', [$orderID, $storeID, 1, 1, 1, 0]);
    }
   }
   echo '<p>Order: '.$orderID.'</p>';
}


function getParams(array $repair){
    $res = ['status_id' => 4, 
    'create_date' => $repair['create_date'], 
    'approve_date' => '0000-00-00 00:00:00', 
    'send_date' => '0000-00-00 00:00:00', 
    'cancel_date' => '0000-00-00 00:00:00', 
    'receive_date' => '0000-00-00 00:00:00'];
    if(in_array($repair['status_admin'], ['Подтвержден', 'Выдан'])){
        $res['status_id'] = 4;
        $res['approve_date'] = $repair['create_date'];
        $res['send_date'] = $repair['create_date'];
        $res['receive_date'] = $repair['create_date'];
    }elseif($repair['status_admin'] == 'В обработке'){
        $res['status_id'] = 2;
        $res['approve_date'] = $repair['create_date'];
    }elseif($repair['status_admin'] == 'Нужны запчасти'){
        $res['status_id'] = 1;
    }elseif($repair['status_admin'] == 'Отклонен'){
        $res['status_id'] = 5;
        $res['cancel_date'] = $repair['create_date'];
    }elseif($repair['status_admin'] == 'Запчасти в пути'){
        $res['status_id'] = 3;
        $res['approve_date'] = $repair['create_date'];
        $res['send_date'] = $repair['create_date'];
    }
    return $res;
}

exit;
/* Синхронизация старых запчастей с новыми */
try{
    $rows = $db->exec('SELECT * FROM `parts` WHERE `parent_id` = 0');
    foreach($rows as $part){
        $rows2 = $db->exec('SELECT `id` FROM `parts2` WHERE `id` = '.$part['id']);
        $partID = $part['id'];
        if($rows2){
            updatePart($partID, $part);
        }else{
            addPart($partID, $part);
        }
        addBalance($partID, $part);
        addModels($partID, $part);
    }
}catch(Exception $e){
    echo '<p style="color:red">'.$e->getMessage().'</p>';
}

function addModels($partID, array $oldPart){
    global $db;
    $modelID = preg_replace('/[^0-9]/', '', $oldPart['model_id']);
    if(!empty($oldPart['serial']) && !empty($modelID)){
        $db->exec('INSERT INTO `parts2_models` (`part_id`, `model_cat_id`, `model_id`, `model_serial`) 
        VALUES (?, ?, ?, ?)', [$partID, (($oldPart['cat']) ? $oldPart['cat'] : 0), $modelID, trim($oldPart['serial'])]); 
    }
    $rows = $db->exec('SELECT * FROM `parts` WHERE `parent_id` = '. $partID);
    foreach($rows as $row){
        $modelID = preg_replace('/[^0-9]/', '', $row['model_id']);
        if(!empty($row['serial']) && !empty($modelID)){
            $db->exec('INSERT INTO `parts2_models` (`part_id`, `model_cat_id`, `model_id`, `model_serial`) 
            VALUES (?, ?, ?, ?)', [$partID, (($row['cat']) ? $row['cat'] : 0), $modelID, trim($row['serial'])]); 
        }
    }
}

function addBalance($partID, array $oldPart){
    global $db;
    $num = ($oldPart['count'] < 0) ? 0 : $oldPart['count'];
    $r = $db->exec('INSERT INTO `parts2_balance` (`part_id`, `depot_id`, `qty`, `place`) 
    VALUES (?, ?, ?, ?)', [$partID, 1, $num, trim($oldPart['place'])]);
    if(!$r){
        throw new Exception('Add balance: '.$db->getErrorInfo());
    }
}

function addPart($partID, array $oldPart){
    global $db;
    $partID = $db->exec('INSERT INTO `parts2` (`id`, `group_id`, `name`, `name_1s`, `description`, `type_id`, `attr_id`, `weight`, `price`, `part_num`, `vendor_id`, `photos`, `del_flag`) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
        $partID, 
        group($oldPart), 
        trim($oldPart['list']), 
        trim($oldPart['list']), 
        trim($oldPart['desc']), 
        typeID($oldPart), 
        1, 
        weight($oldPart), 
        price($oldPart), 
        trim($oldPart['part']), 
        vendor($oldPart), 
        trim($oldPart['imgs']), 
        0]);
    if(!$partID){
        throw new Exception('Add part: '.$db->getErrorInfo());
    }
}

function updatePart($partID, array $oldPart){
    global $db;
    $r = $db->exec('UPDATE `parts2` SET `group_id` = ?, `name` = ?, `name_1s` = ?, `description` = ?, `type_id` = ?, `attr_id` = ?, `weight` = ?, `price` = ?, `part_num` = ?, `vendor_id` = ?, `photos` = ?  
    WHERE `id` = ?', [
        group($oldPart), 
        trim($oldPart['list']), 
        trim($oldPart['list']), 
        trim($oldPart['desc']), 
        typeID($oldPart), 
        1, 
        weight($oldPart), 
        price($oldPart), 
        trim($oldPart['part']), 
        vendor($oldPart), 
        trim($oldPart['imgs']),
        $partID]);
    if(!$r){
        throw new Exception('Upd part: '.$db->getErrorInfo());
    }
}

function vendor(array $oldPart){
    global $db;
    if(empty($oldPart['brand'])){
        return 0;
    }
    $v = Parts::getVendor(0, $oldPart['brand']);
    if($v){
        return $v['id'];
    }
    $id = $db->exec('INSERT INTO `parts2_vendors` (`name`) VALUES (?)', [trim($oldPart['brand'])]);
    if(!$id){
        throw new Exception('vendor: '.$db->getErrorInfo());
    }
    return $id;
}

function price(array $oldPart){
    if(empty($oldPart['price'])){
        return 0;
    }
    return round(str_replace(',', '.', trim($oldPart['price'])), 2);
}

function weight(array $oldPart){
    if(empty($oldPart['weight'])){
        return 0;
    }
    return preg_replace('/[^0-9]/', '', $oldPart['weight']);
}

function typeID(array $oldPart){
    $type = trim($oldPart['type']);
    if(empty($type)){
        return 0;
    }
    if($type == 'БЛОЧНЫЙ ЭЛЕМЕНТ'){
        return 1;
    }
    if($type == 'КОМПОНЕНТНЫЙ ЭЛЕМЕНТ'){
        return 2;
    }
    if($type == 'АКСЕССУАР'){
        return 3;
    }
    return 0;
}

function group(array $oldPart){
    global $db;
    if(empty($oldPart['group'])){
        return 0;
    }
    $m = [];
    preg_match('/ \(([a-z]{1,3})\)/i', $oldPart['group'], $m);
    $code = (isset($m[1])) ? $m[1] : trim($oldPart['codepre']);
    $group = trim(str_replace('(' . $code . ')', '', $oldPart['group']));
    $g = Parts::getGroup(0, $group, $code);
    if($g){
        return $g['id'];
    }
    $groupID = $db->exec('INSERT INTO `parts2_groups` (`name`, `code`) VALUES (?, ?)', [trim($group), trim($code)]);
    if(!$groupID){
        throw new Exception('group: '.$db->getErrorInfo());
    }
    return $groupID;
}

/* $rows = $db->exec('SELECT * FROM `repairs_bak` WHERE `return_id` = ?', [5674]);
$keys = array_keys($rows[0]);
foreach($rows as $row){
   $params = [];
   foreach($keys as $k){
    $params[] = $row[$k];
   }
echo '<pre>';
print_r($row);
echo '</pre>';
 $r = $db->exec('INSERT INTO `repairs` (`'.implode('`, `', $keys).'`)  
    VALUES ('.implode(', ', array_fill(0, count($keys), '?')).')', $params);
if($r ){
    echo '<p style="color:green">yes '.$r.'!</p>';
}else{
    echo '<p style="color:red">no '.$r.'!</p>';
}
} */
// синхронизировать кол-во
/* $rows = $db->exec('SELECT * FROM `parts`');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `parts2` WHERE `name` = ?', [trim($row['list'])]);
    if(!$rows2){
        continue;
    }
    $partID = $rows2[0]['id'];
    $qty = ($row['count'] < 0) ? 0 : $row['count'];
    $r = $db->exec('INSERT INTO `parts2_balance` (`part_id`, `depot_id`, `qty`, `place`) 
    VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `qty` = VALUES(qty), `place` = VALUES(place)', 
    [$partID, 1, $qty, trim($row['place'])]); 
} */
// синхронизировать модели
/* $rows = $db->exec('SELECT * FROM `parts` WHERE `parent_id` 
IN (1108, 3311, 4492, 10929, 11007, 11044, 14676, 14680, 14684, 14897) OR `id` IN (1108, 3311, 4492, 10929, 11007, 11044, 14676, 14680, 14684, 14897)');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `parts2` WHERE `name` = ?', [trim($row['list'])]);
    $partID = $rows2[0]['id'];
    $r = $db->exec('INSERT INTO `parts2_models` (`part_id`, `model_cat_id`, `model_id`, `model_serial`) 
    VALUES (?, ?, ?, ?)', [$partID, $row['cat'], $row['model_id'], trim($row['serial'])]); 
} */
/* // синхронизировать запчасти
$rows = $db->exec('SELECT * FROM `parts` WHERE `parent_id` = 0');
foreach($rows as $row){
    $rows2 = $db->exec('SELECT `id` FROM `parts2` WHERE `name` = ?', [trim($row['list'])]);
    if($rows2){
        continue; 
    }
    $partID = $db->exec('INSERT INTO `parts2` (
        `group_id`, `name`, `name_1s`, `description`, 
    `type_id`, `attr_id`, `weight`, `price`, `part_num`, `vendor_id`, `photos`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            0, trim($row['list']), trim($row['list']), trim($row['desc']), 0, 0, $row['weight'],
         $row['price'], trim($row['part']), 0,  $row['imgs']
        ]);  
        if(!$partID){
            continue; 
        }
        echo '<pre>';
        print_r($row);
        echo '</pre>';
        $qty = ($row['count'] < 0) ? 0 : $row['count'];
        $r = $db->exec('INSERT INTO `parts2_balance` (`part_id`, `depot_id`, `qty`, `place`) 
        VALUES (?, ?, ?, ?)', [$partID, 1, $qty, trim($row['place'])]);   
} */ 

/* $rows = $db->exec('SELECT * FROM `parts` WHERE `serial` != "" ORDER BY `id`');
foreach($rows as $row){
    if(!$row['model_id']){
        continue;
    }
    $id = ($row['parent_id']) ? $row['parent_id'] : $row['id'];
    $qty = ($row['count'] < 0) ? 0 : $row['count'];
    $r = $db->exec('INSERT INTO `parts2_balance` (`model_id`, `model_cat_id`, `model_serial`, `part_id`, `depot_id`, `qty`, `place`) 
    VALUES (?, ?, ?, ?, ?, ?, ?)', [$row['model_id'], $row['cat'], trim($row['serial']), $id, 1, $qty, trim($row['place'])]);
    if($r){
        echo '<p style="color:green">ok '.$id.'</p>';
    }else{
        echo '<p style="color:red">fail '.$id.'</p>';
        echo $db->getErrorInfo();
        echo '<hr>';
    }
}  */

/* $rows = $db->exec('SELECT `id`, `name` FROM `parts2`');
foreach($rows as $row){
    $r = $db->exec('UPDATE `parts2` SET `name` = ? WHERE `id` = ?', [trim($row['name']), $row['id']]);
} */

/*  $rows = $db->exec('SELECT * FROM `parts2_balance` WHERE `model_serial` = ""');
foreach($rows as $row){
    $serial = '';
    $order = '';
    $provider = 0;
    $rows2 = $db->exec('SELECT `model_id` FROM `parts2` WHERE `id` = ?', [$row['part_id']]);
    $rows3 = $db->exec('SELECT * FROM `serials` WHERE `model_id` = ? and `serial` != ""', [$rows2[0]['model_id']]); 
    if($rows3){
        $serial = $rows3[0]['serial'];
        $order = $rows3[0]['order'];
        $provider = $rows3[0]['provider_id'];
    }
    $db->exec('UPDATE `parts2_balance` SET `model_serial` = ?, `serial_order` = ?, `serial_provider_id` = ? WHERE `id` = ?',
     [$serial, $order, $provider, $row['id']]);
}    */

/* $rows = $db->exec('SELECT * FROM `repairs` WHERE `deleted` = 0 AND `hf` = 0 LIMIT 1000');

foreach($rows as $row){
    $cost = getPartsCost($row['id']);
    $r = $db->exec('UPDATE `repairs` SET `parts_cost` = ?, `hf` = 1 WHERE `id` = ?', [$cost, $row['id']]);
    if($cost){
        echo '<p>ремонт '.$row['id'].', цена: '.$cost.'</p>';
    }
  
    if(!$r){
        echo '<p style="color: red">ошибка</p>';
    } 
}

if(count($rows) > 0){
    echo '<script>setTimeout(function(){document.location.reload();}, 1000)</script>';
}else{
    echo '<h1>Загрузка завершена.</h1>';
}

function getPartsCost($repairID)
{
    global $db;
    $rows = $db->exec('SELECT SUM(summ) AS sum FROM `repair_list` WHERE `repair_id` = ' . $repairID . ' AND `harper` != 1');
    return ($rows) ? $rows[0]['sum'] : 0;
} */

/* foreach($rows as $row){
    $cost = getCost($row['onway_type'], $row['service_id'], $row['cat_id']);
    if(!$cost){
        continue;
    }
    $r = $db->exec('UPDATE `repairs` SET `transport_cost` = ? WHERE `id` = ?', [$cost, $row['id']]);
    echo '<p>ремонт '.$row['id'].', цена: '.$cost.'</p>';
    if(!$r){
        echo '<p style="color: red">ошибка</p>';
    }
}


function getCost($zone, $serviceID, $catID)
{
    global $db;
    if(!$catID){
        return 0;
    }
    $k = str_replace('zone', 'zone_', $zone);
    if ($serviceID) {
        $rows = $db->exec('SELECT `' . $k . '` FROM `transfer_service` WHERE `cat_id` = ' . $catID . ' AND `service_id` = ' . $serviceID);
        if (!empty($rows[0][$k])) {
            return $rows[0][$k];
        }
    } 
    $table = Tariffs::getTransportTariffTable($serviceID);
   // $table = 'transfer_2';
    $rows = $db->exec('SELECT `' . $k . '` FROM `' . $table . '` WHERE `cat_id` = ' . $catID);
    return ($rows) ? $rows[0][$k] : 0;
} */

/* $rows = $db->exec('SELECT * FROM `details_problem`');

for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
    $newName = trim(str_replace('(РЕМОНТ) ', '', $rows[$i]['name']));
    $newName = trim(str_replace('(БЕЗ РЕМОНТА) ', '', $newName));
    $newName = trim(str_replace('(ТЕСТИРОВАНИЕ) ', '', $newName));
    $db->exec('UPDATE `details_problem` SET `name` = ? WHERE `id` = ?', [$newName, $rows[$i]['id']]);
} */


/* $rows = $db->exec('SELECT `id`, `receive_date` FROM `repairs` WHERE `create_date` = "0000-00-00 00:00:00" and deleted = 0;');

foreach($rows as $row){
    if(Time::isEmpty($row['receive_date'])){
        continue;
    }
    $db->exec('UPDATE `repairs` SET `create_date` = ? WHERE `id` = ?', [$row['receive_date'] . ' 10:00:00', $row['id']]);
}   */

/* $rows = $db->exec('SELECT `value` FROM `settings2` WHERE `id` = 7');
echo '<pre>';
print_r(json_decode($rows[0]['value'], true));
echo '</pre>'; */

/* $rows = $db->exec('SELECT `id`, `client_type`, `sell_date` FROM `repairs` WHERE `deleted` = 0 AND `status_ship_id` IN (0, 4, 5, 6, 7, 8) AND `status_admin` IN ("Выдан", "Подтвержден") LIMIT 1000');
foreach($rows as $row){
    $status = getShipStatus($row);
    $db->exec('UPDATE `repairs` SET `status_ship_id` = ? WHERE `id` = ?', [$status, $row['id']]);
    echo '<p>'.$row['id'].' new '.$status.'</p>';
}
echo '<p><b>'.count($rows).'</b></p>';
if(count($rows) > 0){
    echo '<script>setTimeout(function(){document.location.reload();}, 1000)</script>';
}elseif(count($rows) <= 0){
    echo '<h1>Готово.</h1>';
}


function getShipStatus(array $repair)
{
  if (models\Repair::isRepeated($repair['id'])) {
    return 3; // Повторный
  }
  if ($repair['client_type'] == 1 || ($repair['client_type'] == 2 && !core\Time::isEmpty($repair['sell_date']))) { 
    return 2; // Клиентский
  }
  if ($repair['client_type'] == 2 && core\Time::isEmpty($repair['sell_date'])) {
    return 1;  // Предторговый   
  }
  return 0;
}
exit; */


/* 
 $rows = $db->exec('SELECT `id`, `date_created_old`, `create_date` FROM `repairs` WHERE `create_date` = "0000-00-00 00:00:00" AND `date_created_old` != "0000-00-00 00:00:00"');
echo '<pre>';
print_r($rows);
echo '</pre>';

echo implode(',', array_column($rows, 'id'));  */



/* $path = '/bad.jpg';
try {
    $img = new Imagick($_SERVER['DOCUMENT_ROOT'] . $path);
     $img->stripImage();
        $img->writeImage($_SERVER['DOCUMENT_ROOT'] . $path);
        $img->clear();
        $img->destroy();
    $source = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'] . $path);
    if (!$source) {
        exit('error');
    }
    echo "ok";
    echo '<img style="width:500px" src="'.$path.'">';
} catch (Exception $e) {
    echo 'Exception caught: ',  $e->getMessage(), "\n";
} */   


/* // дата готовности
$rows = $db->exec('SELECT `id`, `approve_date` FROM `repairs` WHERE `status_admin` IN ("Выдан", "Подтвержден") AND `ready_date` = "0000-00-00"');
foreach($rows as $row){
    if($row['approve_date'] == '0000-00-00'){
        continue;
    }
    $r = $db->exec('UPDATE `repairs` SET `ready_date` = ? WHERE `id` = ?', [$row['approve_date'], $row['id']]);
if(!$r){
    echo '<p>fail '.$row['id'].'</p>';
}
}
echo count($rows);   */

/* $rows = $db->exec('SELECT `id`, `model_id` FROM `repairs` WHERE `cat_id` = ? AND `model_id` != ? LIMIT 1000', [0, 0]);
foreach($rows as $row){
    $cat = models\Cats::getCatByModelID($row['model_id']);
    if(!$cat){
        continue;
    }
   $r = $db->exec('UPDATE `repairs` SET `cat_id` = ? WHERE `id` = ?', [$cat['id'], $row['id']]);
   if($r){
    echo '<p style="color:green">success</p>';
   }else{
    echo '<p style="color:red">fail</p>';
   }
} */

exit;
$rightNum = '51 070250001';
$wrongNum = '1705576D10104047';
$modelID = 2207;

echo '<pre>match<br>';
print_r(isValid1($rightNum, $modelID));
echo '</pre>';

echo '<pre>db<br>';
print_r(isValid2($rightNum, $modelID));
echo '</pre>';

function isValid1($serial, $modelID)
    {
        global $db;
        $serial = trim($serial);
        $rows = $db->exec('SELECT `serial`, `lot` FROM `serials_data` WHERE `model_id` = ?', [$modelID]);
        if(!$rows){
            return 0;
        }
        foreach ($rows as $row) {
            if(empty($row['serial']) || empty($row['lot'])){
                continue;
            }
            $lotLen = strlen($row['lot']);
            $m = [];
            preg_match('/^([0-9a-z- ]+)([0-9]{' . $lotLen . '})([a-z]*)$/i', $serial, $m);
            /* 
                $m[1] - База номера
                $m[2] - Кол-во в партии
                $m[3] - Завершающие буквы (если есть)
            */
            if ((int)$m[2] > $row['lot']) {
                continue;
            }
            if(preg_match('/^'.$m[1].'[0-9]{' . $lotLen . '}'.$m[3].'$/i', $row['serial'])) {
                return 1;
            }
        }
        return 0; 
    }

    function isValid2($serial, $modelID)
    {
        global $db;
        $rows = $db->exec('SELECT COUNT(*) AS cnt FROM `serials` WHERE `serial` = ? AND `model_id` = ?', [$serial, $modelID]);
        return (int)$rows[0]['cnt'];
    }

//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
///////////////////////// Фото из json ///////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
exit;
try {
    $space = new SpacesConnect($config['digocean_key'], $config['digocean_secret'], $config['digocean_name'], $config['digocean_region']);
    $rows = $db->exec('SELECT `id`, `imgs` FROM `parts` WHERE `imgs` != "" AND `done_flag` = 0 LIMIT 30');
    foreach ($rows as $row) {
        echo '<hr><p>Processing: ' . $row['id'] . '</p>';
        $images = json_decode($row['imgs']);
        if (!$images) {
            $db->exec('UPDATE `parts` SET `done_flag` = ? WHERE `id` = ?', [1, $row['id']]);
            echo '<p style="color:lightslategrey">Empty array.</p>';
            continue;
        }
        $resultImages = [];
        $delImages = [];
        foreach ($images as $img) {
            /* Уже готово */
            if (preg_match('/digitalocean/', $img)) {
                echo '<p style="color:lightslategrey">Already done: ' . $img . '</p>';
                $resultImages[] = $img;
                continue;
            }
            $img = trim(preg_replace('~http[s]?:\/\/(crm.r97.ru|service.harper.ru)~', '', $img));
            /* Загрузка нового */
            $newURL = upload($img, $row['id']);
            /* Файл не существует */
            if($newURL == -1){
                continue;
            }
            if (!$newURL) {
                echo '<p style="color:red"><i>Error upload: ' . $img . '</i>.</p>';
                exit;
            }
            $delImages[] = $img;
            $resultImages[] = $newURL;
        }
        $r = $db->exec('UPDATE `parts` SET `imgs` = ?, `done_flag` = ? WHERE `id` = ?', [json_encode($resultImages), 1, $row['id']]);
        if ($r) {
            delete($delImages);
        } else {
            echo '<p style="color:red"><i>Error db: ' . $db->getErrorInfo() . '</i>.</p>';
            exit;
        }
    }
    if (count($rows) > 0) {
        echo '<script>setTimeout(function(){document.location.reload();}, 5000)</script>';
    } else {
        echo '<h1>Загрузка завершена.</h1>';
    }
} catch (Exception $e) {
    exit('<p style="color:red">Exception: ' . $e->getMessage() . '.</p>');
}

function upload($path, $id)
{
    global $space;
    if (!is_file($_SERVER['DOCUMENT_ROOT'] . $path)) {
        echo '<p style="color:green"><i>Already gone: ' . $path . '</i>.</p>';
        return -1;
    }
    $up = [];
    $fileName =  date('d-Hi-s') . rand(1, 99999999);
    $parts = pathinfo($path);
    $ext = mb_strtolower($parts['extension']);
    $up = $space->UploadFile(ltrim($path, '/'), 'public', 'uploads/photos/parts/' . core\FS::getVolByID($id) . '/' . $id . '/' . $fileName . '.' . $ext);
    if (!empty($up['ObjectURL'])) {
        echo '<p style="color:green">Upload: <a href="' . $up['ObjectURL'] . '" target="_blank">' . $up['ObjectURL'] . '</a>.</p>';
        return $up['ObjectURL'];
    }
    echo '<p style="color:red">Can\'t upload: ' . $path . '.</p>';
    return '';
}

function delete(array $paths)
{
    foreach($paths as $path){
        if (!is_file($_SERVER['DOCUMENT_ROOT'] . $path)) {
            echo '<p style="color:blue"><i>Already gone: ' . $path . '</i>.</p>';
            return;
        }
        echo '<p style="color:blue">Delete: ' . $path . '.</p>';
        unlink($_SERVER['DOCUMENT_ROOT'] . $path);
    }
} 

exit;
/* $rows = $db->exec('SELECT `id`, `service` FROM `models` WHERE `brand` IN ("HARPER", "OLTO", "SKYLINE", "TESLER")');
foreach($rows as $row){
    $db->exec('UPDATE `models_users` SET `service` = ? WHERE `model_id` = ? AND `service_id` NOT IN (33, 341)', [$row['service'], $row['id']]);
} */

/* $rows = $db->exec('SELECT * FROM `repairs` where `id` IN (182048,
182223,
182921,
183840,
184905,
184907)');
echo '<pre>';
print_r($rows );
echo '</pre>'; */
/*  $rows = $db->exec('SELECT * FROM `admin_logs` where `date` BETWEEN "2020-12-15 15:00:00" AND "2020-12-15 18:15:00"');
$ids = [];
$messages = [];
foreach($rows as $row){
$m = [];
preg_match("/\d{3,}/", $row['name'], $m);
if(!empty($m[0])){
    $ids[] = $m[0];
    if(isset($messages[$m[0]])){
        $messages[$m[0]] .= '<br>'.$row['name'];
    }else{
        $messages[$m[0]] = $row['name'];
    }
}
}
$ids = array_unique($ids);
foreach($ids as $id){
    $rows = $db->exec('SELECT `date_created`, `app_date` FROM `repairs` where `id` = ? AND `date_created` BETWEEN "2020-01-01 01:00:00" AND "2020-12-15 18:15:00"', [$id]);
    if(!$rows){
        continue;
    }
    echo '<h4>'.$id.'</h4>';
    echo '<p>created '.$rows[0]['date_created'].'</p>';
    echo '<p>app '.$rows[0]['app_date'].'</p>';
    echo '<p>'.$messages[$id].'</p>';
}  */


/* $rows = $db->exec('SELECT `id`, `anrp_number` FROM `repairs`');
foreach($rows as $row){
    if(empty($row['anrp_number'])){
        continue;
    }
    $oldVal = $row['anrp_number'];
    $newVal = trim(str_replace(['акт', 'Акт', 'АКТ'], '', $oldVal), '№ ');
    $parts = explode(' ', $newVal);
    $newVal = filter_var($parts[0], FILTER_SANITIZE_NUMBER_INT);
    echo '<p>old: <b style="color:red">'.$oldVal.'</b>; new: <b style="color:green">'.$newVal.'</b></p>';
    $db->exec('UPDATE `repairs` SET `anrp_number` = ? WHERE `id` = ?', [$newVal, $row['id']]);
} */

/* require_once($_SERVER['DOCUMENT_ROOT'].'/includes/PHPMailer-master/PHPMailerAutoload.php');
$mes = 'test';
$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 1;
$mail->Host = 'smtp.mail.ru';
$mail->SMTPAuth = true;
$mail->SMTPSecure = "ssl";
$mail->Username = 'kan@r97.ru';
$mail->Password = 'rtjkRsD';
$mail->Timeout       =  10;
$mail->Port = 465;
$mail->addReplyTo('kan1@r97.ru', 'имя');
$mail->setFrom('kan@r97.ru', 'R97.RU');
$mail->addAddress('');
$mail->isHTML(true);
$mail->Subject = "TEST";
$mail->CharSet = 'UTF-8';
$mail->Body    = $mes;
$mail->MailerDebug = true;
$mail->send();   */
 
/* $mes = 'test';
                    $headers  = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type: text/html; charset=utf-8\r\n";
                    $headers .= "From: robot@crm.r97.ru\r\n"; 
                    $headers .= "Reply-To: kan@r97.ru\r\n";
                    mail('', "Новое уведомление от R97.RU", $mes, $headers);   */       



/* if(preg_match('/^[\d]{4}\.[\d]{2}\.[\d]{2}$/', 'df2020f.04g.05g')){
echo 'yes';
}else{
    echo 'no';
} */


/* $rows = $db->exec('SELECT * FROM `repairs` WHERE `status_admin` = "Подтвержден" AND `end_date` = ""');

foreach($rows as $row){
    if($row['approve_date'] == '0000-00-00'){
        continue;
    }
    $db->exec('UPDATE `repairs` SET `end_date` = ?, `finish_date` = ? WHERE `id` = ?', [core\Time::format($row['approve_date'], 'd.m.y'), $row['approve_date'], $row['id']]);
} */

 
// Убираем апостроф
/* $rows = $db->exec('SELECT `id`, `group` FROM `parts` WHERE `group` LIKE "%\'%"');

foreach($rows as $row){
    $val = str_replace("'", "", $row['group']);
    $rows = $db->exec('UPDATE `parts` SET `group` = ? WHERE `id` = ?', [$val, $row['id']]);
}  */



//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
/////////////////////// Ремонты и запчасти ///////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
exit;

try{
    $space = new SpacesConnect($config['digocean_key'], $config['digocean_secret'], $config['digocean_name'], $config['digocean_region']);
    $error = 0;
    $rows = $db->exec('SELECT `id`, `repair_id`, `url` FROM `photos` WHERE `url` NOT LIKE "%digitalocean%" LIMIT 100');
    foreach($rows as $row){
        echo '<hr><p>Processing: '.$row['url'].'</p>';  
        /* Уже готово */
        if(preg_match('/digitalocean/', $row['url'])){
            echo '<p style="color:lightslategrey">Already done: '.$row['url'].'</p>';
            continue;
        }
        $row['url'] = preg_replace('~http[s]?:\/\/(crm.r97.ru|service.harper.ru)~', '', $row['url']);
        /* Ремонт не существует */
        $rowsRep = $db->exec('SELECT `receive_date` FROM `repairs` WHERE `id` = ?', [$row['repair_id']]);
        if(!$rowsRep){
            echo '<p style="color:lightcoral">Repair #'.$row['repair_id'].' not exists.</p>';
            $db->exec('DELETE FROM `photos` WHERE `id` = ?', [$row['id']]);
            delete($row['url']);
            continue;
        }
        /* Ранее было сделано */
        /* if(preg_match('/digitalocean/', $row['url_do'])){
            $r = $db->exec('UPDATE `repairs_photo` SET `url` = ? WHERE `id` = ?', [$row['url_do'], $row['id']]);
            if($r){
                echo '<p style="color:#00a8b9">Relocate: '.$row['url_do'].'.</p>';
                delete($row['url']);
            }else{
                echo '<p style="color:#00a8b9"><i>Error relocate: '.$db->getErrorInfo().'</i>.</p>';
            }
            continue;
        } */
        /* Загрузка нового */
        $newURL = upload($row['url'], $rowsRep[0]['receive_date']);
        if(!$newURL){
            $error = 1;
            break;
        }
        if($newURL == -1){
            $db->exec('DELETE FROM `photos` WHERE `id` = ?', [$row['id']]);
            continue;
        }
        $r = $db->exec('UPDATE `photos` SET `url` = ? WHERE `id` = ?', [$newURL, $row['id']]);
        if($r){
            delete($row['url']);
        }else{
            echo '<p style="color:red"><i>Error upload: '.$db->getErrorInfo().'</i>.</p>';
            $error = 1;
            break;
        }
    }
    if(!$error && count($rows) > 0){
        echo '<script>setTimeout(function(){document.location.reload();}, 10000)</script>';
    }elseif($error){
        echo '<h1 style="color:red">Во время загрузки произошла ошибка.</h1>';
    }elseif(count($rows) <= 0){
        echo '<h1>Загрузка завершена.</h1>';
    }
}catch(Exception $e){
    exit('<p style="color:red">Error: '.$e->getMessage().'.</p>');
}

/* function upload($path, $date)
{
    global $space;
    if(!is_file($_SERVER['DOCUMENT_ROOT'] . $path)){
        echo '<p style="color:green"><i>Already gone: '.$path.'</i>.</p>';
        return -1;
    }
    $up = [];
    $fileName =  date('d-Hi-s') . rand(1, 99999999);
    $parts = pathinfo($path);
    $ext = mb_strtolower($parts['extension']);
    $date = (core\Time::isEmpty($date)) ? date('Y-m-d') : $date;
    $up = $space->UploadFile(ltrim($path, '/'), 'public', 'uploads/photos/repairs/' . date('mY', strtotime($date)) . '/' . $fileName . '.' . $ext);
    if (!empty($up['ObjectURL'])) {
        echo '<p style="color:green">Upload: <a href="' . $up['ObjectURL'] . '" target="_blank">' . $up['ObjectURL'] . '</a>.</p>';
        return $up['ObjectURL'];
    }
    echo '<p style="color:red">Can\'t upload: ' . $path . '.</p>';
    return '';
} */

/* function delete($path){
    if(!is_file($_SERVER['DOCUMENT_ROOT'] . $path)){
        echo '<p style="color:blue"><i>Already gone: '.$path.'</i>.</p>';
        return;
    }
    echo '<p style="color:blue">Delete: '.$path.'.</p>';
    unlink($_SERVER['DOCUMENT_ROOT'] . $path);
}  */


//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
////////////////////////// Поддержка /////////////////////////////
//////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////
exit;
try{
    $space = new SpacesConnect($config['digocean_key'], $config['digocean_secret'], $config['digocean_name'], $config['digocean_region']);
    $error = 0;
    $rows = $db->exec('SELECT `id`, `feedback_id`, `url` FROM `feedback_videos` WHERE `url` NOT LIKE "%digitalocean%" LIMIT 5');
    foreach($rows as $row){
        echo '<hr><p>Processing: '.$row['url'].'</p>';  
        /* Уже готово */
        if(preg_match('/digitalocean/', $row['url'])){
            echo '<p style="color:lightslategrey">Already done: '.$row['url'].'</p>';
            continue;
        }
        $row['url'] = preg_replace('~http[s]?:\/\/(crm.r97.ru|service.harper.ru)~', '', $row['url']);
        /* Тикет не существует */
        $rowsRep = $db->exec('SELECT `date` FROM `feedback_admin` WHERE `id` = ?', [$row['feedback_id']]);
        if(!$rowsRep){
            echo '<p style="color:lightcoral">Ticket #'.$row['feedback_id'].' not exists.</p>';
            $db->exec('DELETE FROM `feedback_videos` WHERE `id` = ?', [$row['id']]);
            delete($row['url']);
            continue;
        }
        /* Загрузка нового */
        $newURL = upload($row['url'], $rowsRep[0]['date']);
        if(!$newURL){
            $error = 1;
            break;
        }
        if($newURL == -1){
            $db->exec('DELETE FROM `feedback_videos` WHERE `id` = ?', [$row['id']]);
            continue;
        }
        $r = $db->exec('UPDATE `feedback_videos` SET `url` = ? WHERE `id` = ?', [$newURL, $row['id']]);
        if($r){
            delete($row['url']);
        }else{
            echo '<p style="color:red"><i>Error upload: '.$db->getErrorInfo().'</i>.</p>';
            $error = 1;
            break;
        }
    }
    if(!$error && count($rows) > 0){
        echo '<script>setTimeout(function(){document.location.reload();}, 10000)</script>';
    }elseif($error){
        echo '<h1 style="color:red">Во время загрузки произошла ошибка.</h1>';
    }elseif(count($rows) <= 0){
        echo '<h1>Загрузка завершена.</h1>';
    }
}catch(Exception $e){
    exit('<p style="color:red">Error: '.$e->getMessage().'.</p>');
}

/* function upload($path, $date)
{
    global $space;
    if(!is_file($_SERVER['DOCUMENT_ROOT'] . $path)){
        echo '<p style="color:green"><i>Already gone: '.$path.'</i>.</p>';
        return -1;
    }
    $up = [];
    $fileName =  date('d-Hi-s') . rand(1, 99999999);
    $parts = pathinfo($path);
    $ext = mb_strtolower($parts['extension']);
    $date = (core\Time::isEmpty($date)) ? date('Y-m-d') : date('Y-m-d', strtotime($date));
    $up = $space->UploadFile(ltrim($path, '/'), 'public', 'uploads/video/tickets/' . date('mY', strtotime($date)) . '/' . $fileName . '.' . $ext);
    if (!empty($up['ObjectURL'])) {
        echo '<p style="color:green">Upload: <a href="' . $up['ObjectURL'] . '" target="_blank">' . $up['ObjectURL'] . '</a>.</p>';
        return $up['ObjectURL'];
    }
    echo '<p style="color:red">Can\'t upload: ' . $path . '.</p>';
    return '';
}

function delete($path){
    if(!is_file($_SERVER['DOCUMENT_ROOT'] . $path)){
        echo '<p style="color:blue"><i>Already gone: '.$path.'</i>.</p>';
        return;
    }
    echo '<p style="color:blue">Delete: '.$path.'.</p>';
    unlink($_SERVER['DOCUMENT_ROOT'] . $path);
} */

