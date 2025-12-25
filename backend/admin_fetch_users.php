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
    // Fetch users and their appointment counts
    // Assuming Role_idRole = 3 are the regular users/patients
    $sql = "
        SELECT 
            u.idUser, 
            u.name, 
            u.last_name, 
            u.phone, 
            u.email, 
            u.pass,
            COUNT(au.Appointment_idAppointment) as appointment_count
        FROM User u
        LEFT JOIN Appointment_User au ON u.idUser = au.User_idUser
        WHERE u.Role_idRole = 3
        GROUP BY u.idUser
        ORDER BY u.last_name ASC, u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate stats
    $totalUsers = count($users);
    $usersWithAccount = 0;
    $usersWithoutAccount = 0;

    foreach ($users as $user) {
        if ($user['pass'] === null) {
            $usersWithoutAccount++;
        } else {
            $usersWithAccount++;
        }
    }

    $stats = [
        'total_users' => $totalUsers,
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
