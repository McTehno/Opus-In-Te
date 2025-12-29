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
    <title>Upravljanje Uslugama | Opus in te</title>
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
                    <li><a href="AdminUsers.php">Korisnici</a></li>
                    <li><a href="AdminAppointments.php">Termini</a></li>
                    <li><a href="AdminServices.php" class="active">Usluge</a></li>
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
                <h1 style="margin-bottom: 0;">Pregled Usluga</h1>
                <button id="addServiceBtn" class="services-btn-save"><i class="fa-solid fa-plus"></i> Nova Usluga</button>
            </div>

            <!-- Stats Cards -->
            <div class="services-stats-cards">
                <div class="services-stat-card profitable">
                    <h3>Najprofitabilnija Usluga</h3>
                    <div class="value" id="mostProfitableName">-</div>
                    <div class="sub-value" id="mostProfitableAmount">-</div>
                </div>
                <div class="services-stat-card common">
                    <h3>Najčešća Usluga</h3>
                    <div class="value" id="mostCommonName">-</div>
                    <div class="sub-value" id="mostCommonCount">-</div>
                </div>
                <div class="services-stat-card least">
                    <h3>Najrjeđa Usluga</h3>
                    <div class="value" id="leastCommonName">-</div>
                    <div class="sub-value" id="leastCommonCount">-</div>
                </div>
            </div>

            <!-- Services Table -->
            <div class="services-table-container">
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Naziv Usluge</th>
                            <th>Cijena</th>
                            <th>Trajanje</th>
                            <th>Broj Termina</th>
                            <th>Ukupna Zarada</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody id="servicesTableBody">
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
                <h2 id="modalTitle" style="margin: 0; font-size: 1.2rem;">Uredi Uslugu</h2>
                <button class="close-modal" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color: #999;"><i class="fa-solid fa-times"></i></button>
            </div>
            <form id="editServiceForm" style="padding: 30px;">
                <input type="hidden" id="editServiceId" name="id">
                
                <div class="services-form-group">
                    <label for="editName">Naziv Usluge</label>
                    <input type="text" id="editName" name="name" required>
                </div>

                <div class="services-form-group">
                    <label for="editPrice">Cijena (KM)</label>
                    <input type="number" id="editPrice" name="price" step="0.01" required>
                </div>

                <div class="services-form-group">
                    <label for="editDuration">Trajanje</label>
                    <select id="editDuration" name="duration">
                        <option value="null">Neodredjeno</option>
                        <?php 
                        for($i=15; $i<=180; $i+=15) {
                            echo "<option value='$i'>$i min</option>";
                        }
                        ?>
                    </select>
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
            <h3>Brisanje Usluge</h3>
            <p>Da li ste sigurni da želite obrisati uslugu <strong id="deleteServiceName"></strong>? Ova akcija je nepovratna.</p>
            
            <div class="modal-actions">
                <button class="services-btn-cancel close-modal-btn">Odustani</button>
                <button type="button" id="confirmDeleteBtn" class="services-modal-delete-btn">Obriši</button>
            </div>
        </div>
    </div>

    <script src="js/admin_services.js"></script>
    <script src="js/notifications.js"></script>
    <script src="js/loading_screen.js"></script>
</body>
</html>
