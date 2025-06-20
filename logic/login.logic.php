<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = $_POST['gebruikersnaam'] ?? '';
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    $postData = http_build_query([
        'gebruikersnaam' => $gebruikersnaam,
        'wachtwoord' => $wachtwoord
    ]);

    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'content' => $postData,
            'timeout' => 5
        ]
    ];

    $context = stream_context_create($opts);
    $response = @file_get_contents('https://api.interpol.sd-lab.nl/api/create-session', false, $context);

    if ($response === false) {
        $error = 'Kan geen verbinding maken met de authenticatieserver.';
    } else {
        $data = json_decode($response, true);

        if (isset($data['message'])) {
            $_SESSION['login'] = true;
            $_SESSION['ingelogdAls'] = $data['session']['ingelogdAls'] ?? null;
            $_SESSION['gebruikersnaam'] = $gebruikersnaam;
            $_SESSION['mail'] = $data['session']['mail'] ?? '';
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = $data['error'] ?? 'Ongeldige inloggegevens.';
        }
    }
}

require_once './views/login.view.php';
