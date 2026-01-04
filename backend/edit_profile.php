<?php
// Handles fetching and updating profile (no password change).
// Requires: session started and $pdo available.

$error_message = '';
$success_message = '';
$member_since_label = 'Korisnik';
$show_success_modal = false;

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: /prijava');
    exit;
}

function fetch_user(PDO $pdo, int $user_id): ?array {
    $stmt = $pdo->prepare('SELECT `idUser`, `name`, `last_name`, `email`, `phone` FROM `User` WHERE `idUser` = ?');
    $stmt->execute([$user_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ?: null;
}

$user = fetch_user($pdo, $user_id);
if (!$user) {
    session_destroy();
    header('Location: /prijava');
    exit;
}

// Flash messages (after redirect)
if (!empty($_SESSION['profile_success'])) {
    $success_message = $_SESSION['profile_success'];
    unset($_SESSION['profile_success']);
}
if (!empty($_SESSION['profile_error'])) {
    $error_message = $_SESSION['profile_error'];
    unset($_SESSION['profile_error']);
}

$form_values = [
    'name' => $user['name'] ?? '',
    'last_name' => $user['last_name'] ?? '',
    'email' => $user['email'] ?? '',
    'phone' => $user['phone'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $form_values = [
        'name' => $name,
        'last_name' => $lastname,
        'email' => $email,
        'phone' => $phone,
    ];

    if ($name === '' || $lastname === '' || $email === '' || $phone === '') {
        $error_message = 'Sva polja su obavezna.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Neispravan format email adrese.';
    }

    if ($error_message === '') {
        // Ensure email is unique
        $stmt = $pdo->prepare('SELECT `idUser` FROM `User` WHERE `email` = ? AND `idUser` <> ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error_message = 'Korisnik sa ovim emailom već postoji.';
        }
    }

    if ($error_message === '') {
        try {
            $stmt = $pdo->prepare('UPDATE `User` SET `name` = ?, `last_name` = ?, `email` = ?, `phone` = ? WHERE `idUser` = ?');
            $stmt->execute([$name, $lastname, $email, $phone, $user_id]);

            // If nothing changed, inform user instead of showing a misleading success
            if ($stmt->rowCount() === 0) {
                $_SESSION['profile_error'] = 'Nije bilo promjena (podaci su isti ili korisnik nije pronađen).';
                session_write_close();
                header('Location: /uredi-profil');
                exit;
            }

            // PRG: store success in session and redirect to avoid resubmit
            $_SESSION['profile_success'] = 'Profil je uspješno ažuriran.';
            session_write_close();
            header('Location: /uredi-profil');
            exit;
        } catch (PDOException $e) {
            $error_message = 'Greška prilikom ažuriranja profila: ' . $e->getMessage();
            $_SESSION['profile_error'] = $error_message;
        }
    }
}

// Member since placeholder (no column available)
$member_since_label = 'Korisnik #' . $user['idUser'];

