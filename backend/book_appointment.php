<?php
session_start();
require_once 'connect.php';

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
$workerId = $input['workerId'] ?? 2; // Default to Vanja (2) if not provided
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
            // Update info
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';

            $stmt = $pdo->prepare("UPDATE User SET name = ?, last_name = ?, phone = ? WHERE idUser = ?");
            $stmt->execute([$firstName, $lastName, $phone, $userId]);
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
        
        // Let's make the email look a bit more "sleek" with HTML
        $mail->Body    = "
        <div style='font-family: Arial, sans-serif; color: #333;'>
            <h2 style='color: #C5A76A;'>Hvala Vam na rezervaciji, $name!</h2>
            <p>Vaš termin u <strong>Opus in te</strong> je uspješno rezervisan.</p>
            <hr>
            <p><strong>Detalji termina:</strong></p>
            <ul>
                <li><strong>Usluga:</strong> $serviceName</li>
                <li><strong>Datum:</strong> $date</li>
                <li><strong>Vrijeme:</strong> $time</li>
                <li><strong>Cijena:</strong> $servicePrice KM</li>
                <li><strong>Lokacija:</strong> $location</li>
            </ul>
            <hr>
            <p>Ukoliko imate bilo kakvih pitanja ili želite otkazati termin, molimo Vas da nas kontaktirate.</p>
            <p><em>Srdačan pozdrav,<br>Opus in te Tim</em></p>
        </div>";
        
        $mail->AltBody = "Poštovani $name, Hvala Vam na rezervaciji... (plain text version)";

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