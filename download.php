<?php
// /test_PH/download.php

// Zorg ervoor dat je database.php correct wordt ingeladen
require_once __DIR__ . '/config/database.php';

// Zorg ervoor dat sessies gestart zijn als je authenticatie gebruikt
// session_start(); // Uncomment this if you use sessions for admin login

// --- Beveiliging: Controleer of de gebruiker een admin is ---
// Dit is CRUCIAAL! Zonder dit kan iedereen met de juiste URL bestanden downloaden.
// Implementeer hier je eigen admin check logica. Bijvoorbeeld:
/*
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Niet geautoriseerd
    http_response_code(403); // Forbidden
    die("Toegang geweigerd.");
}
*/
// Voor dit voorbeeld slaan we de authenticatie over, maar voeg dit ZEKER toe in productie!
// --- Einde Beveiliging ---


// Controleer of de order_id parameter aanwezig is in de URL
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    http_response_code(400); // Bad Request
    die("Ongeldige bestellings-ID.");
}

$orderId = (int)$_GET['order_id'];

// Haal het bestandspad en de originele naam op uit de database
$sql = "SELECT bestandspad, bestand_originele_naam FROM bestellingen WHERE id = ?";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId]);
    $bestelling = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bestelling || empty($bestelling['bestandspad'])) {
        http_response_code(404); // Not Found
        die("Bestelling of bestand niet gevonden.");
    }

    $bestandspad_db = $bestelling['bestandspad'];
    $originele_naam = $bestelling['bestand_originele_naam'] ?? 'bestand'; // Fallback naam

    // Construct het volledige pad naar het bestand op de server
    // __DIR__ is de directory van het huidige script (/test_PH/)
    // We voegen het pad uit de database toe (bijv. 'Orders/...')
    $full_file_path = __DIR__ . '/' . $bestandspad_db;

    // Controleer of het bestand daadwerkelijk bestaat op de server
    if (!file_exists($full_file_path)) {
        http_response_code(404); // Not Found
        die("Bestand niet gevonden op de server.");
    }

    // --- Bestand downloaden ---

    // Zorg ervoor dat er geen onnodige output is voordat we headers sturen
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Stel de juiste headers in voor een bestand download
    header('Content-Description: File Transfer');
    // Probeer het MIME type te detecteren, anders gebruik een generiek type
    $mime_type = mime_content_type($full_file_path);
    header('Content-Type: ' . ($mime_type ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . basename($originele_naam) . '"'); // Gebruik originele naam voor download
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($full_file_path));

    // Lees het bestand en stuur het naar de output buffer
    readfile($full_file_path);

    exit; // Stop de scriptuitvoering na het downloaden

} catch (PDOException $e) {
    // Foutafhandeling bij databasefout
    error_log("Error fetching file path for order ID " . $orderId . ": " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    die("Er ging iets mis bij het ophalen van de bestandsinformatie.");
} catch (Exception $e) {
    // Algemene foutafhandeling (bijv. mime_content_type fout)
    error_log("Error during file download for order ID " . $orderId . ": " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    die("Er ging iets mis tijdens het downloaden van het bestand.");
}

?>