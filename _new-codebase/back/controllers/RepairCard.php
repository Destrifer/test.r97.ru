<?php

namespace controllers;

use program\core;
use models;
use models\User;

class RepairCard extends _Controller
{


    public static function run()
    {
        if (!empty(core\App::$URLParams['ajax'])) {
            switch (core\App::$URLParams['ajax']) {
                case 'save-status':
                    $repair = models\Repair::getRepairByID($_POST['repair_id']);
                    models\Repair::changeStatus($_POST['repair_id'], $_POST['status']);
                    models\Log::repair(1, '"' . $repair['status'] . '" на "' . $_POST['status'] . '".', $_POST['repair_id']);
                    exit;

                case 'get-confirm-approve-window':
                    require '_new-codebase/front/templates/main/parts/confirm-approve-window.php';
                    showConfirmApproveWindow(models\services\Settings::getSettings($_POST['repair_id']), $_POST['repair_id']);
                    exit;

                case 'get-save-parts-window':
                    require '_new-codebase/front/templates/main/parts/save-parts-window.php';
                    showSavePartsWindow(models\Repair::getNeedToSaveParts($_POST['repair_id']), User::getData('role'));
                    exit;

                case 'save-parts-window':
                    $res = models\Repair::saveNeedPartsWindow($_POST);
                    echo json_encode($res);
                    exit;
            }
        }
    }
}
