<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Ensure user is logged in and is a worker (Role 2)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check role
$stmt = $pdo->prepare("SELECT Role_idRole FROM User WHERE idUser = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole != 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$workerId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$location = $input['location'] ?? '';
$clientName = trim($input['clientName'] ?? '');
$clientEmail = trim($input['clientEmail'] ?? '');
$clientPhone = trim($input['clientPhone'] ?? '');

$serviceId = $input['serviceId'] ?? '';
$date = $input['date'] ?? '';
$time = $input['time'] ?? '';
$statusId = $input['statusId'] ?? 1; // Default to 'Zakazano' (1)

if (!$location || !$clientName || !$clientEmail || !$clientPhone || !$serviceId || !$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Handle Client (Find or Create)
    $stmt = $pdo->prepare("SELECT idUser FROM User WHERE email = ?");
    $stmt->execute([$clientEmail]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    $clientId = null;

    if ($existingUser) {
        $clientId = $existingUser['idUser'];
        // User exists, do not update their profile data as per request
    } else {
        // Create new user
        $parts = explode(' ', $clientName, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';
        
        $stmt = $pdo->prepare("INSERT INTO User (name, last_name, email, phone, Role_idRole, pass) VALUES (?, ?, ?, ?, 3, NULL)");
        $stmt->execute([$firstName, $lastName, $clientEmail, $clientPhone]);
        $clientId = $pdo->lastInsertId();
    }

    // 2. Get Service Duration
    $stmt = $pdo->prepare("SELECT duration FROM Appointment_Type WHERE idAppointment_Type = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$service) {
        throw new Exception("Invalid service selected.");
    }
    $duration = $service['duration'];

    // 3. Check Overlap (CRITICAL)
    $startDateTime = new DateTime("$date $time");
    $endDateTime = clone $startDateTime;
    $endDateTime->modify("+$duration minutes");

    $startStr = $startDateTime->format('Y-m-d H:i:s');
    $endStr = $endDateTime->format('Y-m-d H:i:s');

    // Check overlap for the worker
    $sql = "
        SELECT COUNT(*) 
        FROM Appointment a
        JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
        JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
        WHERE au.User_idUser = ?
        AND a.Appointment_Status_idAppointment_Status != 4
        AND (
            (a.datetime < ? AND DATE_ADD(a.datetime, INTERVAL at.duration MINUTE) > ?)
        )
    ";
    // Logic: (StartA < EndB) AND (EndA > StartB)
    // Existing appt: StartA = a.datetime, EndA = a.datetime + duration
    // New appt: StartB = $startStr, EndB = $endStr
    // Overlap if: a.datetime < $endStr AND (a.datetime + duration) > $startStr

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$workerId, $endStr, $startStr]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Termin se preklapa sa postojećim terminom.");
    }

    // 4. Create Appointment
    // Handle Location
    $addressId = null;
    if ($location === 'Banja Luka') {
        $addressId = 1;
    } elseif ($location === 'Prijedor') {
        $addressId = 2;
    }
    // Online = null

    $stmt = $pdo->prepare("
        INSERT INTO Appointment (datetime, Appointment_Status_idAppointment_Status, Appointment_Type_idAppointment_Type, Address_idAddress) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$startStr, $statusId, $serviceId, $addressId]);
    $appointmentId = $pdo->lastInsertId();

    // 5. Link Users (Worker AND Client)
    $stmt = $pdo->prepare("INSERT INTO Appointment_User (Appointment_idAppointment, User_idUser) VALUES (?, ?)");
    
    // Link Worker
    $stmt->execute([$appointmentId, $workerId]);
    
    // Link Client
    $stmt->execute([$appointmentId, $clientId]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Termin uspješno zakazan.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
