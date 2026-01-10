<?php
session_start();
require_once 'backend/connect.php';

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /prijava");
    exit;
}

// --- Fetch Data ---
require_once 'backend/admin_dashboard_data.php';

?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Opus in te</title>
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin_responsive.css">
    <link rel="stylesheet" href="/css/admin_modals.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">
    <div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="/img/logo/loader.gif" alt="Loading..." class="loading-logo" />
        </div>
    </div>

    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <a href="/admin-panel" class="logo-link">
                <img src="/img/logo/logo_header.png" alt="Opus in te Logo" style="height: 50px;">
            </a>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/korisnici">Korisnici</a></li>
                    <li><a href="/admin/termini">Termini</a></li>
                    <li><a href="/admin/usluge">Usluge</a></li>
                    <li><a href="/admin/blog">Blog</a></li>
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
            
            <div style="margin-bottom: 20px;">
                <h1 style="margin-bottom: 0;">Kontrolna Tabla</h1>
            </div>

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
                
                <!-- Header Row -->
                <div class="appointments-header">
                    <div class="header-col col-doctor">Doktor</div>
                    <div class="header-col col-patient">Pacijent</div>
                    <div class="header-col col-details">Cijena / Trajanje</div>
                    <div class="header-col col-type">Tip Usluge</div>
                    <div class="header-col col-time">Vrijeme</div>
                </div>

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
                             $docImg = '/img/vanjapic/' . basename($docImg);
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
                            <i class="fa-regular fa-user" style="margin-right: 5px; color: #C5A76A;"></i>
                            <?php echo htmlspecialchars($appt['pat_name'] . ' ' . $appt['pat_lastname']); ?>
                        </div>
                        <div class="appt-details">
                            <span class="appt-price"><i class="fa-solid fa-tag" style="font-size: 0.8rem; margin-right: 5px; color: #aaa;"></i> <?php echo $appt['price']; ?> KM</span>
                            <span class="appt-duration"><i class="fa-regular fa-clock" style="font-size: 0.8rem; margin-right: 5px; color: #aaa;"></i> <?php echo !empty($appt['duration']) ? $appt['duration'] . ' min' : '/'; ?></span>
                        </div>
                        <div class="appt-type-col">
                            <?php echo htmlspecialchars($appt['type_name']); ?>
                        </div>
                        <div class="appt-time">
                            <div><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i> <?php echo $dateStr; ?></div>
                            <div style="font-size: 0.85rem; color: #999; margin-top: 2px;"><?php echo $timeStr; ?></div>
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
    <script src="/js/admin.js"></script>
    <script src="/js/admin_mobile_nav.js"></script>
    <script src="/js/loading_screen.js"></script>
</body>
</html>
