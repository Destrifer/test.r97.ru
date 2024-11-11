<?php

namespace models\documents;

use Exception;
use models\Repair;
use models\Services;
use program\core;
use program\adapters;
use program\core\Time;

/* Квитанция на выездной ремонт */

class ReceiptOutside extends \models\_Model
{

    private $db = null;
    private $template = '';
    private $repair = [];
    private $cellsMap = [
        'A2' => 'service',
        'A3' => 'service_phone',
        'A4' => 'service_address',
        'A6' => 'repair_id',
        'A7' => 'rsc',
        'A8' => 'client',
        'A9' => 'model',
        'A10' => 'serial',
        'A11' => 'repair_type',
        'A12' => 'shop',
        'A13' => 'packaging',
        'A14' => ['client', 'client_phone'],
        'A15' => 'client_address',
        'A16' => 'appearance',
        'A17' => 'defect_client',
        'A23' => 'client',
        'A27' => 'receive_date',
        'A30' => 'client',
        'A34' => 'out_date',
        'A37' => 'client',
        'A38' => 'client'
    ];

    public function __construct($templatesPath, $repairID)
    {
        $this->db = \models\_Base::getDB();
        $this->repair = Repair::getRepairByID($repairID);
        if (!$this->repair) {
            throw new \Exception('Ремонт #' . $repairID . ' не найден.');
        }
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


    private function generateXLS()
    {
        $data = $this->prepareData();
        $xls = adapters\Excel::load($this->template);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Квитанция');
        foreach ($this->cellsMap as $cell => $field) {
            if (is_array($field)) {
                foreach ($field as $f) {
                    $this->setCellValue($f, $cell, $data[$f], $sheet);
                }
            } else {
                $this->setCellValue($field, $cell, $data[$field], $sheet);
            }
        }
        return $xls;
    }


    private function setCellValue($field, $cell, $value, $sheet)
    {
        $v = str_replace('<' . $field . '>', $value, $sheet->getCell($cell)->getValue());
        $sheet->setCellValue($cell, $v);
    }


    private function prepareData()
    {
        $service = Services::getServiceByID($this->repair['service_id']);
        if (!$service) {
            throw new \Exception('СЦ #' . $this->repair['service_id'] . ' не найден.');
        }
        $s = explode(',', trim(str_replace(';', ',', $service['phones']), ' ,'));
        $servicePhone = trim($s[0]);
        return [
            'repair_id' => $this->repair['id'],
            'rsc' => trim($this->repair['rsc']),
            'service' => $service['name_public'],
            'service_phone' => $servicePhone,
            'service_address' => $service['phisical_adress'],
            'client' => trim($this->repair['client']),
            'model' => trim($this->repair['model_name']),
            'serial' => trim($this->repair['serial']),
            'repair_type' => Repair::getRepairType($this->repair['repair_type_id']),
            'shop' => trim($this->repair['name_shop']),
            'packaging' => trim(str_replace('|', ', ', $this->repair['complex']), ' ,'),
            'client_phone' => trim($this->repair['phone']),
            'client_address' => trim($this->repair['address']),
            'appearance' => trim($this->repair['visual']),
            'defect_client' => trim($this->repair['bugs']),
            'receive_date' => date('d.m.Y', strtotime($this->repair['receive_date'])),
            'out_date' => (Time::isEmpty($this->repair['out_date'])) ? '' : date('d.m.Y', strtotime($this->repair['out_date']))
        ];
    }


    private function getTemplateName()
    {
        return 'receipt-outside.xlsx';
    }


    private function getOutputFileName()
    {
        return 'Квитанция-выездной-ремонт-' . $this->repair['id'] . '.xlsx';
    }
}
