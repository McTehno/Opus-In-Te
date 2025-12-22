<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['name']) || !isset($input['price'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$id = $input['id'];
$name = trim($input['name']);
$price = floatval($input['price']);
$duration = $input['duration'];

// Handle duration
if ($duration === 'null' || $duration === null || $duration === '') {
    $duration = null;
} else {
    $duration = intval($duration);
}

try {
    $sql = "UPDATE Appointment_Type SET name = ?, price = ?, duration = ? WHERE idAppointment_Type = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $price, $duration, $id]);

    echo json_encode(['success' => true, 'message' => 'Service updated successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
