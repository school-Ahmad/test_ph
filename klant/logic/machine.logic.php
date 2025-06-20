<?php
// /test_PH/klant/logic/machine.logic.php

// Zorg ervoor dat de databaseverbinding beschikbaar is
// Het pad is relatief vanaf de locatie van dit bestand naar de root van /test_PH/config/database.php
require_once __DIR__ . '/../../config/database.php';

$items = []; // Initialiseer een lege array om de items in op te slaan
$error_message = null; // Variabele om eventuele foutmeldingen op te slaan

try {
    // Bereid de SQL-query voor om alle items op te halen
    // We selecteren alle relevante kolommen
    $stmt = $pdo->prepare("SELECT id, title, description, image_path, created_at FROM items ORDER BY created_at DESC");
    $stmt->execute();

    // Haal alle resultaten op als een associatieve array
    $items = $stmt->fetchAll();

} catch (PDOException $e) {
    // Log de fout voor debugging (niet direct weergeven aan de gebruiker in productie)
    error_log("Database error in machine.logic.php: " . $e->getMessage());
    // Stel een gebruikersvriendelijke foutmelding in
    $error_message = "Er is een fout opgetreden bij het ophalen van de machinegegevens. Probeer het later opnieuw.";
}

// De $items array bevat nu de opgehaalde gegevens (of is leeg bij een fout of geen resultaten).
// De $error_message variabele bevat een foutmelding als er een probleem was.
// Deze variabelen zijn beschikbaar voor het view-bestand dat dit logic-bestand includeert.
?>