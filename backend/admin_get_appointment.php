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
    $stmt = $pdo->prepare("
        SELECT 
            a.idAppointment,
            a.datetime,
            a.Appointment_Type_idAppointment_Type,
            a.Appointment_Status_idAppointment_Status,
            a.Address_idAddress,
            at.duration,
            at.name as type_name,
            (SELECT au.User_idUser 
             FROM Appointment_User au 
             JOIN User u ON au.User_idUser = u.idUser 
             WHERE au.Appointment_idAppointment = a.idAppointment 
             AND u.Role_idRole = 2 
             LIMIT 1) as worker_id
        FROM Appointment a
        JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
        WHERE a.idAppointment = ?
    ");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment) {
        // Split datetime into date and time
        $dt = new DateTime($appointment['datetime']);
        $appointment['date'] = $dt->format('Y-m-d');
        $appointment['time'] = $dt->format('H:i');
        
        echo json_encode(['success' => true, 'data' => $appointment]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
