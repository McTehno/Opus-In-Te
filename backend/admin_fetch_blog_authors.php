<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $sql = "SELECT u.idUser, u.name, u.last_name
            FROM User u
            JOIN Role r ON u.Role_idRole = r.idRole
            WHERE r.name = 'radnik'
            ORDER BY u.last_name ASC, u.name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'authors' => $authors]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Greška pri preuzimanju autora: ' . $e->getMessage()]);
}
