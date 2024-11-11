<?php

namespace models\sender;

use program\core;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * v. 0.2
 * 2021-07-23
 */

class EMail
{

    private $db = null;
    private $to = [];
    private $from = '';
    private $accounts = [];
    private static $initFlag = false;


    public function __construct($db)
    {
        $this->db = $db;
    }


    public function addAccount($host, $username, $password, $name, $replyTo, $defaultFlag = false)
    {
        $this->accounts[$username] = ['host' => $host, 'username' => $username, 'password' => $password, 'name' => $name, 'reply_to' => $replyTo];
        if ($defaultFlag) {
            $this->from = $username;
        }
    }


    public function process($cnt = 5)
    {
        $rows = $this->db->exec('SELECT * FROM `mail_queue` ORDER BY `id` LIMIT ' . $cnt);
        if (!$rows) {
            return;
        }
        foreach ($rows as $row) {
            $this->sendLetter($row['from'], $row['to'], $row['subject'], $row['message']);
            $this->db->exec('DELETE FROM `mail_queue` WHERE `id` = ' . $row['id']);
        }
    }

 
    private function sendLetter($from, $to, $subject, $message)
    {
        $mailer = $this->getMailer($from);
        $emails = explode(',', $to);
        if ($mailer) {
            /* Отправка с сервера */
            $mailer->Subject = $subject;
            $mailer->Body = $message;
            foreach ($emails as $email) {
                $mailer->addAddress($email);
            }
            //$mailer->MailerDebug = true;
            $mailer->send();
            $mailer->clearReplyTos();
            $mailer->clearAddresses();
            return;
        }
        /* Отправка из PHP */
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: " . $from . "\r\n";
        foreach ($emails as $email) {
            mail($email, $subject, $message, $headers);
        }
    }


    private function getMailer($account)
    {
        if (!isset($this->accounts[$account])) {
            return null;
        }
        if (isset($this->accounts[$account]['mailer'])) {
            return $this->accounts[$account]['mailer'];
        }
        if (!self::$initFlag) {
            $path = '_new-codebase/back/vendor/php-mailer/src';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/' . $path . '/Exception.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/' . $path . '/PHPMailer.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/' . $path . '/SMTP.php';
            self::$initFlag = true;
        }
        $mailer = new PHPMailer();
        $mailer->isSMTP();
        $mailer->Host = $this->accounts[$account]['host'];
        $mailer->SMTPAuth = true;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mailer->Username = $this->accounts[$account]['username'];
        $mailer->Password = $this->accounts[$account]['password'];
        $mailer->Timeout = 10;
        $mailer->Port = 465;
        $mailer->SMTPDebug  = 0;
        if (!empty($this->accounts[$account]['reply_to'])) { // только перед setFrom
            $mailer->addReplyTo($this->accounts[$account]['reply_to'], $this->accounts[$account]['reply_to']);
        }
        $mailer->setFrom($this->accounts[$account]['username'], $this->accounts[$account]['name']);
        $mailer->isHTML(true);
        $mailer->CharSet = 'UTF-8';
        $this->accounts[$account]['mailer'] = $mailer;
        return $this->accounts[$account]['mailer'];
    }


    public function from($email)
    {
        $this->from = trim($email);
        return $this;
    }


    public function to(array $emails)
    {
        $this->to = array_filter($emails);
        return $this;
    }


    public function send($subject, array $data, $templateURI, $skipQueue = false)
    {
        if (!$this->to) {
            return;
        }
        $rows = $this->db->exec('SELECT `template` FROM `mail_templates` WHERE `uri` = ?', [$templateURI]);
        if (!$rows) {
            throw new \Exception('E-mail template "' . $templateURI . '" not found.');
        }
        $message = $rows[0]['template'];
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        $from = $this->from;
        if (empty($from)) {
            $from = 'no-reply@' . str_replace('www.', '', $_SERVER["SERVER_NAME"]);
        }
        $subject = trim($subject);
        $to = trim(implode(',', $this->to), ', ');
        if (!$skipQueue) {
            $this->db->exec('INSERT INTO `mail_queue` (`subject`, `from`, `to`, `message`) VALUES (?, ?, ?, ?)', [$subject, $from, $to, $message]);
            return;
        }
        $this->sendLetter($from, $to, $subject, $message);
    }
}
