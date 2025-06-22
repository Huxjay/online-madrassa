<?php


require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function sendApprovalEmail($toEmail, $toName) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'husseinjuma0097@gmail.com';         // ✅ my Gmail
        $mail->Password   = 'gmgu qtym jetc rgld';           // ✅ App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender & Recipient
        $mail->setFrom('husseinjuma0097@gmail.com', 'Online-madrassa');
        $mail->addAddress($toEmail, $toName);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = '✅ Approval Notification - Online-madrassa';
        $mail->Body    = "
            <h3>Assalamu Alaikum $toName,</h3>
            <p>Your teacher account has been <strong>approved</strong> by the admin.</p>
            <p>You may now <a href='http://localhost/online-madrassa/'>log in</a> and start teaching.</p>
            <p>JazakAllah Khair,<br>Online Madrassa Team</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
echo "<pre>Email error: " . $mail->ErrorInfo . "</pre>";
exit;
    }
}
?>