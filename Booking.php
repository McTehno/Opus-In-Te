<?php
session_start();
require_once 'backend/connect.php';
require_once 'backend/role_check.php';

// Fetch User Data if logged in
$userData = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, last_name, email, phone FROM User WHERE idUser = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch Services
$services = [];
try {
    $stmt = $pdo->query("SELECT * FROM Appointment_Type ORDER BY name");
    $allServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group manually based on name keywords or exact matches
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
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zakažite Termin | Opus in te</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body class="booking-body">
<div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loading.gif" alt="Loading..." class="loading-logo"/>
        </div> 
        
</div>

    <div class="booking-modal">
        <div class="booking-header">
            <a href="/pocetna" class="close-booking"><i class="fas fa-times"></i></a>
        </div>

        <div class="progress-bar-container">
            <div class="progress-bar-line"></div>
            <div class="step active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Lokacija</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Usluga</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Datum i Vrijeme</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Podaci</div>
            </div>
            <div class="step" data-step="5">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Potvrda</div>
            </div>
        </div>

        <div class="booking-content">

            <div class="booking-step active" data-step="1">
                <h2 class="step-title">Izaberite Lokaciju</h2>
                <div class="location-picker">
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

            <div class="booking-step" data-step="2">
                <h2 class="step-title">Izaberite Uslugu</h2>
                <div class="service-accordion">
                    <?php 
                    // Define preferred order of categories
                    $categoryOrder = ['Psihoterapija', 'Psihološko Savjetovanje', 'Opservacije i Izvještaji', 'Ostalo'];
                    
                    foreach ($categoryOrder as $category) {
                        if (!empty($services[$category])) {
                            $items = $services[$category];
                    ?>
                    <div class="service-item">
                        <button class="service-header"><?php echo htmlspecialchars($category); ?> <i class="fas fa-chevron-down icon"></i></button>
                        <div class="service-content">
                            <?php foreach ($items as $item): ?>
                            <div class="service-option" 
                                 data-id="<?php echo $item['idAppointment_Type']; ?>"
                                 data-service="<?php echo htmlspecialchars($item['name']); ?>" 
                                 data-price="<?php echo number_format($item['price'], 0); ?> KM" 
                                 data-duration="<?php echo $item['duration'] ? $item['duration'] . 'min' : 'N/A'; ?>"
                                 data-duration-val="<?php echo $item['duration']; ?>">
                                <span><?php echo htmlspecialchars($item['name']); ?></span> 
                                <span><?php echo number_format($item['price'], 0); ?> KM <?php echo $item['duration'] ? '/ ' . $item['duration'] . 'min' : ''; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php 
                        }
                    } 
                    ?>
                </div>
            </div>

            <div class="booking-step" data-step="3">
                <div class="date-time-container">

                    <div class="calendar-panel">
                        <div class="calendar-header">
                            <button id="prevMonth" aria-label="Previous Month"><i class="fas fa-chevron-left"></i></button>
                            <h3 id="monthYear">Septembar 2025</h3>
                            <button id="nextMonth" aria-label="Next Month"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="calendar-grid">
                            </div>
                    </div>

                    <div class="time-slots-panel">
                        <div class="time-slots-header">
                            <h4 id="timeSlotsTitle">Slobodni Termini</h4>
                        </div>
                        <div class="time-slots-list" id="timeSlotsList">
                            <div class="time-slots-placeholder">
                                <i class="fas fa-calendar-alt"></i>
                                <p>Izaberite datum sa kalendara da vidite dostupne termine.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="booking-step" data-step="4">
                <h2 class="step-title">Vaši Podaci</h2>
                <form class="details-form">
                    <div class="form-group">
                        <label for="fullName">Ime i Prezime</label>
                        <input type="text" id="fullName" placeholder="Unesite Vaše puno ime" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Adresa</label>
                        <input type="email" id="email" placeholder="npr. email@primjer.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Broj Telefona</label>
                        <input type="tel" id="phone" placeholder="Vaš broj telefona" required>
                    </div>
                    <button type="button" class="cta-button form-next-btn" aria-label="Dalje na potvrdu"><i class="fas fa-arrow-right"></i></button>
                </form>
            </div>

            <div class="booking-step" data-step="5">
    <div class="final-confirmation-wrapper">
        <div class="final-confirmation-icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <h2 class="step-title">Hvala!</h2>
        <p>Vaša rezervacija je uspješno primljena. Uskoro ćete na Vašu email adresu dobiti poruku sa potvrdom i svim detaljima Vašeg termina.</p>
        <a href="index.php" class="cta-button home-btn">Vrati se na početnu</a>
    </div>
</div>

        </div>
    </div>

    <script>
        const loggedInUser = <?php echo json_encode($userData); ?>;
    </script>
    <script src="js/booking.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <script src="js/loading_screen.js"></script>
</body>
</html>