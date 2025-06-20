<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $postData = http_build_query([
        'username' => $username,
        'password' => $password
    ]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'content' => $postData,
            'timeout' => 5
        ]
    ]);

    $response = @file_get_contents('https://api.interpol.sd-lab.nl/api/create-session', false, $context);

    if ($response === false) {
        $error = 'Authenticatieserver niet bereikbaar.';
    } else {
        $data = json_decode($response, true);

        if (isset($data['message'])) {
            // Zet sessie
            $_SESSION['login'] = true;
            $_SESSION['ingelogdAls'] = $data['session']['ingelogdAls'] ?? null;
            $_SESSION['username'] = $username;
            $_SESSION['mail'] = $data['session']['mail'] ?? '';

            // Doorsturen op basis van rol
            if ($_SESSION['ingelogdAls'] === 'DOCENT') {
                header('Location: index.php?page=dashboard');
                exit;
            } elseif ($_SESSION['ingelogdAls'] === 'STUDENT') {
                header('Location: ../klant/views/index.php');
                exit;
            } else {
                session_destroy();
                header('Location: index.php?page=login');
                exit;
            }
        } else {
            $error = $data['error'] ?? 'Ongeldige inloggegevens.';
        }
    }
}

require_once './views/login.view.php';
