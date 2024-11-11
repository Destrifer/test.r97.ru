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

 foreach ($products as $product) {
            $list .= '<tr style="">
                <td width=46 valign=center style="width:35.2000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:1.0000pt solid windowtext;mso-border-left-alt:0.5000pt solid windowtext;border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"><p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['id'].'</span></p> </div>
                </td>
                <td width=50 valign=center style="width:37.9500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"> <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.(($product['number']) ? $product['number'] : 'н/д').'</span></p>  </div>                </td>
                <td width=91 valign=center style="width:68.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <div class="avoid"><p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['name'].'</span></p> </div>
                 </td>
                <td width=98 valign=center style="width:74.1500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <div class="avoid"><p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.(($product['sn']) ? $product['sn'] : 'н/д').'</span></p> </div>
                                    </td>
                <td width=61 valign=center style="width:46.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"> <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.(($product['date']) ? $product['date'] : 'н/д').'</span></p>  </div>
                                    </td>
                <td width=126 valign=center style="width:94.5000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;vertical-align: middle;">
                    <div class="avoid"><p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;    vertical-align: middle;">'.(($product['problem']) ? $product['problem'] : 'н/д').'</span></p>  </div>
                                    </td>
                <td width=204 valign=center style="width:100.3000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"> <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.preg_replace("/,([^\s])/", ", $1", $product['stack']).'</span></p> </div>
                                    </td>
            </tr>';
            }


$client_ret = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content_return['client_id']).'\' LIMIT 1;'));
if ($_GET['contr']) {
$contr = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `contrahens` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['contr']).'\' LIMIT 1;'));
} else {
$contr['name'] = 'ООО «ОПТИМА-М»';
$contr['desc'] = 'Генеральный директор';
}
$last_date = mysqli_fetch_array(mysqli_query($db, 'SELECT DATE_FORMAT(STR_TO_DATE(app_date, "%Y.%m.%d"), "%d.%m.%Y") FROM `repairs` WHERE `return_id` = '.$_GET['id'].' AND `deleted` = 0 ORDER BY DATE(`app_date`) LIMIT 1;'));

$content = '
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">

