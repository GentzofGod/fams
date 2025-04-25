<?php
require __DIR__ . '../../vendor/autoload.php';  // Adjust path if necessary

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $message) {
    // Validate email format
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'godwinowolabi777@gmail.com';  // Your email
        $mail->Password = 'wxjhxhylppslkgwx';  // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Use SSL encryption (not STARTTLS)
        $mail->Port = 465;  // Port for SSL
 
        $mail->setFrom('godwinowolabi777@gmail.com', 'Attendance System');
        $mail->addAddress($to);  // Recipient email address
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}

?>
