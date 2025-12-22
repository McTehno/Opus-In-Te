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

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
    exit;
}

$id = $input['id'];

try {
    $sql = "DELETE FROM Appointment_Type WHERE idAppointment_Type = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Service deleted successfully']);

} catch (PDOException $e) {
    // Check for foreign key constraint violation (Error 1451)
    if ($e->getCode() == '23000') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete service because there are appointments associated with it.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
