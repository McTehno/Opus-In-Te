<?php
require_once 'connect.php';

header('Content-Type: application/json');

try {
    // 1. Fetch Categories and Statuses (for initial render)
    $categories = [];
    $stmt = $pdo->query("SELECT * FROM Blog_Post_Category ORDER BY name");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }

    $statuses = [];
    $stmt = $pdo->query("SELECT * FROM Blog_Post_Status ORDER BY idBlog_Post_Status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $statuses[] = $row;
    }

    // 2. Build Query Logic
    $whereClause = " WHERE 1=1";
    $queryParams = [];

    // Search
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $whereClause .= " AND (bp.title LIKE ? OR u.name LIKE ? OR u.last_name LIKE ?)";
        $queryParams[] = $search;
        $queryParams[] = $search;
        $queryParams[] = $search;
    }

    // Categories (Filter by ID)
    if (isset($_GET['categories']) && !empty($_GET['categories'])) {
        $catIds = is_array($_GET['categories']) ? $_GET['categories'] : explode(',', $_GET['categories']);
        $catIds = array_map('intval', $catIds);
        // Filter out invalid IDs if any
        $catIds = array_filter($catIds);
        
        if (!empty($catIds)) {
            $placeholders = implode(',', array_fill(0, count($catIds), '?'));
            $whereClause .= " AND EXISTS (
                SELECT 1 FROM Blog_Post_Blog_Post_Category sub_bpbpc 
                WHERE sub_bpbpc.Blog_Post_idBlog_Post = bp.idBlog_Post 
                AND sub_bpbpc.Blog_Post_Category_idBlog_Post_Category IN ($placeholders)
            )";
            $queryParams = array_merge($queryParams, $catIds);
        }
    }

    // Statuses
    if (isset($_GET['statuses']) && !empty($_GET['statuses'])) {
        $statusIds = is_array($_GET['statuses']) ? $_GET['statuses'] : explode(',', $_GET['statuses']);
        $statusIds = array_map('intval', $statusIds);
        $statusIds = array_filter($statusIds);
        
        if (!empty($statusIds)) {
            $placeholders = implode(',', array_fill(0, count($statusIds), '?'));
            $whereClause .= " AND bp.Blog_Post_Status_idBlog_Post_Status IN ($placeholders)";
            $queryParams = array_merge($queryParams, $statusIds);
        }
    }

    // Get Stats (Min/Max Views) for the current filtered set
    $statsSql = "SELECT MIN(bp.viewcount) as min_views, MAX(bp.viewcount) as max_views 
                 FROM Blog_Post bp 
                 JOIN User u ON bp.User_idUser = u.idUser" . $whereClause;
    
    $stmtStats = $pdo->prepare($statsSql);
    $stmtStats->execute($queryParams);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    
    $minViews = $stats['min_views'] ?? 0;
    $maxViews = $stats['max_views'] ?? 0;

    // 4. Apply View Count Filter to Main Query
    if (isset($_GET['min_view_filter']) && is_numeric($_GET['min_view_filter'])) {
        $whereClause .= " AND bp.viewcount >= ?";
        $queryParams[] = $_GET['min_view_filter'];
    }

    // Final Main Query
    $sql = "SELECT 
                bp.idBlog_Post, 
                bp.title, 
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
            " . $whereClause . "
            GROUP BY bp.idBlog_Post
            ORDER BY bp.date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($queryParams);
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'categories' => $categories,
        'statuses' => $statuses,
        'blogs' => $blogs,
        'range_min' => $minViews,
        'range_max' => $maxViews
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
