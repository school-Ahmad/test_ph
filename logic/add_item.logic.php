<?php
// logic/add_item.logic.php

require_once './config/database.php'; // Zorg dat de database verbinding hier beschikbaar is

$message = '';
$message_type = ''; // 'success' of 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vervang FILTER_SANITIZE_STRING door FILTER_UNSAFE_RAW en strip_tags()
    // FILTER_UNSAFE_RAW haalt de ruwe string op.
    // strip_tags() verwijdert vervolgens alle HTML- en PHP-tags, vergelijkbaar met FILTER_SANITIZE_STRING.
    $title = filter_input(INPUT_POST, 'title', FILTER_UNSAFE_RAW);
    $description = filter_input(INPUT_POST, 'description', FILTER_UNSAFE_RAW);

    // Zorg ervoor dat de waarden niet null zijn voordat strip_tags wordt toegepast
    $title = $title !== null ? strip_tags($title) : '';
    $description = $description !== null ? strip_tags($description) : '';

    // Initialisatie van bestandsgegevens
    $imageFile = $_FILES['image'] ?? null;
    $uniqueFileName = '';

    // Validatie
    // De 'required' attribuut op de input en deze server-side check zorgen ervoor
    // dat er altijd een bestand geselecteerd moet zijn.
    // Als de gebruiker client-side de selectie verwijdert, zal 'imageFile' leeg zijn
    // en deze validatie afvangen.
    if (empty($title) || empty($description) || $imageFile === null || $imageFile['error'] !== UPLOAD_ERR_OK) {
        $message = 'Alle velden (Titel, Beschrijving, Afbeelding) zijn verplicht en een afbeelding moet correct worden geüpload.';
        $message_type = 'error';
    } else {
        $uploadDir = './uploads/'; // Directory om geüploade afbeeldingen op te slaan
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Maak de directory als deze niet bestaat
        }

        $imageFileName = basename($imageFile['name']);
        $imageFileType = strtolower(pathinfo($imageFileName, PATHINFO_EXTENSION));
        $uniqueFileName = uniqid('item_') . '.' . $imageFileType; // Genereer een unieke bestandsnaam
        $targetFilePath = $uploadDir . $uniqueFileName;

        // Controleer of het bestand een echte afbeelding is
        $check = getimagesize($imageFile['tmp_name']);
        if ($check === false) {
            $message = 'Bestand is geen afbeelding.';
            $message_type = 'error';
        } else {
            // Toegestane bestandstypen
            $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
            if (!in_array($imageFileType, $allowedTypes)) {
                $message = 'Alleen JPG, JPEG, PNG & GIF bestanden zijn toegestaan.';
                $message_type = 'error';
            } else {
                // Controleer bestandsgrootte (bijv. 5MB)
                if ($imageFile['size'] > 5 * 1024 * 1024) {
                    $message = 'Afbeelding is te groot, maximale grootte is 5MB.';
                    $message_type = 'error';
                } else {
                    // Verplaats de geüploade afbeelding
                    if (move_uploaded_file($imageFile['tmp_name'], $targetFilePath)) {
                        try {
                            // Bereid de SQL-insert voor
                            $stmt = $pdo->prepare("INSERT INTO items (title, description, image_path) VALUES (:title, :description, :image_path)");
                            $stmt->bindParam(':title', $title);
                            $stmt->bindParam(':description', $description);
                            $stmt->bindParam(':image_path', $uniqueFileName);
                            $stmt->execute();

                            $message = 'Item succesvol toegevoegd!';
                            $message_type = 'success';

                            // Optioneel: reset formulierwaarden na succesvolle indiening
                            // Dit zorgt ervoor dat het formulier leeg is na succesvolle submit.
                            // Commentarieer uit als je de waarden wilt behouden.
                            // $_POST = [];
                        } catch (PDOException $e) {
                            $message = 'Fout bij het toevoegen van het item aan de database: ' . $e->getMessage();
                            $message_type = 'error';
                            // Verwijder de geüploade afbeelding als de database-insert mislukt
                            if (file_exists($targetFilePath)) {
                                unlink($targetFilePath);
                            }
                        }
                    } else {
                        $message = 'Fout bij het uploaden van de afbeelding.';
                        $message_type = 'error';
                    }
                }
            }
        }
    }
}

// Data die aan de view moet worden doorgegeven
$view_data = [
    'message' => $message,
    'message_type' => $message_type,
    // Voeg hier eventueel andere data toe die de view nodig heeft
];

// Include de view file
require_once './views/add_item.view.php';