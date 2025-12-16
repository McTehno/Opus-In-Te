<?php
require_once 'backend/connect.php';
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O Meni | Opus in te</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>
<div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loading.gif" alt="Loading..." class="loading-logo"/>
        </div> 
        <p>Učitavanje...</p>
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
                    <li><a href="OMeni.php" class="active">O Meni</a></li>
                    <li><a href="Blog.php">Blog</a></li>
                    <li><a href="Contact.php">Kontakt</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="booking.php" class="cta-button nav-cta">Zakažite termin</a>
                <a href="Login.php" class="login-icon" aria-label="Korisnički nalog"><i class="fa-solid fa-circle-user"></i></a>
            </div>
        </div>
    </header>

    <main>
        <section class="page-title-section about-me-header">
            <div class="container page-title-content">
                <h1>Upoznajte Vašeg Terapeuta</h1>
                <p>Vanja Dejanović, posvećena Vašem unutrašnjem rastu i blagostanju.</p>
            </div>
        </section>

       <section class="about-me-main">
    <div class="container about-me-container">

        <div class="about-me-image-wrapper">
            <div class="about-me-image fade-in">
                <img src="img/vanjapic/indexpic.jpg" alt="Vanja Dejanović">
            </div>
        </div>

        <div class="about-me-text-wrapper">
            <div class="about-me-text">
                <h2 class="section-title fade-in">Dobrodošli u Vaš siguran prostor.</h2>
                <p class="fade-in" data-delay="1"><strong>Moje ime je Vanja Dejanović, i moja misija je da Vam pružim sigurno i podržavajuće okruženje u kojem možete slobodno istražiti svoje misli, osjećaje i životne izazove.</strong></p>
                <p class="fade-in" data-delay="2">Vjerujem da je psihoterapija putovanje koje klijent i terapeut prolaze zajedno. To je proces otkrivanja unutrašnje snage, razumijevanja ličnih obrazaca i izgradnje kapaciteta za ispunjeniji život. Moj pristup je utemeljen na empatiji, povjerenju i dubokom poštovanju prema jedinstvenoj priči svake osobe.</p>
            </div>
        </div>

    </div>

    <div class="pull-quote-section">
        <div class="container">
            <blockquote class="fade-in" data-delay="1">
                "Opus in te – djelo u tebi – srž je moje filozofije: pomoći Vam da otkrijete i oblikujete remek-djelo koje već nosite u sebi."
            </blockquote>
        </div>
    </div>

    <div class="container about-me-qualifications">
         <div class="qualifications-content fade-in">
            <h3>Moj Put i Stručnost</h3>
            <p>Moja fascinacija ljudskim umom i odnosima započela je davno, vodeći me ka formalnom obrazovanju iz psihologije i kontinuiranom usavršavanju u različitim terapijskim modalitetima. Kroz godine rada, shvatila sam da je najvažniji alat u terapiji istinski ljudski kontakt.</p>
            
            <ul class="qualifications-list">
                <li class="fade-in" data-delay="1"><i class="fas fa-certificate"></i><span>Certifikovani psihoterapeut sa višegodišnjim iskustvom.</span></li>
                <li class="fade-in" data-delay="2"><i class="fas fa-brain"></i><span>Specijalizacija u Kognitivno-bihevioralnoj terapiji.</span></li>
                <li class="fade-in" data-delay="3"><i class="fas fa-users"></i><span>Član relevantnih stručnih udruženja.</span></li>
                <li class="fade-in" data-delay="4"><i class="fas fa-book-open"></i><span>Kontinuirana edukacija u skladu sa najvišim etičkim standardima.</span></li>
            </ul>
        </div>
    </div>
</section>
        
        <section class="final-cta-section">
            <div class="container">
                <h2>Spremni za prvi korak?</h2>
                <p>Vaše putovanje ka boljem sutra počinje jednom odlukom. Tu sam da Vas podržim.</p>
                <a href="Contact.php" class="cta-button">Stupite u Kontakt</a>
            </div>
        </section>
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

<script src="js/omeni_animations.js"></script>
<script src="js/loading_screen.js"></script>
</body>
</html>