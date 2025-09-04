<?php
// PHPMailer integration for GadgetLoop
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $subject, $message) {
    $config = require __DIR__ . '/phpmailer_config.php';
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = $config['port'];

        //Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
