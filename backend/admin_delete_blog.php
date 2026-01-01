<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nedostaje ID objave.']);
    exit;
}

$blogId = intval($input['id']);

try {
    $stmt = $pdo->prepare("DELETE FROM Blog_Post WHERE idBlog_Post = ?");
    $stmt->execute([$blogId]);

    echo json_encode(['success' => true, 'message' => 'Objava je obrisana.']);
} catch (PDOException $e) {
    $message = $e->getCode() === '23000'
        ? 'Objava se ne može obrisati zbog povezanih zapisa.'
        : 'Greška: ' . $e->getMessage();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $message]);
}
