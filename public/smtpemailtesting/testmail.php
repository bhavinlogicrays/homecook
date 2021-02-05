<?php
// $to = "testlr@senduvu.com";
// $subject = "My subject";
// $txt = "Hello world!";
// $headers = "From: webmaster@example.com";

// echo mail($to,$subject,$txt,$headers);

// use PHPMailer\PHPMailer\Exception;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

// $recipients = "testlr@senduvu.com"; 

// $headers["From"]    = "mailfrom@example.com"; 
// $headers["To"]      = "testlr@senduvu.com"; 
// $headers["Subject"] = "Test message"; 

// $body = "TEST MESSAGE!!!"; 

// $params["host"] = "smtp-mail.outlook.com"; 
// $params["port"] = "587"; 
// $params["auth"] = true; 
// $params["username"] = "recipe_cookbook@hotmail.com"; 
// $params["password"] = "recipecookbook123"; 

function send_mail($to, $subject, $msg){
    try {
        $mail = new PHPMailer(true);
        //Server settings
        $from = 'support@dev.halal.masumparvej.me';
        
        $mail->SMTPDebug = 3;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.dreamhost.com';                        // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $from;                                   // SMTP username
        $mail->Password   = 'wr9l9HbCaaclgwuazz';                               // SMTP password
        $mail->SMTPSecure = 'ssl';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom($from, 'SMTP Email Test');
        // $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
        $mail->addAddress($to);               // Name is optional
        $mail->addReplyTo($from, 'HomCook');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');

        // Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $msg;
        
        $mail->send();
        // echo 'Message has been sent';
        return true;
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>