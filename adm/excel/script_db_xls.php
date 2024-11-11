<?php
# Подключаем  конфиг:
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/configuration.php');

$content_return = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `returns` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\'  LIMIT 1;'));
unlink('files.zip');
clearDir();
function model($id) {
  global $db;
$sql = mysqli_query($db, 'SELECT * FROM `models` where `id` = '.$id);
      while ($row = mysqli_fetch_array($sql)) {
      $content = $row;

      }
    return $content;
}


    require_once 'vendor/autoload.php';


    function writeToFile($array)
    {
        $lfcr = chr(10);
        $filename = file_get_contents('act2.txt');
        $new_file = 'files/' . $array['12'] . '.xlsx';
        copy('file.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();


        $sheet->setCellValue('B1', 'Акт технического заключения №'.file_get_contents('act2.txt'));
        $sheet->setCellValue('C3', $array['0']);
        $sheet->setCellValue('C5', $array['1']);
        $sheet->setCellValue('C7', $array['2']);
        $sheet->setCellValue('C9', $array['11']);
        $sheet->setCellValue('C11', $array['3']);
        $sheet->setCellValue('C13', $array['4']);

        $sheet->setCellValue('C15', $array['5']);

        $sheet->setCellValue('C17', $array['6']);
        $xls->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $xls->getActiveSheet()->getStyle('C1:C'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $sheet->setCellValue('C20', $array['7']);
        $sheet->setCellValue('C22', $array['8']);
        $sheet->setCellValue('C24', $array['9']);
        $sheet->setCellValue('C26', $array['10']);
        //$sheet->setCellValue('C28', $array['11']);
        //$sheet->setCellValue('C29', $array['12']);
        $sheet->setCellValue('C27', $array['13']);
        $sheet->setCellValue('C30', $_GET['date']);
        $sheet->setCellValue('C33', date("d.m.Y"));
        $sheet->setCellValue('C36', date("d.m.Y"));

        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');

        $objWriter->save($new_file);
        file_put_contents('act2.txt', file_get_contents('act2.txt')+1);
    }


    function getTypeAndModel($str)
    {
        preg_match('/(.*)(Harper.*)/u', $str, $out);
        return $out;
    }

    function createZip()
    {
        $ZipFileName = 'files.zip';
        unlink($ZipFileName);

        $pathdir='files/';
        $nameArhive = $ZipFileName;
        $zip = new ZipArchive;

        if ($zip -> open($nameArhive, ZipArchive::CREATE) === TRUE){
            $dir = opendir($pathdir);

            while( $file = readdir($dir)){

                if (is_file($pathdir.$file)){
                    $zip -> addFile($pathdir.$file, $file);
                }

            }

            $zip -> close();

        }

    }

    function clearDir()
    {
        if (file_exists('./files')) {
            foreach (glob('./files/*') as $file) {
                unlink($file);
            }
        }
    }

    function file_download($filename) {

        if (file_exists($filename)) {

            header('Location: ' . $filename);

        } else {

            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            header('Status: 404 Not Found');
        }

        exit;
    }

    function getSerialNumber($serial)
    {
        if($serial == 'NULL' || !isset($serial)) {
            return '—';
        }
        return $serial;
    }

    function checkFileType($file_path)
    {
        $valid = false;

            $reader = PHPExcel_IOFactory::createReader('Excel2007');
            if ($reader->canRead($file_path)) {
                $valid = true;
            }


        if(!$valid) {
            header("Location: {$_SERVER['HTTP_REFERER']}");
            exit;
        }
    }

$array_status = array(1 => 'Гарантийный', 5 =>'Условно-гарантийный', 6 => 'Платный', 7 => 'Предпродажный');
$array_repair_final = array(1 => 'Дефект не обнаружен', 2 => 'Подтвердилось', 3 => 'Отказано в гарантии');
if ($content_return) {
$act = $_GET['act'];
$i = 0;
$sql = mysqli_query($db, 'SELECT * FROM `repairs` WHERE `return_id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' and `deleted` = 0');
if (mysqli_num_rows($sql) > 0) {
while ($row = mysqli_fetch_array($sql)) {

        if ($row['repair_final'] == 1 || $row['repair_final'] == 3) {
        $model = model($row['model_id']);
        $cat = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `cats` WHERE `id` = \''.mysqli_real_escape_string($db, $model['cat']).'\' LIMIT 1;'));
        $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content_return['client_id']).'\' LIMIT 1;'));

       /* writeToFile(array(
        $cat['name'],
        $model['name'],
        $row['serial'],
        $row['rsc'],
        $row['date'],
        $array_status[$row['status_id']],
        $row['client'].', '.$row['address'].', '.$row['phone'],
        str_replace('|', ', ', $row['complex']),
        str_replace('|', ', ', $row['visual']),
        $row['bugs'],
        $array_repair_final[$row['repair_final']],
        $client['name'],
        $row['id'],
        $row['repair_final_cancel']
        ));

        $zip = 1; */

        } else {

        $model = model($row['model_id']);
        $complex = str_replace('|', ', ', $row['complex']);
        $products[$i]['id'] = $i+1;
        $products[$i]['number'] = $row['rsc'];
        $products[$i]['name'] = $model['name'];
        $products[$i]['sn'] = ($row['serial'] != 'NULL') ? $row['serial'] : '-';
        $products[$i]['problem'] = $row['bugs'];
        $products[$i]['date'] = $row['date'];
        $products[$i]['stack'] = $complex;
        $i++;

       }

}
}

