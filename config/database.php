<?php
// config/database.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db   = 'PH_Order';
$user = 'db088484';
$pass = 'Momo6318!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Zorgt ervoor dat PDO-excepties gegooid worden bij fouten
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Toon de foutmelding voor debuggen (uitschakelen in productie)
    echo "Fout bij verbinden met de database: " . $e->getMessage();
    exit();
}
?>
