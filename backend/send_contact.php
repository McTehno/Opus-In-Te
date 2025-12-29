<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Load mail configuration
if (file_exists('mail_config.php')) {
    require_once 'mail_config.php';
} else {
    // Fallback defaults
    define('SMTP_HOST', 'mailhog');
    define('SMTP_USERNAME', '');
    define('SMTP_PASSWORD', '');
    define('SMTP_PORT', 1025);
    define('SMTP_SECURE', '');
    define('SMTP_FROM_EMAIL', 'info@opusinte.ba');
    define('SMTP_FROM_NAME', 'Opus In Te');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$message = trim($input['message'] ?? '');

if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'message' => 'Molimo ispunite sva obavezna polja.']);
    exit;
}

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();                                            
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = !empty(SMTP_USERNAME);
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    
    if (SMTP_SECURE === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif (SMTP_SECURE === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = '';
    }
    
    $mail->Port       = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    // Recipients
    // Send TO the system email (from config)
    $mail->setFrom(SMTP_FROM_EMAIL, 'Opus In Te Contact Form');
    $mail->addAddress(SMTP_FROM_EMAIL); 
    
    // Set Reply-To to the user's email so we can reply directly
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);                                  
    $mail->Subject = "Nova poruka sa web sajta: $name";
    
    // Styled Email Body
    $mail->Body    = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: 'Montserrat', Arial, sans-serif; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
            .header { background-color: #1a1a1a; padding: 20px; text-align: center; }
            .header h2 { color: #C5A76A; margin: 0; font-family: 'Playfair Display', serif; }
            .content { padding: 30px; background-color: #ffffff; }
            .intro { font-size: 16px; line-height: 1.5; }
            .details { background-color: #f9f9f9; padding: 20px; border-left: 4px solid #C5A76A; margin: 20px 0; }
            .details p { margin: 5px 0; }
            .link { color: #C5A76A; text-decoration: none; }
            .message-title { color: #1a1a1a; border-bottom: 1px solid #eee; padding-bottom: 10px; }
            .message-body { font-size: 15px; line-height: 1.6; color: #555; white-space: pre-wrap; }
            .footer { background-color: #f4f4f4; padding: 15px; text-align: center; font-size: 12px; color: #888; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nova Kontakt Poruka</h2>
            </div>
            <div class='content'>
                <p class='intro'>Primili ste novu poruku putem kontakt forme na sajtu.</p>
                
                <div class='details'>
                    <p><strong>Ime i Prezime:</strong> $name</p>
                    <p><strong>Email:</strong> <a href='mailto:$email' class='link'>$email</a></p>
                    <p><strong>Telefon:</strong> " . ($phone ? $phone : 'Nije navedeno') . "</p>
                </div>

                <h3 class='message-title'>Poruka:</h3>
                <p class='message-body'>$message</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Opus in te. Ova poruka je automatski generisana.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $mail->AltBody = "Nova poruka od: $name\nEmail: $email\nTelefon: $phone\n\nPoruka:\n$message";

    $mail->send();
    
    echo json_encode(['success' => true, 'message' => 'Vaša poruka je uspješno poslana!']);

} catch (Exception $e) {
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    echo json_encode(['success' => false, 'message' => 'Došlo je do greške prilikom slanja poruke. Molimo pokušajte ponovo kasnije.', 'error' => $mail->ErrorInfo]);
}
