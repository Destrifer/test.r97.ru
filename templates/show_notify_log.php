<?php
header("Content-type: text/html; charset=utf-8");


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

function check_important() {
  global $db;
$count_important = 0;
$count = mysqli_num_rows(mysqli_query($db, 'SELECT `feedback_id` FROM `feedback_messages` WHERE `read` = 0 GROUP BY `feedback_id`'));
return $count;
}

function get_important() {
  global $db;
$sql = mysqli_query($db, 'SELECT `feedback_id` FROM `feedback_messages` WHERE `read` = 0 GROUP BY `feedback_id`');
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
$where = '';
} else {
$where = 'WHERE `user_id` = '.\models\User::getData('id');
}


//echo 'SELECT * FROM `feedback_admin` '.$where.' ORDER by `id` DESC';
# Считаем количество записей:

$count = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `feedback_admin` '.$where.' ORDER by `id` DESC;'));
$countElements = $count['COUNT(*)'];
if ($countElements == 0) {
   $content_list['body'] = '<tr><td colspan="9" style="text-align:center">Обращений с такими условиями нет!</td></tr>';
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
          if ($_GET['important'] == 'true') {
            if (\models\User::hasRole('admin')) {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$model['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td>'.$row['message'].'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a>  <a class="t-5" style="float:" onclick="return confirm(\'Подтвердите удаление запроса\');" href="del_tickets.php?id='.$row['id'].'"></a></td></tr>';
            } else {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$model['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td>'.$row['message'].'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a></td></tr>';
            }
          } else if ($_GET['important'] != 'true') {
            if (\models\User::hasRole('admin')) {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$model['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td style="width:200px;">'.$row['message'].'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a>  <a class="t-5" style="float:" onclick="return confirm(\'Подтвердите удаление запроса\');" href="del_tickets.php?id='.$row['id'].'"></a> </td></tr>';
            } else {
            $content_list['body'] .= '<tr class="'.$class.'"><td class="sorting_1">'.$row['id'].'</td><td>'.$model['name'].'</td><td>'.str_replace(' ', '<br>', $row['date']).'</td><td>'.str_replace(' ', '<br>', $last_message['date']).'</td><td style="text-align:center">'.$row['status'].'</td><td style="width:200px;">'.$row['message'].'</td><td align="center" >'.$pub.'</td><td align="center" class="linkz"><a class="t-3" href="/tickets/'.$row['id'].'/" ></a> </td></tr>';
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


if ($_GET['id']) {
$content = stripslashes_array(notify_log_get($_GET['id']));
}



function get_services_allow($brand_id) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `users` where `status_id` = 1 AND `role_id` = 3;');
      while ($row = mysqli_fetch_array($sql)) {
       $info = get_request_info_by_user_id($row['id']);
       if ($info['name']) {
       $servies_array[$row['id']] = trim($info['name']);
       }
      }

//print_r( $servies_array);
asort($servies_array);
//print_r( $servies_array);
foreach ($servies_array as $service_id => $service_name) {
$sel = (mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `brands_users` WHERE `service_id` = \''.mysqli_real_escape_string($db, $service_id).'\' and `brand_id` = '.$brand_id.' and `service` = 1 LIMIT 1;'))['COUNT(*)'] == 1) ? 'selected' : '';
if ($sel != '') {
$content[] = $service_id;
}
}

$content = implode(',', $content);

    return $content;

}

function notify_log_get($id) {
  global $config, $db;
$sql = mysqli_query($db, 'SELECT * FROM `notify_logs` WHERE `id` = '.$id.';');
return mysqli_fetch_array($sql);
}

function brands_list() {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands` ;');
      while ($row = mysqli_fetch_array($sql)) {

      $service_list = get_services_allow($row['id']);
      if ($service_list != '') {
      $content[] = '<a href="#" class="select_services" style="color:#77ad07;text-decoration:underline" data-id="'.$row['id'].'" data-services="'.$service_list.'">'.$row['name'].'</a>';
      }
      }

      $content = implode(', ', $content);

    return $content;
}


function services_list2($list) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `users` where `status_id` = 1 and `role_id` = 3;');
      while ($row = mysqli_fetch_array($sql)) {
       $info = get_request_info_by_user_id($row['id']);
       if ($info['name']) {
       $servies_array[$row['id']] = trim($info['name']);
       }
      }

print_r( $servies_array);
asort($servies_array);
print_r( $servies_array);

$check_array = explode(',', $list);

foreach ($servies_array as $service_id => $service_name) {

if (in_array($service_id, $check_array)) {
$content .= '<option value="'.$service_id.'" selected>'.$service_name.'</option>'; 
} else {
$content .= '<option value="'.$service_id.'" >'.$service_name.'</option>';
}



}



    return $content;
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



?>
<!doctype html>
<html>
<head>
<meta charset=utf-8>
<title>Панель управления</title>
<link href="/css/fonts.css" rel="stylesheet" />
<link href="/css/style.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="/date/jquery.datetimepicker.css"/>
<link rel="stylesheet" href="/_new-codebase/front/vendor/select2/4.0.4/select2.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal-default-theme.min.css" />
<link rel="stylesheet" href="/_new-codebase/front/vendor/remodal/remodal.css" />

<link rel="stylesheet" href="/_new-codebase/front/vendor/font-awesome.css" />
<link rel="stylesheet" href="/redactor/redactor.css" />
<script src="/_new-codebase/front/vendor/jquery/jquery-1.7.2.min.js"  ></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.placeholder.min.js"></script>
<script src="/js/jquery.formstyler.min.js"></script>
<script src="/js/main.js"></script>
<script src="/_new-codebase/front/vendor/remodal/remodal.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/4.0.4/select2.full.min.js"></script>
<script src="/_new-codebase/front/vendor/select2/select2.multi-checkboxes.js"></script>

<script src="/notifier/js/index.js"></script>
<link rel="stylesheet" type="text/css" href="/notifier/css/style.css">
<link rel="stylesheet" href="/_new-codebase/front/vendor/animate.min.css" />
<link rel="stylesheet" href="/js/fSelect.css" />
<script src='/_new-codebase/front/vendor/mustache/mustache.min.js'></script>
<script src="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.concat.min.js"></script>
<link rel="stylesheet" href="/_new-codebase/front/vendor/malihu/jquery.mCustomScrollbar.min.css" />

<script  src="/_new-codebase/front/vendor/datatables/1.10.12/jquery.dataTables.min.js"></script>

<link rel="stylesheet" type="text/css" href="/js/datatables.css">
<link rel="stylesheet" type="text/css" href="/js/daterangepicker.css">
<script src="/js/moment.min.js"></script>
<script src="/js/jquery.daterangepicker.js"></script>
<script >
// Таблица
$(document).ready(function() {

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


    $(document).on("click",".select_services", function(e){

    var array = $(this).data('services').split(",");
    $.each(array,function(i){
      $('.select2-multiple2').find('option[value="'+array[i]+'"]').prop("selected", true);
    });

    $('.select2-multiple2').fSelect('reload');

    return false;

    });



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
.select2-container {
    width: 450px !important;
}

.select2-result-label .wrap:before{
    position:absolute;
    left:4px;
    font-family:fontAwesome;
    color:#999;
    content:"\f096";
    width:25px;
    height:25px;

}
.select2-result-label .wrap.checked:before{
    content:"\f14a";
}
.select2-result-label .wrap{
    margin-left:15px;
}

/* not required css */

.row
{
  padding: 10px;
}
</style>
<script src="/redactor/redactor.min.js"></script>

<script src="/redactor/lang/ru.js"></script>
<script src="/js/fSelect.js"></script>
  <script >


  $(document).ready(
    function()		{
    $('#redactor_text').redactor({minHeight: 200, lang: 'ru', imageUpload: '/image_upload.php', fileUpload: '/file_upload.php'});
    $('#redactor_text2').redactor({ minHeight: 200, lang: 'ru', imageUpload: '/image_upload.php', fileUpload: '/file_upload.php'});

 /* $('.select2-multiple2').select2MultiCheckboxes({
    templateSelection: function(selected, total) {
      return "Выбрано " + selected.length + " из " + total;
    }
  }) */

$('.select2-multiple2').fSelect({
    placeholder: 'Список',
    numDisplayed: 1,
    overflowText: '{n} выбрано',
    noResultsText: 'Не найдено',
    searchText: 'Поиск',
    showSearch: true
});

$( ".sel_all" ).on( "click", function() {
  $('.select2-multiple2').find('option').prop("selected", true);
  $('.select2-multiple2').fSelect('reload');
  $('input[name="send_to_all"]').val(1);
  return false;
});
$( ".desel_all" ).on( "click", function() {
  $('.select2-multiple2').find('option').prop("selected", false);
  $('.select2-multiple2').fSelect('reload');
  $('input[name="send_to_all"]').val(0);
  return false;
});

$( ".select2-multiple2" ).on( "change", function() {
  $('input[name="send_to_all"]').val(0);
});


    }




  );
  </script>
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

  </div><!-- .adm-tab -->  <br>

           <form id="send" class="sendform" method="POST" enctype="multipart/form-data">
            <div class="adm-form">
                <br>


            <div id="add_form">

            <div class="item" style="display: block;  width: 100%;">
              <div class="level" style="display: block;  width: 100%;">Сообщение:</div>
              <div class="value" style="display: block;  width: 100%;">
                <div class="adm-w-text" style="border:0px;">
                  <textarea id="redactor_text" name="answer" rows="5" ><?=$content['message'];?></textarea>
                </div>
              </div>
            </div>

            <div class="item">
              <div class="level" style="display: block;text-align: center;width: 100%;">Отправлено СЦ:</div>
              <div class="value" style="display:block;">
                <input type="hidden" name="send_to_all" value="0">
              <select name="receiver[]" class="nomenu select2-multiple2" multiple>

               <!--<option value="all">Всем</option>-->
               <?=services_list2($content['users_ids']);?>
              </select>
              </div>
            </div>


            <br>
<br><br>



            </div>


        </div>

      </form>



</div><!-- .wrapper -->

</div><!-- .viewport-wrapper -->

</body>
</html>