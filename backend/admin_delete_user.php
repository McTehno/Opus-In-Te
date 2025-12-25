<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

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
    $sql = "DELETE FROM User WHERE idUser = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);

} catch (PDOException $e) {
    if ($e->getCode() == '23000') { // Integrity constraint violation
        echo json_encode(['success' => false, 'message' => 'Nije moguće obrisati korisnika jer ima povezane termine.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
