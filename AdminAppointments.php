<?php
session_start();
require_once 'backend/connect.php';

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: Login.php");
    exit;
}

// Fetch Filter Options

// 1. Appointment Types (Fetch duration too for JS logic)
$stmt = $pdo->query("SELECT idAppointment_Type, name, duration FROM Appointment_Type ORDER BY name");
$appointment_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Durations
$stmt = $pdo->query("SELECT DISTINCT duration FROM Appointment_Type ORDER BY duration");
$durations = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 3. Prices
$stmt = $pdo->query("SELECT DISTINCT price FROM Appointment_Type ORDER BY price");
$prices = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Statuses
$stmt = $pdo->query("SELECT idAppointment_Status, status_name FROM Appointment_Status");
$statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje Terminima | Opus in te</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_modals.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <li><a href="AdminAppointments.php" class="active">Termini</a></li>
                    <li><a href="#">Proizvodi</a></li>
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
            
            <div class="appointments-controls">
                <!-- Search -->
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Pretraži po doktoru ili pacijentu...">
                </div>

                <!-- Filters Toggle (Mobile) -->
                <button id="toggleFilters" class="filter-toggle-btn"><i class="fa-solid fa-filter"></i> Filteri</button>
            </div>

            <div class="content-grid">
                <!-- Filters Sidebar -->
                <aside class="filters-sidebar" id="filtersSidebar">
                    <div class="filter-group">
                        <h4>Lokacija</h4>
                        <label><input type="checkbox" class="filter-location" value="1"> Banja Luka</label>
                        <label><input type="checkbox" class="filter-location" value="2"> Prijedor</label>
                        <label><input type="checkbox" class="filter-location" value="NULL"> Online</label>
                    </div>

                    <div class="filter-group">
                        <h4>Status</h4>
                        <?php foreach ($statuses as $status): 
                            $statusLabel = $status['status_name'];
                            // Translate common statuses
                            $map = ['confirmed' => 'Potvrđeno', 'completed' => 'Završeno', 'cancelled' => 'Otkazano', 'unconfirmed' => 'Nepotvrđeno'];
                            if (isset($map[$statusLabel])) $statusLabel = $map[$statusLabel];
                        ?>
                        <label>
                            <input type="checkbox" class="filter-status" value="<?php echo $status['idAppointment_Status']; ?>"> 
                            <?php echo ucfirst($statusLabel); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Tip Usluge</h4>
                        <div class="scrollable-filter">
                            <?php foreach ($appointment_types as $type): ?>
                            <label>
                                <input type="checkbox" class="filter-type" value="<?php echo $type['idAppointment_Type']; ?>"> 
                                <?php echo htmlspecialchars($type['name']); ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Trajanje (min)</h4>
                        <div class="scrollable-filter">
                            <label><input type="checkbox" class="filter-duration" value="NULL"> Nije definisano (/)</label>
                            <?php foreach ($durations as $dur): 
                                if ($dur === null) continue; // Handled above
                            ?>
                            <label>
                                <input type="checkbox" class="filter-duration" value="<?php echo $dur; ?>"> 
                                <?php echo $dur; ?> min
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Cijena (KM)</h4>
                        <div class="scrollable-filter">
                            <?php foreach ($prices as $price): ?>
                            <label>
                                <input type="checkbox" class="filter-price" value="<?php echo $price; ?>"> 
                                <?php echo $price; ?> KM
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button id="resetFilters" class="reset-btn">Poništi Filtere</button>
                </aside>

                <!-- Appointments List -->
                <div class="appointments-list-container">
                    <div class="list-header">
                        <span id="resultCount">Učitavanje...</span>
                    </div>
                    
                    <div class="appointments-header-row">
                        <div class="col-doctor">Doktor</div>
                        <div class="col-patient">Pacijent</div>
                        <div class="col-info">Detalji</div>
                        <div class="col-location">Lokacija</div>
                        <div class="col-status">Status</div>
                        <div class="col-actions">Akcije</div>
                    </div>

                    <ul id="appointmentsList" class="full-appointments-list">
                        <!-- Populated by JS -->
                    </ul>
                    
                    <div id="loadingSpinner" class="spinner" style="display: none;">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modals -->
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content delete-modal">
            <div class="modal-icon delete-icon">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3>Brisanje Termina</h3>
            <p>Da li ste sigurni da želite obrisati ovaj termin? Ova akcija je nepovratna.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal('deleteModal')">Odustani</button>
                <button class="btn-confirm-delete" id="confirmDeleteBtn">Obriši</button>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content edit-modal">
            <div class="modal-header">
                <h3>Uredi Termin</h3>
                <button class="close-modal" onclick="closeModal('editModal')"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editApptId">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tip Usluge</label>
                            <select id="editType" class="form-control">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select id="editStatus" class="form-control">
                                <?php foreach ($statuses as $status): 
                                    $statusLabel = $status['status_name'];
                                    $map = ['confirmed' => 'Potvrđeno', 'completed' => 'Završeno', 'cancelled' => 'Otkazano', 'unconfirmed' => 'Nepotvrđeno'];
                                    if (isset($map[$statusLabel])) $statusLabel = $map[$statusLabel];
                                ?>
                                <option value="<?php echo $status['idAppointment_Status']; ?>"><?php echo ucfirst($statusLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Lokacija</label>
                            <select id="editLocation" class="form-control">
                                <option value="1">Banja Luka</option>
                                <option value="2">Prijedor</option>
                                <option value="NULL">Online</option>
                            </select>
                        </div>
                    </div>

                    <div class="calendar-section">
                        <label>Datum i Vrijeme</label>
                        <div class="calendar-wrapper">
                            <!-- Calendar Header -->
                            <div class="calendar-header">
                                <button type="button" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                                <span id="monthYear"></span>
                                <button type="button" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <!-- Calendar Grid -->
                            <div class="calendar-grid" id="calendarGrid"></div>
                        </div>
                        
                        <div class="time-slots-section">
                            <h5 id="timeSlotsTitle">Izaberite datum</h5>
                            <div id="timeSlotsList" class="time-slots-list"></div>
                            <input type="hidden" id="editDate">
                            <input type="hidden" id="editTime">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel-edit" onclick="closeModal('editModal')"><i class="fa-solid fa-times"></i></button>
                <button class="btn-save-edit" id="saveEditBtn"><i class="fa-solid fa-check"></i></button>
            </div>
        </div>
    </div>

    <!-- Save Confirmation Modal -->
    <div id="saveConfirmModal" class="modal-overlay">
        <div class="modal-content save-modal">
            <div class="modal-icon save-icon">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <h3>Sačuvaj Izmjene</h3>
            <p>Da li ste sigurni da želite sačuvati izmjene na ovom terminu?</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal('saveConfirmModal')">Odustani</button>
                <button class="btn-confirm-save" id="confirmSaveBtn">Sačuvaj</button>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP data to JS
        const appointmentTypes = <?php echo json_encode($appointment_types); ?>;
    </script>
    <script src="js/admin_appointments.js"></script>
</body>
</html>
