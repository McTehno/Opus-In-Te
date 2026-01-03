<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if (empty($id) || empty($name) || empty($lastname) || empty($phone) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Sva polja su obavezna.']);
        exit;
    }

    // Check email uniqueness
    $stmt = $pdo->prepare("SELECT idUser FROM User WHERE email = ? AND idUser != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email već postoji.']);
        exit;
    }

    try {
        // Fetch current user data
        $stmt = $pdo->prepare("SELECT * FROM User WHERE idUser = ?");
        $stmt->execute([$id]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentUser) {
            echo json_encode(['success' => false, 'message' => 'Korisnik nije pronađen.']);
            exit;
        }

        $picturePath = $currentUser['picture_path'];
        $roleId = $currentUser['Role_idRole'];

        // Handle Picture Upload (Only for Workers - Role 2)
        if ($roleId == 2) {
            // Check if name/lastname changed to rename folder
            $oldFolderName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($currentUser['name'] . $currentUser['last_name']));
            $newFolderName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name . $lastname));
            
            $baseDir = '../img/workerpic/';
            $oldDir = $baseDir . $oldFolderName . '/';
            $newDir = $baseDir . $newFolderName . '/';

            // If name changed, rename directory
            if ($oldFolderName !== $newFolderName && is_dir($oldDir)) {
                rename($oldDir, $newDir);
                // Update picture path to reflect new directory
                if ($picturePath) {
                    $picturePath = str_replace($oldFolderName, $newFolderName, $picturePath);
                }
            } elseif (!is_dir($newDir)) {
                // Create new dir if it doesn't exist (e.g. if user didn't have a folder before)
                mkdir($newDir, 0777, true);
            }

            // Handle new file upload
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['picture']['tmp_name'];
                $fileName = $_FILES['picture']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
                $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

                if (in_array($fileExtension, $allowedfileExtensions)) {
                    // Delete old picture if exists
                    if ($picturePath && file_exists('../' . $picturePath)) {
                        unlink('../' . $picturePath);
                    }

                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $newDir . $newFileName;

                    if(move_uploaded_file($fileTmpPath, $dest_path)) {
                        $picturePath = 'img/workerpic/' . $newFolderName . '/' . $newFileName;
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Greška pri čuvanju slike.']);
                        exit;
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Nedozvoljen format slike.']);
                    exit;
                }
            }
        }

        $sql = "UPDATE User SET name = ?, last_name = ?, phone = ?, email = ?, picture_path = ? WHERE idUser = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $lastname, $phone, $email, $picturePath, $id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Greška prilikom ažuriranja.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
