<?php
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT 
                bp.idBlog_Post, 
                bp.title, 
                bp.contents,
                bp.viewcount, 
                bp.date, 
                bp.picture_path,
                GROUP_CONCAT(c.name SEPARATOR ', ') as category_names
            FROM Blog_Post bp
            LEFT JOIN Blog_Post_Blog_Post_Category bpc ON bp.idBlog_Post = bpc.Blog_Post_idBlog_Post
            LEFT JOIN Blog_Post_Category c ON bpc.Blog_Post_Category_idBlog_Post_Category = c.idBlog_Post_Category
            WHERE bp.idBlog_Post = ? AND bp.Blog_Post_Status_idBlog_Post_Status = 2
            GROUP BY bp.idBlog_Post";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        echo json_encode($post);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
