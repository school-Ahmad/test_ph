<?php
session_start();
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!isset($data['session'])) {
    echo json_encode(['error' => 'No session data']);
    exit;
}

$session = $data['session'];

$_SESSION['login'] = true;
$_SESSION['ingelogdAls'] = $session['ingelogdAls'] ?? null;
$_SESSION['gebruikersnaam'] = $session['gebruikersnaam'] ?? '';
$_SESSION['mail'] = $session['mail'] ?? '';

echo json_encode(['status' => 'ok']);
