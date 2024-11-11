<?php

    require_once 'vendor/autoload.php';


    if(isset($_FILES['excelFile'])) {
        $file = $_FILES['excelFile']['tmp_name'];

        checkFileType($file);

        $xls = PHPExcel_IOFactory::load($file);
        $xls->setActiveSheetIndex(0);

        $sheet = $xls->getActiveSheet();

        $array = $sheet->toArray();


        foreach($array as $item) {
            if($item[17] == 'ДЕФЕКТ НЕ ОБНАРУЖЕН' || $item[17] == 'ОТКАЗАНО В ГАРАНТИИ') {

                writeToFile($item);

            } else {

                $items[] = $item;

            }

        }


        createZip();

        clearDir();



    }

    function writeToFile($array)
    {
        $lfcr = chr(10);
        $filename = file_get_contents('act2.txt');
        $new_file = 'files/' . $filename . '.xlsx';
        copy('file.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $typeAndModel = getTypeAndModel($array[2]);
        $number = getSerialNumber($array[3]);

        $sheet->setCellValue('B1', 'Акт технического заключения №'.file_get_contents('act2.txt'));
        $sheet->setCellValue('C3', $typeAndModel[1]);
        $sheet->setCellValue('C5', $typeAndModel[2]);
        $sheet->setCellValue('C7', $number);
        $sheet->setCellValue('C11', $array[1]);
        $sheet->setCellValue('C13', $array[6]);

        if ($array[6] == '') {
        $sheet->setCellValue('C15', 'ПРЕДПРОДАЖНЫЙ');
        } else {
        $sheet->setCellValue('C15', 'ГАРАНТИЙНЫЙ');
        }

        $sheet->setCellValue('C17', $array[14]);
        $sheet->setCellValue('C18', $array[15] . $lfcr . $array[16]);
        $xls->getActiveSheet()->getColumnDimension('C')->setWidth(50);
        $xls->getActiveSheet()->getStyle('C1:C'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $sheet->setCellValue('C20', $array[11]);
        $sheet->setCellValue('C22', $array[12]);
        $sheet->setCellValue('C24', $array[9]);
        $sheet->setCellValue('C26', $array[17]);
        $sheet->setCellValue('C27', $array[18]);

        $sheet->setCellValue('C30', $_POST['date']);
        $sheet->setCellValue('C33', date("d/m/Y"));
        $sheet->setCellValue('C36', date("d/m/Y"));

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


if ($items) {
$act = $_POST['act'];
$i = 0;
foreach ($items as $row) {
        if ($i != 0) {
        $products[$i]['id'] = $i;
        $products[$i]['number'] = $row['1'];
        $products[$i]['name'] = $row['2'];
        $products[$i]['sn'] = ($row['3'] != 'NULL') ? $row['3'] : '-';
        $products[$i]['problem'] = $row['9'];
        $products[$i]['date'] = trim($row['6']);
        $products[$i]['stack'] = $row['11'];
        }
        $i++;

}
$i = $i-1;
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
            $list .= '<tr style="height:15.0000pt;page-break-inside: avoid;">
                <td width=46 valign=center style="page-break-inside: avoid;width:35.2000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:1.0000pt solid windowtext;mso-border-left-alt:0.5000pt solid windowtext;border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"> <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['id'].'</span></p> </div>
                </td>
                <td width=50 valign=center style="page-break-inside: avoid;width:37.9500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"> <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['number'].'</span></p>  </div>                </td>
                <td width=91 valign=center style="page-break-inside: avoid;width:68.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <div class="avoid"><p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['name'].'</span></p> </div>
                 </td>
                <td width=98 valign=center style="page-break-inside: avoid;width:74.1500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <div class="avoid"><p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['sn'].'</span></p> </div>
                                    </td>
                <td width=61 valign=center style="page-break-inside: avoid;width:46.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"> <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['date'].'</span></p>  </div>
                                    </td>
                <td width=126 valign=center style="page-break-inside: avoid;width:94.5000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <div class="avoid"><p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.$product['problem'].'</span></p>  </div>
                                    </td>
                <td width=204 valign=center style="page-break-inside: avoid;width:100.3000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                   <div class="avoid"> <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">'.preg_replace("/,([^\s])/", ", $1", $product['stack']).'</span></p> </div>
                                    </td>
            </tr>';
            }



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
    page-break-inside: avoid !important;
    margin: 4px 0 4px 0;  /* to keep the page break from cutting too close to the text in the div */
  }
    .MsoNormalTable tr {
  page-break-inside: avoid;
  }
  table, tr, td, th, tbody, thead, tfoot {
    page-break-inside: avoid !important;
}
</style>

</head>

<body style="tab-interval:35pt;">
    <!--StartFragment-->
    <div class="Section0" style="layout-grid:115.0000pt;">
        <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >&#1059;&#1090;&#1074;&#1077;&#1088;&#1078;&#1076;&#1072;&#1102;</span></b><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p></o:p></span></b></p>
        <p class=MsoNormal style="text-align:right;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1043;&#1077;&#1085;&#1077;&#1088;&#1072;&#1083;&#1100;&#1085;&#1099;&#1081; &#1076;&#1080;&#1088;&#1077;&#1082;&#1090;&#1086;&#1088;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">_______________________</span><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" ></span></u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"></span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">(&#1087;&#1086;&#1076;&#1087;&#1080;&#1089;&#1100; &#1088;&#1091;&#1082;&#1086;&#1074;&#1086;&#1076;&#1080;&#1090;&#1077;&#1083;&#1103;)</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal align=center style="text-align:center;"><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" >'.true_russian_date_forms(strftime("«%d» %B %Y", time())).' г.</span></u><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" ><o:p></o:p></span></u></p>
        <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" ><o:p> </o:p></span></b></p>
        <p class=MsoNormal style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" >&#1054;&#1054;&#1054; &#171;&#1054;&#1055;&#1058;&#1048;&#1052;&#1040;-&#1052;&#187; </span></b><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:16.0000pt;" ><o:p></o:p></span></b></p>
        <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">&#1040;&#1050;&#1058; &#8470; </span><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:16.0000pt;" >'.$act.'</span></u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;">&#1058;&#1077;&#1093;&#1085;&#1080;&#1095;&#1077;&#1089;&#1082;&#1086;&#1075;&#1086; &#1089;&#1086;&#1089;&#1090;&#1086;&#1103;&#1085;&#1080;&#1103; &#1086;&#1073;&#1086;&#1088;&#1091;&#1076;&#1086;&#1074;&#1072;&#1085;&#1080;&#1103;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:16.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><strong>От: '.true_russian_date_forms(strftime("<u>«%d»</u> <u>%B</u> <u>%Y</u>", time())).' г.</strong></span></b></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p> </o:p></span></b></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >&#1047;&#1072;&#1082;&#1072;&#1079;&#1095;&#1080;&#1082;</span></b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">: </span><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" >&#1054;&#1054;&#1054; &#171;&#1052;.&#1042;&#1080;&#1076;&#1077;&#1086; &#1052;&#1077;&#1085;&#1077;&#1076;&#1078;&#1077;&#1084;&#1077;&#1085;&#1090;&#187;, &#1075;.&#1052;&#1086;&#1089;&#1082;&#1074;&#1072;</span></u><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" ><o:p></o:p></span></u></p>
        <p class=MsoNormal><u><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';text-decoration:underline;text-underline:single;font-size:20.0000pt;" >                                                    </span></u><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >&#1057;&#1087;&#1080;&#1089;&#1086;&#1082; &#1086;&#1073;&#1086;&#1088;&#1091;&#1076;&#1086;&#1074;&#1072;&#1085;&#1080;&#1103;</span></b><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p></o:p></span></b></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p> </o:p></span></b></p>
                <br><br>
        <table class=MsoNormalTable style="border-collapse:collapse;width:100%;mso-table-layout-alt:fixed;mso-padding-alt:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;">
            <tr style="height:15.0000pt;">
                <td width=46 valign=center style="width:35.2000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:1.0000pt solid windowtext;mso-border-left-alt:0.5000pt solid windowtext;border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">&#8470;&#1055;&#1055;</span><span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=50 valign=center style="width:37.9500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">&#1053;&#1086;&#1084;&#1077;&#1088;</span><span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=91 valign=center style="width:68.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">&#1053;&#1072;&#1080;&#1084;&#1077;&#1085;&#1086;&#1074;&#1072;&#1085;&#1080;&#1077;</span><span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=98 valign=center style="width:74.1500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">&#1057;/&#1085;</span><span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=61 valign=center style="width:46.3500pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">&#1044;&#1072;&#1090;&#1072; &#1087;&#1088;&#1086;&#1076;&#1072;&#1078;&#1080;</span><span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=126 valign=center style="width:94.5000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">&#1044;&#1077;&#1092;&#1077;&#1082;&#1090;</span><span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;"><o:p></o:p></span></p>
                </td>
                <td width=204 valign=center style="width:100.3000pt;padding:0.0000pt 5.4000pt 0.0000pt 5.4000pt ;border-left:31.8750pt none rgb(255,255,255);mso-border-left-alt:31.8750pt none rgb(255,255,255);border-right:1.0000pt solid windowtext;mso-border-right-alt:0.5000pt solid windowtext;border-top:1.0000pt solid windowtext;mso-border-top-alt:0.5000pt solid windowtext;border-bottom:1.0000pt solid windowtext;mso-border-bottom-alt:0.5000pt solid windowtext;">
                    <p class=MsoNormal align=center style="text-align:center;"><span style="mso-spacerun:\'yes\';font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;">&#1050;&#1086;&#1084;&#1087;&#1083;&#1077;&#1082;&#1090;&#1085;&#1086;&#1089;&#1090;&#1100;</span><span style="font-family:Calibri;mso-fareast-font-family:\'Times New Roman\';mso-bidi-font-family:\'Times New Roman\';color:rgb(0,0,0);font-size:15.0000pt;"><o:p></o:p></span></p>
                </td>
            </tr>
            '.$list.'
        </table>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p> </o:p></span></b></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p> </o:p></span></b></p>
        <br>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >&#1048;&#1090;&#1086;&#1075;&#1086;: '.$i.' &#1096;&#1090;&#1091;&#1082;.</span></b><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p></o:p></span></b></p>
        <p class=MsoNormal><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p> </o:p></span></b></p>
         <br>  <br>
        <p class=MsoNormal align=center style="text-align:center;"><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" >&#1056;&#1077;&#1079;&#1091;&#1083;&#1100;&#1090;&#1072;&#1090;&#1099; &#1086;&#1089;&#1084;&#1086;&#1090;&#1088;&#1072; &#1080; &#1079;&#1072;&#1082;&#1083;&#1102;&#1095;&#1077;&#1085;&#1080;&#1077;:</span></b><b><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-weight:bold;font-size:20.0000pt;" ><o:p></o:p></span></b></p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1055;&#1086; &#1091;&#1082;&#1072;&#1079;&#1072;&#1085;&#1085;&#1086;&#1081; &#1074; &#1040;&#1082;&#1090;&#1077; &#1090;&#1077;&#1093;&#1085;&#1080;&#1082;&#1077;, &#1079;&#1072;&#1103;&#1074;&#1083;&#1077;&#1085;&#1085;&#1099;&#1081; &#1076;&#1077;&#1092;&#1077;&#1082;&#1090; &#1087;&#1086;&#1076;&#1090;&#1074;&#1077;&#1088;&#1078;&#1076;&#1077;&#1085;. &#1058;&#1077;&#1093;&#1085;&#1080;&#1082;&#1072; &#1085;&#1077;&#1080;&#1089;&#1087;&#1088;&#1072;&#1074;&#1085;&#1072; &#1080; &#1091;&#1090;&#1088;&#1072;&#1090;&#1080;&#1083;&#1072; &#1090;&#1086;&#1074;&#1072;&#1088;&#1085;&#1099;&#1081; &#1074;&#1080;&#1076; &#1074; &#1087;&#1088;&#1086;&#1094;&#1077;&#1089;&#1089;&#1077; &#1101;&#1082;&#1089;&#1087;&#1083;&#1091;&#1072;&#1090;&#1072;&#1094;&#1080;&#1080; &#1080;&#1083;&#1080; &#1079;&#1072;&#1074;&#1086;&#1076;&#1089;&#1082;&#1086;&#1075;&#1086; &#1076;&#1077;&#1092;&#1077;&#1082;&#1090;&#1072;. &#1057;&#1083;&#1077;&#1076;&#1086;&#1074; &#1085;&#1072;&#1088;&#1091;&#1096;&#1077;&#1085;&#1080;&#1103; &#1091;&#1089;&#1083;&#1086;&#1074;&#1080;&#1081; &#1075;&#1072;&#1088;&#1072;&#1085;&#1090;&#1080;&#1080;, &#1084;&#1077;&#1093;&#1072;&#1085;&#1080;&#1095;&#1077;&#1089;&#1082;&#1080;&#1093; &#1087;&#1086;&#1074;&#1088;&#1077;&#1078;&#1076;&#1077;&#1085;&#1080;&#1081;, &#1087;&#1086;&#1087;&#1072;&#1076;&#1072;&#1085;&#1080;&#1103; &#1078;&#1080;&#1076;&#1082;&#1086;&#1089;&#1090;&#1077;&#1081; &#1080; &#1087;&#1086;&#1089;&#1090;&#1086;&#1088;&#1086;&#1085;&#1085;&#1080;&#1093; &#1087;&#1088;&#1077;&#1076;&#1084;&#1077;&#1090;&#1086;&#1074; &#1085;&#1077; &#1086;&#1073;&#1085;&#1072;&#1088;&#1091;&#1078;&#1077;&#1085;&#1086;.</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1053;&#1077;&#1080;&#1089;&#1087;&#1088;&#1072;&#1074;&#1085;&#1072;&#1103; &#1090;&#1077;&#1093;&#1085;&#1080;&#1082;&#1072; &#1086;&#1089;&#1090;&#1072;&#1074;&#1083;&#1077;&#1085;&#1072; &#1085;&#1072; &#1086;&#1090;&#1074;&#1077;&#1090;&#1089;&#1090;&#1074;&#1077;&#1085;&#1085;&#1086;&#1077; &#1093;&#1088;&#1072;&#1085;&#1077;&#1085;&#1080;&#1077; &#1074; &#1087;&#1088;&#1077;&#1076;&#1089;&#1090;&#1072;&#1074;&#1080;&#1090;&#1077;&#1083;&#1100;&#1089;&#1090;&#1074;&#1077; &#1082;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1080;.</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal align=justify style="text-align:justify;text-justify:inter-ideograph;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1055;&#1086;&#1076;&#1083;&#1077;&#1078;&#1072;&#1090; &#1082;&#1086;&#1084;&#1087;&#1077;&#1085;&#1089;&#1072;&#1094;&#1080;&#1080; &#1079;&#1072;&#1082;&#1091;&#1087;&#1086;&#1095;&#1085;&#1086;&#1081; &#1089;&#1090;&#1086;&#1080;&#1084;&#1086;&#1089;&#1090;&#1080; &#1055;&#1086;&#1089;&#1090;&#1072;&#1074;&#1097;&#1080;&#1082;&#1086;&#1084;, &#1082;&#1072;&#1082; &#1090;&#1086;&#1074;&#1072;&#1088; &#1085;&#1077;&#1085;&#1072;&#1076;&#1083;&#1077;&#1078;&#1072;&#1097;&#1077;&#1075;&#1086; &#1082;&#1072;&#1095;&#1077;&#1089;&#1090;&#1074;&#1072;.</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal style="margin-left:35.4000pt;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1057;&#1077;&#1088;&#1074;&#1080;&#1089;-&#1084;&#1077;&#1085;&#1077;&#1076;&#1078;&#1077;&#1088;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">______________________</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">.</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal style="margin-left:35.4000pt;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal style="margin-left:35.4000pt;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1057; &#1072;&#1082;&#1090;&#1086;&#1084; &#1086;&#1079;&#1085;&#1072;&#1082;&#1086;&#1084;&#1083;&#1077;&#1085;: &#171;___&#187;____________ 2017 &#1075;&#1086;&#1076;&#1072;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal style="margin-left:35.4000pt;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p> </o:p></span></p>
        <p class=MsoNormal style="margin-left:35.4000pt;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1047;&#1072;&#1082;&#1072;&#1079;&#1095;&#1080;&#1082; (&#1087;&#1088;&#1077;&#1076;&#1089;&#1090;&#1072;&#1074;&#1080;&#1090;&#1077;&#1083;&#1100; &#1047;&#1072;&#1082;&#1072;&#1079;&#1095;&#1080;&#1082;&#1072;)  __________________</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">____________________</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
        <p class=MsoNormal style="margin-left:35.4000pt;"><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1055;&#1086;&#1076;&#1087;&#1080;&#1089;&#1100;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#9;</span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;">&#1060;&#1072;&#1084;&#1080;&#1083;&#1080;&#1103; &#1048;.&#1054;. </span><span style="mso-spacerun:\'yes\';font-family:\'Times New Roman\';font-size:20.0000pt;"><o:p></o:p></span></p>
    </div>
    <!--EndFragment-->
</body>

</html>';

file_put_contents($_SERVER['DOCUMENT_ROOT'].'/temp/html/1.html', $content);
$pdf = file_get_contents('http://r97.ru/pdf/test_pdf.php?url=http://harper.ru/temp/html/1.html');
header('Content-Type: application/pdf');
header('Content-Length: '.strlen( $pdf ));
header('Content-disposition: inline; filename="doc.pdf"');
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header( "refresh:2;url=files.zip" );
echo $pdf;

}

?>

