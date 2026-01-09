<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /prijava");
    exit;
}
require_once 'backend/connect.php';

// Fetch user details and role
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT u.*, r.name as role_name 
    FROM User u 
    JOIN Role r ON u.Role_idRole = r.idRole 
    WHERE u.idUser = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: /prijava");
    exit;
}

// Ensure only workers can access this page
if ($user['role_name'] !== 'radnik') {
    header("Location: /korisnicki-panel");
    exit;
}

// Worker Logic
// Fetch appointments assigned to this worker
$sql = "
    SELECT 
        a.idAppointment, 
        a.datetime, 
        at.name as type_name, 
        at.idAppointment_Type,
        at.duration, 
        ast.status_name,
        ast.idAppointment_Status,
        client.name as client_name,
        client.last_name as client_last_name,
        client.phone as client_phone,
        client.email as client_email,
        addr.street,
        addr.street_number,
        city.name as city_name
    FROM Appointment a
    JOIN Appointment_User au_worker ON a.idAppointment = au_worker.Appointment_idAppointment
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    JOIN Appointment_Status ast ON a.Appointment_Status_idAppointment_Status = ast.idAppointment_Status
    LEFT JOIN Appointment_User au_client ON a.idAppointment = au_client.Appointment_idAppointment AND au_client.User_idUser != ?
    LEFT JOIN User client ON au_client.User_idUser = client.idUser
    LEFT JOIN Address addr ON a.Address_idAddress = addr.idAddress
    LEFT JOIN City city ON addr.City_idCity = city.idCity
    WHERE au_worker.User_idUser = ?
    ORDER BY a.datetime ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id, $user_id]);
$appointments = $stmt->fetchAll();

// Convert appointments to JSON for JS
$appointments_json = json_encode($appointments);

// Fetch options for edit modal
$statuses = $pdo->query("SELECT * FROM Appointment_Status")->fetchAll();
$types = $pdo->query("SELECT * FROM Appointment_Type")->fetchAll();

// Render Worker Dashboard
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radna Ploča | Opus in te</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/worker.css">
    <link rel="stylesheet" href="css/worker_responsive.css">

</head>
<body class="worker-dashboard">
    <header class="worker-header">
        <div class="worker-logo">
            <img src="img/logo/logo_header.png" alt="Opus in te Logo">
        </div>
        <div class="worker-info">
            <span class="worker-name"><?php echo htmlspecialchars($user['name'] . ' ' . $user['last_name']); ?></span>
            <a href="/odjava" class="logout-btn" title="Odjava"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </header>

    <main class="worker-main">
        <div class="dashboard-header-row">
            <h1 class="dashboard-title">Vaši Termini</h1>
            <a href="/radnik-zakazivanje" class="btn-new-appointment"><i class="fas fa-plus"></i> Novo Zakazivanje</a>
        </div>
        
        <div class="appointment-panel">
            <div class="appointment-view-container">
                <!-- Calendar View -->
                <div class="calendar-view active-view">
                    <div class="calendar-panel dashboard-calendar">
                        <div class="calendar-header">
                            <button id="prevMonth" aria-label="Previous Month"><i class="fas fa-chevron-left"></i></button>
                            <h4 id="monthYear"></h4>
                            <button id="nextMonth" aria-label="Next Month"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="calendar-grid">
                        </div>
                    </div>
                </div>

                <!-- List View (Hidden by default) -->
                <div class="appointment-list-view">
                    <button class="back-to-calendar-btn"><i class="fas fa-arrow-left"></i> Nazad na Kalendar</button>
                    <h3 id="details-panel-title">Termini za Izabrani Dan</h3>
                    <div id="appointment-list" class="appointments-grid">
                        <p class="no-appointments">Izaberite dan na kalendaru sa označenim terminom.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Uredi Termin</h3>
                <button class="close-modal"><i class="fas fa-times"></i></button>
            </div>
            <form id="editAppointmentForm">
                <input type="hidden" name="appointment_id" id="appointmentId">
                
                <div class="form-group">
                    <label for="statusSelect">Status Termina</label>
                    <select name="status_id" id="statusSelect" class="form-control">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['idAppointment_Status']; ?>">
                                <?php echo htmlspecialchars($status['status_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="typeSelect">Tip Usluge</label>
                    <select name="type_id" id="typeSelect" class="form-control">
                        <?php foreach ($types as $type): ?>
                            <option value="<?php echo $type['idAppointment_Type']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel">Otkaži</button>
                    <button type="submit" class="btn-save">Sačuvaj Promjene</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Pass PHP data to JS
        const workerAppointments = <?php echo $appointments_json; ?>;
    </script>
    <script src="js/worker_dashboard.js"></script>
</body>
</html>
