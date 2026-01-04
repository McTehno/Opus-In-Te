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

// Handle FormData input
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$contents = isset($_POST['contents']) ? $_POST['contents'] : '';
$authorId = isset($_POST['author_id']) ? intval($_POST['author_id']) : 0;
$statusId = isset($_POST['status_id']) ? intval($_POST['status_id']) : 0;
$categoryIds = isset($_POST['category_ids']) && is_array($_POST['category_ids'])
    ? array_map('intval', $_POST['category_ids'])
    : [];

if ($title === '' || $contents === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Naslov i sadržaj ne mogu biti prazni.']);
    exit;
}

// Handle Image Upload
$picturePath = 'img/blogplaceholder/blog_placeholder_2.jpg'; // Default

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../img/blogplaceholder/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($fileExt, $allowedExts)) {
        $newFileName = uniqid('blog_', true) . '.' . $fileExt;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $picturePath = 'img/blogplaceholder/' . $newFileName;
        }
    }
}

try {
    $pdo->beginTransaction();

    $currentDate = date('Y-m-d');

    $insertSql = "INSERT INTO Blog_Post (title, contents, date, viewcount, picture_path, User_idUser, Blog_Post_Status_idBlog_Post_Status) 
                  VALUES (?, ?, ?, 0, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute([$title, $contents, $currentDate, $picturePath, $authorId, $statusId]);
    
    $blogId = $pdo->lastInsertId();

    if (!empty($categoryIds)) {
        $insertCatSql = "INSERT INTO Blog_Post_Blog_Post_Category (Blog_Post_idBlog_Post, Blog_Post_Category_idBlog_Post_Category)
                      VALUES (?, ?)";
        $stmtInsert = $pdo->prepare($insertCatSql);
        foreach ($categoryIds as $catId) {
            $stmtInsert->execute([$blogId, $catId]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Nova objava je uspješno kreirana.', 
        'id' => $blogId,
        'picture_path' => $picturePath
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Greška pri kreiranju: ' . $e->getMessage()]);
}
