<?php
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: UserDashboard.php"); // Or index.php
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Molimo unesite email i lozinku.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['pass'])) {
            // Password is correct
            $_SESSION['user_id'] = $user['idUser'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_lastname'] = $user['last_name'];
            $_SESSION['user_role'] = $user['Role_idRole'];
            
            header("Location: UserDashboard.php"); // Redirect to dashboard
            exit;
        } else {
            $error_message = "Pogrešan email ili lozinka.";
        }
    }
}
?>