<head>
    <meta http-equiv=Content-Type content="text/html; charset=UTF-8">
    <title>Механические разрушения</title>
    <style>
        @font-face {
            font-family: "Times New Roman";
        }


        @font-face {
            font-family: "Wingdings";
        }

        @font-face {
            font-family: "Tahoma";
        }

        @font-face {
            font-family: "Calibri";
        }

        p.MsoNormal {
            mso-style-name: Normal;
            mso-style-parent: "";
            margin: 0pt;
            margin-bottom: .0001pt;
            font-family: \'Times New Roman\';
            font-size: 20.0000pt;
        }

        span.10 {
            font-family: \'Times New Roman\';
        }

        span.15 {
            font-family: \'Times New Roman\';
            font-size: 20.0000pt;
        }

        span.16 {
            font-family: \'Times New Roman\';
            color: rgb(0, 0, 255);
            text-decoration: underline;
            text-underline: single;
        }

        span.17 {
            font-family: \'Times New Roman\';
            color: rgb(128, 0, 128);
            text-decoration: underline;
            text-underline: single;
        }

        span.18 {
            font-family: \'Times New Roman\';
            font-size: 20.0000pt;
        }

        span.19 {
            font-family: Tahoma;
            font-size: 15.0000pt;
        }

        p.MsoAcetate {
            mso-style-name: "Balloon Text";
            margin: 0pt;
            margin-bottom: .0001pt;
            font-family: Tahoma;
            mso-fareast-font-family: \'Times New Roman\';
            font-size: 15.0000pt;
        }

        p.NewStyle21 {
            mso-style-name: xl67;
            margin-top: 5.0000pt;
            margin-bottom: 5.0000pt;
            mso-margin-top-alt: auto;
            mso-margin-bottom-alt: auto;
            border-top: 1.0000pt solid windowtext;
            mso-border-top-alt: 0.5000pt solid windowtext;
            border-right: 1.0000pt solid windowtext;
            mso-border-right-alt: 0.5000pt solid windowtext;
            border-bottom: 1.0000pt solid windowtext;
            mso-border-bottom-alt: 0.5000pt solid windowtext;
            border-left: 1.0000pt solid windowtext;
            mso-border-left-alt: 0.5000pt solid windowtext;
            padding: 0pt 0pt 0pt 0pt;
            font-family: \'Times New Roman\';
            font-size: 15.0000pt;
        }

        p.MsoFooter {
            mso-style-name: Footer;
            margin: 0pt;
            margin-bottom: .0001pt;
            font-family: \'Times New Roman\';
            font-size: 20.0000pt;
        }

        p.NewStyle23 {
            mso-style-name: xl65;
            margin-top: 5.0000pt;
            margin-bottom: 5.0000pt;
            mso-margin-top-alt: auto;
            mso-margin-bottom-alt: auto;
            text-align: center;
            vertical-align: middle;
            font-family: \'Times New Roman\';
            font-size: 20.0000pt;
        }

        p.MsoHeader {
            mso-style-name: Header;
            margin: 0pt;
            margin-bottom: .0001pt;
            font-family: \'Times New Roman\';
            font-size: 20.0000pt;
        }

        p.NewStyle25 {
            mso-style-name: xl66;
            margin-top: 5.0000pt;
            margin-bottom: 5.0000pt;
            mso-margin-top-alt: auto;
            mso-margin-bottom-alt: auto;
            border-top: 1.0000pt solid windowtext;
            mso-border-top-alt: 0.5000pt solid windowtext;
            border-right: 1.0000pt solid windowtext;
            mso-border-right-alt: 0.5000pt solid windowtext;
            border-bottom: 1.0000pt solid windowtext;
            mso-border-bottom-alt: 0.5000pt solid windowtext;
            border-left: 1.0000pt solid windowtext;
            mso-border-left-alt: 0.5000pt solid windowtext;
            padding: 0pt 0pt 0pt 0pt;
            text-align: center;
            vertical-align: middle;
            font-family: \'Times New Roman\';
            font-size: 15.0000pt;
        }

        span.msoIns {
            mso-style-type: export-only;
            mso-style-name: "";
            text-decoration: underline;
            text-underline: single;
            color: blue;
        }

        span.msoDel {
            mso-style-type: export-only;
            mso-style-name: "";
            text-decoration: line-through;
            color: red;
        }

        table.MsoNormalTable {
            mso-style-name: "Table Normal";
            mso-style-parent: "";
            mso-style-noshow: yes;
            mso-tstyle-rowband-size: 0;
            mso-tstyle-colband-size: 0;
            mso-padding-alt: 0.0000pt 5.4000pt 0.0000pt 5.4000pt;
            mso-para-margin: 0pt;
            mso-para-margin-bottom: .0001pt;
            mso-pagination: widow-orphan;
            font-family: \'Times New Roman\';
            font-size: 10.0000pt;
            mso-ansi-language: #0400;
            mso-fareast-language: #0400;
            mso-bidi-language: #0400;
        }

        @page {
            mso-page-border-surround-header: no;
            mso-page-border-surround-footer: no;
        }

        @page Section0 {
            margin-top: 35.9500pt;
            margin-bottom: 21.3000pt;
            margin-left: 56.7000pt;
            margin-right: 28.3000pt;
            size: 595.3000pt 841.9000pt;
            layout-grid: 115.0000pt;
        }

        div.Section0 {
            page: Section0;
        }
    </style>
    <style type="text/css">
  .avoid {
     !important;
    margin: 45px 0 45px 0;  /* to keep the page break from cutting too close to the text in the div */
  }
    .MsoNormalTable tr {
  ;
  }
  table, tr, td, th, tbody, thead, tfoot {
     !important;
}
</style>

</head>

