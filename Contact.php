<?php
require_once 'backend/connect.php';
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt | Opus in te</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

</head>
<body>
<div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loading.gif" alt="Loading..." class="loading-logo"/>
        </div> 
        
</div>

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
                    <li><a href="Contact.php" class="active">Kontakt</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="booking.php" class="cta-button nav-cta">Zakažite termin</a>
                <a href="Login.php" class="login-icon" aria-label="Korisnički nalog"><i class="fa-solid fa-circle-user"></i></a>
            </div>
        </div>
    </header>

    <main>
        <section class="page-title-section page-title-section-kontakt">
    <div class="background-lines-container">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>
    <div class="container page-title-content">
        <a href="#contact-form-section" class="page-title-link"><h1>Stupite u Kontakt</h1></a>
        <p>Rado ćemo odgovoriti na Vaša pitanja. Vaš put ka blagostanju počinje ovdje.</p>
    </div>
</section>

        <section class="booking-prompt">
            <div class="container">
                <p>Trebate zakazati termin? <a href="#">Zakažite ovdje</a> i osigurajte svoj razgovor.</p>
            </div>
        </section>

        <section id="contact-form-section" class="contact-main-section">
            <div class="container contact-container">
                
                <div class="contact-info fade-in">
                    <h3>Kontakt Informacije</h3>
                    <p>Ovdje nas možete pronaći i kontaktirati. Radujemo se Vašem pozivu ili poruci.</p>
                    <div class="info-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <strong>Adresa</strong>
                            <p>Jevrejska 56, 78000 Banja Luka</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-clock"></i>
                        <div>
                            <strong>Radno Vrijeme</strong>
                            <p>Pon - Pet: 09:00 - 17:00</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <strong>Email</strong>
                            <p>info@opusinte.ba</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-phone"></i>
                        <div>
                            <strong>Telefon</strong>
                            <p>+387 65 123 456</p>                    
                        </div>
                    </div>
                </div>

                <div class="contact-form-wrapper fade-in">
                    <h3>Pošaljite Nam Poruku</h3>
                    <form action="#" method="POST" class="contact-form">
                        <div class="form-group">
                            <label for="name">Ime i Prezime</label>
                            <input type="text" id="name" name="name" required placeholder="Unesite Vaše ime">
                        </div>
                        <div class="form-group">
                            <label for="email-contact">Email Adresa</label>
                            <input type="email" id="email-contact" name="email" required placeholder="npr. email@example.com">
                        </div>
                        <div class="form-group">
                            <label for="phone">Broj Telefona (Opcionalno)</label>
                            <input type="tel" id="phone" name="phone" placeholder="Unesite Vaš broj telefona">
                        </div>
                        <div class="form-group">
                            <label for="message">Vaša Poruka</label>
                            <textarea id="message" name="message" rows="6" required placeholder="Kako Vam možemo pomoći?"></textarea>
                        </div>
                        <button type="submit" class="cta-button">Pošalji Poruku</button>
                    </form>
                </div>
            </div>
        </section>

        <div class="map-section-wrapper fade-in">
            <section class="map-section">
                <div class="container">
                    <h2 class="section-title fade-in">Pronađite Nas</h2>
                    <div id="map-container" class="fade-in">
                    </div>
                </div>
            </section>
</div>
    </main>

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

<script src="js/kontakt_animations.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="js/loading_screen.js"></script>
</body>
</html>