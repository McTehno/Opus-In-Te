<?php
session_start();
require_once 'backend/connect.php';

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: Login.php");
    exit;
}

// --- Fetch Data ---

// 1. Completed Appointments (Total or Monthly? Prompt says "number of all appointments with... completed")
// I will assume ALL completed appointments ever, as it's a general stat.
$stmt = $pdo->query("SELECT COUNT(*) FROM Appointment WHERE Appointment_Status_idAppointment_Status = (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'completed')");
$completed_appointments = $stmt->fetchColumn();

// 2. Confirmed Appointments (Upcoming/To happen)
$stmt = $pdo->query("SELECT COUNT(*) FROM Appointment WHERE Appointment_Status_idAppointment_Status = (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'confirmed')");
$confirmed_appointments = $stmt->fetchColumn();

// 3. Monthly Income (Current Month)
$stmt = $pdo->query("
    SELECT SUM(at.price) 
    FROM Appointment a 
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type 
    WHERE MONTH(a.datetime) = MONTH(CURRENT_DATE()) 
    AND YEAR(a.datetime) = YEAR(CURRENT_DATE())
    AND a.Appointment_Status_idAppointment_Status != (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'cancelled')
");
$monthly_income = $stmt->fetchColumn() ?: 0;

// 4. Graphs Data (Last 30 days for example, or all time? "scroll throughout the days" implies a lot of data. Let's get last 30 days for now to keep it manageable, or maybe current month + future?)
// User said "x axis is the dates... scroll throughout the days". Let's fetch a range, e.g., -30 days to +30 days or just this month.
// Let's fetch data for the current month and the previous month to have some data points.
// Actually, let's just get all appointments grouped by date.
$stmt = $pdo->query("
    SELECT 
        DATE(a.datetime) as date, 
        COUNT(*) as count,
        SUM(at.price) as income
    FROM Appointment a
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    WHERE a.Appointment_Status_idAppointment_Status != (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'cancelled')
    GROUP BY DATE(a.datetime)
    ORDER BY DATE(a.datetime) ASC
");
$graph_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dates = [];
$daily_counts = [];
$daily_income = [];

foreach ($graph_data as $row) {
    $dates[] = $row['date'];
    $daily_counts[] = $row['count'];
    $daily_income[] = $row['income'];
}

// 5. Recent Appointments List
// We need: Doctor (Worker), Patient (User), Price, Duration, Time, Date, Status
$stmt = $pdo->query("
    SELECT 
        a.idAppointment,
        a.datetime,
        at.price,
        at.duration,
        ast.status_name,
        u_doc.name as doc_name,
        u_doc.last_name as doc_lastname,
        u_doc.picture_path as doc_pic,
        u_pat.name as pat_name,
        u_pat.last_name as pat_lastname
    FROM Appointment a
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    JOIN Appointment_Status ast ON a.Appointment_Status_idAppointment_Status = ast.idAppointment_Status
    -- Join for Doctor (Role 2)
    LEFT JOIN Appointment_User au_doc ON a.idAppointment = au_doc.Appointment_idAppointment 
        AND au_doc.User_idUser IN (SELECT idUser FROM User WHERE Role_idRole = 2)
    LEFT JOIN User u_doc ON au_doc.User_idUser = u_doc.idUser
    -- Join for Patient (Role 3)
    LEFT JOIN Appointment_User au_pat ON a.idAppointment = au_pat.Appointment_idAppointment 
        AND au_pat.User_idUser IN (SELECT idUser FROM User WHERE Role_idRole = 3)
    LEFT JOIN User u_pat ON au_pat.User_idUser = u_pat.idUser
    ORDER BY a.datetime DESC
    LIMIT 20 -- Fetch more than 10 to allow expansion
");
$recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Opus in te</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li><a href="#">Termini</a></li>
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
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Završeni Termini</h3>
                    <span class="stat-number" data-target="<?php echo $completed_appointments; ?>">0</span>
                </div>
                <div class="stat-card">
                    <h3>Zakazani Termini</h3>
                    <span class="stat-number" data-target="<?php echo $confirmed_appointments; ?>">0</span>
                </div>
                <div class="stat-card">
                    <h3>Mjesečni Prihod</h3>
                    <span class="stat-number" data-target="<?php echo $monthly_income; ?>">0</span>
                    <span style="font-size: 0.9rem; color: #95a5a6;">KM</span>
                </div>
            </div>

            <!-- Graphs -->
            <div class="charts-grid">
                <div class="chart-container">
                    <h3>Pregled Termina</h3>
                    <div class="chart-wrapper">
                        <canvas id="appointmentsChart"></canvas>
                    </div>
                </div>
                <div class="chart-container">
                    <h3>Dnevni Prihod</h3>
                    <div class="chart-wrapper">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="recent-appointments">
                <h3>Nedavni Termini</h3>
                <ul class="appointments-list">
                    <?php foreach ($recent_appointments as $index => $appt): 
                        $isHidden = $index >= 10 ? 'hidden' : '';
                        $displayStyle = $index >= 10 ? 'display: none;' : '';
                        
                        // Format Date and Time
                        $dateObj = new DateTime($appt['datetime']);
                        $dateStr = $dateObj->format('d.m.Y');
                        $timeStr = $dateObj->format('H:i');
                        
                        // Doctor Image
                        $docName = urlencode($appt['doc_name'] . ' ' . $appt['doc_lastname']);
                        $docImg = $appt['doc_pic'];
                        
                        if ($docImg && strpos($docImg, 'C:') === 0) {
                             $docImg = 'img/vanjapic/' . basename($docImg);
                        }
                        
                        if (!$docImg) {
                             $docImg = "https://ui-avatars.com/api/?name=$docName&background=random";
                        }
                        
                        // Status Translation
                        $status_map = [
                            'confirmed' => 'Potvrđeno',
                            'completed' => 'Završeno',
                            'cancelled' => 'Otkazano',
                            'unconfirmed' => 'Nepotvrđeno'
                        ];
                        $status_display = isset($status_map[$appt['status_name']]) ? $status_map[$appt['status_name']] : $appt['status_name'];
                    ?>
                    <li class="appointment-item <?php echo $isHidden; ?>" style="<?php echo $displayStyle; ?>">
                        <div class="doctor-info">
                            <img src="<?php echo htmlspecialchars($docImg); ?>" alt="Doctor" class="doctor-pic">
                            <div class="doctor-details">
                                <span class="doctor-name"><?php echo htmlspecialchars($appt['doc_name'] . ' ' . $appt['doc_lastname']); ?></span>
                                <span class="status-badge status-<?php echo $appt['status_name']; ?>">
                                    <?php echo $status_display; ?>
                                </span>
                            </div>
                        </div>
                        <div class="patient-info">
                            <?php echo htmlspecialchars($appt['pat_name'] . ' ' . $appt['pat_lastname']); ?>
                        </div>
                        <div class="appt-details">
                            <span class="appt-price"><?php echo $appt['price']; ?> KM</span>
                            <span>Trajanje: <?php echo $appt['duration']; ?> min</span>
                        </div>
                        <div class="appt-time">
                            <div><?php echo $dateStr; ?></div>
                            <div><?php echo $timeStr; ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (count($recent_appointments) > 10): ?>
                <div class="expand-btn-container">
                    <button id="expand-appointments" class="expand-btn"><i class="fa-solid fa-chevron-down"></i></button>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="admin-footer">
        <p>&copy; <?php echo date('Y'); ?> Opus in te. Sva prava zadržana.</p>
    </footer>

    <!-- Pass PHP Data to JS -->
    <script>
        const adminChartData = {
            dates: <?php echo json_encode($dates); ?>,
            appointmentCounts: <?php echo json_encode($daily_counts); ?>,
            dailyIncome: <?php echo json_encode($daily_income); ?>
        };
    </script>
    <script src="js/admin.js"></script>
</body>
</html>
