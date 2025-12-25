<?php
session_start();
require_once 'connect.php';

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
date_default_timezone_set('Europe/Sarajevo'); // Fix timezone bug

$input = json_decode(file_get_contents('php://input'), true);

// Extract inputs
$workerId = $input['workerId'] ?? null;
$patientName = trim($input['patientName'] ?? '');
$patientEmail = trim($input['patientEmail'] ?? '');
$patientPhone = trim($input['patientPhone'] ?? '');
$typeId = $input['typeId'] ?? null;
$statusId = $input['statusId'] ?? null;
$locationId = $input['locationId'] ?? null; // 1, 2, or NULL (Online)
$date = $input['date'] ?? null;
$time = $input['time'] ?? null;

// Validation
if (!$workerId || !$patientName || !$patientEmail || !$patientPhone || !$typeId || !$statusId || !$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Sva polja su obavezna.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Check Availability
    // Get duration of the new appointment type
    $stmt = $pdo->prepare("SELECT duration FROM Appointment_Type WHERE idAppointment_Type = ?");
    $stmt->execute([$typeId]);
    $duration = $stmt->fetchColumn();
    if ($duration === false) throw new Exception("Invalid Appointment Type");
    $duration = $duration ? (int)$duration : 60; // Default 60 if null

    $newStart = new DateTime("$date $time");
    $newEnd = clone $newStart;
    $newEnd->modify("+$duration minutes");

    // Check against existing appointments for this worker
    // We need to check overlap: (StartA < EndB) and (EndA > StartB)
    // Existing appointments:
    $sql = "
        SELECT a.datetime, t.duration 
        FROM Appointment a
        JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
        JOIN Appointment_Type t ON a.Appointment_Type_idAppointment_Type = t.idAppointment_Type
        WHERE au.User_idUser = ? 
        AND a.Appointment_Status_idAppointment_Status != 4 -- Ignore cancelled
        AND DATE(a.datetime) = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$workerId, $date]);
    $existingAppts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($existingAppts as $appt) {
        $existStart = new DateTime($appt['datetime']);
        $existDuration = $appt['duration'] ? (int)$appt['duration'] : 60;
        $existEnd = clone $existStart;
        $existEnd->modify("+$existDuration minutes");

        if ($newStart < $existEnd && $newEnd > $existStart) {
            throw new Exception("Doktor je već zauzet u ovom terminu.");
        }
    }

    // 2. Handle Patient User
    // Check if email exists
    $stmt = $pdo->prepare("SELECT idUser FROM User WHERE email = ?");
    $stmt->execute([$patientEmail]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        $userId = $existingUser['idUser'];
        // Update info
        $parts = explode(' ', $patientName, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';
        $stmt = $pdo->prepare("UPDATE User SET name = ?, last_name = ?, phone = ? WHERE idUser = ?");
        $stmt->execute([$firstName, $lastName, $patientPhone, $userId]);
    } else {
        // Create new user
        $parts = explode(' ', $patientName, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';
        $stmt = $pdo->prepare("INSERT INTO User (name, last_name, email, phone, Role_idRole, pass) VALUES (?, ?, ?, ?, 3, NULL)");
        $stmt->execute([$firstName, $lastName, $patientEmail, $patientPhone]);
        $userId = $pdo->lastInsertId();
    }

    // 3. Create Appointment
    $datetimeStr = $newStart->format('Y-m-d H:i:s');
    $locVal = ($locationId === 'NULL' || $locationId === null) ? null : $locationId;

    $stmt = $pdo->prepare("INSERT INTO Appointment (datetime, Address_idAddress, Appointment_Type_idAppointment_Type, Appointment_Status_idAppointment_Status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$datetimeStr, $locVal, $typeId, $statusId]);
    $appointmentId = $pdo->lastInsertId();

    // 4. Link Users
    $stmt = $pdo->prepare("INSERT INTO Appointment_User (Appointment_idAppointment, User_idUser) VALUES (?, ?)");
    $stmt->execute([$appointmentId, $userId]); // Patient
    $stmt->execute([$appointmentId, $workerId]); // Doctor

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>