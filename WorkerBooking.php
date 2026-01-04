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

if (!$user || $user['role_name'] !== 'radnik') {
    header("Location: /korisnicki-panel");
    exit;
}

// Fetch Services for Step 2
$services = [];
try {
    $stmt = $pdo->query("SELECT * FROM Appointment_Type ORDER BY name");
    $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allServices as $service) {
        $category = 'Ostalo';
        $name = $service['name'];
        
        if (stripos($name, 'psihoterapija') !== false || stripos($name, 'rad sa djecom') !== false) {
            $category = 'Psihoterapija';
        } elseif (stripos($name, 'savjetovanje') !== false) {
            $category = 'Psihološko Savjetovanje';
        } elseif (stripos($name, 'opservacija') !== false || stripos($name, 'nalaza') !== false) {
            $category = 'Opservacije i Izvještaji';
        }
        
        $services[$category][] = $service;
    }
} catch (PDOException $e) {
    // Handle error
}

// Fetch Statuses for Step 4
$statuses = $pdo->query("SELECT * FROM Appointment_Status")->fetchAll();

?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interno Zakazivanje | Opus in te</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/worker.css">
    <style>
        /* Inline styles for specific WorkerBooking needs if not in worker.css yet */
        /* Will move to css/worker.css later */
        html {
            overflow-y: scroll; /* Force scrollbar to prevent layout shift/jitter */
        }

        .location-btn {
            padding: 2.5rem !important;
            min-width: 200px;
            border: 2px solid #eee !important;
            border-radius: 20px !important;
            background: white;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .location-btn:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(197, 167, 106, 0.25);
            border-color: var(--soft-gold) !important;
        }

        .location-btn i {
            font-size: 3rem !important;
            color: var(--charcoal);
            transition: color 0.3s;
        }

        .location-btn:hover i {
            color: var(--soft-gold);
        }

        .location-btn span {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--charcoal);
        }

        /* Fix scrollbar jitter */
        .booking-content {
            overflow-x: hidden; /* Prevent horizontal scrollbar during transitions */
            width: 100%;
            box-sizing: border-box;
            padding: 1rem; /* Add padding to prevent content touching edges */
        }
        
        /* Ensure container doesn't collapse or shift */
        .booking-container {
            overflow: hidden; 
            min-height: 100vh; /* Ensure full height */
        }
    </style>
