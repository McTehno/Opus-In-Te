<?php

$host = 'podatkovna-baza';
$db   = 'opus';
$user = 'root';
$pass = 'superVarnoGeslo';
$charset = 'utf8mb4';

// (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // 4. Create the PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // test
    echo "Connection Success with db 'Opus'!"; 
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>