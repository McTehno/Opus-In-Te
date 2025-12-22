<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Check Admin Access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Base Query
$sql = "
    SELECT 
        a.idAppointment,
        a.datetime,
        at.name as type_name,
        at.price,
        at.duration,
        ast.status_name,
        u_doc.name as doc_name,
        u_doc.last_name as doc_lastname,
        u_pat.name as pat_name,
        u_pat.last_name as pat_lastname,
        c.name as city_name,
        a.Address_idAddress
    FROM Appointment a
    JOIN Appointment_Type at ON a.Appointment_Type_idAppointment_Type = at.idAppointment_Type
    JOIN Appointment_Status ast ON a.Appointment_Status_idAppointment_Status = ast.idAppointment_Status
    LEFT JOIN Appointment_User au_doc ON a.idAppointment = au_doc.Appointment_idAppointment 
        AND au_doc.User_idUser IN (SELECT idUser FROM User WHERE Role_idRole = 2)
    LEFT JOIN User u_doc ON au_doc.User_idUser = u_doc.idUser
    LEFT JOIN Appointment_User au_pat ON a.idAppointment = au_pat.Appointment_idAppointment 
        AND au_pat.User_idUser IN (SELECT idUser FROM User WHERE Role_idRole = 3)
    LEFT JOIN User u_pat ON au_pat.User_idUser = u_pat.idUser
    LEFT JOIN Address ad ON a.Address_idAddress = ad.idAddress
    LEFT JOIN City c ON ad.City_idCity = c.idCity
    WHERE 1=1
";

$params = [];

// 1. Search
if (!empty($input['search'])) {
    $search = '%' . trim($input['search']) . '%';
    $sql .= " AND (
        u_doc.name LIKE ? OR 
        u_doc.last_name LIKE ? OR 
        CONCAT(u_doc.name, ' ', u_doc.last_name) LIKE ? OR
        u_pat.name LIKE ? OR 
        u_pat.last_name LIKE ? OR
        CONCAT(u_pat.name, ' ', u_pat.last_name) LIKE ?
    )";
    $params = array_merge($params, [$search, $search, $search, $search, $search, $search]);
}

// 2. Filter: Types
if (!empty($input['types'])) {
    $placeholders = implode(',', array_fill(0, count($input['types']), '?'));
    $sql .= " AND at.idAppointment_Type IN ($placeholders)";
    $params = array_merge($params, $input['types']);
}

// 3. Filter: Statuses
if (!empty($input['statuses'])) {
    $placeholders = implode(',', array_fill(0, count($input['statuses']), '?'));
    $sql .= " AND ast.idAppointment_Status IN ($placeholders)";
    $params = array_merge($params, $input['statuses']);
}

// 4. Filter: Prices
if (!empty($input['prices'])) {
    $placeholders = implode(',', array_fill(0, count($input['prices']), '?'));
    $sql .= " AND at.price IN ($placeholders)";
    $params = array_merge($params, $input['prices']);
}

// 5. Filter: Durations
if (!empty($input['durations'])) {
    $durations = $input['durations'];
    $hasNull = in_array('NULL', $durations, true) || in_array(null, $durations, true);
    
    // Filter out NULLs for the IN clause
    $validDurations = array_filter($durations, function($v) { return $v !== 'NULL' && $v !== null; });
    
    $conditions = [];
    if (!empty($validDurations)) {
        $placeholders = implode(',', array_fill(0, count($validDurations), '?'));
        $conditions[] = "at.duration IN ($placeholders)";
        $params = array_merge($params, $validDurations);
    }
    
    if ($hasNull) {
        $conditions[] = "at.duration IS NULL";
    }
    
    if (!empty($conditions)) {
        $sql .= " AND (" . implode(' OR ', $conditions) . ")";
    }
}

// 6. Filter: Locations
if (!empty($input['locations'])) {
    $locations = $input['locations'];
    $hasNull = in_array('NULL', $locations, true) || in_array(null, $locations, true);
    
    // Filter out NULLs for the IN clause
    $validLocations = array_filter($locations, function($v) { return $v !== 'NULL' && $v !== null; });
    
    $conditions = [];
    if (!empty($validLocations)) {
        $placeholders = implode(',', array_fill(0, count($validLocations), '?'));
        $conditions[] = "a.Address_idAddress IN ($placeholders)";
        $params = array_merge($params, $validLocations);
    }
    
    if ($hasNull) {
        $conditions[] = "a.Address_idAddress IS NULL";
    }
    
    if (!empty($conditions)) {
        $sql .= " AND (" . implode(' OR ', $conditions) . ")";
    }
}

// Order by date desc
$sql .= " ORDER BY a.datetime DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process data for frontend
    foreach ($appointments as &$appt) {
        // Format Date
        $dateObj = new DateTime($appt['datetime']);
        $appt['formatted_date'] = $dateObj->format('d.m.Y');
        $appt['formatted_time'] = $dateObj->format('H:i');
        
        // Location Name
        if ($appt['Address_idAddress'] === null) {
            $appt['location_display'] = 'Online';
        } else {
            $appt['location_display'] = $appt['city_name'] ?? 'Nepoznato';
        }
        
        // Status Translation
        $status_map = [
            'confirmed' => 'Potvrđeno',
            'completed' => 'Završeno',
            'cancelled' => 'Otkazano',
            'unconfirmed' => 'Nepotvrđeno'
        ];
        $appt['status_display'] = isset($status_map[$appt['status_name']]) ? $status_map[$appt['status_name']] : $appt['status_name'];
    }
    
    echo json_encode(['success' => true, 'data' => $appointments, 'count' => count($appointments)]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
