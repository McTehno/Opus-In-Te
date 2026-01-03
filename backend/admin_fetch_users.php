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
    // Fetch users (Role=3) and workers (Role=2)
    $sql = "
        SELECT 
            u.idUser, 
            u.name, 
            u.last_name, 
            u.phone, 
            u.email, 
            u.pass,
            u.Role_idRole,
            u.picture_path,
            COUNT(au.Appointment_idAppointment) as appointment_count
        FROM User u
        LEFT JOIN Appointment_User au ON u.idUser = au.User_idUser
        WHERE u.Role_idRole IN (2, 3)
        GROUP BY u.idUser
        ORDER BY u.Role_idRole ASC, u.last_name ASC, u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate stats
    $workersCount = 0;
    $usersWithAccount = 0;
    $usersWithoutAccount = 0;

    foreach ($users as $user) {
        if ($user['Role_idRole'] == 2) {
            $workersCount++;
        } elseif ($user['Role_idRole'] == 3) {
            if ($user['pass'] === null) {
                $usersWithoutAccount++;
            } else {
                $usersWithAccount++;
            }
        }
    }

    $stats = [
        'workers_count' => $workersCount,
        'users_with_account' => $usersWithAccount,
        'users_without_account' => $usersWithoutAccount
    ];

    echo json_encode([
        'success' => true,
        'users' => $users,
        'stats' => $stats
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
