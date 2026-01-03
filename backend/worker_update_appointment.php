<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify user is a worker
$stmt = $pdo->prepare("SELECT r.name as role_name FROM User u JOIN Role r ON u.Role_idRole = r.idRole WHERE u.idUser = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['role_name'] !== 'radnik') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'] ?? null;
    $status_id = $_POST['status_id'] ?? null;
    $type_id = $_POST['type_id'] ?? null;
    // Location fields could be added here, but for simplicity let's stick to status and type first, 
    // or handle address update if provided.
    
    if (!$appointment_id || !$status_id || !$type_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    try {
        // Verify the appointment belongs to this worker
        $checkStmt = $pdo->prepare("SELECT 1 FROM Appointment_User WHERE Appointment_idAppointment = ? AND User_idUser = ?");
        $checkStmt->execute([$appointment_id, $user_id]);
        if (!$checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found or not assigned to you']);
            exit;
        }

        // Update Appointment
        $updateStmt = $pdo->prepare("
            UPDATE Appointment 
            SET Appointment_Status_idAppointment_Status = ?, 
                Appointment_Type_idAppointment_Type = ?
            WHERE idAppointment = ?
        ");
        $updateStmt->execute([$status_id, $type_id, $appointment_id]);

        echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
