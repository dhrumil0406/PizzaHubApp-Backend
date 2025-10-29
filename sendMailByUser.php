<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendMailByUser($from, $subject, $body, $name)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dhrumilmandaviya464@gmail.com'; // SMTP username
        $mail->Password   = 'eiojjxbwfkatmfgc';   // App password (not Gmail password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom($from, $name);
        $mail->addAddress('dhrumilmandaviya464@gmail.com');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
