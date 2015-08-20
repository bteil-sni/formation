<?php
/**
 * Created by PhpStorm.
 * User: Benoit
 * Date: 14/08/2015
 * Time: 16:29
 */

include_once (__DIR__.'/lib/swiftmailer/swift_required.php');

Class SendMail {

    private static $_SMTP_HOST = 'smtp.mandrillapp.com';
    private static $_SMTP_USERNAME = 'bteil@snieditions.com';
    private static $_SMTP_PASSWORD = '75GENzZ4lPiJkyoWLgExbg';
    private static $_SMTP_PORT = 587;
    private static $_SMTP_PROTOCOL = 'ssl';

    private $fromAddress;
    private $fromName;
    private $toAddress;
    private $toName;
    private $subject;
    private $body;

    public function __construct($params = array()) {
        $this->fromAddress = (isset($params['fromAddress'])) ? $params['fromAddress'] : 'robot@santenatureinnovation.com';
        $this->fromName = (isset($params['fromName'])) ? $params['fromName'] : 'Robot SNI';
        $this->toAddress = (isset($params['toAddress'])) ? $params['toAddress'] : 'bteil@santenatureinnovation.com';
        $this->toName = (isset($params['toName'])) ? $params['toName'] : 'Benoît TEIL';
        $this->subject = (isset($params['subject'])) ? $params['subject'] : '';
        $this->body = (isset($params['body'])) ? $params['body'] : '';
    }

    public function send() {
        $transport = Swift_SmtpTransport::newInstance(self::$_SMTP_HOST, self::$_SMTP_PORT, self::$_SMTP_PROTOCOL)
            ->setUsername(self::$_SMTP_USERNAME)
            ->setPassword(self::$_SMTP_PASSWORD);
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance();
        $message->setContentType("text/plain");
        $message->setCharset("UTF-8");
        $message->setFrom($this->fromAddress, $this->fromName);
        $message->setTo($this->toAddress, $this->toName);
        $message->setSubject($this->subject);
        $message->setBody($this->body);
        return $mailer->send($message);
    }

}