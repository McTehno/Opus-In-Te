<?php
require_once 'connect.php';
require_once 'app_config.php';

$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;

if (!$id || !$token) {
    die('Invalid request.');
}

// Validate Token
$expectedToken = hash_hmac('sha256', $id, APP_SECRET);
if (!hash_equals($expectedToken, $token)) {
    die('Invalid token.');
}

try {
    // Update status to 2 (Confirmed)
    // Assuming 1 = Unconfirmed, 2 = Confirmed
    $stmt = $pdo->prepare("UPDATE Appointment SET Appointment_Status_idAppointment_Status = 2 WHERE idAppointment = ?");
    $stmt->execute([$id]);

    // Fetch appointment details for the success page
    $stmt = $pdo->prepare("
        SELECT a.datetime, at.name as service_name 
        FROM Appointment a 
        JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type 
        WHERE a.idAppointment = ?
    ");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potvrda Termina | Opus in te</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f9f9f9;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
        }
        .confirmation-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .icon-check {
            color: #4CAF50;
            font-size: 60px;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #C5A76A;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background-color: #a88b55;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="confirmation-card">
        <i class="fas fa-check-circle icon-check"></i>
        <h1>Termin Potvrđen!</h1>
        <p>Vaš termin za <strong><?php echo htmlspecialchars($appointment['service_name']); ?></strong> dana <strong><?php echo date('d.m.Y H:i', strtotime($appointment['datetime'])); ?></strong> je uspješno potvrđen.</p>
        <a href="/pocetna" class="btn">Povratak na početnu</a>
    </div>
</body>
</html>
