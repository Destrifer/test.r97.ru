<?php

namespace models\sender;

use program\core;

/**
 * v. 0.2
 * 2021-07-19
 */

class Bell
{

    private $db = null;
    private $usersIDs = [];


    public function __construct($db)
    {
        $this->db = $db;
    }


    public function to(array $usersIDs)
    {
        $this->usersIDs = array_filter($usersIDs);
        return $this;
    }


    public function send($title, $text, $link)
    {
        if (!$this->usersIDs) {
            return;
        }
        $query = '';
        foreach ($this->usersIDs as $userID) {
            $query .= '("' . $title . '", "' . $text . '", "' . $link . '", ' . $userID . '),';
        }
        return (bool)$this->db->exec('INSERT INTO `notification` (`subject`, `text`, `link`, `user_id`) VALUES '
            . rtrim($query, ','));
    }


    public function toggleRead($messageID, $userID)
    {
        $rows = $this->db->exec('SELECT `id`, `read_flag` FROM `bell_recips` WHERE `message_id` = ? AND `user_id` = ?', [$messageID, $userID]);
        if (!$rows) {
            return;
        }
        $this->db->exec('UPDATE `bell_recips` SET `read_flag` = ? WHERE `id` = ?', [(($rows[0]['read_flag']) ? 0 : 1), $rows[0]['id']]);
    }


    public function readAll($userID)
    {
        $this->db->exec('UPDATE `bell_recips` SET `read_flag` = 1 WHERE `user_id` = ?', [$userID]);
    }


    public function getMessages($userID)
    {
        $rows = $this->db->exec('SELECT * FROM `bell_recips` rec LEFT JOIN `bell_messages` mes 
        ON rec.`message_id` = mes.`id` WHERE rec.`user_id` = ? ORDER BY rec.`id` DESC LIMIT 50', [$userID]);
        for ($i = 0, $cnt = count($rows); $i < $cnt; $i++) {
            $rows[$i]['add_time'] = core\Time::format($rows[$i]['add_date'], 'H:i');
            $rows[$i]['add_date'] = core\Time::formatVerbose($rows[$i]['add_date']);
        }
        return $rows;
    }


    public function getUnreadCnt($userID)
    {
        $rows = $this->db->exec('SELECT COUNT(*) AS cnt FROM `bell_recips` 
        WHERE `user_id` = ? AND `read_flag` = 0', [$userID]);
        return $rows[0]['cnt'];
    }
}
