<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$page = $_GET['page'] ?? 'dashboard';
$ingelogdAls = $_SESSION['ingelogdAls'] ?? null;

$publicPages = ['login', 'logout'];

// Niet ingelogd → naar login
if (!isset($_SESSION['login']) && !in_array($page, $publicPages)) {
    header('Location: index.php?page=login');
    exit;
}

// Studenten mogen niet naar dashboard/producten/add_item
$studentRestricted = ['dashboard', 'producten', 'add_item'];
if ($ingelogdAls === 'STUDENT' && in_array($page, $studentRestricted)) {
    header('Location: ../klant/views/index.php'); // geforceerde redirect als check
    exit;
}

switch ($page) {
    case 'dashboard':
        require_once './logic/dashboard.logic.php';
        break;
    case 'producten':
        require_once './logic/producten.logic.php';
        break;
    case 'add_item':
        require_once './logic/add_item.logic.php';
        break;
    case 'login':
        require_once './logic/login.logic.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    default:
        require_once './logic/dashboard.logic.php';
        break;
}
