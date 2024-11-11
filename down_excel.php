<?php

# Подключаем  конфиг:

use models\User;
use models\Users;

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');
# Подключаем функции:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/functions.php');
# Подключаем авторизацию
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/auth.php');



function myUrlEncode($string) {
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}



function clients($cat_id) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `clients` group by `name` order by `name` asc;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($cat_id == $row['id']  || $_COOKIE['client_id'] == $row['id']) {
      $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
      } else {
       $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      }
    return $content;
}

function brands($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands`;');
      while ($row = mysqli_fetch_array($sql)) {
      if (User::getData('id') == 33 || User::getData('id') == 1) {
      $content .= '<li><label><input type="checkbox" name="brands[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
      } else if ($row['name'] == 'HARPER' || $row['name'] == 'TESLER' || $row['name'] == 'OLTO' || $row['name'] == 'SKYLINE' || $row['name'] == 'NESONS') {
      $content .= '<li><label><input type="checkbox" name="brands[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
      }

      }
    return $content;
}

function get_last_photo($repair_id, $type) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `repairs_photo` where `photo_id` = '.$type.' and `repair_id` = '.$repair_id.' order by id desc LIMIT 1');
    if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {
       $content = ($row['url_do'] != '') ? $row['url_do'] : $row['url'];
      }
      }
    return $content;
}

function check_payed_repair($year, $month, $service_id) {
  global $db;

//  echo $year.'|'.$month.'|'.$service_id.'<br>';

$sql = mysqli_fetch_array(mysqli_query($db, 'SELECT COUNT(*) FROM `pay_billing` where `service_id` = \''.$service_id.'\' and `month` = \''.$month.'\' and `year` = \''.$year.'\' and (`type` = 2 OR `type` = 4) and `status` = 1 and `original` = 1 ;'));
//ho  'SELECT COUNT(*) FROM `pay_billing` where `service_id` = \''.$service_id.'\' and `month` = \''.$month.'\' and `year` = \''.$year.'\' and (`type` = 2 OR `type` = 4) and `status` = 1 and `original` = 1 ;';
if ($sql['COUNT(*)'] > 0) {
return true;
} else {
return false;
}


}


function models($cat_id) {
  global $db;
$content = array();
$sql = mysqli_query($db, 'SELECT * FROM `models` order by `name` ASC;');
      while ($row = mysqli_fetch_array($sql)) {

      if (User::getData('id') == 33 || User::getData('id') == 1) {
        if ($cat_id == $row['id']) {
        $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
        } else {
         $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
      } else {
        if ($cat_id == $row['id']) {
        $content .= '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
        } else if ($row['brand'] == 'HARPER' || $row['brand'] == 'TESLER' || $row['brand'] == 'OLTO' || $row['brand'] == 'SKYLINE' || $row['brand'] == 'NESONS') {
         $content .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
        }
      }

      }
    return $content;
}

function brands2($id = '') {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `brands`;');
      while ($row = mysqli_fetch_array($sql)) {
      if ($row['id'] != 4) {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="'.$row['name'].'" checked/>'.$row['name'].'</label></li>';
      } else {
      $content .= '<li><label><input type="checkbox" name="brands2[]" value="'.$row['name'].'" />'.$row['name'].'</label></li>';
      }
      }
    return $content;
}

function get_provider_info($model_id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `serials` WHERE `model_id` = '.$model_id);
      if (mysqli_num_rows($sql) != false) {
      while ($row = mysqli_fetch_array($sql)) {


       $model = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `models` WHERE `id` = \''.mysqli_real_escape_string($db, $model_id).'\' LIMIT 1;'));
       $exp_provider = explode('|', $model['provider']);
       foreach ($exp_provider as $provider) {
         if ($row['provider_id'] == $provider) {

            $content['name'] = privider_name($provider);
            $content['order_id'] = $row['order'];
            break;
         }


       }

      }

      return $content;

      }



}

function privider_name($id) {
  global $db;

$sql = mysqli_query($db, 'SELECT * FROM `providers` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row['name'];
      }
    return $content;
}



$glob_id = 7;



   //     $xls = PHPExcel_IOFactory::load($new_file);
  //      $xls->setActiveSheetIndex(0);
  //      $sheet = $xls->getActiveSheet();



$body = '
<html>
<script src="/_new-codebase/front/vendor/jquery/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

<script  src="/_new-codebase/front/vendor/print.min.js"></script>
<script src="/_new-codebase/front/vendor/zapjs.com/download.js"></script>
<script>
$( document ).ready(function() {

    function exportHTML(){
       var header = "<html xmlns:o=\'urn:schemas-microsoft-com:office:office\' "+
            "xmlns:w=\'urn:schemas-microsoft-com:office:word\' "+
            "xmlns=\'http://www.w3.org/TR/REC-html40\'>"+
            "<head><meta charset=\'utf-8\'><title>Export HTML to Word Document with JavaScript</title></head><body>";
       var footer = "</body></html>";
       var sourceHTML = header+document.getElementById("printable").innerHTML+footer;

       var source = \'data:application/vnd.ms-word;charset=utf-8,\' + encodeURIComponent(sourceHTML);
       var fileDownload = document.createElement("a");
       document.body.appendChild(fileDownload);
       fileDownload.href = source;
       fileDownload.download = \'tv_report.doc\';
       fileDownload.click();
       document.body.removeChild(fileDownload);
    }

$.expr[":"].contains = $.expr.createPseudo(function(arg) {
    return function( elem ) {
        return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});

      $(\'.download\').on(\'click\', function() {
        //printJS(\'printable\', \'html\');
        download(document.getElementById("printable").outerHTML, "report.html", "text/html");
        });


      $(\'.download2\').on(\'click\', function() {
       exportHTML();
        });

$(\'.zui-table\').find(\'td:not(.image)\').each(function() {
  $(this).click(function() {
    $(\'.zui-table td\').not($(this)).prop(\'contenteditable\', false);
    $(this).prop(\'contenteditable\', true);
  });
  $(this).blur(function() {
    $(this).prop(\'contenteditable\', false);
  });

});

      $(\'.del_sel\').on(\'click\', function() {

      $(\'#printable\ input[type=checkbox]:checked:visible\').each(function(index){
  //part where the magic happens
  $(this).parent().parent().parent().remove();
});

         });

      $(\'.sel_all\').on(\'click\', function() {
          var checkboxes = $(\'#printable\').find(\':checkbox:visible\');
          checkboxes.prop(\'checked\', true);
         });
      $(\'.desel_all\').on(\'click\', function() {
          var checkboxes = $(\'#printable\').find(\':checkbox:visible\');
          checkboxes.prop(\'checked\', false);
         });

$(".upload").on("click", function() {
    var file_data = $(this).parent().find(\'input[type="file"]\').prop("files")[0];
    var content_id = $(this).parent().find(\'input[type="file"]\').data(\'id\');
    var content_type = $(this).parent().find(\'input[type="file"]\').data(\'type\');
    console.log(content_id);
    console.log(content_type);
    var form_data = new FormData();
    var td = $(this).parent();
    form_data.append("file", file_data);
    form_data.append("content_id", content_id);
    form_data.append("content_type", content_type);
    $.ajax({
        url: "/editor_img.php",
        dataType: \'script\',
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: \'GET\',
        success: function(result){
            td.html(\'<a target="_blank" href="\'+result+\'"><img class="lazy" src="http://crm.r97.ru/resizer.php?src=\'+result+\'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a>\');
        }
    });
    return false;
});

function update_pp() {
var id = 0;
$(\'#printable\ td.pp:visible\').each(function(index){
  id++;
  $(this).text(id);
});
}

update_pp();

function change_update() {
$(\'#printable tr\').show();
if ($(\'#filter1\').val()) {
$("#printable td.col1:contains(\'" + $(\'#filter1\').val() + "\'):visible").parent().show();
$("#printable td.col1:not(:contains(\'" + $(\'#filter1\').val() + "\'))").parent().hide();
}
if ($(\'#filter2\').val()) {
$("#printable td.col2:contains(\'" + $(\'#filter2\').val() + "\'):visible").parent().show();
$("#printable td.col2:not(:contains(\'" + $(\'#filter2\').val() + "\'))").parent().hide();
}
if ($(\'#filter3\').val()) {
$("#printable td.col3:contains(\'" + $(\'#filter3\').val() + "\'):visible").parent().show();
$("#printable td.col3:not(:contains(\'" + $(\'#filter3\').val() + "\'))").parent().hide();
}
if ($(\'#filter4\').val() ) {
$("#printable td.col4:contains(\'" + $(\'#filter4\').val() + "\'):visible").parent().show();
$("#printable td.col4:not(:contains(\'" + $(\'#filter4\').val() + "\'))").parent().hide();
}
if ($(\'#filter5\').val()) {
$("#printable td.col5:contains(\'" + $(\'#filter5\').val() + "\'):visible").parent().show();
$("#printable td.col5:not(:contains(\'" + $(\'#filter5\').val() + "\'))").parent().hide();
}
if ($(\'#filter6\').val() ) {
$("#printable td.col6:contains(\'" + $(\'#filter6\').val() + "\'):visible").parent().show();
$("#printable td.col6:not(:contains(\'" + $(\'#filter6\').val() + "\'))").parent().hide();
}
if ($(\'#filter7\').val() ) {
$("#printable td.col7:contains(\'" + $(\'#filter7\').val() + "\'):visible").parent().show();
$("#printable td.col7:not(:contains(\'" + $(\'#filter7\').val() + "\'))").parent().hide();
}
if ($(\'#filter8\').val()) {
$("#printable td.col8:contains(\'" + $(\'#filter8\').val() + "\'):visible").parent().show();
$("#printable td.col8:not(:contains(\'" + $(\'#filter8\').val() + "\'))").parent().hide();
}
update_pp();
}

    $(\'#filter1\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col1:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col1:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter2\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col2:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col2:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
      update_pp();
    });
    $(\'#filter3\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col3:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col3:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter4\').change(function() {
    if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col4:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col4:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter5\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col5:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col5:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter6\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col6:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col6:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter7\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col7:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col7:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });
    $(\'#filter8\').change(function() {
        if ($(this).val() == \'\') {
        change_update();
        } else {
        $("#printable td.col8:contains(\'" + $(this).val() + "\'):visible").parent().show();
        $("#printable td.col8:not(:contains(\'" + $(this).val() + "\'))").parent().hide();
        }
        update_pp();
    });




      });



</script>
<body  >

<style>
button.download{
height: 47px;
    background: #80bd03;
    font-size: 17px;
    color: #fff;
    border:0;
    margin-left:30px;
}
.delete_row {
 display:block !important;
}
</style>



<table id="printable" class="zui-table">

<style>

    @page WordSection1{
         mso-page-orientation: landscape;
         size: 841.95pt 595.35pt; /* EU A4 */
         /* size:11.0in 8.5in; */ /* US Letter */
     }
     #printable {
         page: WordSection1;
     }

    table { page-break-inside:auto }
    tr    { page-break-inside:avoid; page-break-after:auto }
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
table {
  border-collapse: collapse;
  font-size:1vw;
  word-wrap: break-word; table-layout: fixed; width: 100%;

}
tr {
  border-bottom: 1px solid #ccc;
}
th, td {
  text-align: left;
  padding: 4px;
  border: 1px solid #ccc;
  height:200px;
}
.header {
text-align:center;
}
 @media print
{
    .no-print, .no-print *
    {
        display: none !important;
    }
}


</style>

<tr><td colspan="12">ИП Кулиджанов Андрей Александрович ИНН 773771305797; ОГРНИП 313774621100330; Адрес местонахождения: 115569, г. Москва, ул. Маршала Захарова, дом 20, кв. 92;  mail: ak@r97.ru; tel.: +7 (495) 136-90-75</td></tr>
<tr><td colspan="12" style="height:30px;border:0;"></td></tr>
<tr>
<td class="header" colspan="3">Период от: Period started: </td>
<td style="text-align:center;">'.date("d.m.Y", strtotime($_GET['date1'])).'</td>
<td class="header" colspan="2">До: Period over: </td>
<td style="text-align:center;">'.date("d.m.Y", strtotime($_GET['date2'])).'</td>
<td colspan="5"></td>
</tr>
<tr><td colspan="10" style="height:30px;border:0;"></td></tr>
<tr>
<td class="header">№ПП</td>
<td class="header">Factory</td>
<td class="header">Нoменклатура / Model Name</td>
<td class="header">SN</td>
<td class="header">Order number</td>
<td class="header">Причина обращения/Reason</td>
<td class="header">Дефект/Defect</td>
<td class="header">Бракованная деталь №/defect spare parts</td>
<td class="header">Позиционное обозначение (на плате/схеме) Name of the board</td>
<td class="header">Photo defect</td>
<td class="header">Panel#  (photo)</td>
<td class="header">Доп. фото/Add photo</td>
</tr>';

if ($_GET['date1'] != '' && $_GET['date2'] != '') {
$where_date = 'and DATE(app_date) BETWEEN \''.$_GET['date1'].'\' AND \''.$_GET['date2'].'\'';
}

$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `repair_type_id` = 4 and `cat_id` IN(48,52,53,54,55,56,57,163,164,165,166,167,168,169) and `deleted` = 0 '.$where_user.' '.$where_date.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\');');
//echo  'SELECT * FROM `repairs` WHERE `repair_type_id` = 4 and `cat_id` IN(48,52,53,54,55,56,57,163,164,165,166,167,168,169) and `deleted` = 0 '.$where_user.' '.$where_date.' and (`status_admin` = \'Подтвержден\' OR `status_admin` = \'Выдан\');';

 //$xls->getActiveSheet()->insertNewRowBefore(8, mysqli_num_rows($sql)-1);



      while ($row = mysqli_fetch_array($sql)) {

      $count = mysqli_fetch_array(mysqli_query($db, 'SELECT `id` FROM `repairs` WHERE `serial` = \''.mysqli_real_escape_string($db, $row['serial']).'\' order by id asc limit 1;'))['id'];

        if ($row['anrp_number'] == '' && $row['anrp_use'] == 0) {

        $content = $row;
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['issue'] = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `issues` WHERE `id` = \''.mysqli_real_escape_string($db, $content['disease']).'\';'));
        $serialInfo = models\Serials::getSerial($content['serial'], $content['model_id']);
        $provider = $serialInfo['provider'];

      if ($_GET['plant'] == '' || $provider == $_GET['plant']) {

$repair_list = @mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs_work` WHERE `repair_id` = \''.mysqli_real_escape_string($db, $content['id']).'\';'));



