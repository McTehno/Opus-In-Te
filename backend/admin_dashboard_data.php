<?php
require_once __DIR__ . '/connect.php';

// --- Fetch Admin Dashboard Data ---

// 1. Completed Appointments (Total)
$stmt = $pdo->query("SELECT COUNT(*) FROM Appointment WHERE Appointment_Status_idAppointment_Status = (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'završeno')");
$completed_appointments = $stmt->fetchColumn();

// 2. Confirmed Appointments (Upcoming/To happen)
$stmt = $pdo->query("SELECT COUNT(*) FROM Appointment WHERE Appointment_Status_idAppointment_Status = (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'potvrđeno')");
$confirmed_appointments = $stmt->fetchColumn();

// 3. Monthly Income (Current Month)
$stmt = $pdo->query("
    SELECT SUM(at.price) 
    FROM Appointment a 
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type 
    WHERE MONTH(a.datetime) = MONTH(CURRENT_DATE()) 
    AND YEAR(a.datetime) = YEAR(CURRENT_DATE())
    AND a.Appointment_Status_idAppointment_Status != (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'otkazano')
");
$monthly_income = $stmt->fetchColumn() ?: 0;

// 4. Graphs Data (Last 10 work days)
// Generate last 10 work days (Mon-Fri)
$dates_last_10 = [];
$count = 0;
$i = 0;
while ($count < 10) {
    $timestamp = strtotime("-$i days");
    $dayOfWeek = date('N', $timestamp);
    if ($dayOfWeek <= 5) { // Mon-Fri
        $dates_last_10[] = date('Y-m-d', $timestamp);
        $count++;
    }
    $i++;
}
$dates_last_10 = array_reverse($dates_last_10);

$stmt = $pdo->query("
    SELECT 
        DATE(a.datetime) as date, 
        COUNT(*) as count,
        SUM(at.price) as income
    FROM Appointment a
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    WHERE a.Appointment_Status_idAppointment_Status != (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'otkazano')
    AND DATE(a.datetime) >= DATE_SUB(CURRENT_DATE, INTERVAL 20 DAY) -- Fetch enough back to cover weekends
    GROUP BY DATE(a.datetime)
    ORDER BY DATE(a.datetime) ASC
");
$fetched_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map fetched data by date
$data_map = [];
foreach ($fetched_data as $row) {
    $data_map[$row['date']] = $row;
}

$dates = [];
$daily_counts = [];
$daily_income = [];

foreach ($dates_last_10 as $date) {
    $dates[] = $date;
    if (isset($data_map[$date])) {
        $daily_counts[] = $data_map[$date]['count'];
        $daily_income[] = $data_map[$date]['income'];
    } else {
        $daily_counts[] = 0;
        $daily_income[] = 0;
    }
}

// 5. Recent Appointments List
// We need: Doctor (Worker), Patient (User), Price, Duration, Time, Date, Status
$stmt = $pdo->query("
    SELECT 
        a.idAppointment,
        a.datetime,
        at.price,
        at.duration,
        at.name as type_name,
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
