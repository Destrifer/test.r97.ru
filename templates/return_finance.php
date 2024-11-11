<?php

use models\Tariffs;

$sql = mysqli_query($db, 'SELECT `repair_type_id`,`repair_final`,`cat_id`,`model_id`,`total_price`,`master_user_id` FROM `repairs` where `return_id` = \''.$_GET['return_id'].'\';');
      while ($row = mysqli_fetch_array($sql)) {

              // Стоимость партии возврата:
              /*switch($row['repair_type_id']) {

              case 1:
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `block` FROM `prices_service` where `cat_id` = \''.$row['cat_id'].'\' and `service_id` = 33 ;'))['block'];
                break;
              case 4:
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `anrp` FROM `prices_service` where `cat_id` = \''.$row['cat_id'].'\' and `service_id` = 33 ;'))['anrp'];
                $model_price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              case 5:
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `ato` FROM `prices_service` where `cat_id` = \''.$row['cat_id'].'\' and `service_id` = 33 ;'))['ato'];
                $model_price_ato = @mysqli_fetch_array(mysqli_query($db, 'SELECT `price_usd` FROM `models` where `id` = \''.$row['model_id'].'\' ;'))['price_usd'];
                break;
              case 2:
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `component` FROM `prices_service` where `cat_id` = \''.$row['cat_id'].'\' and `service_id` = 33 ;'))['component'];
                break;
              case 3:
                $price = @mysqli_fetch_array(mysqli_query($db, 'SELECT `acess` FROM `prices_service` where `cat_id` = \''.$row['cat_id'].'\' and `service_id` = 33 ;'))['acess'];
                break;
              }*/
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
              unset($price);

              // Сумма техники списанной:
              $return_usd += $model_price;
              unset($model_price);

              // Сумма техники списанной ато:
              $return_ato_usd += $model_price_ato;
              unset($model_price_ato);

              // Сумма мастерам за работу:
              //$return_master_sum += $row['total_price'];
              $return_master_sum += count_pay_master_funk($row['total_price'], $row['master_user_id']);

      }

$usd = @mysqli_fetch_array(mysqli_query($db, 'SELECT `usd` FROM `returns` where `id` = \''.$_GET['return_id'].'\'  ;'))['usd'];
//$usd = str_replace(',', '.', json_decode(file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js'))->Valute->USD->Value);

function get_problem_price($id, $cat, $service_id) {
  global $db;
  $table = Tariffs::getServiceTariffTable($service_id);
  
$sql = mysqli_query($db, 'SELECT `type` FROM `details_problem` where `id` = \''.$id.'\' ;');
            while ($row = mysqli_fetch_array($sql)) {

              switch($row['type']) {
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
              }



            }

    return $price;
}

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

    var max_fields      = 50; //maximum input boxes allowed
    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $(".add_field_button"); //Add button ID

    var x = 1; //initlal text box count
    var select_new = '';
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
            x++; //text box increment

            $( "select[name='provider[]']" ).each(function() {

              select_new += '<option value="'+$( this ).val()+'">'+$( this ).find('option:selected').text()+'</option>';
            });

            $(wrapper).append('<div class="i"><input style="width: 200px;" type="text" name="serials_first[]" placeholder="Начальный номер"/><input style="width: 200px;" type="text" name="serials_lot[]" placeholder="Размер лота"/><select name="serial_provider[]"><option value="">Выберите поставщика</option>'+select_new+'</select><input style="width: 100px;" type="text" name="order[]" placeholder="Заказ"/><select name="production[]"><option value="">Выберите сборщика</option><option value="Горизонт-союз">Горизонт-союз</option><option value="ТПВ Си Ай Эс">ТПВ Си Ай Эс</option></select> <a href="#" class="remove_field del"></a></div>'); //add input box
            $('select:not(.nomenu)').selectmenu({
            open: function(){
              $(this).selectmenu('menuWidget').css('width', $(this).selectmenu('widget').outerWidth());
            }}).addClass("selected_menu");

            select_new = '';

        }
    });

    $(document).on("change","input[name='serials_first[]']", function(){ //user click on remove text
        var first = $(this).val();
        //$(this).val();
    });

    $(document).on("change","input[name='serials_lot[]']", function(){
        var lot = $(this).val();
        var first = $(this).parent().find("input[name='serials_first[]']").val();

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
    .adm-form .item-feature .i:after {
    display:none;
    }
</style>
</head>

<body>

<div class="viewport-wrapper" style="max-width: 1200px;margin: 0 auto;">


<div class="wrapper">


           <br>
           <h2 style="text-align:center;">Финансовая статистика партии #<?=$_GET['return_id'];?></h2>
  <form id="send" method="POST">
   <div class="adm-form" style="padding-top:0;">

                  <div class="item">
              <div class="level">Стоимость партии возврата:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=$return_sum;?>" style="cursor:pointer"  readonly/> ₽
              </div>
            </div>

                  <div class="item">
              <div class="level">Сумма списанной техники:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=$return_usd;?>" style="cursor:pointer" readonly /> $
              </div>
            </div>
                  <div class="item">
              <div class="level">Сумма возвращенной техники клиенту:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=$return_ato_usd;?>" style="cursor:pointer" readonly/> $
              </div>
            </div>


                  <div class="item">
              <div class="level">Сумма оплаченная мастерам за работу:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=$return_master_sum;?>" style="cursor:pointer" readonly/> ₽
              </div>
            </div>

                   <div class="item">
              <div class="level">Экономический смысл для сервиса:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=($return_sum - $return_master_sum);?>" style="<?=(($return_sum - $return_master_sum) < 0) ? 'color:red;' : 'color:green;';?>cursor:pointer" readonly /> ₽
              </div>
            </div>

                    <div class="item">
              <div class="level">Экономический смысл для клиента:</div>
              <div class="value">
                <input type="text" name="model_id" value="<?=($return_ato_usd*$usd-$return_sum);?>"  style="<?=(($return_ato_usd*$usd-$return_sum) < 0) ? 'color:red;' : 'color:green;';?>cursor:pointer" readonly /> ₽
              </div>
            </div>

            <p style="margin-top:15px;">Курс: <?=$usd;?></p>

        </div>

      </form>




        </div>
  </div>
</body>
</html>