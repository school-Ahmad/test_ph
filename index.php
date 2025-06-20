<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$page = $_GET['page'] ?? 'dashboard';

$publicPages = ['login', 'logout', 'set-session'];
$ingelogdAls = $_SESSION['ingelogdAls'] ?? null;

// Niet ingelogd? Alleen login/set-session toegestaan
if (!isset($_SESSION['login']) && !in_array($page, $publicPages)) {
    header('Location: index.php?page=login');
    exit;
}

// Studenten mogen geen toegang tot deze pagina's
$studentRestricted = ['dashboard', 'producten', 'add_item'];
if ($ingelogdAls === 'STUDENT' && in_array($page, $studentRestricted)) {
    header('Location: ../klant/views/index.php');
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
    case 'set-session':
        require_once './logic/set-session.logic.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    default:
        require_once './logic/dashboard.logic.php';
        break;
}
