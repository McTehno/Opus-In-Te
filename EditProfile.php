<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uredi Profil | Opus in te</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body class="dashboard-body">

    <header class="main-header scrolled"> 
        <div class="container">
            <a href="index.php" class="logo-link">
                <img src="img/logo/headlogo.png" alt="Opus in te Logo" class="logo-image">
            </a>
           <nav class="main-nav">
                 <ul>
                    <li><a href="index.php">Početna</a></li>
                    <li><a href="Services.php">Usluge</a></li>
                    <li><a href="About.php">O Meni</a></li>
                    <li><a href="Blog.php">Blog</a></li>
                   <li><a href="Contact.php">Kontakt</a></li>
                   </ul>
            </nav>
            <div class="header-actions">
                <a href="booking.php" class="cta-button nav-cta">Zakažite Termin</a>
                <a href="#" id="logout-link" class="login-icon" aria-label="Odjava"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </header>

    <main class="dashboard-main">
        <section class="dashboard-content">
            <div class="container">
                <div class="edit-profile-card"> <div class="profile-card-sidebar">
                <div class="profile-picture-placeholder">
                    <i class="fas fa-user-circle"></i> </div>
                <h3 id="profile-card-name">[Ime Prezime]</h3> <p>Član od: [Datum]</p> </div>
            <div class="profile-card-form">
                <h2>Uredite Vaš Profil</h2>
                <p>Ažurirajte Vaše lične podatke.</p>

                <form id="edit-profile-form">
                    <div class="form-group">
                        <label for="profile-name-edit">Ime i Prezime</label>
                        <input type="text" id="profile-name-edit" name="name" required placeholder="Vaše ime i prezime" value="Ime Prezime">
                    </div>
                    <div class="form-group">
                        <label for="profile-email-edit">Email Adresa</label>
                        <input type="email" id="profile-email-edit" name="email" required placeholder="Vaša email adresa" value="email@example.com">
                    </div>
                    <div class="form-group">
                        <label for="profile-phone-edit">Broj Telefona</label>
                        <input type="tel" id="profile-phone-edit" name="phone" placeholder="Unesite Vaš broj telefona" value="Broj telefona">
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
                <i class="fas fa-envelope-open-text"></i> 
             </div>
            <h3>Potvrda putem Emaila</h3>
            <p>Poslali smo Vam email za potvrdu izmjena. Molimo provjerite Vaš inbox.</p>
            <button class="cta-button close-modal-btn">Zatvori</button>
        </div>
    </div>


    <footer class="main-footer"> 
         <div class="container footer-container">
            <div class="footer-col">
                <a href="index.php" class="footer-logo-link">
                    <img src="img/logo/fulltransparentlogo.png" alt="Opus in te Logo" class="footer-logo-image">
                </a>
            </div>
            <div class="footer-col">
                <h4>Opus in te</h4>
                <p>Jevrejska 56, Banja Luka<br>
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
                    <li><a href="#" aria-label="Posjetite našu Facebook stranicu"><i class="fa-brands fa-facebook-f"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš Instagram profil"><i class="fa-brands fa-instagram"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš TikTok profil"><i class="fa-brands fa-tiktok"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš X profil"><i class="fa-brands fa-x-twitter"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Opus in te | Sva prava zadržana | Politika privatnosti</p>
        </div>
    </footer>


    <script src="js/edit_profile.js"></script>

    </body>
</html>