date_default_timezone_set('Europe/Moscow');
header('Content-Type: text/html; charset=UTF-8');
//file_download('files.zip');
setlocale(LC_ALL, 'ru_RU.UTF-8');
function true_russian_date_forms($the_date = '') {
if ( substr_count($the_date , '---') > 0 ) {
return str_replace('---', '', $the_date);
}
$replacements = array(
"Январь" => "января",
"Февраль" => "февраля",
"Март" => "марта",
"Апрель" => "апреля",
"Май" => "мая",
"Июнь" => "июня",
"Июль" => "июля",
"Август" => "августа",
"Сентябрь" => "сентября",
"Октябрь" => "октября",
"Ноябрь" => "ноября",
"Декабрь" => "декабря"
);
return strtr($the_date, $replacements);
}


$client_ret = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content_return['client_id']).'\' LIMIT 1;'));
if ($_GET['contr']) {
$contr = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `contrahens` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['contr']).'\' LIMIT 1;'));
} else {
$contr['name'] = 'ООО «ОПТИМА-М»';
$contr['desc'] = 'Генеральный директор';
}
$last_date = mysqli_fetch_array(mysqli_query($db, 'SELECT DATE_FORMAT(STR_TO_DATE(app_date, "%Y.%m.%d"), "%d.%m.%Y") FROM `repairs` WHERE `return_id` = '.$_GET['id'].' AND `approve_date` != "0000-00-00" ORDER BY `approve_date` LIMIT 1;'));

/*ТУТ*/

        if (file_exists('files')) {
            foreach (glob('files/*') as $file) {
                unlink($file);
            }
        }

        $path = $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/content/templates/excel/returns/ats-p.xlsx';
        $new_file = 'files/1.xlsx';
        copy($path, $new_file);
   
        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue('F2', $contr['name']);
        $sheet->setCellValue('F4', $contr['desc']);
        $sheet->setCellValue('D8', 'АКТ № '.$act);
        $sheet->setCellValue('B10', $last_date['0']);
        $sheet->setCellValue('B11', $client_ret['name'].', '.$client_ret['address']);
        $sheet->setCellValue('B20', $i.' шт.');
        $sheet->setCellValue('D24', '«___»____________ '.date('Y').' года');


        $num = $i;

        if ($num > 0) {


        if ($num > 1) {
           $xls->getActiveSheet()->insertNewRowBefore(19,$num-1);
        }



        $id = 18;
        $id_num = 1;

 foreach ($products as $product) {

        $sheet->setCellValue('A'.$id, $product['id']);
        $sheet->setCellValue('B'.$id, (($product['number']) ? $product['number'] : 'н/д'));
        $sheet->setCellValue('C'.$id, $product['name']);
        $sheet->setCellValue('D'.$id, (($product['sn']) ? $product['sn'] : 'н/д'));
        $sheet->setCellValue('E'.$id, (($product['date']) ? $product['date'] : 'н/д'));
        $sheet->setCellValue('F'.$id, (($product['problem']) ? $product['problem'] : 'н/д'));
        $sheet->setCellValue('G'.$id, preg_replace("/,([^\s])/", ", $1", $product['stack']));
        //$sheet->setCellValue('H'.$id, implode(', ', array_filter(explode('|', $row['visual']))).' '.$row['visual_comment']);
       // $sheet->setCellValue('I'.$id, $row['client']);
        //$sheet->setCellValue('J'.$id, 1);

        $id++;
        $id_num++;


            }



        }else{
            $sheet->setCellValue('A18', '');
            $sheet->setCellValue('B18', '');
            $sheet->setCellValue('C18', '');
            $sheet->setCellValue('D18', '');
            $sheet->setCellValue('E18', '');
            $sheet->setCellValue('F18', '');
            $sheet->setCellValue('G18', '');
        }


        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="act_'.$_GET['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();




}

?>

