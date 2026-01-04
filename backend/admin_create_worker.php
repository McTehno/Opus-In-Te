<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($name) || empty($lastname) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Sva polja osim telefona su obavezna.']);
    exit;
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT idUser FROM User WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Korisnik sa ovim emailom već postoji.']);
    exit;
}

// Handle File Upload
$picturePath = null;
if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['picture']['tmp_name'];
    $fileName = $_FILES['picture']['name'];
    $fileSize = $_FILES['picture']['size'];
    $fileType = $_FILES['picture']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
    if (in_array($fileExtension, $allowedfileExtensions)) {
        // Create directory: img/workerpic/{name}-{lastname}/
        // Sanitize folder name
        $folderName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name . $lastname));
        $uploadFileDir = '../img/workerpic/' . $folderName . '/';
        
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $dest_path = $uploadFileDir . $newFileName;

        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            // Save relative path for DB (remove ../)
            $picturePath = 'img/workerpic/' . $folderName . '/' . $newFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Greška pri čuvanju slike.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nedozvoljen format slike.']);
        exit;
    }
}

// Hash Password
$hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

try {
    $stmt = $pdo->prepare("INSERT INTO User (name, last_name, email, phone, pass, Role_idRole, picture_path) VALUES (?, ?, ?, ?, ?, 2, ?)");
    $stmt->execute([$name, $lastname, $email, $phone, $hashedPassword, $picturePath]);
    
    echo json_encode(['success' => true, 'message' => 'Radnik uspješno kreiran.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>