<?php

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($name) || empty($lastname) || empty($phone) || empty($email) || empty($password)) {
        $error_message = "Sva polja su obavezna.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Neispravan format email adrese.";
    } elseif (strlen($password) < 8) {
        $error_message = "Lozinka mora imati najmanje 8 karaktera.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT idUser FROM User WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = "Korisnik sa ovim emailom već postoji.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role_id = 3; // Default role: user

            // Insert new user
            $sql = "INSERT INTO User (name, last_name, phone, email, pass, Role_idRole) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            try {
                if ($stmt->execute([$name, $lastname, $phone, $email, $hashed_password, $role_id])) {
                    $success_message = "Registracija uspješna! Možete se prijaviti.";
                    // Optional: Redirect to login page after a delay or show a link
                    // header("Location: Login.php");
                    // exit;
                } else {
                    $error_message = "Došlo je do greške prilikom registracije.";
                }
            } catch (PDOException $e) {
                $error_message = "Greška baze podataka: " . $e->getMessage();
            }
        }
    }
}
?>