<?php

# Подключаем  конфиг:
require_once('includes/configuration.php');
# Подключаем функции:
require_once('includes/functions.php');

function get_request_info($id) {
  global $db;
return mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
}

function get_request_info_serice($id) {
  global $db;
$req = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `requests` WHERE `user_id` = '.$id));
return $req;
}

$get_max_id = mysqli_fetch_array(mysqli_query($db, 'SELECT max(bill_id) FROM `brands` where bill_id != 0 LIMIT 1'))['max(bill_id)'];
$get_max_id = $get_max_id + 2;
$sql = mysqli_query($db, 'SELECT * FROM `brands` where `bill_id` = 0 and `act_id` = 0;');
      while ($row = mysqli_fetch_array($sql)) {
        echo $row['name'].' - '.($get_max_id+1).' '.($get_max_id+2).'<br>';
        $get_max_id = $get_max_id + 2;
      }

//mysqli_query($db, 'UPDATE `pay_billing` set sum = \'\' where `year` = 2018 service_id = 33');

//get_service_summ_fast(33, 11, 2019, 'HORIZONT', 33);
//get_service_summ_fast(33, 11, 2019, 'SVEN', 33);
//get_service_summ_fast(33, 11, 2019, 'TESLER', 33);
//get_service_summ_fast(33, 11, 2019, 'HORIZONT', 33);

/*get_service_summ_fast(33, 06, 2019, 'HORIZONT', 33);
get_service_summ_fast(33, 06, 2019, 'SVEN', 33);
get_service_summ_fast(33, 04, 2019, 'SVEN', 33); */

/*get_service_summ_fast(33, 12, 2018, 'HARPER');
get_service_summ_fast(33, 12, 2018, 'SVEN');
get_service_summ_fast(33, 12, 2018, 'TESLER');
get_service_summ_fast(33, 12, 2018, 'HORIZONT');

get_service_summ_fast(33, 11, 2018, 'HARPER');
get_service_summ_fast(33, 11, 2018, 'SVEN');
get_service_summ_fast(33, 11, 2018, 'TESLER');
get_service_summ_fast(33, 11, 2018, 'HORIZONT');

get_service_summ_fast(33, 10, 2018, 'HARPER');
get_service_summ_fast(33, 10, 2018, 'SVEN');
get_service_summ_fast(33, 10, 2018, 'TESLER');
get_service_summ_fast(33, 10, 2018, 'HORIZONT');

get_service_summ_fast(33, 9, 2018, 'HARPER');
get_service_summ_fast(33, 9, 2018, 'SVEN');
get_service_summ_fast(33, 9, 2018, 'TESLER');
get_service_summ_fast(33, 9, 2018, 'HORIZONT');   */

/*get_service_summ_fast(33, date('n'), 2019, 'HORIZONT');
get_service_summ_fast(33, 6, 2019, 'HORIZONT');
get_service_summ_fast(33, 5, 2019, 'HORIZONT');
get_service_summ_fast(33, 4, 2019, 'HORIZONT');
get_service_summ_fast(33, 3, 2019, 'HORIZONT');
get_service_summ_fast(33, 2, 2019, 'HORIZONT');
get_service_summ_fast(33, 1, 2019, 'HORIZONT');
get_service_summ_fast(33, 6, 2018, 'HORIZONT');
get_service_summ_fast(33, 7, 2018, 'HORIZONT');
get_service_summ_fast(33, 8, 2018, 'HORIZONT');
get_service_summ_fast(33, 9, 2018, 'HORIZONT');
get_service_summ_fast(33, 10, 2018, 'HORIZONT');
get_service_summ_fast(33, 11, 2018, 'HORIZONT');
get_service_summ_fast(33, 12, 2018, 'HORIZONT');    */

?>