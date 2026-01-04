<?php
require_once 'connect.php';

header('Content-Type: application/json');

try {
    // 1. Fetch Categories with Post Counts
    $categoriesQuery = "
        SELECT 
            c.idBlog_Post_Category, 
            c.name, 
            COUNT(bpc.Blog_Post_idBlog_Post) as count
        FROM Blog_Post_Category c
        LEFT JOIN Blog_Post_Blog_Post_Category bpc ON c.idBlog_Post_Category = bpc.Blog_Post_Category_idBlog_Post_Category
        LEFT JOIN Blog_Post bp ON bpc.Blog_Post_idBlog_Post = bp.idBlog_Post
        WHERE bp.Blog_Post_Status_idBlog_Post_Status = 2 OR bp.idBlog_Post IS NULL
        GROUP BY c.idBlog_Post_Category, c.name
    ";
    // Note: The WHERE clause filters counted posts to only published ones. 
    // However, if a category has no posts, it should still appear (LEFT JOIN).
    // But if it has posts that are NOT published, they shouldn't be counted.
    // The condition `bp.Blog_Post_Status_idBlog_Post_Status = 2` is on the joined table.
    // If there are no posts, bp.idBlog_Post is NULL.
    
    // Correct logic for counting only published posts:
    $categoriesQuery = "
        SELECT 
            c.idBlog_Post_Category, 
            c.name, 
            (SELECT COUNT(*) 
             FROM Blog_Post_Blog_Post_Category bpc 
             JOIN Blog_Post bp ON bpc.Blog_Post_idBlog_Post = bp.idBlog_Post 
             WHERE bpc.Blog_Post_Category_idBlog_Post_Category = c.idBlog_Post_Category 
             AND bp.Blog_Post_Status_idBlog_Post_Status = 2) as count
        FROM Blog_Post_Category c
    ";

    $stmt = $pdo->query($categoriesQuery);
    $categories = $stmt->fetchAll();

    // 2. Fetch Popular Posts (Top 3 by viewcount)
    $popularQuery = "
        SELECT idBlog_Post, title, picture_path, viewcount 
        FROM Blog_Post 
        WHERE Blog_Post_Status_idBlog_Post_Status = 2 
        ORDER BY viewcount DESC 
        LIMIT 3
    ";
    $stmt = $pdo->query($popularQuery);
    $popular = $stmt->fetchAll();

    // 3. Fetch ALL Published Posts (Client-side filtering/sorting)
    // We fetch everything and let Isotope handle the rest for smooth animations.
    // We include category IDs in the result for filtering.
    
    $sql = "
        SELECT DISTINCT
            bp.idBlog_Post, 
            bp.title, 
            bp.contents, 
            bp.date, 
            bp.viewcount, 
            bp.picture_path,
            GROUP_CONCAT(c.name SEPARATOR ', ') as category_names,
            GROUP_CONCAT(c.idBlog_Post_Category SEPARATOR ',') as category_ids
        FROM Blog_Post bp
        LEFT JOIN Blog_Post_Blog_Post_Category bpc ON bp.idBlog_Post = bpc.Blog_Post_idBlog_Post
        LEFT JOIN Blog_Post_Category c ON bpc.Blog_Post_Category_idBlog_Post_Category = c.idBlog_Post_Category
        WHERE bp.Blog_Post_Status_idBlog_Post_Status = 2
        GROUP BY bp.idBlog_Post
        ORDER BY bp.date DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Process posts to add excerpt
    foreach ($posts as &$post) {
        $post['excerpt'] = mb_substr(strip_tags($post['contents']), 0, 150) . '...';
        // Add a timestamp for easier JS sorting
        $post['timestamp'] = strtotime($post['date']);
        // Remove full contents to optimize JSON payload
        unset($post['contents']);
    }

    echo json_encode([
        'categories' => $categories,
        'popular' => $popular,
        'posts' => $posts
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
