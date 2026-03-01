<?php
/*
    File: mail_config.example.php
    Purpose: Email Configuration Template
    Instructions:
    1. Rename this file to 'mail_config.php'
    2. Add your Gmail and App Password
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'YOUR_GMAIL_ADDRESS_HERE'; // e.g. student@gmail.com
    $mail->Password   = 'YOUR_APP_PASSWORD_HERE';  // e.g. abcd efgh ijkl mnop
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender Info
    $mail->setFrom('YOUR_GMAIL_ADDRESS_HERE', 'Campus Lost & Found System'); 
    
    $mail->isHTML(true);
} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
?>