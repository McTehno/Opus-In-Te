<?php

$host = 'podatkovna-baza';
$db   = 'opus';
$user = 'root';
$pass = 'superVarnoGeslo';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return arrays indexed by column name
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements (better security)
];

// (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // test
    //echo "Connection Success with db 'Opus'!"; 
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>