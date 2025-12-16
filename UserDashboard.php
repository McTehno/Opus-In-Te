<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}
require_once 'backend/connect.php';

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, last_name, email, phone FROM User WHERE idUser = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    // Handle case where user is not found (shouldn't happen if session is valid)
    session_destroy();
    header("Location: Login.php");
    exit;
}

// Fetch user appointments
// Assuming appointments are linked via Appointment_User table based on opus.sql
$sql = "
    SELECT a.idAppointment, a.datetime, at.name as type_name, at.duration, ast.status_name
    FROM Appointment a
    JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    JOIN Appointment_Status ast ON a.Appointment_Status_idAppointment_Status = ast.idAppointment_Status
    WHERE au.User_idUser = ?
    ORDER BY a.datetime ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll();

// Convert appointments to JSON for JS
$appointments_json = json_encode($appointments);
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaš Profil | Opus in te</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body class="dashboard-body">
<div id="loading-screen">
        <div class="loading-logo-wrapper">
            <img src="img/logo/loading.gif" alt="Loading..." class="loading-logo"/>
        </div> 
        
</div>

    <header class="main-header scrolled">
        <div class="container">
            <a href="index.php" class="logo-link">
                <img src="img/logo/headlogo.png" alt="Opus in te Logo" class="logo-image">
            </a>
           <nav class="main-nav">
                 <ul>
                    <li><a href="index.php">Početna</a></li>
                    <li><a href="Services.php">Usluge</a></li>
                    <li><a href="About.php">O Meni</a></li>
                    <li><a href="Blog.php">Blog</a></li>
                   <li><a href="Contact.php">Kontakt</a></li>
                   </ul>
            </nav>
            <div class="header-actions">
                <a href="booking.php" class="cta-button nav-cta">Zakažite Termin</a>
                <a href="backend/logout.php" id="logout-link" class="login-icon" aria-label="Odjava"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </header>

    <main class="dashboard-main">
        <section class="dashboard-welcome">
            <div class="container">
                <h1 id="welcome-message">Dobrodošli, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <p>Pregledajte Vaše zakazane termine.</p>
            </div>
        </section>

       <section class="dashboard-content">
            <div class="container dashboard-container">

                <div class="user-profile-panel">
                    <h3>Vaš Profil</h3>
                    <div class="profile-details">
                        <div class="profile-item">
                            <label><i class="fas fa-user"></i> Ime i Prezime:</label>
                            <span id="profile-name"><?php echo htmlspecialchars($user['name'] . ' ' . $user['last_name']); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><i class="fas fa-envelope"></i> Email:</label>
                            <span id="profile-email"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="profile-item">
                            <label><i class="fas fa-phone"></i> Telefon:</label>
                            <span id="profile-phone"><?php echo htmlspecialchars($user['phone']); ?></span>
                        </div>
                        </div>
                    <a href="EditProfile.php" id="edit-profile-btn" class="cta-button edit-profile-button">Uredi Profil</a>
                </div>

                <div class="appointment-panel">
                    <div class="appointment-view-container">
                        <div class="calendar-view active-view">
                            <h3>Vaši Termini</h3>
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

                        <div class="appointment-list-view">
                            <button class="back-to-calendar-btn"><i class="fas fa-arrow-left"></i> Nazad na Kalendar</button>
                            <h3 id="details-panel-title">Termini za Izabrani Dan</h3>
                            <div id="appointment-list">
                                <p class="no-appointments">Izaberite dan na kalendaru sa označenim terminom.</p>
                                </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

<script>
    // Pass PHP data to JS
    const userAppointments = <?php echo $appointments_json; ?>;
</script>
    <footer class="main-footer">
         <div class="container footer-container">
            <div class="footer-col">
                <a href="index.php" class="footer-logo-link">
                    <img src="img/logo/fulltransparentlogo.png" alt="Opus in te Logo" class="footer-logo-image">
                </a>
            </div>
            <div class="footer-col">
                <h4>Opus in te</h4>
                <p>Jevrejska 56, Banja Luka<br>
                   info@opusinte.ba<br>
                   +387 65 123 456</p>
            </div>
            <div class="footer-col">
                <h4>Brzi Linkovi</h4>
                <ul>
                    <li><a href="index.php">Početna</a></li>
                    <li><a href="Services.php">Usluge</a></li>
                    <li><a href="About.php">O Meni</a></li>
                    <li><a href="Contact.php">Kontakt</a></li>
                </ul>
            </div>
           <div class="footer-col">
                <h4>Pratite nas</h4>
                <ul class="social-links">
                    <li><a href="#" aria-label="Posjetite našu Facebook stranicu"><i class="fa-brands fa-facebook-f"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš Instagram profil"><i class="fa-brands fa-instagram"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš TikTok profil"><i class="fa-brands fa-tiktok"></i></a></li>
                    <li><a href="#" aria-label="Posjetite naš X profil"><i class="fa-brands fa-x-twitter"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 Opus in te | Sva prava zadržana | Politika privatnosti</p>
        </div>
    </footer>

<script src="js/dashboard_calendar.js"></script>
<script src="js/loading_screen.js"></script>
</body>
</html>