<body style="tab-interval:35pt;">
  <!--StartFragment-->
  <div class="Section0" style="layout-grid:115.0000pt;position:relative;">
    <p class=MsoNormal style="text-align:right;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;font-weight:bold;">'.$contr['name'].'</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal align=center style="text-align:right;">
      <b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" >Утверждаю</span></b>
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" >
          <o:p></o:p>
        </span>
      </b>
    </p>
    <p class=MsoNormal style="text-align:right;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">'.$contr['desc'].'</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal style="text-align:right;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">_______________________</span><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" ></span></u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"></span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal align=center style="text-align:right;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">(подпись руководителя)</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">
        <o:p> </o:p>
      </span>
    </p>
    <p class=MsoNormal align=center style="text-align:center;">
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" >
          <o:p> </o:p>
        </span>
      </b>
    </p>
    <p class=MsoNormal>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">
        <o:p> </o:p>
      </span>
    </p>
    <p class=MsoNormal align=center style="text-align:center;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">АКТ № </span><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:16.0000pt;" >'.$act.'</span></u>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal align=center style="text-align:center;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">Технического состояния товара</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" ><strong>От: '.$last_date['0'].' г.</strong></span></b></p>
    <p class=MsoNormal>
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >
          <o:p> </o:p>
        </span>
      </b>
    </p>
    <p class=MsoNormal>
      <b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" >Заказчик</span></b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">: </span><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:16.0000pt;" >'.$client_ret['name'].', '.$client_ret['address'].'</span></u>
      <u>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" >
          <o:p></o:p>
        </span>
      </u>
    </p>
    <p class=MsoNormal>
      <u><span style=" !important;mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" > </span></u><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" >Список товара</span></b>
      <b>
        <span style=" !important;mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >
          <o:p></o:p>
        </span>
      </b>
    </p>
    <br><br>
    <table class=MsoNormalTable style="position:relative; !important;border-collapse:collapse;width:100%;mso-table-layout-alt:fixed;mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
      <tr class="avoid" style="height:15.0000pt;">
        <td class="avoid" width=46 valign=center style="width:35.2000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:1.0000pt solid windowtext;mso-border-left-alt:0.5000pt solid windowtext;border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
          <p class=MsoNormal align=center style="text-align:center;">
            <span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">№ПП</span>
            <span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">
              <o:p></o:p>
            </span>
          </p>
        </td>
        <td class="avoid" width=50 valign=center style="width:37.9500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
          <p class=MsoNormal align=center style="text-align:center;">
            <span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">Номер</span>
            <span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">
              <o:p></o:p>
            </span>
          </p>
        </td>
        <td class="avoid" width=91 valign=center style="width:68.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
          <p class=MsoNormal align=center style="text-align:center;">
            <span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">Наименование</span>
            <span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">
              <o:p></o:p>
            </span>
          </p>
        </td>
        <td class="avoid" width=98 valign=center style="width:74.1500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
          <p class=MsoNormal align=center style="text-align:center;">
            <span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">С/н</span>
            <span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">
              <o:p></o:p>
            </span>
          </p>
        </td>
        <td class="avoid" width=61 valign=center style="width:46.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
          <p class=MsoNormal align=center style="text-align:center;">
            <span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">Дата продажи</span>
            <span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">
              <o:p></o:p>
            </span>
          </p>
        </td>
        <td class="avoid" width=126 valign=center style="width:94.5000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
          <p class=MsoNormal align=center style="text-align:center;">
            <span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">Дефект</span>
            <span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">
              <o:p></o:p>
            </span>
          </p>
        </td>
        <td class="avoid" width=204 valign=center style="width:100.3000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
          <p class=MsoNormal align=center style="text-align:center;">
            <span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">Комплектность</span>
            <span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">
              <o:p></o:p>
            </span>
          </p>
        </td>
      </tr>
      '.$list.'
    </table>
    <p class=MsoNormal>
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >
          <o:p> </o:p>
        </span>
      </b>
    </p>
    <p class=MsoNormal>
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >
          <o:p> </o:p>
        </span>
      </b>
    </p>
    <br>
    <p class=MsoNormal>
      <b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" >Итого: '.$i.' шт.</span></b>
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >
          <o:p></o:p>
        </span>
      </b>
    </p>
    <p class=MsoNormal>
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >
          <o:p> </o:p>
        </span>
      </b>
    </p>
    <br> <br>
    <p class=MsoNormal align=center style="text-align:center;">
      <b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >Результаты осмотра и заключение:</span></b>
      <b>
        <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >
          <o:p></o:p>
        </span>
      </b>
    </p>
    <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">По указанной в Акте технике, заявленный дефект подтвержден. Техника неисправна и утратила товарный вид в процессе эксплуатации или заводского дефекта. Следов нарушения условий гарантии, механических повреждений, попадания жидкостей и посторонних предметов не обнаружено.</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">Неисправная техника оставлена на ответственное хранение в представительстве компании.</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">Данная техника подлежит компенсации по закупочной стоимости Поставщиком, как товар ненадлежащего качества.</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p> </o:p>
      </span>
    </p>
    <p class=MsoNormal style="margin-left:35.4000pt;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">Сервис-менеджер</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">______________________</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">.</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <br><br><br>
    <p class=MsoNormal>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p> </o:p>
      </span>
    </p>
    <p class=MsoNormal style="margin-left:35.4000pt;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">С актом ознакомлен: «___»____________ '.date('Y').' года</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal style="margin-left:35.4000pt;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">
        <o:p> </o:p>
      </span>
    </p>
    <p class=MsoNormal style="margin-left:35.4000pt;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">Заказчик (представитель Заказчика) ______________________________</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">____________________</span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">
        <o:p></o:p>
      </span>
    </p>
    <p class=MsoNormal style="margin-left:35.4000pt;">
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">Подпись</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">	</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">Фамилия И.О. </span>
      <span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:12.0000pt;">
        <o:p></o:p>
      </span>
    </p>
  </div>
  <!--EndFragment-->
</body>

</html>';
//echo $content;

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

file_put_contents($_SERVER['DOCUMENT_ROOT'].'/temp2/html/1.html', $content);
$pdf = file_get_contents('https://r97.ru/pdf/test_pdf.php?url=https://crm.r97.ru/temp2/html/1.html', false, stream_context_create($arrContextOptions));
header('Content-Type: application/pdf');
header('Content-Length: '.strlen( $pdf ));
header('Content-disposition: inline; filename="doc2.pdf"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
if ($zip == 1) {
createZip();  
header( "refresh:2;url=files.zip" );
}
echo $pdf;

}

?>

