<?php
session_start();
require_once 'backend/connect.php';

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: Login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Korisnicima | Opus in te</title>
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
            <a href="AdminDashboard.php" class="logo-link">
                <img src="img/logo/headlogo.png" alt="Opus in te Logo" style="height: 50px;">
            </a>
            <nav class="admin-nav">
                <ul>
                    <li><a href="AdminUsers.php" class="active">Korisnici</a></li>
                    <li><a href="AdminAppointments.php">Termini</a></li>
                    <li><a href="AdminServices.php">Usluge</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </nav>
            <div class="admin-actions">
                <a href="backend/admin_logout.php" title="Odjava"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="admin-dashboard">
        <div class="container">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="margin-bottom: 0;">Pregled Korisnika</h1>
                <!-- Optional: Add User Button if needed, but usually users register themselves -->
            </div>

            <!-- Stats Cards -->
            <div class="services-stats-cards">
                <div class="services-stat-card profitable">
                    <h3>Ukupno Korisnika</h3>
                    <div class="value" id="totalUsers">-</div>
                </div>
                <div class="services-stat-card common">
                    <h3>Korisnici sa Nalogom</h3>
                    <div class="value" id="usersWithAccount">-</div>
                </div>
                <div class="services-stat-card least">
                    <h3>Korisnici bez Naloga</h3>
                    <div class="value" id="usersWithoutAccount">-</div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="services-table-container">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Ime</th>
                            <th>Prezime</th>
                            <th>Telefon</th>
                            <th>Email</th>
                            <th>Broj Termina</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content" style="padding: 0; overflow: hidden;">
            <div class="modal-header" style="padding: 20px 30px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f9f9f9;">
                <h2 id="modalTitle" style="margin: 0; font-size: 1.2rem;">Uredi Korisnika</h2>
                <button class="close-modal" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color: #999;"><i class="fa-solid fa-times"></i></button>
            </div>
            <form id="editUserForm" style="padding: 30px;">
                <input type="hidden" id="editUserId" name="id">
                
                <div class="services-form-group">
                    <label for="editName">Ime</label>
                    <input type="text" id="editName" name="name" required>
                </div>

                <div class="services-form-group">
                    <label for="editLastName">Prezime</label>
                    <input type="text" id="editLastName" name="lastname" required>
                </div>

                <div class="services-form-group">
                    <label for="editPhone">Telefon</label>
                    <input type="text" id="editPhone" name="phone" required>
                </div>

                <div class="services-form-group">
                    <label for="editEmail">Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="services-btn-cancel close-modal-btn">Odustani</button>
                    <button type="submit" class="services-btn-save">Sačuvaj</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content delete-modal">
            <div class="modal-icon delete-icon">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3>Brisanje Korisnika</h3>
            <p>Da li ste sigurni da želite obrisati korisnika <strong id="deleteUserName"></strong>? Ova akcija je nepovratna.</p>
            
            <div class="modal-actions">
                <button class="services-btn-cancel close-modal-btn">Odustani</button>
                <button type="button" id="confirmDeleteBtn" class="services-modal-delete-btn">Obriši</button>
            </div>
        </div>
    </div>

    <script src="js/admin_users.js"></script>
    <script src="js/loading_screen.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html>