/*$sheet->setCellValue("B$glob_id", $provider['name']);
$sheet->setCellValue("C$glob_id", $content['model']['name']);
$sheet->setCellValue("D$glob_id", $content['serial']);
$sheet->setCellValue("E$glob_id", $provider['order_id']);
$sheet->setCellValue("F$glob_id", $content['bugs']);
$sheet->setCellValue("G$glob_id", $content['issue']['name']);
$sheet->setCellValue("H$glob_id", $repair_list['name']);
$sheet->setCellValue("I$glob_id", $repair_list['position']);     */

$bug = get_last_photo($content['id'], 3);
$shildik = get_last_photo($content['id'], 2);

if ($bug) {
/*$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setPath('/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
$objDrawing->setCoordinates("J$glob_id");
$objDrawing->setWorksheet($xls->getActiveSheet());
$objDrawing->setResizeProportional(false);
$objDrawing->setHeight(20);
$objDrawing->setWidth(130);*/
//$sheet->setCellValue("J$glob_id", '/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
}

if ($shildik) {
/*$objDrawing2 = new PHPExcel_Worksheet_Drawing();
$objDrawing2->setPath('/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $shildik));
$objDrawing2->setCoordinates("K$glob_id");
$objDrawing2->setWorksheet($xls->getActiveSheet());
$objDrawing2->setResizeProportional(false);
$objDrawing2->setHeight(20);
$objDrawing2->setWidth(110);   */
//$sheet->setCellValue("K$glob_id", '/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $bug));
}




