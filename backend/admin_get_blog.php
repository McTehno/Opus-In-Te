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
                u.name as author_name, 
                u.last_name as author_lastname,
                bps.name as status_name,
                GROUP_CONCAT(bpc.name SEPARATOR ', ') as category_names
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
        echo json_encode($blog);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Blog post not found']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
