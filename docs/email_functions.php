<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

function sendBookingNotification($clientName, $service, $phone) {
    $mail = new PHPMailer(true);

    try {
        // --- SMTP Server Settings ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Or your provider
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; 
        $mail->Password   = 'your-app-password'; // Use an App Password, not your login pass
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Recipients ---
        $mail->setFrom('system@kingkayo.com', 'King Kayo System');
        $mail->addAddress('admin-email@kingkayo.com'); // Where YOU get the alert

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = "New Booking Alert: $service";
        
        // Modern HTML Email Body
        $mail->Body = "
            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee;'>
                <h2 style='color: #D4AF37;'>New Service Request</h2>
                <p><strong>Client:</strong> $clientName</p>
                <p><strong>Service:</strong> $service</p>
                <p><strong>Contact:</strong> $phone</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>Log in to the dashboard to confirm this booking.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}