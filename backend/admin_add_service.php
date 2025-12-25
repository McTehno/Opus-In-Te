<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$name = $data['name'] ?? '';
$price = $data['price'] ?? '';
$duration = $data['duration'] ?? null;

if (empty($name) || empty($price)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if ($duration === 'null' || $duration === '') {
    $duration = null;
}

try {
    $stmt = $pdo->prepare("INSERT INTO Appointment_Type (name, price, duration) VALUES (?, ?, ?)");
    $stmt->execute([$name, $price, $duration]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>