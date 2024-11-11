<?php

namespace models\documents;

use program\core;
use program\adapters;

class DetailReport extends \models\_Model
{

    private $db = null;
    private $template = '';
    private $from = '';
    private $to = '';
    private $month = '';
    private $year = '';
    private $serviceID = '';
    private $brand = '';

    public function __construct($templatesPath, $month, $year, $serviceID, $brand)
    {
        $this->db = \models\_Base::getDB();
        $this->year = $year;
        $this->month = $month;
        $this->from = $year . '-' . $month . '-01';
        $this->to = $year . '-' . $month . '-' . cal_days_in_month(CAL_GREGORIAN, (int)$month, $year);
        $this->serviceID = $serviceID;
        $this->brand = strtoupper($brand);
        $this->template = $templatesPath . '/' . $this->getTemplateName();
    }


    public function display()
    {
        try {
            adapters\Excel::display($this->generateXLS(), $this->getOutputFileName());
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    public function save($path = '')
    {
        if (!$path) {
            $path = '/_new-codebase/uploads/temp/' . md5(time() . mt_rand(1, 10000000)) . '.xlsx';
        }
        adapters\Excel::save($this->generateXLS(), $path);
        return $path;
    }


    private function getSummary(array $repairs)
    {
        $summary = [];
        $rows = $this->db->exec('SELECT sv.`name` AS service_owner, sv.`name_public` AS service_name, 
       ct.`fcity_name` AS city
       FROM `requests` sv 
       LEFT JOIN `cityfull` ct ON ct.`fcity_id` = sv.`city` 
       WHERE sv.`user_id` = ?', [$this->serviceID]);
        $summary['header'] = ' Детализация Отчета СЦ "' . $rows[0]['service_owner'] . ', ' . $rows[0]['service_name'] . ', ' . $rows[0]['city'] . '" за период с ' . date('d.m.Y', strtotime($this->from)) . ' по ' . date('d.m.Y', strtotime($this->to));
        $summary['repairs_cost'] = 0;
        $summary['parts_cost'] = 0;
        $summary['transport_cost'] = 0;
        foreach ($repairs as $r) {
            $summary['repairs_cost'] += (int) $r['repair_price'];
            $summary['parts_cost'] += (int) $r['parts_cost'];
            $summary['transport_cost'] += (int) $r['transport_cost'];
            $summary['install_cost'] += (int) $r['install_cost'];
            $summary['dismant_cost'] += (int) $r['dismant_cost'];
        }
        $summary['total_cost'] = $summary['repairs_cost'] + $summary['parts_cost'] + $summary['transport_cost'] + $summary['install_cost'] + $summary['dismant_cost'];
        return $summary;
    }

    private function getRepairs()
    {
        $rows = $this->db->exec('SELECT rep.`id`, rep.`status_admin`, rep.`receive_date`, rep.`service_id`,  
        rep.`rsc`, rep.`client`, rep.`client_id`, rep.`address`, rep.`phone`, rep.`model_id`, rep.`serial`,
        rep.`sell_date`, rep.`begin_date`, rep.`finish_date`, rep.`transport_cost`, rep.`parts_cost`, rep.`install_cost`, rep.`dismant_cost`, rep.`onway`, rep.`onway_type`, rep.`repair_type_id`, rep.`app_date`, rep.`approve_date`, rep.`total_price` AS repair_price, 
        rep.`name_shop` AS shop_name, rep.`address_shop` AS shop_address, rep.`master_user_id`, rep.`phone_shop` AS shop_phone,  
        md.`id` AS model_id, md.`name` AS model_name, md.`cat` AS model_cat, md.`brand` AS model_brand, 
        iss.`name` AS issue_name 
        FROM `repairs` rep 
        LEFT JOIN `models` md ON md.`id` = rep.`model_id` 
        LEFT JOIN `issues` iss ON iss.`id` = rep.`disease`    
        WHERE rep.`service_id` = ? AND rep.`app_date` REGEXP "' . $this->year . '.' . $this->month . '." AND rep.`status_admin` IN ("Подтвержден", "Выдан") AND rep.`deleted` = 0 AND rep.`status_id` != 6', [$this->serviceID]);
        if (!$rows) {
            throw new \Exception('Ремонты не найдены.');
        }
        $newRows = [];
        $horizontBrands = ['ЗЭБТ-Harper', 'Horizont', 'Hartens', 'ЗЭБТ-Горизонт', 'Белит-Горизонт', 'OK', 'ЗЭБТ-HARTENS', 'ЗЭБТ-Skyworth', 'ЗЭБТ-Prestigio', 'ROSENLEW'];
        $dateCurrent = new \DateTime("01/" . $this->month . "/" . $this->year);
        $dateFrom = new \DateTime("01/10/2019");
        if ($dateCurrent >= $dateFrom) {
            $harperBrands = ['HARPER', 'OLTO', 'SKYLINE', 'NESONS'];
        } else {
            $harperBrands = ['HARPER', 'OLTO'];
        }

        foreach ($rows as $row) {
            if ($this->brand == 'HARPER' && in_array($row['model_brand'], $harperBrands)) {
                if ($row['master_user_id'] <= 0 && $row['service_id'] == 33) {
                    continue;
                }
                $newRows[] = $row;
                continue;
            }
            if ($this->brand == 'HORIZONT' && in_array($row['model_brand'], $horizontBrands)) {
                $newRows[] = $row;
                continue;
            }
            if ($row['model_brand'] == $this->brand) {
                $newRows[] = $row;
            }
        }
        return $newRows;
    }

    private function appendAdditionalData(array &$repairs)
    {
        for ($i = 0, $n = 1, $len = count($repairs); $i < $len; $i++, $n++) {
            $this->appendParts($repairs[$i]);
            $this->appendATOANRP($repairs[$i]);
            $this->appendRepairStatus($repairs[$i]);
            $this->appendIssues($repairs[$i]);
            $this->appendClient($repairs[$i]);
            $repairs[$i]['begin_date'] = core\Time::format($repairs[$i]['begin_date']);
            $repairs[$i]['finish_date'] = core\Time::format($repairs[$i]['finish_date']);
            $repairs[$i]['receive_date'] = core\Time::format($repairs[$i]['receive_date']);
            $repairs[$i]['num'] = $n;
            $repairs[$i]['sell_date'] = (!core\Time::isEmpty($repairs[$i]['sell_date'])) ? core\Time::format($repairs[$i]['sell_date']) : 'ПРЕДТОРГ';
        }
    }

    private function appendATOANRP(array &$repair)
    {
        $repair['ato_anrp'] = '';
        if ($repair['repair_type_id'] == 4) {
            $repair['ato_anrp'] = 'Выдан АНРП, ';
        } elseif ($repair['repair_type_id'] == 5) {
            $repair['ato_anrp'] = 'Выдан АТО, ';
        }
        $repair['ato_anrp'] .= implode(', ', $repair['parts_repair_types']);
    }

    private function appendRepairStatus(array &$repair)
    {
        $repair['repair_status'] = '';
        if ($repair['status_admin'] == 'Подтвержден' || $repair['status_admin'] == 'Выдан') {
            $repair['repair_status'] = 'Закрыто';
            if (in_array('АТО', $repair['parts_problem_types']) || in_array('АНРП', $repair['parts_problem_types'])) {
                $repair['repair_status'] .= ' / возврат товара';
            }
        }
        $repair['repair_status'] .= ' / ' . implode(', ', $repair['parts_repair_types']);
    }

    private function appendParts(array &$repair)
    {
        $repair['parts'] = '';
        $repair['has_parts'] = false;
        $repair['fixed_parts'] = '';
        $repair['parts_problem_ids'] = '';
        $repair['parts_problems'] = '';
        $repair['parts_repair_types'] = [];
        $repair['parts_problem_types'] = [];
        if (preg_match('/дефект не обнаружен/iu', $repair['issue_name'])) {
            $repair['fixed_parts'] = 'Не выявлены';
        }
        $rows = $this->db->exec('SELECT repl.`name`, repl.`sum`, repl.`problem_id`,
        prob.`type` AS problem_type, prob.`name` AS problem_name, rtyp.`name` AS repair_type 
        FROM `repairs_work` repl 
        LEFT JOIN `details_problem` prob ON prob.`id` = repl.`problem_id` 
        LEFT JOIN `repair_type` rtyp ON rtyp.`id` = repl.`repair_type_id` 
        WHERE repl.`repair_id` = ? AND prob.`repair_name` NOT IN (?, ?)', [$repair['id'], 'В гарантии отказано', 'Дефект не обнаружен']);
        if (!$rows) {
            return;
        }
        $repair['has_parts'] = true;
        $repair['parts'] = implode(', ', array_column($rows, 'name'));
        $repair['parts_problem_ids'] = implode(',', array_column($rows, 'problem_id'));
        $repair['parts_repair_types'] = array_column($rows, 'repair_type');
        $repair['parts_problem_types'] = array_column($rows, 'problem_type');
        $repair['parts_problems'] = implode(', ', array_column($rows, 'problem_name'));
        $repair['fixed_parts'] = $repair['parts'];
    }

    private function appendClient(array &$repair)
    {
        $repair['client_full'] = '';
        if (!$repair['client_id']) {
            if (!empty($repair['address'])) {
                $repair['client_full'] = implode(', ', array_filter([$repair['client'], $repair['address'], $repair['phone']]));
                return;
            }
            $repair['client_full'] = implode(', ', array_filter([$repair['shop_name'], $repair['shop_address'], $repair['shop_phone']]));
            return;
        }
        $rows = $this->db->exec('SELECT `name`, `address`, `phone` FROM `clients` WHERE `id` = ?', [$repair['client_id']]);
        if (!$rows) {
            $repair['client_id'] = 0;
            $this->appendClient($repair);
            return;
        }
        $repair['client_full'] = implode(', ', array_filter([$rows[0]['name'], $rows[0]['address'], $rows[0]['phone']]));
    }

    private function appendIssues(array &$repair)
    {
        //$repair['issues'] = '';
        // if (empty($repair['issue_id'])) {
        //    return;
        // }
        //$rows = $this->db->exec('SELECT `name` FROM `' . entities\Cats::$tableIssues . '` WHERE `id` IN (' . $repair['issue_id'] . ')');
        //$repair['issues'] = implode(', ', array_column($rows, 'name'));
    }


    private function generateXLS()
    {
        $repairs = $this->getRepairs();
        $this->appendAdditionalData($repairs);
        $summary = $this->getSummary($repairs);
        //
        $xls = adapters\Excel::load($this->template);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Детальный отчет');
        $sheet->setCellValue('A1', $summary['header']);
        $cnt = count($repairs);
        if ($cnt > 1) {
            $sheet->insertNewRowBefore(4, $cnt - 1);
        }
        $totalRowNum = $cnt + 3;
        $sheet->setCellValue('N' . $totalRowNum, $summary['parts_cost']);
        $sheet->setCellValue('O' . $totalRowNum, $summary['transport_cost']);
        $sheet->setCellValue('P' . $totalRowNum, $summary['dismant_cost']);
        $sheet->setCellValue('Q' . $totalRowNum, $summary['install_cost']);
        $sheet->setCellValue('R' . $totalRowNum, $summary['repairs_cost']);
        $totalRowNum++;
        $sheet->setCellValue('R' . $totalRowNum, $summary['total_cost']);
        $n = 3;
        foreach ($repairs as $r) {
            $sheet->setCellValue('A' . $n, $r['num']);
            $sheet->setCellValue('B' . $n, $r['id']);
            $sheet->setCellValue('C' . $n, $r['model_name']);
            $sheet->setCellValue('D' . $n, trim($r['serial']));
            $sheet->setCellValue('E' . $n, $r['receive_date']);
            $sheet->setCellValue('F' . $n, $r['finish_date']);
            $sheet->setCellValue('G' . $n, $r['sell_date']);
            $sheet->setCellValue('H' . $n, $r['issue_name']);
            $sheet->setCellValue('I' . $n, $r['fixed_parts']);
            $sheet->setCellValue('J' . $n, $r['parts_problems']);
            $sheet->setCellValue('K' . $n, $r['ato_anrp']);
            $sheet->setCellValue('L' . $n, $r['client_full']);
            if ((int)$r['parts_cost'] > 0) {
                $sheet->setCellValue('M' . $n, $r['parts']);
            } else {
                $sheet->setCellValue('M' . $n, '');
            }
            $sheet->setCellValue('N' . $n, $r['parts_cost']);
            $sheet->setCellValue('O' . $n, $r['transport_cost']);
            $sheet->setCellValue('P' . $n, $r['dismant_cost']);
            $sheet->setCellValue('Q' . $n, $r['install_cost']);
            $sheet->setCellValue('R' . $n, $r['repair_price']);
            $sheet->setCellValue('S' . $n, $r['rsc']);
            $n++;
        }
        $cols = range('A', 'S');
        foreach ($cols as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        return $xls;
    }

    private function getTemplateName()
    {
        return 'detail-report.xlsx';
    }

    private function getOutputFileName()
    {
        return 'Detail-report.xlsx';
    }
}
