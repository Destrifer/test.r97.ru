<?php

namespace controllers;

use program\core;
use models;

class Status extends _Controller
{


    public static function run()
    {
        if (!empty(core\App::$URLParams['ajax'])) {
            switch (core\App::$URLParams['ajax']) {
                case 'get-reject-form':
                    echo getRejectFormHTML(core\App::$URLParams['repair-id']);
                    exit;
                case 'save-reject-form':
                    $repair = models\Repair::getRepairByID($_POST['repair_id']);
                    models\Repair::changeStatus($_POST['repair_id'], 'Отклонен');
                    models\Log::repair(1, '"'.$repair['status'].'" на "Отклонен".', $repair['id']);
                    models\Log::repair(13, 'Причина: '.mb_substr(strip_tags($_POST['message']), 0, 128).'...', $_POST['repair_id']);
                    notice_add('Ремонт отклонен.', 'Перейдите, чтобы узнать причину.', $repair['service_id'], '/edit-repair/' . $_POST['repair_id'] . '/step/6/');
                    models\Support::sendMessage(['message' => 'Причина отклонения ремонта: ' . $_POST['message'], 'repair_id' => $_POST['repair_id']], 1, models\Support::getSupportThread($_POST['repair_id'])['id']);
                    exit;
            }
        }
    }
}

require('_new-codebase/front/templates/main/parts/status.php');
