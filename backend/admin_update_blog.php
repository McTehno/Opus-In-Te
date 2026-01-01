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

if (!$input || !isset($input['id'], $input['title'], $input['contents'], $input['author_id'], $input['status_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nedostaju obavezna polja.']);
    exit;
}

$blogId = intval($input['id']);
$title = trim($input['title']);
$contents = $input['contents'];
$authorId = intval($input['author_id']);
$statusId = intval($input['status_id']);
$categoryIds = isset($input['category_ids']) && is_array($input['category_ids'])
    ? array_map('intval', $input['category_ids'])
    : [];

if ($title === '' || $contents === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Naslov i sadržaj ne mogu biti prazni.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $updateSql = "UPDATE Blog_Post 
                  SET title = ?, contents = ?, User_idUser = ?, Blog_Post_Status_idBlog_Post_Status = ?
                  WHERE idBlog_Post = ?";
    $stmt = $pdo->prepare($updateSql);
    $stmt->execute([$title, $contents, $authorId, $statusId, $blogId]);

    // Reset categories
    $deleteSql = "DELETE FROM Blog_Post_Blog_Post_Category WHERE Blog_Post_idBlog_Post = ?";
    $stmtDelete = $pdo->prepare($deleteSql);
    $stmtDelete->execute([$blogId]);

    if (!empty($categoryIds)) {
        $insertSql = "INSERT INTO Blog_Post_Blog_Post_Category (Blog_Post_idBlog_Post, Blog_Post_Category_idBlog_Post_Category)
                      VALUES (?, ?)";
        $stmtInsert = $pdo->prepare($insertSql);
        foreach ($categoryIds as $catId) {
            $stmtInsert->execute([$blogId, $catId]);
        }
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Objava je uspješno ažurirana.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Greška pri ažuriranju: ' . $e->getMessage()]);
}
