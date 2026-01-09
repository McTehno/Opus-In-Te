<?php
require_once 'backend/connect.php';
require_once 'backend/role_check.php';
?>
<!DOCTYPE html>
<html lang="bs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Opus in te</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
</head>

<body>
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
                    <li><a href="/blog" class="active">Blog</a></li>
                    <li><a href="/kontakt">Kontakt</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <button class="mobile-menu-toggle" aria-label="Otvori navigaciju" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <a href="/zakazivanje" class="cta-button nav-cta">Zakažite termin</a>
                <a href="/prijava" class="login-icon" aria-label="Korisnički nalog"><i
                        class="fa-solid fa-circle-user"></i></a>
            </div>
        </div>
    </header>

    <main>
        <section class="blog-content-section">
            <div class="container blog-container">
                <div class="blog-posts-main">
                    <button class="mobile-sidebar-toggle" id="mobile-sidebar-toggle">
                        <i class="fas fa-filter"></i> Pretraga i Kategorije
                    </button>
                    <div class="blog-controls">
                        <div class="sort-dropdown">
                            <button id="sort-btn" class="sort-btn">
                                Sortiraj po <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="sort-options" id="sort-options">
                                <a href="#" data-sort="date_desc" class="active">Najnovije</a>
                                <a href="#" data-sort="date_asc">Najstarije</a>
                                <a href="#" data-sort="views_desc">Najgledanije</a>
                                <a href="#" data-sort="views_asc">Najmanje gledano</a>
                            </div>
                        </div>
                    </div>

                    <div id="featured-post-container">
                        <!-- Featured Post will be injected here -->
                    </div>

                    <div class="blog-grid" id="blog-grid">
                        <!-- Blog Posts will be injected here -->
                    </div>

                    <div class="load-more-container">
                        <button id="load-more-btn" class="cta-button">Učitaj više</button>
                    </div>

                </div>

                <!-- Blog Detail View (Initially Hidden) -->
                <div id="blog-detail-view" class="blog-detail-view hidden">
                    
                    <div class="detail-header">
                        <div class="detail-image-wrapper">
                            <img id="detail-img" src="" alt="Blog Cover">
                        </div>
                        <div class="detail-title-section">
                            <button id="back-to-blog-btn" class="blog-back-btn">
                                <i class="fas fa-arrow-left"></i> Nazad na blog
                            </button>
                            <span id="detail-category" class="detail-category-badge"></span>
                            <h1 id="detail-title"></h1>
                            <div class="detail-meta-info">
                                <span id="detail-date"><i class="far fa-calendar-alt"></i> <span></span></span>
                                <span id="detail-views"><i class="far fa-eye"></i> <span></span></span>
                            </div>
                        </div>
                    </div>

                    <div id="detail-content" class="detail-body-content">
                        <!-- Content injected here -->
                    </div>
                </div>

                <aside class="blog-sidebar" id="blog-sidebar">
                    <div class="sidebar-widget">
                        <h4 class="widget-title">Pretraga</h4>
                        <form class="search-form" id="search-form">
                            <input type="search" id="search-input" placeholder="Ukucajte pojam...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <div class="sidebar-widget">
                        <h4 class="widget-title">Kategorije</h4>
                        <ul class="categories-list" id="categories-list">
                            <!-- Categories will be injected here -->
                        </ul>
                    </div>

                    <div class="sidebar-widget">
                        <h4 class="widget-title">Popularni Članci</h4>
                        <ul class="popular-posts-list" id="popular-posts-list">
                            <!-- Popular posts will be injected here -->
                        </ul>
                    </div>
                </aside>
            </div>
        </section>
    </main>

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

    <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="js/mobile_nav.js"></script>
    <script src="js/loading_screen.js"></script>
    <script src="js/blog.js"></script>
</body>

</html>