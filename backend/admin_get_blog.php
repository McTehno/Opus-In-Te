<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing ID']);
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
                bp.User_idUser AS author_id,
                bp.Blog_Post_Status_idBlog_Post_Status AS status_id,
                u.name AS author_name, 
                u.last_name AS author_lastname,
                bps.name AS status_name,
                GROUP_CONCAT(bpc.name ORDER BY bpc.name SEPARATOR ', ') AS category_names,
                GROUP_CONCAT(bpc.idBlog_Post_Category) AS category_ids
            FROM Blog_Post bp
            JOIN User u ON bp.User_idUser = u.idUser
            JOIN Blog_Post_Status bps ON bp.Blog_Post_Status_idBlog_Post_Status = bps.idBlog_Post_Status
            LEFT JOIN Blog_Post_Blog_Post_Category bpbpc ON bp.idBlog_Post = bpbpc.Blog_Post_idBlog_Post
            LEFT JOIN Blog_Post_Category bpc ON bpbpc.Blog_Post_Category_idBlog_Post_Category = bpc.idBlog_Post_Category
            WHERE bp.idBlog_Post = ?
            GROUP BY bp.idBlog_Post";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($blog) {
        $blog['category_ids'] = $blog['category_ids']
            ? array_map('intval', explode(',', $blog['category_ids']))
            : [];

        echo json_encode($blog);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Blog post not found']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
