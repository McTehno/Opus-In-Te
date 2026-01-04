<?php
session_start();
require_once 'backend/connect.php';

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /prijava");
    exit;
}
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Blogovima | Opus in te</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_modals.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loading.gif" alt="Loading..." class="loading-logo" />
        </div>
    </div>

    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <a href="/admin-panel" class="logo-link">
                <img src="img/logo/headlogo.png" alt="Opus in te Logo" style="height: 50px;">
            </a>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/korisnici">Korisnici</a></li>
                    <li><a href="/admin/termini">Termini</a></li>
                    <li><a href="/admin/usluge">Usluge</a></li>
                    <li><a href="/admin/blog" class="active">Blog</a></li>
                </ul>
            </nav>
            <div class="admin-actions">
                <a href="/admin/odjava" title="Odjava"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-dashboard">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="margin-bottom: 0;">Pregled Blogova</h1>
                <button id="addBlogBtn" class="btn-primary"><i class="fa-solid fa-plus"></i> Novi Blog</button>
            </div>

            <div class="appointments-controls">
                <!-- Search -->
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Naslov ili autor...">
                </div>

                <!-- Filters Toggle (Mobile) -->
                <button id="toggleFilters" class="filter-toggle-btn"><i class="fa-solid fa-filter"></i> Filteri</button>
            </div>

            <div class="blog-layout">
                <!-- Sidebar Filters -->
                <aside class="blog-sidebar" id="filtersSidebar">
                    <div class="filter-group">
                        <h3>Kategorije</h3>
                        <div id="categoryFilters" class="checkbox-list">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3>Status</h3>
                        <div id="statusFilters" class="checkbox-list">
                            <!-- Populated by JS -->
                        </div>
                    </div>

                    <div class="filter-group">
                        <h3>Broj pregleda</h3>
                        <div class="range-slider-container">
                            <input type="range" id="viewCountSlider" min="0" max="1000" value="0" class="slider">
                            <div class="slider-values">
                                <span id="minViewLabel">0</span>
                                <span id="currentViewLabel">0+</span>
                                <span id="maxViewLabel">1000</span>
                            </div>
                        </div>
                    </div>
                    
                    <button id="resetFilters" class="btn-secondary" style="width: 100%; margin-top: 10px;">Poništi filtere</button>
                </aside>

                <!-- Blog Grid -->
                <div class="blog-grid-container">
                    <div id="blogGrid" class="blog-grid">
                        <!-- Populated by JS -->
                    </div>
                    <div id="noResults" style="display: none; text-align: center; padding: 20px;">
                        Nema rezultata za odabrane filtere.
                    </div>
                </div>
            </div>

            <!-- Single Blog Detail View (Initially Hidden) -->
            <div id="blogDetailView" class="blog-detail-view" style="display: none;">
                <button id="backToGridBtn" class="btn-secondary" style="margin-bottom: 20px;">
                    <i class="fa-solid fa-arrow-left"></i> Nazad na listu
                </button>
                
                <div class="blog-detail-container">
                    <div class="blog-detail-top">
                        <div class="blog-detail-image-wrapper">
                            <img id="detailImage" src="" alt="Blog Image">
                        </div>
                        <div class="blog-detail-info">
                            <h1 id="detailTitle">Naslov Bloga</h1>
                            <div id="detailMetaGrid" class="detail-meta-grid">
                                <div class="meta-item">
                                    <i class="fa-solid fa-user"></i>
                                    <span id="detailAuthor">Autor</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-calendar"></i>
                                    <span id="detailDate">Datum</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-eye"></i>
                                    <span id="detailViews">Pregledi</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-tag"></i>
                                    <span id="detailCategories">Kategorije</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span id="detailStatus">Status</span>
                                </div>
                            </div>

                            <div class="blog-detail-actions" id="detailActions">
                                <button id="editBlogBtn" class="blog-action-btn action-edit"><i class="fa-solid fa-pen"></i> Uredi</button>
                                <button id="deleteBlogBtn" class="blog-action-btn action-delete"><i class="fa-solid fa-trash"></i> Obriši</button>
                                <button id="cancelEditBtn" class="blog-action-btn action-cancel" style="display: none;"><i class="fa-solid fa-xmark"></i> Poništi</button>
                            </div>
                            <p id="editHelperText" class="edit-helper" style="display: none;">Klikni na polje koje želiš urediti, zatim sačuvaj promjene.</p>
                        </div>
                    </div>
                    <div class="blog-detail-body" id="detailContent">
                        <!-- Content goes here -->
                    </div>
                </div>
            </div>

            <!-- Delete Blog Modal -->
            <div id="deleteBlogModal" class="modal-overlay">
                <div class="modal-content delete-modal">
                    <div class="modal-icon delete-icon"><i class="fa-solid fa-trash"></i></div>
                    <h3>Obriši blog objavu?</h3>
                    <p>Ova akcija je trajna. Potvrdite da želite obrisati odabranu objavu.</p>
                    <div class="modal-actions">
                        <button class="btn-cancel" id="cancelDeleteBlog">Otkaži</button>
                        <button class="btn-confirm-delete" id="confirmDeleteBlog">Obriši</button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script src="js/loading_screen.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/admin_blog.js"></script>
</body>
</html>
