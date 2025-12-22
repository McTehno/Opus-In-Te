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
