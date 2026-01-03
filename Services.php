<?php
require_once 'backend/connect.php';
require_once 'backend/role_check.php';
?>
<!DOCTYPE html>
<html lang="bs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usluge | Opus in te</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
</head>

<body>
    <div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loading.gif" alt="Loading..." class="loading-logo" />
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
                    <li><a href="Services.php" class="active">Usluge</a></li>
                    <li><a href="About.php">O Meni</a></li>
                    <li><a href="Blog.php">Blog</a></li>
                    <li><a href="Contact.php">Kontakt</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="booking.php" class="cta-button nav-cta">Zakažite termin</a>
                <a href="Login.php" class="login-icon" aria-label="Korisnički nalog"><i
                        class="fa-solid fa-circle-user"></i></a>
            </div>
        </div>
    </header>

    <main>
        <section class="page-title-section-usluge">
            <div class="container">
                <div class="page-title-content">
                    <h1>Naše Usluge</h1>
                    <p>Pružamo siguran prostor i stručnu podršku za Vaš lični razvoj.</p>
                </div>
            </div>
        </section>

        <section class="services-accordion-section">
            <div class="container">
                <div class="accordion">

                    <div class="accordion-item">
                        <button class="accordion-header">
                            Psihoterapija
                            <i class="fas fa-chevron-down icon"></i>
                        </button>
                        <div class="accordion-content">
                            <p>Kroz individualni ili grupni rad, psihoterapija nudi dublji uvid u obrasce ponašanja i
                                osjećanja, otvarajući put ka trajnim promjenama i ličnom zadovoljstvu. Nudimo:</p>
                            <ul>
                                <li>Individualna psihoterapija za odrasle</li>
                                <li>Grupna psihoterapija</li>
                                <li>Online psihoterapija</li>
                                <li>Psihoterapijski rad sa djecom i adolescentima</li>
                            </ul>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header">
                            Psihološko Savjetovanje
                            <i class="fas fa-chevron-down icon"></i>
                        </button>
                        <div class="accordion-content">
                            <p>Savjetovanje je usmjereno na rješavanje konkretnih životnih izazova, poboljšanje
                                komunikacijskih vještina i jačanje kapaciteta za suočavanje sa stresom.</p>
                            <ul>
                                <li>Psihološko savjetovanje za odrasle</li>
                                <li>Psihološko savjetovanje za djecu</li>
                            </ul>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <button class="accordion-header">
                            Opservacije i Izvještaji
                            <i class="fas fa-chevron-down icon"></i>
                        </button>
                        <div class="accordion-content">
                            <p>Stručna psihološka procjena i opservacija pružaju jasan uvid u kognitivno i emocionalno
                                funkcionisanje, uz izradu detaljnih nalaza za različite svrhe.</p>
                            <ul>
                                <li>Psihološka opservacija odraslih</li>
                                <li>Psihološka opservacija djece</li>
                                <li>Pisanje nalaza u pojedinačne svrhe</li>
                            </ul>
                        </div>
                    </div>

                </div>

                <div class="pricelist-cta">
                    <button id="openPriceListBtn" class="cta-button">Pogledajte Cjenovnik</button>
                </div>
            </div>
        </section>
    </main>

    <div id="priceListModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <img src="img/pricelist/pricelist.jpg" alt="Cjenovnik Usluga Opus in te" class="modal-image">
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
                    <li><a href="#">Kontakt</a></li>
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

    <script src="js/usluge.js"></script>
    <script src="js/loading_screen.js"></script>

</body>

</html>