$sql2 = mysqli_query($db, 'SELECT * FROM `photos` where `repair_id` = '.$content['id']);
      if (mysqli_num_rows($sql2) != false) {
      while ($row2 = mysqli_fetch_array($sql2)) {

      $add_photo = $row2['url'];

/*$objDrawing3 = new PHPExcel_Worksheet_Drawing();
$objDrawing3->setPath('/var/www/service.harper.ru/data/www/service.harper.ru'.str_replace('http://service.harper.ru', '', $row2['url']));
$objDrawing3->setCoordinates("L$glob_id");
$objDrawing3->setWorksheet($xls->getActiveSheet());
$objDrawing3->setResizeProportional(false);
$objDrawing3->setHeight(20);
$objDrawing3->setWidth(110); */

      }
      }

      $serialInfo = models\Serials::getSerial($content['serial'], $content['model_id']);

$body .= '<tr>
<td class="pp" style="text-align:center"></td>
<td class="col1" style="position:relative;"><span style="position:absolute;left:3px;top:3px;color:red;cursor:pointer" class="delete_row no-print"><input type="checkbox"></span>'.$provider.'</td>
<td class="col2">'.$content['model']['name'].'</td>
<td class="col3">'.$content['serial'].'</td>
<td class="col4" style="text-align:center">'.$serialInfo['order'].'</td>
<td class="col5">'.$content['bugs'].'</td>
<td class="col6">'.$content['issue']['name'].'</td>
<td class="col7">'.$repair_list['name'].'</td>
<td class="col8">'.$repair_list['position'].'</td>';



