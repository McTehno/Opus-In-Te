<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Fetch all services with stats
    // We count ALL appointments for popularity (demand)
    // We sum price only for non-cancelled appointments for income
    $sql = "
        SELECT 
            at.idAppointment_Type,
            at.name,
            at.price,
            at.duration,
            COUNT(a.idAppointment) as appointment_count,
            SUM(CASE 
                WHEN a.Appointment_Status_idAppointment_Status != (SELECT idAppointment_Status FROM Appointment_Status WHERE status_name = 'otkazano') 
                THEN at.price 
                ELSE 0 
            END) as total_income
        FROM Appointment_Type at
        LEFT JOIN Appointment a ON at.idAppointment_Type = a.Appointment_Type_idAppointment_Type
        GROUP BY at.idAppointment_Type
        ORDER BY appointment_count DESC
    ";

    $stmt = $pdo->query($sql);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Stats
    $mostProfitable = null;
    $mostCommon = null;
    $leastCommon = null;

    if (!empty($services)) {
        // Most Common is the first one because we sorted by count DESC
        $mostCommon = $services[0];

        // Least Common is the last one
        $leastCommon = $services[count($services) - 1];

        // Find Most Profitable
        $maxIncome = -1;
        foreach ($services as $service) {
            if ($service['total_income'] > $maxIncome) {
                $maxIncome = $service['total_income'];
                $mostProfitable = $service;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'services' => $services,
        'stats' => [
            'most_profitable' => $mostProfitable,
            'most_common' => $mostCommon,
            'least_common' => $leastCommon
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
