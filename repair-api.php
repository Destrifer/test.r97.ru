<?php

/* API ремонтов */

require $_SERVER['DOCUMENT_ROOT'] . '/includes/configuration.php';
require $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/_new-codebase/back/autoload.php';

use models\Log;
use program\core;
use models\Repair;
use models\repair\Attention;
use models\repair\Check;
use models\Serials;
use models\User;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

core\App::$config = $config;
core\App::run();

if (!User::isAuth()) {
    exitRightsError();
}

if (empty($_POST['action'])) {
    echo json_encode(['message' => 'Пуст параметр action.', 'error_flag' => 1]);
    exit;
}

switch ($_POST['action']) {
    case 'set-has-questions': // Метка "Есть вопросы"
        if (!User::hasRole('admin', 'slave-admin')) {
            exitRightsError();
        }
        \models\Log::repair(23, 'Статус '.(($_POST['has_questions']) ? 'установлен' : 'снят').' вручную.', $_POST['repair_id']);
        echo json_encode(Repair::setHasQuestions($_POST['repair_id'], $_POST['has_questions']));
        break;

    case 'set-install-approved-status': // Подтверждает/отклоняет монтаж
        if (!User::hasRole('admin', 'slave-admin', 'taker')) {
            exitRightsError();
        }
        echo json_encode(Repair::setInstallApprovedStatus($_POST['repair_id'], $_POST['is_approved'], $_POST['comment']));
        break;

    case 'set-dismant-approved-status': // Подтверждает/отклоняет демонтаж
        if (!User::hasRole('admin', 'slave-admin', 'taker')) {
            exitRightsError();
        }
        echo json_encode(Repair::setDismantApprovedStatus($_POST['repair_id'], $_POST['is_approved'], $_POST['comment']));
        break;

    case 'set-outside-approved-status': // Подтверждает/отклоняет выездной ремонт
        if (!User::hasRole('admin', 'slave-admin', 'taker')) {
            exitRightsError();
        }
        echo json_encode(Repair::setOutsideApprovedStatus($_POST['repair_id'], $_POST['is_approved'], $_POST['comment']));
        break;

    case 'need-to-confirm-master':
        echo json_encode(Repair::needToConfirmMaster($_POST['repair_id'], $_POST['master_id']));
        break;

    case 'check-common':
        echo json_encode(Check::hasCommonErrors($_POST['repair_id']));
        break;

    case 'check-parts':
        echo json_encode(Check::hasPartsErrors($_POST['repair_id']));
        break;

    case 'check-repair':
        echo json_encode(Check::hasFillErrors($_POST['repair_id']));
        break;

    case 'need-to-save-parts':
        echo json_encode([
            'need_to_save_parts_flag' => Repair::needToSaveParts($_POST['repair_id']),
            'error_flag' => 0,
            'message' => ''
        ]);
        break;

    case 'check-photos':
        echo json_encode(Check::hasPhotoErrors($_POST['repair_id']));
        break;

    case 'del':
        if (!User::hasRole('admin', 'slave-admin', 'taker', 'service', 'master')) {
            exitRightsError();
        }
        if (User::hasRole('service')) {
            $repair = Repair::getRepairByID($_POST['repair_id']);
            if (User::getData('id') != $repair['service_id'] || 
				($repair['status_admin'] != 'Принят' && $repair['status_admin'] != 'В работе')) {
				exitRightsError();
			}
        }
        $permFlag = (!empty($_POST['perm_flag'])) ? true : false;
        Log::repair(14, ($permFlag) ? 'Полное удаление.' : 'Помечен на удаление.', $_POST['repair_id']);
        echo json_encode(['message' => Repair::delRepair($_POST['repair_id'], $permFlag)]);
        break;

    case 'set-master':
        if (!User::hasRole('admin', 'slave-admin', 'taker')) {
            exitRightsError();
        }
        echo json_encode(['message' => Repair::setMaster($_POST['master_id'], $_POST['repair_id'])]);
        break;

    case 'create-prototype':
        echo json_encode(['new_repair_id' => Repair::createPrototype($_POST['repair_id'])]);
        break;

    case 'change-approve-date':
        if (!User::hasRole('admin', 'slave-admin', 'taker')) {
            exitRightsError();
        }
        echo json_encode(['message' => Repair::setApproveDate($_POST['date'], $_POST['repair_id'])]);
        break;

    case 'update-attention-message':
        if (!User::hasRole('admin')) {
            exitRightsError();
        }
        echo json_encode(['message' => Attention::updateMessage($_POST['message_id'], $_POST['message'])]);
        break;

    case 'change-attention-flag':
        if (!User::hasRole('admin', 'slave-admin', 'master')) {
            exitRightsError();
        }
        echo json_encode(['message' => Attention::change($_POST['attention_flag'], $_POST['repair_id'], $_POST['message'])]);
        break;

    case 'get-serial-info':
        echo json_encode(Serials::getSerial($_POST['serial'], $_POST['model_id']));
        break;

    default:
        echo json_encode(['message' => 'Ошибочный параметр action.', 'error_flag' => 1]);
}


function exitRightsError()
{
    echo json_encode(['message' => 'У пользователя недостаточно прав.', 'error_flag' => 1]);
    exit;
}
