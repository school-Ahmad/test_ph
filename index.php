<?php
// index.php - Frontcontroller

// Zet foutmeldingen aan voor debugdoeleinden (uitschakelen in productie!)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Bepaal welke pagina wordt opgevraagd (default: dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

switch ($page) {
    case 'dashboard':
        require_once './logic/dashboard.logic.php';
        break;
    case 'producten':
        require_once './logic/producten.logic.php';
        break;
    case 'add_item': // NIEUWE CASE voor het toevoegen van items
        require_once './logic/add_item.logic.php';
        break;
    default:
        // Optioneel: handle 404 error of redirect naar dashboard
        require_once './logic/dashboard.logic.php'; // Fallback naar dashboard
        break;
}
?>