<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Ensure user is logged in and is a worker (Role 2)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check role
$stmt = $pdo->prepare("SELECT Role_idRole FROM User WHERE idUser = ?");
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole != 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Search for clients (Role 3)
// Search by name, last_name, email, or phone
$sql = "
    SELECT idUser, name, last_name, email, phone 
    FROM User 
    WHERE Role_idRole = 3 
    AND (
        name LIKE :query OR 
        last_name LIKE :query OR 
        email LIKE :query OR 
        phone LIKE :query
    )
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$searchTerm = "%$query%";
$stmt->execute(['query' => $searchTerm]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($clients);
