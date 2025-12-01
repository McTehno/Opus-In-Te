<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Opus in te</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="main-header scrolled"> 
        <div class="container">
            <a href="Index.php" class="logo-link">
                <img src="img/logo/headlogo.png" alt="Opus in te Logo" class="logo-image">
            </a>
           <nav class="main-nav">
                 <ul>
                    <li><a href="Index.php">Početna</a></li>
                    <li><a href="Services.php">Usluge</a></li>
                    <li><a href="About.php">O Meni</a></li>
                    <li><a href="Blog.php" class="active">Blog</a></li>
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
       

        <section class="blog-content-section">
            <div class="container blog-container">
                <div class="blog-posts-main">
                    <article class="featured-post-card">
                        <div class="card-image-container">
                            <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?q=80&w=1974&auto=format&fit=crop" alt="Žena čita knjigu u biblioteci">
                        </div>
                        <div class="card-content">
                            <span class="card-category">Lični Razvoj</span>
                            <h2 class="card-title"><a href="#">5 navika koje će Vam pomoći da smanjite anksioznost</a></h2>
                            <p class="card-excerpt">Anksioznost je sastavni dio života, ali postoje tehnike i navike koje nam mogu pomoći da je držimo pod kontrolom. Saznajte više o pet jednostavnih koraka...</p>
                            <a href="#" class="read-more-link">Pročitaj više →</a>
                        </div>
                    </article>

                    <div class="blog-grid">
                        <article class="blog-card">
                             <div class="card-image-container">
                                <img src="img/blogplaceholder/blog2.jpg" alt="Dječiji crtež porodice">
                            </div>
                            <div class="card-content">
                                <span class="card-category">Roditeljstvo</span>
                                <h3 class="card-title"><a href="#">Kako razgovarati sa djecom o teškim temama</a></h3>
                                <a href="#" class="read-more-link">Pročitaj više →</a>
                            </div>
                        </article>

                        <article class="blog-card">
                             <div class="card-image-container">
                                <img src="img/blogplaceholder/blog3.jpeg" alt="Dvije osobe razgovaraju uz kafu">
                            </div>
                            <div class="card-content">
                                <span class="card-category">Psihoterapija</span>
                                <h3 class="card-title"><a href="#">Šta očekivati od prve seanse psihoterapije?</a></h3>
                                <a href="#" class="read-more-link">Pročitaj više →</a>
                            </div>
                        </article>
                    </div>

                </div>

                <aside class="blog-sidebar">
                    <div class="sidebar-widget">
                        <h4 class="widget-title">Pretraga</h4>
                        <form class="search-form">
                            <input type="search" placeholder="Ukucajte pojam...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <div class="sidebar-widget">
                        <h4 class="widget-title">Popularni Članci</h4>
                        <ul class="popular-posts-list">
                            <li>
                                <a href="#">
                                    <img src="https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?q=80&w=1974&auto=format&fit=crop" alt="Popularni članak 1">
                                    <span>5 navika koje će Vam pomoći da smanjite anksioznost</span>
                                </a>
                            </li>
                             <li>
                                <a href="#">
                                    <img src="https://images.unsplash.com/photo-1604881991720-f91add269612?q=80&w=2070&auto=format&fit=crop" alt="Popularni članak 2">
                                    <span>Šta očekivati od prve seanse psihoterapije?</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="sidebar-widget">
                        <h4 class="widget-title">Kategorije</h4>
                        <ul class="categories-list">
                            <li><a href="#">Lični Razvoj</a></li>
                            <li><a href="#">Roditeljstvo</a></li>
                            <li><a href="#">Psihoterapija</a></li>
                            <li><a href="#">Stres i Anksioznost</a></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container footer-container">
             <div class="footer-col">
                <a href="Index.php" class="footer-logo-link">
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
                    <li><a href="Index.php">Početna</a></li>
                    <li><a href="Services.php">Usluge</a></li>
                    <li><a href="About.php">O Meni</a></li>
                    <li><a href="#">Kontakt</a></li>
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

</body>
</html>