if ($bug) {
//$base_bug = base64_encode(file_get_contents("http://service.harper.ru/resizer.php?src=".myUrlEncode($bug)."&h=200&w=200&zc=4&q=70"));
//data:image/jpg;base64,'.$base_bug.'
$body .= '<td style="height:200px;width:200px;" class="image"><a target="_blank" href="'.$bug.'"><img  src="http://crm.r97.ru/resizer.php?src='.$bug.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td style="height:200px;width:200px;" class="image" style="text-align:center"><input data-id="'.$content['id'].'" data-type="3" style="max-width:120px;" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

if ($shildik) {
$body .= '<td style="height:200px;width:200px;" class="image"><a target="_blank" href="'.$shildik.'"><img src="http://crm.r97.ru/resizer.php?src='.$shildik.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td style="height:200px;width:200px;" class="image" style="text-align:center"><input style="max-width:120px;" data-id="'.$content['id'].'" data-type="2" type="file" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

if ($add_photo) {
$body .= '<td style="height:200px;width:200px;" class="image"><a target="_blank" href="'.$add_photo.'"><img src="http://crm.r97.ru/resizer.php?src='.$add_photo.'&h=200&w=200&zc=4&q=70" style="max-width:200px;"></a></td>';
} else {
$body .= '<td style="height:200px;width:200px;" class="image" style="text-align:center"><input style="max-width:120px;" type="file" data-id="'.$content['id'].'" data-type="0" name="sortpic" /><br><a style="color:#000;font-weight:bold;" href="#" class="upload">Загрузить</a></td>';
}

$body .= '</tr>';

unset($content['parts_pics']);
unset($shildik);
unset($bug);
unset($add_photo);
/**/



 $glob_id++;
    }
     }
      }

//   header("Content-type: application/octet-stream");
 //     header("Content-Disposition: attachment; filename=\"report.html\"");
      echo $body.'<tr><td colspan="12" style="height:30px;border:0;"></td></tr><tr><td colspan="12">Акт составлен в 3-х экземплярах/ Report is made in 3 copies</td></tr><tr><td colspan="12" style="height:30px;border:0;"></td></tr><tr><td colspan="12">Signature ______________________________</td></tr>
<style>
.delete_row {
 display:none;
}
</style>
</table>


</body></html>';



       /* header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="report_'.date('d.m.Y').'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, GET-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);  */

exit;

?>