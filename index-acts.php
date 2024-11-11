<?php 

if ($_GET['query'] == 'get-act') {

  //
  //
 // if (User::hasRole('admin')) {
   models\Repair::updateOutDate($_GET['id']);
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['parts_info']['sum'] = 0;
        $content['master_info'] = master_info($content['master_id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/ract.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $sheet->setCellValue('A2', 'АКТ ВЫПОЛНЕННЫХ РАБОТ № '.$content['id']);
        if ($content['service_id'] == 33) {
        $sheet->setCellValue('A5', $content['service_info']['name']);
        } else {
        $sheet->setCellValue('A5', $content['service_info']['name_public']);
        }
        $sheet->setCellValue('W8', $content['rsc']);
        $sheet->setCellValue('W9', $content['cat_info']['name']);
        $sheet->setCellValue('W10', $content['model']['name']);
        $sheet->setCellValue('W11', $content['serial']);
        $sheet->setCellValue('W12', date("d.m.Y", strtotime($content['receive_date'])));
        if ($content['client_type'] == 2) {
            $client = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `clients` WHERE `id` = \''.mysqli_real_escape_string($db, $content['client_id']).'\' LIMIT 1;'));
            $sheet->setCellValue('A7', 'Принят от:');
            $sheet->setCellValue('W7', $client['name']);
        } else if ($content['client_type'] == 1) {
            $sheet->setCellValue('A7', 'Принят от:');
            $sheet->setCellValue('W7', $content['client']);
        }
        if (!Time::isEmpty($content['sell_date'])) {
        $sheet->setCellValue('W13', date("d.m.Y", strtotime($content['sell_date'])));
        } else {
        $sheet->setCellValue('W13', '');
        }
        $sheet->setCellValue('A14', 'Статус приёма');
        $sheet->setCellValue('W14', 'Клиентский');
        $sheet->setCellValue('W15', $status_array[$content['status_id']]);
        $sheet->setCellValue('W16', $content['client'].', '.$content['address'].', '.$content['phone']);
        $sheet->setCellValue('W18', $content['id']);
        $sheet->setCellValue('W19', implode(', ', explode('|', $content['complex'])));
        $sheet->setCellValue('W20', implode(', ', explode('|', $content['visual'])).' '.$content['visual_comment']);
        $sheet->setCellValue('W21', $content['bugs']);
        $sheet->setCellValue('W22', $content['comment']);
        $sheet->setCellValue('W30', Time::format($content['finish_date']));

        /*$xls->getActiveSheet()->getStyle('A26:A'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('U26:U'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('AT26:AT'.$xls->getActiveSheet()->getHighestRow())
              ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L26:L'.$xls->getActiveSheet()->getHighestRow())
              ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L32:L'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getColumnDimension('L')->setWidth(20);  */
        //$xls->getActiveSheet()->getRowDimension(25)->setRowHeight(strlen(get_content_by_id('repair_type', $content['parts_info']['repair_id'])['name'])/1.2);



        //Ремонт:
        if (count(array_filter($content['parts_info'])) > 0) {
        $id = 26;
        $xls->getActiveSheet()->insertNewRowBefore(27, count($content['parts_info'])-1);
        $border_style= array('borders' => array('right' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

        foreach (array_filter($content['parts_info']) as $parts) {

        $xls->getActiveSheet()->getRowDimension($id)->setRowHeight(40);

        if (count(array_filter($content['parts_info'])) > 1) {
        $xls->getActiveSheet()->mergeCells('A'.$id.':K'.$id);
        $xls->getActiveSheet()->mergeCells('W'.$id.':BA'.$id);
        $xls->getActiveSheet()->mergeCells('BB'.$id.':BH'.$id);
        }

        //$xls->getActiveSheet()->mergeCells('A'.$id.':K'.$id);

        if (is_numeric($parts['name']) && $parts['ordered_flag'] == 1) {
        $part_array = part_by_id($parts['name']);
        $name_part = $part_array['list'];
        } else {
        $name_part = $parts['name'];
        }

        $sheet->setCellValue('A'.$id, get_content_by_id('repair_type', $parts['repair_type_id'])['name']);
        $sheet->setCellValue('W'.$id, $name_part);
        $sheet->setCellValue('BB'.$id, $parts['qty']);
        $sheet->setCellValue('L'.$id, $parts['position']);

        $sheet->getStyle('BB'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $sheet->getStyle('A'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $sheet->getStyle('W'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

       $style = array('borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        ));
        unset($name_part);
        $xls->getActiveSheet()->getStyle('A'.$id.':BH'.$id)->applyFromArray($style);

        $sheet->getStyle('AT'.$id)->getAlignment()->applyFromArray(
            array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

        $sheet->getStyle('L'.$id)->getAlignment()->applyFromArray(
            array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER)
        );

           $id++;


        }
        }


        //$xls->getActiveSheet()->getColumnDimension('A')->setWidth(strlen($content['works'])/2.5);
        /*$sheet->getStyle('A25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('L25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('U25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);*/


      $date1 = new DateTime($content['begin_date']);
      $date1_ready = $date1->format('d.m.Y');
      
        $nums = [28, 30, 32, 34];

         // Остаток:
        $sheet->setCellValue('W'.($nums[0]+count(array_filter($content['parts_info']))), $date1_ready);
        $date_app_date = @DateTime::createFromFormat('Y.m.d', $content['app_date']);
        if($date_app_date){
          $sheet->setCellValue('W'.($nums[1]+count(array_filter($content['parts_info']))), $date_app_date->format('d.m.Y'));
        }
       

        if ($content['service_id'] == 33) {
        $sheet->setCellValue('L'.($nums[2]+count(array_filter($content['parts_info']))), 'Клюев Александр Александрович');
        } else {
        $sheet->setCellValue('L'.($nums[2]+count(array_filter($content['parts_info']))), $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);
        }

        if ($content['service_id'] == 33) {
        $sheet->setCellValue('A'.($nums[3]+count(array_filter($content['parts_info']))), 'Руководитель АСЦ');
        }

        $sheet->setCellValue('L'.($nums[3]+count(array_filter($content['parts_info']))), $content['service_info']['req_gen_fio']);
   
        //original code...
       /* $titlecolwidth = $sheet->getColumnDimension('A')->getWidth();
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth($titlecolwidth);    */
        //echo $titlecolwidth;
        if(User::hasRole('service')){
          $sheet->getProtection()->setSheet(true);
        }
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="act_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'act-from') {
  
  
 // if (User::hasRole('admin')) {
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info_array($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['return_info'] = return_info($_GET['id']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/act-from-to.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue('C5', $config['a1_org']);
        $sheet->setCellValue('A14', $config['a1_sender']);
        $sheet->setCellValue('D14', $config['a1_receiver']);
        $sheet->setCellValue('I10', $_GET['id']);
        $sheet->setCellValue('J10', $content['return_info']['date']);

        $sheet->setCellValue('U26', $parts_name);
        $sheet->setCellValue('AT26', $parts_count);
        $sheet->setCellValue('L26', $parts_position);
        //

        $sql = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$_GET['id']);
        $num = mysqli_num_rows($sql);

        if ($num > 0) {
        $sheet->setCellValue('J20', $num);

        if ($num > 1) { $xls->getActiveSheet()->insertNewRowBefore(20,$num-1); }

        $id = 19;
        $id_num = 1;
        while ($row = mysqli_fetch_array($sql)) {
        $row['model'] = model_info($row['model_id']);

        $sheet->setCellValue('A'.$id, $id_num);
        $sheet->setCellValue('B'.$id, $row['rsc']);
        $sheet->setCellValue('C'.$id, $row['model']['name']);
        $sheet->setCellValue('D'.$id, $row['model']['model_id']);
        $sheet->setCellValue('E'.$id, $row['serial']);
        $sheet->setCellValue('F'.$id, $row['bugs']);
        $sheet->setCellValue('G'.$id, implode(', ', array_filter(explode('|', $row['complex']))));
        $sheet->setCellValue('H'.$id, implode(', ', array_filter(explode('|', $row['visual']))).' '.$row['visual_comment']);
        $sheet->setCellValue('I'.$id, $row['client']);
        $sheet->setCellValue('J'.$id, 1);

        $id++;
        $id_num++;
        }
        }


        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="act_from_'.$_GET['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

if ($_GET['query'] == 'act-to') {
  
  
  // if (User::hasRole('admin')) {
   require_once 'adm/excel/vendor/autoload.php';
 
         $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
         $content['service_info'] = service_request_info($content['service_id']);
         $content['cat_info'] = model_cat_info($content['model']['cat']);
         $content['parts_info'] = repairs_parts_info_array($content['id']);
         $content['master_info'] = master_info($content['master_id']);
         $content['return_info'] = return_info($_GET['id']);
 
         if (file_exists('adm/excel/files')) {
             foreach (glob('adm/excel/files/*') as $file) {
                 unlink($file);
             }
         }
 
         $lfcr = chr(10);
         $new_file = 'adm/excel/files/1.xlsx';
         copy('adm/excel/act-from-to2.xlsx', $new_file);
 
         $xls = PHPExcel_IOFactory::load($new_file);
         $xls->setActiveSheetIndex(0);
         $sheet = $xls->getActiveSheet();
 
         $sheet->setCellValue('C5', $config['a2_org']);
         $sheet->setCellValue('A14', $config['a2_sender']);
         $sheet->setCellValue('D14', $config['a2_receiver']);
         $sheet->setCellValue('I10', $_GET['id']);
         $last = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$_GET['id'].' ORDER BY `app_date` DESC LIMIT 1'));
 
         $sheet->setCellValue('J10', $content['return_info']['date_out']);
 
         $sheet->setCellValue('U26', $parts_name);
         $sheet->setCellValue('AT26', $parts_count);
         $sheet->setCellValue('L26', $parts_position);
         //
 
         $sql = mysqli_query($db, 'SELECT * FROM `repairs` where `return_id` = '.$_GET['id']);
         $num = mysqli_num_rows($sql);
 
         if ($num > 0) {
         $sheet->setCellValue('J20', $num);
 
         if ($num > 1) { $xls->getActiveSheet()->insertNewRowBefore(20,$num-1); }
 
 
 
         $id = 19;
         $id_num = 1;
         while ($row = mysqli_fetch_array($sql)) {
         $row['model'] = model_info($row['model_id']);
 
         $sheet->setCellValue('A'.$id, $id_num);
         $sheet->setCellValue('B'.$id, $row['rsc']);
         $sheet->setCellValue('C'.$id, $row['model']['name']);
         $sheet->setCellValue('D'.$id, $row['model']['model_id']);
         $sheet->setCellValue('E'.$id, $row['serial']);
         $sheet->setCellValue('F'.$id, $row['bugs']);
         $sheet->setCellValue('G'.$id, implode(', ', array_filter(explode('|', $row['complex']))));
         $sheet->setCellValue('H'.$id, implode(', ', array_filter(explode('|', $row['visual']))).' '.$row['visual_comment']);
         $sheet->setCellValue('I'.$id, $row['client']);
         $sheet->setCellValue('J'.$id, 1);
 
         $id++;
         $id_num++;
         }
         }
 
 
         $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
         $objWriter->save($new_file);
 
         header('Content-Description: File Transfer');
         header('Content-Type: application/octet-stream');
         header('Content-Disposition: attachment; filename="act_from_'.$_GET['id'].'.xlsx"');
         header('Content-Transfer-Encoding: binary');
         header('Expires: 0');
         header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
         header('Pragma: public');
         header('Content-Length: ' . filesize($new_file));
         ob_clean();
         flush();
         readfile($new_file);
         exit();
 
   //}
 }

 if ($_GET['query'] == 'get-reject') {
  //
  //
 // if (User::hasRole('admin')) {
  models\Repair::updateOutDate($_GET['id']);
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));

        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['city'] = get_city($content['service_info']['city']);
        /*if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }*/

        $lfcr = chr(10);

        $new_file = 'adm/excel/files/1.xlsx';

        if ($content['model']['brand'] == 'ZARGET' || $content['model']['brand'] == 'FRIO' || $_GET['id'] == 1100) {
        copy('adm/excel/zarget.xlsx', $new_file);
        } else {
        copy('adm/excel/2.xlsx', $new_file);
        }

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();


        // заргет
        if ($content['model']['brand'] == 'ZARGET' || $content['model']['brand'] == 'FRIO') {

         $xls->getActiveSheet()
        ->getStyle('B12')
        ->getNumberFormat()
        ->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_TEXT
        );
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $fail_type = array(1 => 'Деталь или ПО не постваляется', 2 => 'Отказано в гарантии', 3 => 'Клиент от ремонта отказался', 4 => 'Нарушены сроки ремонта', 5 => 'Не ремонтопригоден', 6 => 'Нет технической информации (схем)');


        $sheet->setCellValue('B3', $content['id']);
        $sheet->setCellValue('H27', $content['id']);
        $sheet->setCellValue('B4', $content['city']['fcity_name']);
        $sheet->setCellValue('H4', 'СЦ'.$content['service_id']);
        /*if ($content['model']['brand'] == 'TESLER') {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } else {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } */
        $sheet->setCellValue('B5', $content['service_info']['name'].', '.$content['city']['fcity_name'].', '.$content['service_info']['phisical_adress']);
        $sheet->setCellValue('B6', $content['service_info']['phones']);
        $sheet->setCellValue('B13', 1);
        $sheet->setCellValue('B7', $content['service_info']['contact_email']);
        $sheet->setCellValue('E8', $content['cat_info']['name']);
        $sheet->setCellValue('B9', $content['model']['name']);
        $sheet->setCellValue('B10', $content['serial']);
        $sheet->setCellValue('B11', (($content['sell_date'] != '0000-00-00') ? date("d.m.Y", strtotime($content['sell_date'])) : ''));
        $sheet->setCellValue('B12', date("d.m.Y", strtotime($content['receive_date'])));
        $sheet->setCellValue('B14', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B15', $content['bugs']);
        $sheet->setCellValue('B17', $content['client']);
        $sheet->setCellValue('B18', $content['address']);
        $sheet->setCellValue('B19', $content['phone']);
        $sheet->setCellValue('B20', $content['name_shop']);
        $sheet->setCellValue('B21', $content['address_shop']);
        $sheet->setCellValue('B22', $content['phone_shop']);

        if ($content['disease']) {
        $issue = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `issues` WHERE `id` = \''.mysqli_real_escape_string($db, $content['disease']).'\' LIMIT 1;'));
        }
        $sheet->setCellValue('B23', $issue['name']);

        $name_add = ($content['parts_info']['name'] != '') ? '('.$content['parts_info']['name'].')' : '';
        $sheet->setCellValue('B29', get_content_by_id('details_problem', $content['parts_info']['problem_id'])['repair_name'].'. '.preg_replace('/\(.*?\)/', '', get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name']).' '.$name_add);

        $sheet->setCellValue('B16', $content['rsc']);
        $sheet->setCellValue('B18', Time::format($content['sell_date']));
        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B24', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        //$sheet->setCellValue('B29', implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);

        if ($content['service_id'] == 33) {
        $sheet->setCellValue('B32', 'Клюев Александр Александрович');
        } else {
        $sheet->setCellValue('B32', $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);
        }
        $sheet->setCellValue('B34', $content['service_info']['req_gen_fio']);

        $sheet->setCellValue('B27', $content['parts_info']['name']);
        $sheet->setCellValue('F27', date("d.m.Y"));
        $sheet->setCellValue('B36', date("d.m.Y"));


        } else {

        $xls->getActiveSheet()
        ->getStyle('B12')
        ->getNumberFormat()
        ->setFormatCode(
            PHPExcel_Style_NumberFormat::FORMAT_TEXT
        );
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $fail_type = array(1 => 'Деталь или ПО не постваляется', 2 => 'Отказано в гарантии', 3 => 'Клиент от ремонта отказался', 4 => 'Нарушены сроки ремонта', 5 => 'Не ремонтопригоден', 6 => 'Нет технической информации (схем)');


        $sheet->setCellValue('A1', 'Акт неремонтопригодности (АНРП) №'.$content['id']);
        /*if ($content['model']['brand'] == 'TESLER') {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } else {
        $sheet->setCellValue('A3', 'Внимание! Данный АКТ, является основанием для обмена, либо возврата техники  в торговую сеть и получения компенсации за неё. Местонахождение техники указано внизу данного АКТа.');
        } */
        $sheet->setCellValue('A6', $content['service_info']['name'].', '.$content['city']['fcity_name'].', '.$content['service_info']['phisical_adress'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B8', $content['cat_info']['name']);
        $sheet->setCellValue('B10', $content['model']['name']);
        $sheet->setCellValue('B12', $content['serial']);


        $sheet->setCellValue('B14', implode(', ', array_filter(array($content['name_shop'], $content['city_shop'], $content['address_shop'], $content['phone_shop']))));
        $sheet->setCellValue('B16', $content['rsc']);
        $sheet->setCellValue('B18', (($content['sell_date'] != '0000-00-00') ? date("d.m.Y", strtotime($content['sell_date'])) : ''));
        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B24', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        $sheet->setCellValue('B27', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B29', implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);
        $sheet->setCellValue('B31', $content['bugs']);

        $name_add = ($content['parts_info']['name'] != '') ? '('.$content['parts_info']['name'].')' : '';
        $problem = get_content_by_id('details_problem', $content['parts_info']['problem_id']);
        $t = $problem['repair_name'].'. '.preg_replace('/\(.*?\)/', '', $problem['name']).' '.$name_add;
        $t = (trim($content['repair_final_cancel'], '- ')) ? $content['repair_final_cancel'] : $t;
        $sheet->setCellValue('B33', $t);

        $sheet->setCellValue('B35', date("d.m.Y", strtotime($content['receive_date'])));
        $sheet->setCellValue('B37', date("d.m.Y"));
        $sheet->setCellValue('B39', date("d.m.Y"));
        if ($content['service_id'] == 33) {
        $sheet->setCellValue('B41', 'Клюев Александр Александрович');

        } else {
        $sheet->setCellValue('B41', $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);

        }
        $sheet->setCellValue('B43', $content['service_info']['req_gen_fio']);

        }
        $params = models\services\Settings::getSettings($content['id'], $content['service_info']['user_id'], $content['service_info']['country']);
        $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        if($content['service_id'] == 33){
            if($content['repair_final'] == 2){
              $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
            }elseif($content['repair_final'] == 1 || $content['repair_final'] == 3){
              $anrpValue = 'Аппарат выдан на руки клиенту';
            }
        }elseif($params && $params['anrp_value'] != 2){
          $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        }else{
          if(!$params && $content['model']['brand'] == 'TESLER'){
            $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
          }else{
            $anrpValue = 'Аппарат выдан на руки клиенту';
          }   
        }
        $sheet->setCellValue('A46', $anrpValue);
        if(User::hasRole('service')){
          $sheet->getProtection()->setSheet(true);
        }
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="anrp_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}

# Дашборд:
if ($_GET['query'] == 'get-tech') {
  //
  //
 // if (User::hasRole('admin')) {
  models\Repair::updateOutDate($_GET['id']);
  require_once 'adm/excel/vendor/autoload.php';

        $content = mysqli_fetch_array(mysqli_query($db, 'SELECT * FROM `repairs` WHERE `id` = \''.mysqli_real_escape_string($db, $_GET['id']).'\' LIMIT 1;'));
        $content['model'] = model_info($content['model_id']);
        $content['service_info'] = service_request_info($content['service_id']);
        $content['cat_info'] = model_cat_info($content['model']['cat']);
        $content['parts_info'] = repairs_parts_info($content['id']);
        $content['master_info'] = master_info($content['master_id']);
        $content['city'] = get_city($content['service_info']['city']);

        if (file_exists('adm/excel/files')) {
            foreach (glob('adm/excel/files/*') as $file) {
                unlink($file);
            }
        }

        $lfcr = chr(10);
        $new_file = 'adm/excel/files/1.xlsx';
        copy('adm/excel/3.xlsx', $new_file);

        $xls = PHPExcel_IOFactory::load($new_file);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $status_array = array(1 => 'Гарантийный', 3 => 'Повторный', 5 => 'Условно-гарантийный', 6 => 'Платный');
        $status_type = array(1 => 'Блочный', 2 => 'Компонентный', 3 => 'Платный', 4 => 'Справка', 5 => 'Замена аксессуара', 6 => 'Повторный', 7 => 'Диагностика');
        $fail_type = array(1 => 'Деталь или ПО не постваляется', 2 => 'Отказано в гарантии', 3 => 'Клиент от ремонта отказался', 4 => 'Нарушены сроки ремонта', 5 => 'Не ремонтопригоден', 6 => 'Нет технической информации (схем)');

        if ($content['parts_info']['problem_id'] == 5) {
            $problem = get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'].'. '.get_content_by_id('repair_type', $content['parts_info']['repair_type_id'])['name'].' '.$content['repair_final_cancel'];
        }elseif (in_array($content['parts_info']['problem_id'], array(3, 14, 15, 16, 18, 19, 23, 43, 41))) {
             if (get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name']) {
            $problem_add = get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'];
            }

            $problem_add = ($content['repair_final_cancel'] != '') ? $content['repair_final_cancel'] : $problem_add;

            $problem = 'В гарантии отказано.';


        }elseif(in_array($content['parts_info']['problem_id'], [35, 57])){
          $problem = get_content_by_id('details_problem', $content['parts_info']['problem_id'])['name'];
        }else{
          $problem = $content['repair_final_cancel'];
        }

        $sheet->setCellValue('A1', 'Акт технического заключения, осмотра (АТЗ/О) №'.$content['id']);
        $xls->getActiveSheet()->getStyle('A6')->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->mergeCells('A6:B6');
        $xls->getActiveSheet()->getRowDimension('6')->setRowHeight(50);
        $sheet->setCellValue('A6', $content['service_info']['name'].', '.$content['city']['fcity_name'].', '.$content['service_info']['phisical_adress'].', тел. '.$content['service_info']['phones']);
        $sheet->setCellValue('B8', $content['cat_info']['name']);
        $sheet->setCellValue('B10', $content['model']['name']);
        $sheet->setCellValue('B12', $content['serial']);
        $sheet->setCellValue('B14', implode(', ', array_filter(array($content['name_shop'], $content['city_shop'], $content['address_shop'], $content['phone_shop']))));
        $sheet->setCellValue('B16', $content['rsc']);
        if ($content['sell_date'] != '0000-00-00') {
        $sheet->setCellValue('B18', date("d.m.Y", strtotime($content['sell_date'])));
        } else {
        $sheet->setCellValue('B18', '');
        }

        $sheet->setCellValue('B20', $status_array[$content['status_id']]);
        $sheet->setCellValue('B22', implode(', ', array_filter(array($content['client'], $content['address'], $content['phone']))));
        $sheet->setCellValue('B25', implode(', ', array_filter(explode('|', $content['complex']))));
        $sheet->setCellValue('B27', implode(', ', array_filter(explode('|', $content['visual']))).' '.$content['visual_comment']);
        $sheet->setCellValue('B29', $content['bugs']);
        $sheet->setCellValue('B31', $problem.' '.$problem_add);
         unset($problem_add);
        //$sheet->setCellValue('B31', get_content_by_id('details_problem', $content['parts_info']['problem'])['repair_name'].'. '.preg_replace('/\(.*?\)/', '', get_content_by_id('details_problem', $content['parts_info']['problem'])['name']));
        $sheet->setCellValue('B33', date("d.m.Y", strtotime($content['receive_date'])));
        $sheet->setCellValue('B35', date("d.m.Y", strtotime($content['approve_date'])));
        $sheet->setCellValue('B37', date("d.m.Y"));


        if ($content['service_id'] == 33) {
        $sheet->setCellValue('B39', 'Клюев Александр Александрович');
        $sheet->setCellValue('A41', 'Руководитель');
        $sheet->setCellValue('B41', $content['service_info']['req_gen_fio']);
        } else {
        $sheet->setCellValue('B39', $content['master_info']['surname'].' '.$content['master_info']['name'].' '.$content['master_info']['third_name']);
        $sheet->setCellValue('B41', $content['service_info']['req_gen_fio']);
        }

        $params = models\services\Settings::getSettings($content['id'], $content['service_info']['user_id'], $content['service_info']['country']);
        $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        if($problem == 'В гарантии отказано.' || $content['parts_info']['problem_id'] == 5){
          $anrpValue = 'Аппарат выдан на руки клиенту';
        }elseif($content['service_id'] == 33){
            if($content['repair_final'] == 2){
              $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
            }elseif($content['repair_final'] == 1 || $content['repair_final'] == 3){
              $anrpValue = 'Аппарат выдан на руки клиенту';
            }
        }elseif($params && $params['anrp_value'] != 2){
          $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
        }else{
          if(!$params && $content['model']['brand'] == 'TESLER'){
            $anrpValue = 'Аппарат оставлен в сервисе на ответственное хранение';
          }else{
            $anrpValue = 'Аппарат выдан на руки клиенту';
          }   
        }
        $sheet->setCellValue('A43', $anrpValue);

        $xls->getActiveSheet()->getPageSetup()->setPrintArea('A1:B44');
        $xls->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A1')->getFont()->setSize(14);
        $xls->getActiveSheet()->getStyle('A14')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $xls->getActiveSheet()->getStyle('A29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
       /* $xls->getActiveSheet()->getStyle('A25:A'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getStyle('L31:L'.$xls->getActiveSheet()->getHighestRow())
        ->getAlignment()->setWrapText(true);
        $xls->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        //$xls->getActiveSheet()->getRowDimension(25)->setRowHeight(strlen(get_content_by_id('repair_type', $content['parts_info']['repair_id'])['name'])/1.2);
        $xls->getActiveSheet()->getRowDimension(25)->setRowHeight(28); */

        //$xls->getActiveSheet()->getColumnDimension('A')->setWidth(strlen($content['works'])/2.5);
        /*$sheet->getStyle('A25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('L25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('U25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('AT25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('BC25')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); */
         // Остаток:
       /* $sheet->setCellValue('W27', date("d.m.Y", strtotime($content['start_date'])));
        $sheet->setCellValue('W29', date("d.m.Y", strtotime($content['end_date'])));
        $sheet->setCellValue('L31', $content['master_info']['name'].' '.$content['master_info']['surname']);
        $xls->getActiveSheet()->getStyle('L31')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setBold(true);
        $xls->getActiveSheet()->getStyle('A5')->getFont()->setSize(13);   */
        /*$sheet->setCellValue('AC31', $content['service_info']['req_gen_fio']); */
        //$sheet->setCellValue('X35', $content['service_info']['req_gen_fio']);

        $sheet->calculateColumnWidths();

        //original code...
        $titlecolwidth = $sheet->getColumnDimension('A')->getWidth();
        $sheet->getColumnDimension('A')->setAutoSize(false);
        $sheet->getColumnDimension('A')->setWidth($titlecolwidth);
        //echo $titlecolwidth;
        if(User::hasRole('service')){
          $sheet->getProtection()->setSheet(true);
        }
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');
        $objWriter->save($new_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="ato_'.$content['id'].'.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($new_file));
        ob_clean();
        flush();
        readfile($new_file);
        exit();

  //}
}