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

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

try {
    // Delete related records first if foreign keys don't cascade
    // Appointment_User
    $stmt = $pdo->prepare("DELETE FROM Appointment_User WHERE Appointment_idAppointment = ?");
    $stmt->execute([$id]);
    
    // Appointment
    $stmt = $pdo->prepare("DELETE FROM Appointment WHERE idAppointment = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
