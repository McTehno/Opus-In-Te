<?php
session_start();
require_once 'backend/connect.php';
require 'backend/login.php';
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava | Opus in te</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body class ="login-body">
<div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loading.gif" alt="Loading..." class="loading-logo"/>
        </div> 
        <p>Učitavanje...</p>
</div>

    

    <main class="login-section">
        <div class="login-background"></div>
            <div class="login-form-wrapper">

    <h3>Prijava</h2>
        <p>Unesite svoje podatke</p>

    <form action="#" method="POST">
        <div class="form-group">
            <label for="email">Email Adresa</label>
            <input type="email" id="email" name="email" required placeholder="npr. email@example.com">
        </div>
        <div class="form-group">
            <label for="password">Lozinka</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="cta-button login-btn">Prijavite se</button>
        
        <a href="index.php" class="cta-button back-btn">Nazad</a> 
    </form>
    <div class="register-link">
        <p>Nemate profil? <a href="Register.php">Registrujte se</a></p>
    </div>

</div>
    </main>

    
<script src="js/login_animations.js"></script>
<script src="js/loading_screen.js"></script>
<script src="js/notifications.js"></script>
<?php if (!empty($error_message)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("<?php echo htmlspecialchars($error_message); ?>", "error");
    });
</script>
<?php endif; ?>
</body>
</html>