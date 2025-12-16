<?php
session_start();
require_once 'connect.php';

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
            $stmt = $pdo->prepare("UPDATE User SET name = ?, phone = ? WHERE idUser = ?");
            $stmt->execute([$name, $phone, $userId]);
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

    // Send Confirmation Email
    $to = $email;
    $subject = "Potvrda rezervacije termina - Opus in te";
    
    // Fetch service name for email
    $stmt = $pdo->prepare("SELECT name, price FROM Appointment_Type WHERE idAppointment_Type = ?");
    $stmt->execute([$serviceId]);
    $serviceData = $stmt->fetch(PDO::FETCH_ASSOC);
    $serviceName = $serviceData['name'] ?? 'Usluga';
    $servicePrice = $serviceData['price'] ?? '0';

    $message = "
    Poštovani/a $name,

    Hvala Vam na rezervaciji termina u Opus in te.

    Detalji Vašeg termina:
    Usluga: $serviceName
    Datum: $date
    Vrijeme: $time
    Cijena: $servicePrice KM
    Lokacija: $location

    Ukoliko imate bilo kakvih pitanja ili želite otkazati termin, molimo Vas da nas kontaktirate.

    Srdačan pozdrav,
    Opus in te Tim
    ";

    $headers = "From: no-reply@opusinte.ba" . "\r\n" .
               "Reply-To: info@opusinte.ba" . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Attempt to send email (suppress errors to avoid breaking JSON response)
    @mail($to, $subject, $message, $headers);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>