<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$page = $_GET['page'] ?? 'dashboard';

// Blokkeer toegang als niet ingelogd en niet op loginpagina
if (!isset($_SESSION['login']) && $page !== 'login') {
    header('Location: index.php?page=login');
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
