<?php

namespace models\repair;


class Attention extends \models\_Model
{

    const TABLE = 'repairs_attention';
    const TABLE_MESS = 'repairs_attention_messages';

    private static $db = null;


    public static function init()
    {
        self::$db = \models\_Base::getDB();
    }


    public static function has($modelID, $serial)
    {
        if (empty($modelID) || empty($serial)) {
            return false;
        }
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `model_id` = ? AND `serial` = ? LIMIT 1', [$modelID, $serial]);
        return !empty($rows[0]['id']);
    }


    public static function get($modelID, $serial, $onlyActiveFlag = false)
    {
        if (empty($modelID) || empty($serial)) {
            return [];
        }
        $rows = self::$db->exec('SELECT * FROM `' . self::TABLE . '` WHERE `model_id` = ? AND `serial` = ? ' . (($onlyActiveFlag) ? 'AND `active_flag` = 1' : ''), [$modelID, $serial]);
        if (!$rows) {
            return [];
        }
        $rowsMes = self::$db->exec('SELECT * FROM `' . self::TABLE_MESS . '` WHERE `attention_id` = ' . $rows[0]['id'] . ' ORDER BY `id` DESC');
        for ($i = 0, $cnt = count($rowsMes); $i < $cnt; $i++) {
            $rowsMes[$i]['add_date'] = date('d.m.Y H:i', strtotime($rowsMes[$i]['add_date']));
            $t = explode(' ', $rowsMes[$i]['add_date']);
            $rowsMes[$i]['add_date'] = $t[0];
            $rowsMes[$i]['add_time'] = $t[1];
        }
        $rows[0]['messages'] = $rowsMes;
        return $rows[0];
    }


    /**
     * Включает или отключает флаг
     * 
     * @param int $newAttentionFlag Новое значение флага
     * @param int $repairID Ремонт
     * @param string $message Сообщение
     * 
     * @return void
     */
    public static function change($newAttentionFlag, $repairID, $message = '')
    {
        $rows = self::$db->exec('SELECT `model_id`, `serial` FROM `repairs` WHERE `id` = ?', [$repairID]);
        if (!$rows) {
            return;
        }
        $modelID = $rows[0]['model_id'];
        $serial = trim($rows[0]['serial']);
        if (empty($modelID) || empty($serial)) {
            return;
        }
        if (!$newAttentionFlag) {
            if (self::off($modelID, $serial)) {
                \models\Log::repair(17, 'Флаг снят.', $repairID);
            }
        } else {
            if (self::on($modelID, $serial, $message)) {
                \models\Log::repair(17, 'Флаг поставлен: ' . mb_substr(strip_tags($message), 0, 64) . '...', $repairID);
            }
        }
    }


    public static function updateMessage($messageID, $message)
    {
        return self::$db->exec('UPDATE `' . self::TABLE_MESS . '` SET `message` = ? WHERE `id` = ?', [trim($message), $messageID]);
    }


    /**
     * Включает флаг
     * 
     * @param int $modelID Модель
     * @param string $serial Серийный номер модели
     * @param string $message Сообщение
     * 
     * @return bool Флаг результата
     */
    private static function on($modelID, $serial, $message)
    {
        self::$db->transact('begin');
        $rows = self::$db->exec('SELECT `id` FROM `' . self::TABLE . '` WHERE `model_id` = ? AND `serial` = ?', [$modelID, $serial]);
        if (!$rows) {
            $attentionID = self::$db->exec('INSERT INTO `' . self::TABLE . '` (`model_id`, `serial`, `active_flag`) VALUES (?, ?, ?)', [$modelID, $serial, 1]);
        } else {
            $attentionID = $rows[0]['id'];
        }
        if (!$attentionID) {
            return false;
        }
        $r = self::$db->exec('INSERT INTO `' . self::TABLE_MESS . '` (`message`, `attention_id`) VALUES (?, ?)', [trim($message), $attentionID]);
        $r2 = self::$db->exec('UPDATE `repairs` SET `attention_flag` = 1 WHERE `model_id` = ? AND `serial` = ?', [$modelID, $serial]);
        if ($r && $r2) {
            self::$db->transact('commit');
            return true;
        }
        return false;
    }


    /**
     * Отключает флаг
     * 
     * @param int $modelID Модель
     * @param string $serial Серийный номер модели
     * 
     * @return bool Флаг результата
     */
    private static function off($modelID, $serial)
    {
        self::$db->transact('begin');
        $r = self::$db->exec('UPDATE `' . self::TABLE . '` SET `active_flag` = 0 WHERE `model_id` = ? AND `serial` = ?', [$modelID, $serial]);
        $r2 = self::$db->exec('UPDATE `repairs` SET `attention_flag` = 0 WHERE `model_id` = ? AND `serial` = ?', [$modelID, $serial]);
        if ($r && $r2) {
            self::$db->transact('commit');
            return true;
        }
        return false;
    }
}

Attention::init();
