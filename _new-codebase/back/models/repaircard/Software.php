<?php

namespace models\repaircard;

use models\Repair;
use models\Support;

class Software extends \models\_Model
{

    private static $db = null;
    public static $types = [
        'instr' => 'Инструкция', 'firmware' => 'Прошивка', 'update' => 'Способ обновления',
        'scheme' => 'Схема', 'photo' => 'Фото', 'pdu' => 'ПДУ'
    ];


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function requestSoftware(array $data, $repairID)
    {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['message' => 'Пожалуйста, введите корректный e-mail.', 'error_flag' => 1];
        }
        if (empty($data['type'])) {
            return ['message' => 'Пожалуйста, выберите тип запроса.', 'error_flag' => 1];
        }
        $messageData = [
            'message' => 'Требуется ПО (' . self::$types[$data['type']] . ') на e-mail: <a href="mailto:' . $_POST['email'] . '">' . $_POST['email'] . '</a>',
            'repair_id' => $repairID
        ];
        $thread = Support::getSupportThread($repairID);
        $r = Support::sendMessage($messageData, 3, $thread['id']);
        if (!$r) {
            return ['message' => 'Во время отправки произошла ошибка, пожалуйста, свяжитесь с администратором.', 'error_flag' => 1];
        }
        $repair = Repair::getRepairByID($repairID);
        Repair::changeStatus($repairID, 'Запрос ПО');
        \models\Log::repair(1, '"' . $repair['status'] . '" на "Запрос ПО", при запросе ПО.', $repairID);
        notice_add('Новый вопрос в ремонте #' . $_GET['id'], 'Поступил новый вопрос в ремонте в службу поддержки. Пожалуйста, ознакомьтесь.', 1, '/edit-repair/' . $repairID . '/step/6/');
        return ['message' => 'Ваш запрос успешно отправлен.', 'error_flag' => 0];
    }
}

Software::init();
