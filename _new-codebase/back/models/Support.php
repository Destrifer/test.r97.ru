<?php

namespace models;

/** 
 * v. 0.1
 * 2021-06-23
 */

class Support extends _Model
{

    private static $db = null;


    public static function init()
    {
        self::$db = _Base::getDB();
    }


    public static function getMessagesCnt($repairID)
    {
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `feedback_messages` WHERE `feedback_id` = (SELECT `id` FROM `feedback_admin` WHERE `repair_id` = ' . $repairID . ' LIMIT 1)');
        return (!empty($rows[0]['cnt'])) ? $rows[0]['cnt'] : 0;
    }


    public static function getUnreadCnt($repairID)
    {
        $field = 'read';
        if (User::hasRole('admin')) {
            $field = 'read_admin';
        }
        $rows = self::$db->exec('SELECT COUNT(*) AS cnt FROM `feedback_admin` WHERE `repair_id` = ' . $repairID . ' AND `' . $field . '` = 0');
        return $rows[0]['cnt'];
    }


    /** 
     * $data: [message, repair_id]
     * $userType: 1 (админ) || 3 (СЦ)
     */
    public static function sendMessage(array $data, $userType, $threadID = 0)
    {
        if (empty($data['message'])) {
            return;
        }
        switch ($userType) {
            case 1:
                $typeID = 2;
                $read = ['read' => 0, 'read_admin' => 1];
                break;
            case 3:
                $typeID = 1;
                $read = ['read' => 1, 'read_admin' => 0];
                break;
        }
        if (!$threadID) {
            $threadID = self::addThread($data);
        }
        self::$db->exec(
            'INSERT INTO `feedback_messages` 
        (`date`, `user_type`, `message`, `feedback_id`, `read`, `read_admin`) VALUES (?, ?, ?, ?, ?, ?)',
            [date('Y-m-d H:i:s'), $typeID, $data['message'], $threadID, $read['read'], $read['read_admin']]
        );
        self::$db->exec('UPDATE `feedback_admin` SET `read` = ?, `read_admin` = ?  
        WHERE `id` = ?', [$read['read'], $read['read_admin'], $threadID]);
        return $threadID;
    }


    private static function addThread(array $data)
    {
        $rows = self::$db->exec('SELECT `service_id` FROM `repairs` WHERE `id` = ?', [$data['repair_id']]);
        if (!$rows) {
            throw new \Exception('Ремонт #' . $data['repair_id'] . ' не найден.');
        }
        return self::$db->exec(
            'INSERT INTO `feedback_admin` 
        (`date`, `message`, `user_id`, `status`, `repair_id`) VALUES (?, ?, ?, ?, ?)',
            [date('Y-m-d H:i:s'), $data['message'], $rows[0]['service_id'], 'Вопрос открыт', $data['repair_id']]
        );
    }


    public static function getSupportThread($repairID)
    {
        $rows = self::$db->exec('SELECT *   
        FROM `feedback_admin`   
        WHERE `repair_id` = ?', [$repairID]);
        if (!$rows) {
            return ['id' => 0, 'date' => date('d.m.Y H:i'), 'repair_id' => $repairID];
        }
        $rows[0]['date'] = date('d.m.Y H:i', strtotime($rows[0]['date']));
        return $rows[0];
    }
}


Support::init();
