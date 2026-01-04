<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;
$date = $input['date'] ?? null;
$time = $input['time'] ?? null;
$typeId = $input['typeId'] ?? null;
$statusId = $input['statusId'] ?? null;
// Location can be null (Online), so we check if key exists, but input is JSON so isset works
$locationId = array_key_exists('locationId', $input) ? $input['locationId'] : null; 
// Note: locationId 'NULL' string from JS should be converted to actual null or handled. 
// Usually JS sends null or a value. If it sends "NULL" string, we need to handle it.

if (!$id || !$date || !$time || !$typeId || !$statusId) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Handle "NULL" string if passed, though ideally JS sends null
if ($locationId === 'NULL') $locationId = null;

try {
    $datetime = "$date $time:00";

    // Validate Availability
    // 1. Get Doctor ID and Duration of the NEW type
    $stmt = $pdo->prepare("
        SELECT 
            au.User_idUser as worker_id,
            at.duration
        FROM Appointment a
        JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
        JOIN User u ON au.User_idUser = u.idUser
        JOIN Appointment_Type at ON at.idAppointment_Type = ?
        WHERE a.idAppointment = ?
        AND u.Role_idRole = 2
    ");
    $stmt->execute([$typeId, $id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$info) {
         echo json_encode(['success' => false, 'message' => 'Appointment or Doctor not found']);
         exit;
    }
    
    $workerId = $info['worker_id'];
    $duration = $info['duration'];
    
    // 2. Check overlap
    $start = strtotime($datetime);
    $end = $start + ($duration * 60);
    $startStr = date('Y-m-d H:i:s', $start);
    $endStr = date('Y-m-d H:i:s', $end);
    
    $sql = "
        SELECT COUNT(*) 
        FROM Appointment a
        JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
        JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
        WHERE au.User_idUser = ?
        AND a.idAppointment != ?
        AND a.Appointment_Status_idAppointment_Status != 4
        AND (
            a.datetime < ? AND 
            DATE_ADD(a.datetime, INTERVAL at.duration MINUTE) > ?
        )
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$workerId, $id, $endStr, $startStr]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Izabrani termin je zauzet.']);
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE Appointment 
        SET 
            datetime = ?, 
            Appointment_Type_idAppointment_Type = ?, 
            Appointment_Status_idAppointment_Status = ?, 
            Address_idAddress = ?
        WHERE idAppointment = ?
    ");
    
    $stmt->execute([$datetime, $typeId, $statusId, $locationId, $id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
