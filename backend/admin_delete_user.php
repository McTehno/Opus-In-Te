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
    // Fetch user info before deleting to get picture path
    $stmt = $pdo->prepare("SELECT Role_idRole, picture_path, name, last_name FROM User WHERE idUser = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $sql = "DELETE FROM User WHERE idUser = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // If worker (Role 2), delete picture folder
    if ($user['Role_idRole'] == 2) {
        $folderName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($user['name'] . $user['last_name']));
        $dirPath = '../img/workerpic/' . $folderName . '/';

        if (is_dir($dirPath)) {
            // Delete all files in directory
            $files = glob($dirPath . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            // Remove directory
            rmdir($dirPath);
        }
    }

    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);

} catch (PDOException $e) {
    if ($e->getCode() == '23000') { // Integrity constraint violation
        echo json_encode(['success' => false, 'message' => 'Nije moguće obrisati korisnika jer ima povezane termine.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
