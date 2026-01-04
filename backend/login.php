<?php
require_once __DIR__ . '/admin_config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /korisnicki-panel"); // Or index.php
    exit;
}
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: /admin-panel");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Molimo unesite email i lozinku.";
    } else {
        // Admin Check
        if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
            $_SESSION['is_admin'] = true;
            header("Location: /admin-panel");
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['pass'])) {
            // Password is correct
            $_SESSION['user_id'] = $user['idUser'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_lastname'] = $user['last_name'];
            $_SESSION['user_role'] = $user['Role_idRole'];

            header("Location: /korisnicki-panel"); // Redirect to dashboard
            exit;
        } else {
            $error_message = "Pogrešan email ili lozinka.";
        }
    }
}
?>