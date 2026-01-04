<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check for Admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    // Prevent redirect loop if already on AdminDashboard
    if ($_SERVER['REQUEST_URI'] !== '/admin-panel') {
        header("Location: /admin-panel");
        exit;
    }
}

// 2. Check for Worker (Radnik)
if (isset($_SESSION['user_id'])) {
    // We need to check the role from the database if it's not explicitly stored as a name
    // Assuming we have a DB connection available. If not, we might need to include it.
    // However, including connect.php here might cause double inclusion issues if the parent file also includes it.
    // Best practice: Check if $pdo is set, if not, require it.
    
    if (!isset($pdo)) {
        // Try to find connect.php relative to this file or document root
        $possible_paths = [
            __DIR__ . '/connect.php',
            __DIR__ . '/../backend/connect.php',
            $_SERVER['DOCUMENT_ROOT'] . '/backend/connect.php'
        ];
        
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                break;
            }
        }
    }

    if (isset($pdo)) {
        $stmt = $pdo->prepare("
            SELECT r.name as role_name 
            FROM User u 
            JOIN Role r ON u.Role_idRole = r.idRole 
            WHERE u.idUser = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $role = $stmt->fetchColumn();

        if ($role === 'radnik') {
            // Prevent redirect loop
            if ($_SERVER['REQUEST_URI'] !== '/radni-panel') {
                header("Location: /radni-panel");
                exit;
            }
        }
    }
}
?>