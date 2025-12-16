<?php
require_once 'connect.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
$duration = (int)($_GET['duration'] ?? 60);

if (!$date) {
    echo json_encode(['error' => 'Date is required']);
    exit;
}

// Parse date (expecting YYYY-MM-DD)
try {
    $dateTime = new DateTime($date);
    $formattedDate = $dateTime->format('Y-m-d');
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid date format']);
    exit;
}

// Working hours
$startHour = 9;
$endHour = 17;
$interval = 15; // minutes

// Fetch Workers (Role 2 = worker)
// In a real scenario, we might filter by service capability, but for now assume all workers do all services
$stmt = $pdo->query("SELECT idUser, name, last_name, picture_path FROM User WHERE Role_idRole = 2");
$workers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($workers)) {
    echo json_encode([]);
    exit;
}

// Fetch Appointments for the date
// We need to check overlap. An appointment blocks time from start to start+duration.
// We need to know the duration of existing appointments.
// The Appointment table links to Appointment_Type which has duration.
$sql = "
    SELECT 
        a.datetime, 
        at.duration,
        au.User_idUser as worker_id
    FROM Appointment a
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
    JOIN User u ON au.User_idUser = u.idUser
    WHERE DATE(a.datetime) = ?
    AND u.Role_idRole = 2
    AND a.Appointment_Status_idAppointment_Status != 4 -- Exclude cancelled
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$formattedDate]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize appointments by worker
$workerAppointments = [];
foreach ($appointments as $app) {
    $workerAppointments[$app['worker_id']][] = [
        'start' => strtotime($app['datetime']),
        'end' => strtotime($app['datetime']) + ($app['duration'] * 60)
    ];
}

$availableSlots = [];

foreach ($workers as $worker) {
    $workerId = $worker['idUser'];
    $workerName = $worker['name'] . ' ' . $worker['last_name'];
    // Fix picture path if needed (relative to web root)
    $picPath = $worker['picture_path'];
    if ($picPath) {
        // Extract filename if it's a full path or ensure relative path
        $picPath = 'img/vanjapic/' . basename($picPath); // Simplified for now based on context
    } else {
        $picPath = 'img/default-user.png';
    }

    $currentTime = strtotime("$formattedDate $startHour:00:00");
    $endTime = strtotime("$formattedDate $endHour:00:00");

    while ($currentTime + ($duration * 60) <= $endTime) {
        $slotStart = $currentTime;
        $slotEnd = $currentTime + ($duration * 60);
        
        $isFree = true;
        if (isset($workerAppointments[$workerId])) {
            foreach ($workerAppointments[$workerId] as $busy) {
                // Check overlap
                // Overlap if (StartA < EndB) and (EndA > StartB)
                if ($slotStart < $busy['end'] && $slotEnd > $busy['start']) {
                    $isFree = false;
                    break;
                }
            }
        }

        if ($isFree) {
            $availableSlots[] = [
                'time' => date('H:i', $slotStart),
                'worker_id' => $workerId,
                'worker_name' => $workerName,
                'worker_image' => $picPath,
                'available' => true
            ];
        }

        $currentTime += ($interval * 60);
    }
}

echo json_encode($availableSlots);
?>