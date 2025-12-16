<?php
require_once 'backend/connect.php';
require 'backend/register.php';
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija | Opus in te</title>
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
        
</div>

    <main class="login-section">
        <div class="login-background"></div>
        <div class="login-form-wrapper">
            
            
            <h3>Kreirajte Vaš Nalog</h2>
            <p>Pridružite nam se i zakažite Vaš prvi termin.</p>

            <form action="#" method="POST">
                <div class="form-group new-field">
                    <label for="name">Ime</label>
                    <input type="text" id="name" name="name" required placeholder="Vaše ime">
                </div>
                <div class="form-group new-field">
                    <label for="lastname">Prezime</label>
                    <input type="text" id="lastname" name="lastname" required placeholder="Vaše prezime">
                </div>
                <div class="form-group new-field">
                    <label for="phone">Broj telefona</label>
                    <input type="tel" id="phone" name="phone" required placeholder="06x xxx xxx">
                </div>
                <div class="form-group">
                    <label for="email">Email Adresa</label>
                    <input type="email" id="email" name="email" required placeholder="npr. email@example.com">
                </div>
                <div class="form-group">
                    <label for="password">Lozinka</label>
                    <input type="password" id="password" name="password" required placeholder="Najmanje 8 karaktera">
                </div>
                <button type="submit" class="cta-button login-btn">Registrujte se</button>
                <a href="index.php" class="cta-button back-btn">Nazad</a> 
            </form>

            <div class="register-link">
                <p>Već imate profil? <a href="Login.php">Prijavite se</a></p>
                
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
<?php if (!empty($success_message)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification("<?php echo htmlspecialchars($success_message); ?>", "success");
    });
</script>
<?php endif; ?>
</body>
</html>