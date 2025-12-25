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
        $sql = "UPDATE User SET name = ?, last_name = ?, phone = ?, email = ? WHERE idUser = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $lastname, $phone, $email, $id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Greška prilikom ažuriranja.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
