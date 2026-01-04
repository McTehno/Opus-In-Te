<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Ensure user is logged in and is a worker (Role 2)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check role
$stmt = $pdo->prepare("SELECT Role_idRole FROM User WHERE idUser = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole != 2) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$workerId = $_SESSION['user_id'];
$date = $_GET['date'] ?? '';
$duration = (int)($_GET['duration'] ?? 60);

if (!$date) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

// Parse date (expecting YYYY-MM-DD)
try {
    $dateTime = new DateTime($date);
    $formattedDate = $dateTime->format('Y-m-d');
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

// Working hours
$startHour = 9;
$endHour = 17;
$interval = 15; // minutes

// Fetch Appointments for the date for this worker
$sql = "
    SELECT 
        a.datetime, 
        at.duration
    FROM Appointment a
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
    WHERE DATE(a.datetime) = ?
    AND au.User_idUser = ?
    AND a.Appointment_Status_idAppointment_Status != 4 -- Exclude cancelled
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$formattedDate, $workerId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize appointments
$busySlots = [];
foreach ($appointments as $app) {
    // Handle NULL duration (e.g. reports) - default to 60 mins to be safe, or 0 if it shouldn't block
    // Assuming if it has a datetime, it blocks time.
    $apptDuration = $app['duration'] ? $app['duration'] : 60; 
    
    $busySlots[] = [
        'start' => strtotime($app['datetime']),
        'end' => strtotime($app['datetime']) + ($apptDuration * 60)
    ];
}

$availableSlots = [];

$currentTime = strtotime("$formattedDate $startHour:00:00");
$endTime = strtotime("$formattedDate $endHour:00:00");

while ($currentTime + ($duration * 60) <= $endTime) {
    $slotStart = $currentTime;
    $slotEnd = $currentTime + ($duration * 60);
    
    $isFree = true;
    foreach ($busySlots as $busy) {
        // Check overlap
        // Overlap if (StartA < EndB) and (EndA > StartB)
        if ($slotStart < $busy['end'] && $slotEnd > $busy['start']) {
            $isFree = false;
            break;
        }
    }

    if ($isFree) {
        $availableSlots[] = date('H:i', $slotStart);
    }

    $currentTime += ($interval * 60);
}

echo json_encode($availableSlots);
