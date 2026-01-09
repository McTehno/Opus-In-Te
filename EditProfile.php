<?php
session_start();
// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: /prijava");
    exit;
}
require_once 'backend/connect.php';
require_once 'backend/role_check.php';
require_once 'backend/edit_profile.php';
?>
<!DOCTYPE html>
<html lang="bs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uredi Profil | Opus in te</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
</head>

<body class="dashboard-body">
    <div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loader.gif" alt="Loading..." class="loading-logo" />
        </div>

    </div>

    <header class="main-header scrolled">
        <div class="container">
            <a href="/pocetna" class="logo-link">
                <img src="img/logo/logo_header.png" alt="Opus in te Logo" class="logo-image">
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="/pocetna">Početna</a></li>
                    <li><a href="/usluge">Usluge</a></li>
                    <li><a href="/o-meni">O Meni</a></li>
                    <li><a href="/blog">Blog</a></li>
                    <li><a href="/kontakt">Kontakt</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <button class="mobile-menu-toggle" aria-label="Otvori navigaciju" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <a href="booking.php" class="cta-button nav-cta">Zakažite Termin</a>
                <a href="backend/logout.php" id="logout-link" class="login-icon" aria-label="Odjava"><i
                        class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </header>

    <main class="dashboard-main">
        <section class="dashboard-content">
            <div class="container">
                <div class="edit-profile-card">
                    <div class="profile-card-sidebar">
                        <div class="profile-picture-placeholder">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h3 id="profile-card-name">
                            <?php echo htmlspecialchars($form_values['name'] . ' ' . $form_values['last_name']); ?></h3>
                    </div>
                    <div class="profile-card-form">
                        <h2>Uredite Vaš Profil</h2>
                        <p>Ažurirajte Vaše lične podatke.</p>

                        <form id="edit-profile-form" action="EditProfile.php" method="POST">
                            <div class="form-group">
                                <label for="profile-name-edit">Ime</label>
                                <input type="text" id="profile-name-edit" name="name" required placeholder="Vaše ime"
                                    value="<?php echo htmlspecialchars($form_values['name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="profile-lastname-edit">Prezime</label>
                                <input type="text" id="profile-lastname-edit" name="lastname" required
                                    placeholder="Vaše prezime"
                                    value="<?php echo htmlspecialchars($form_values['last_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="profile-email-edit">Email Adresa</label>
                                <input type="email" id="profile-email-edit" name="email" required
                                    placeholder="Vaša email adresa"
                                    value="<?php echo htmlspecialchars($form_values['email']); ?>">
                            </div>
                            <div class="form-group">
                                <label for="profile-phone-edit">Broj Telefona</label>
                                <input type="tel" id="profile-phone-edit" name="phone" required
                                    placeholder="Unesite Vaš broj telefona"
                                    value="<?php echo htmlspecialchars($form_values['phone']); ?>">
                            </div>
                            <button type="submit" class="cta-button save-profile-btn">Sačuvaj Promjene</button>
                            <a href="UserDashboard.php" class="cta-button back-btn">Nazad na Profil</a>
                        </form>
                        <div class="forgot-password-link">
                            <p>Imate problem sa lozinkom? <a href="#">Resetujte ovdje</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div id="success-modal" class="modal">
        <div class="modal-content confirmation-modal-content">
            <span class="close-button modal-close-btn"></span>
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Izmjene sačuvane</h3>
            <p>Vaši podaci su uspješno ažurirani.</p>
            <button class="cta-button close-modal-btn">Zatvori</button>
        </div>
    </div>


    <footer class="main-footer">
        <div class="container footer-container">
            <div class="footer-col">
                <a href="index.php" class="footer-logo-link">
                    <img src="img/logo/logo_transparent.png" alt="Opus in te Logo" class="footer-logo-image">
                </a>
            </div>
            <div class="footer-col">
                <h4>Opus in te</h4>
                <p>Vidovdanska Ulica 2, Banja Luka<br>
                    info@opusinte.ba<br>
                    +387 65 123 456</p>
            </div>
            <div class="footer-col">
                <h4>Brzi Linkovi</h4>
                <ul>
                    <li><a href="index.php">Početna</a></li>
                    <li><a href="Services.php">Usluge</a></li>
                    <li><a href="About.php">O Meni</a></li>
                    <li><a href="Contact.php">Kontakt</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Pratite nas</h4>
                <ul class="social-links">
                    <li><a href="#" aria-label="Posjetite našu Facebook stranicu"><i
                                class="fa-brands fa-facebook-f"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš Instagram profil"><i
                                class="fa-brands fa-instagram"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš TikTok profil"><i class="fa-brands fa-tiktok"></i></a>
                    </li>
                    <li><a href="#" aria-label="Posjetite naš X profil"><i class="fa-brands fa-x-twitter"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Opus in te | Sva prava zadržana | Politika privatnosti</p>
        </div>
    </footer>


    <script src="js/edit_profile.js"></script>
    <script src="js/mobile_nav.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/loading_screen.js"></script>

    <?php if (!empty($error_message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showNotification("<?php echo htmlspecialchars($error_message); ?>", "error");
            });
        </script>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showNotification("<?php echo htmlspecialchars($success_message); ?>", "success");
                window.showProfileSuccessModal = true;
            });
        </script>
    <?php endif; ?>

</body>

</html>