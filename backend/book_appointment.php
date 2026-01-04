<?php
session_start();
require_once 'connect.php';
require_once 'app_config.php';

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
    // Fallback defaults or error handling
    define('SMTP_HOST', 'mailhog');
    define('SMTP_USERNAME', '');
    define('SMTP_PASSWORD', '');
    define('SMTP_PORT', 1025);
    define('SMTP_SECURE', '');
    define('SMTP_FROM_EMAIL', 'info@opusinte.ba');
    define('SMTP_FROM_NAME', 'Opus In Te');
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$location = $input['location'] ?? '';
$serviceId = $input['serviceId'] ?? '';
$date = $input['date'] ?? '';
$time = $input['time'] ?? '';
$workerId = $input['workerId']; 
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');

if (!$serviceId || !$date || !$time || !$name || !$email || !$phone) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Handle User
    $userId = null;
    
    // Check if logged in
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        // Optional: Update user info if changed?
        // For now, let's just update phone/name if they are empty in DB, or always update?
        // Let's update to ensure latest contact info is used.
        
        // Split name into first and last name to avoid duplication bug
        $parts = explode(' ', $name, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';

        $stmt = $pdo->prepare("UPDATE User SET name = ?, last_name = ?, email = ?, phone = ? WHERE idUser = ?");
        $stmt->execute([$firstName, $lastName, $email, $phone, $userId]);
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT idUser FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $userId = $existingUser['idUser'];
            // User exists, do not update their profile data
        } else {
            // Create new user
            $stmt = $pdo->prepare("INSERT INTO User (name, last_name, email, phone, Role_idRole, pass) VALUES (?, ?, ?, ?, 3, NULL)");
            // We don't have last_name in the form? The form has "Ime i Prezime" in one field.
            // We need to split it.
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';
            
            $stmt->execute([$firstName, $lastName, $email, $phone]);
            $userId = $pdo->lastInsertId();
        }
    }

    // 2. Handle Location
    $addressId = null;
    if ($location === 'Banja Luka') {
        $addressId = 1;
    } elseif ($location === 'Prijedor') {
        $addressId = 2;
    }
    // Online = null

    // 3. Create Appointment
    $datetime = "$date $time:00";
    
    // Get Appointment Type details (price) if needed, but we just need ID
    
    $stmt = $pdo->prepare("INSERT INTO Appointment (datetime, Address_idAddress, Appointment_Type_idAppointment_Type, Appointment_Status_idAppointment_Status) VALUES (?, ?, ?, 1)"); // 1 = unconfirmed
    $stmt->execute([$datetime, $addressId, $serviceId]);
    $appointmentId = $pdo->lastInsertId();

    // 4. Link Users (Client and Worker)
    $stmt = $pdo->prepare("INSERT INTO Appointment_User (Appointment_idAppointment, User_idUser) VALUES (?, ?)");
    
    // Link Client
    $stmt->execute([$appointmentId, $userId]);
    
    // Link Worker
    $stmt->execute([$appointmentId, $workerId]);

    $pdo->commit();

    // Fetch service details for email
    $stmt = $pdo->prepare("SELECT name, price FROM Appointment_Type WHERE idAppointment_Type = ?");
    $stmt->execute([$serviceId]);
    $serviceDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    $serviceName = $serviceDetails['name'] ?? 'Usluga';
    $servicePrice = $serviceDetails['price'] ?? '0';

    // Generate Token
    $token = hash_hmac('sha256', $appointmentId, APP_SECRET);
    
    // URLs
    $confirmUrl = BASE_URL . "/backend/confirm_appointment.php?id=$appointmentId&token=$token";
    $pdfUrl = BASE_URL . "/backend/generate_appointment_pdf.php?id=$appointmentId&token=$token";
    
    // QR Code (URL encoded) - Now points to the confirmation URL
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($confirmUrl);

    $mail = new PHPMailer(true);

    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();                                            
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = !empty(SMTP_USERNAME); // Only auth if username is set
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

        // Character encoding
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $name);     // Add the client

        // Content
        $mail->isHTML(true);                                  
        $mail->Subject = 'Potvrda rezervacije termina - Opus in te';
        
        // Styled Email Body
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
                .header { background-color: #2a2d2fff; padding: 30px; text-align: center; color: #ffffff; }
                .header h1 { margin: 0; font-size: 24px; font-weight: 300; letter-spacing: 1px; }
                .content { padding: 30px; color: #333333; line-height: 1.6; }
                .details { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #C5A76A; }
                .details p { margin: 8px 0; }
                .btn-container { text-align: center; margin: 30px 0; }
                .btn { 
                    display: inline-block; 
                    padding: 14px 35px; 
                    background-color: #C5A76A; 
                    color: #ffffff !important; 
                    text-decoration: none !important; 
                    border-radius: 50px; 
                    font-weight: bold; 
                    font-size: 16px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    transition: background-color 0.3s ease;
                }
                .btn:hover { background-color: #b0935b; }
                .qr-container { text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; }
                .qr-code { width: 150px; height: 150px; margin-top: 10px; }
                .footer { background-color: #333333; color: #888888; text-align: center; padding: 20px; font-size: 12px; }
                .footer a { color: #C5A76A; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Potvrda Rezervacije</h1>
                </div>
                <div class='content'>
                    <p>Poštovani/a <strong>$name</strong>,</p>
                    <p>Hvala Vam što ste izabrali <strong>Opus in te</strong>. Vaš termin je uspješno rezervisan.</p>
                    
                    <div class='details'>
                        <p><strong>Usluga:</strong> $serviceName</p>
                        <p><strong>Datum:</strong> $date</p>
                        <p><strong>Vrijeme:</strong> $time</p>
                        <p><strong>Cijena:</strong> $servicePrice KM</p>
                        <p><strong>Lokacija:</strong> $location</p>
                    </div>

                    <div class='btn-container'>
                        <a href='$confirmUrl' class='btn'>Potvrdi Termin</a>
                    </div>
                    
                    <p style='text-align: center; font-size: 14px; color: #666;'>Molimo Vas da potvrdite dolazak klikom na dugme iznad.</p>

                    <div class='qr-container'>
                        <p>Skenirajte QR kod za brzu potvrdu termina:</p>
                        <img src='$qrCodeUrl' alt='QR Code' class='qr-code'>
                    </div>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Opus in te. Sva prava zadržana.</p>
                    <p><a href='" . BASE_URL . "'>www.opusinte.ba</a></p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Poštovani $name, Hvala Vam na rezervaciji. Molimo potvrdite termin klikom na: $confirmUrl";

        $mail->send();
        
        // Success response
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // If mail fails, we still consider the booking successful, but warn the frontend? 
        // Or just log it. For now, let's return success but log error internally.
        // Ideally, you shouldn't tell the user 'Failed' if the DB insert worked.
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        echo json_encode(['success' => true, 'mail_error' => $mail->ErrorInfo]); 
    }

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>