</head>
<body class="worker-booking-body">
    
    <div class="booking-container">
        <header class="booking-header">
            <div class="header-left">
                <h2>Interno Zakazivanje</h2>
            </div>
            <div class="worker-info">
                <span><?php echo htmlspecialchars($user['name'] . ' ' . $user['last_name']); ?></span>
            </div>
        </header>

        <div class="progress-bar-container">
            <div class="progress-bar-line">
                <div class="progress-bar-fill"></div>
            </div>
            <div class="step active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Lokacija</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Klijent</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Usluga</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Vrijeme</div>
            </div>
            <div class="step" data-step="5">
                <div class="step-circle">5</div>
                <div class="step-label">Detalji</div>
            </div>
            <div class="step" data-step="6">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Kraj</div>
            </div>
        </div>

        <div class="booking-content">
            <a href="/radni-panel" class="close-booking-btn"><i class="fas fa-times"></i></a>
            
            <!-- Step 1: Location Selection -->
            <div class="booking-step active" data-step="1">
                <h2 class="step-title">Izaberite Lokaciju</h2>
                <div class="location-picker" style="display: flex; gap: 1.5rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap;">
                    <button class="location-btn" data-location="Banja Luka">
                        <i class="fa-solid fa-map-marker-alt"></i>
                        <span>Banja Luka</span>
                    </button>
                    <button class="location-btn" data-location="Prijedor">
                        <i class="fa-solid fa-map-marker-alt"></i>
                        <span>Prijedor</span>
                    </button>
                    <button class="location-btn" data-location="Online">
                        <i class="fa-solid fa-laptop"></i>
                        <span>Online</span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Client Input -->
            <div class="booking-step" data-step="2">
                <h2 class="step-title">Podaci o Klijentu</h2>
                <div class="form-group">
                    <label for="clientName">Ime i Prezime</label>
                    <input type="text" id="clientName" class="form-control" placeholder="Ime i prezime klijenta">
                </div>
                <div class="form-group">
                    <label for="clientEmail">Email Adresa</label>
                    <input type="email" id="clientEmail" class="form-control" placeholder="Email adresa klijenta">
                </div>
                <div class="form-group">
                    <label for="clientPhone">Broj Telefona</label>
                    <input type="tel" id="clientPhone" class="form-control" placeholder="Broj telefona klijenta">
                </div>
                <div class="step-actions">
                    <button class="btn-prev"><i class="fas fa-arrow-left"></i> Nazad</button>
                    <button class="btn-next">Dalje <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 3: Service Selection -->
            <div class="booking-step" data-step="3">
                <h2 class="step-title">Izaberite Uslugu</h2>
                <div class="services-accordion">
                    <?php foreach ($services as $category => $categoryServices): ?>
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span><?php echo htmlspecialchars($category); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="accordion-content">
                                <?php foreach ($categoryServices as $service): ?>
                                    <div class="service-card" data-id="<?php echo $service['idAppointment_Type']; ?>" data-duration="<?php echo htmlspecialchars($service['duration'] ?? ''); ?>">
                                        <div class="service-info">
                                            <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                                            <p><?php echo htmlspecialchars($service['duration'] ?? 'N/A'); ?> min | <?php echo htmlspecialchars($service['price'] ?? '0'); ?> KM</p>
                                        </div>
                                        <div class="service-select-icon"><i class="far fa-circle"></i></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="step-actions">
                    <button class="btn-prev"><i class="fas fa-arrow-left"></i> Nazad</button>
                    <button class="btn-next" disabled>Dalje <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 4: Date & Time -->
            <div class="booking-step" data-step="4">
                <h2 class="step-title">Datum i Vrijeme</h2>
                <div class="datetime-container">
                    <div class="calendar-wrapper">
                        <div class="calendar-header">
                            <button id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                            <h4 id="monthYear"></h4>
                            <button id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="calendar-grid" id="calendarGrid"></div>
                    </div>
                    <div class="slots-wrapper">
                        <h4>Slobodni Termini</h4>
                        <div id="timeSlots" class="slots-grid">
                            <p class="select-date-msg">Izaberite datum.</p>
                        </div>
                    </div>
                </div>
                <div class="step-actions">
                    <button class="btn-prev"><i class="fas fa-arrow-left"></i> Nazad</button>
                    <button class="btn-next" disabled>Dalje <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 5: Status & Notes -->
            <div class="booking-step" data-step="5">
                <h2 class="step-title">Detalji Termina</h2>
                <div class="form-group">
                    <label for="statusSelect">Status</label>
                    <select id="statusSelect" class="form-control">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status['idAppointment_Status']; ?>">
                                <?php echo htmlspecialchars($status['status_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="summary-card">
                    <h3>Pregled</h3>
                    <p><strong>Lokacija:</strong> <span id="summaryLocation"></span></p>
                    <p><strong></strong>Klijent:</strong> <span id="summaryClient"></span></p>
                    <p><strong>Usluga:</strong> <span id="summaryService"></span></p>
                    <p><strong>Datum:</strong> <span id="summaryDate"></span></p>
                    <p><strong>Vrijeme:</strong> <span id="summaryTime"></span></p>
                </div>

                <div class="step-actions">
                    <button class="btn-prev"><i class="fas fa-arrow-left"></i> Nazad</button>
                    <button class="btn-finish">Zakaži Termin <i class="fas fa-check"></i></button>
                </div>
            </div>

            <!-- Step 6: Success -->
            <div class="booking-step" data-step="6" style="text-align: center; padding: 3rem 1rem;">
                <div class="success-icon" style="font-size: 5rem; color: var(--accent-color); margin-bottom: 1.5rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="step-title" style="border: none; margin-bottom: 1rem;">Termin Uspješno Zakazan!</h2>
                <p style="color: var(--text-light); margin-bottom: 2rem; font-size: 1.1rem;">Rezervacija je potvrđena i sačuvana u sistemu.</p>
                <a href="/radni-panel" class="btn-next" style="text-decoration: none; display: inline-block;">Povratak na Dashboard</a>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <script src="js/worker_booking.js"></script>
</body>
</html>
