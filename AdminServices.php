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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Specific styles for services page */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .stat-card h3 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
        }
        .stat-card .sub-value {
            font-size: 0.85rem;
            color: #888;
            margin-top: 5px;
        }
        .stat-card.profitable { border-left: 4px solid #C5A76A; }
        .stat-card.common { border-left: 4px solid #967D4A; }
        .stat-card.least { border-left: 4px solid #5C4B2B; }

        .services-table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .services-table {
            width: 100%;
            border-collapse: collapse;
        }
        .services-table th, .services-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .services-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #444;
        }
        .services-table tr:last-child td {
            border-bottom: none;
        }
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1.1rem;
            margin: 0 5px;
            transition: color 0.2s;
        }
        .edit-btn { color: #C5A76A; }
        .delete-btn { color: #e74c3c; }
        .edit-btn:hover { color: #A0864C; }
        .delete-btn:hover { color: #c0392b; }

        /* Modal Form Styling */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #C5A76A;
            outline: none;
        }
        .btn-save {
            background-color: #C5A76A;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-save:hover {
            background-color: #A0864C;
        }
        .btn-cancel {
            background-color: #f0f0f0;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn-cancel:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body class="admin-body">

    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <a href="AdminDashboard.php" class="logo-link">
                <img src="img/logo/headlogo.png" alt="Opus in te Logo" style="height: 50px;">
            </a>
            <nav class="admin-nav">
                <ul>
                    <li><a href="#">Korisnici</a></li>
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
            
            <h1 style="margin-bottom: 20px;">Pregled Usluga</h1>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card profitable">
                    <h3>Najprofitabilnija Usluga</h3>
                    <div class="value" id="mostProfitableName">-</div>
                    <div class="sub-value" id="mostProfitableAmount">-</div>
                </div>
                <div class="stat-card common">
                    <h3>Najčešća Usluga</h3>
                    <div class="value" id="mostCommonName">-</div>
                    <div class="sub-value" id="mostCommonCount">-</div>
                </div>
                <div class="stat-card least">
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
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Uredi Uslugu</h2>
            <form id="editServiceForm">
                <input type="hidden" id="editServiceId" name="id">
                
                <div class="form-group">
                    <label for="editName">Naziv Usluge</label>
                    <input type="text" id="editName" name="name" required>
                </div>

                <div class="form-group">
                    <label for="editPrice">Cijena (KM)</label>
                    <input type="number" id="editPrice" name="price" step="0.01" required>
                </div>

                <div class="form-group">
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
                    <button type="button" class="btn-cancel close-modal-btn">Odustani</button>
                    <button type="submit" class="btn-save">Sačuvaj</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Obriši Uslugu</h2>
            <p>Da li ste sigurni da želite obrisati uslugu <strong id="deleteServiceName"></strong>?</p>
            <p class="warning-text" style="color: #e74c3c; font-size: 0.9rem; margin-top: 10px;">Ova akcija je nepovratna.</p>
            
            <div class="form-actions">
                <button type="button" class="btn-cancel close-modal-btn">Odustani</button>
                <button type="button" id="confirmDeleteBtn" class="btn-delete" style="background-color: #e74c3c; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Obriši</button>
            </div>
        </div>
    </div>

    <script src="js/admin_services.js"></script>
</body>
</html>
