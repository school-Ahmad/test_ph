<?php
// File: /test_PH/klant/logic/upload_temp_file.logic.php

// --- Error Reporting & Logging ---
error_reporting(E_ALL);
ini_set('display_errors', 0); // Zet UIT voor AJAX response
ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/../../../logs/php_errors.log'); // Voorbeeld: specifieke logfile

// --- Configuratie & Includes ---
require_once __DIR__ . '/../../config/database.php'; // Pas pad aan naar database.php

// --- Definieer Paden ---
// PROJECT_ROOT is de map /test_PH/
define('PROJECT_ROOT', __DIR__ . '/../../..'); // Ga 3 niveaus omhoog vanuit klant/logic

// Pad naar de *tijdelijke* upload map
define('TEMP_UPLOAD_DIR_ABS', PROJECT_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);
// Relatief pad voor DB (vanaf webroot /test_PH/)
define('TEMP_UPLOAD_DIR_REL', 'uploads/temp/');

// --- Helper Functie ---
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit; // Stop script na versturen response
}

// --- Request Validatie ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[upload_temp_file] Ongeldige request methode: " . $_SERVER['REQUEST_METHOD']);
    sendJsonResponse(['status' => 'error', 'message' => 'Ongeldige request methode.']);
}

// Controleer of er een bestand is geüpload
if (!isset($_FILES['product_file']) || $_FILES['product_file']['error'] !== UPLOAD_ERR_OK) {
    $error_code = $_FILES['product_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $error_message = "Geen bestand geüpload of uploadfout ({$error_code}).";
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $error_message = "Het geüploade bestand is te groot (max. " . ini_get('upload_max_filesize') . ").";
            break;
        case UPLOAD_ERR_PARTIAL:
            $error_message = "Het bestand is maar gedeeltelijk geüpload.";
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $error_message = "Tijdelijke map ontbreekt op de server.";
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $error_message = "Kan niet schrijven naar schijf.";
            break;
        case UPLOAD_ERR_EXTENSION:
            $error_message = "Een PHP-extensie heeft de upload gestopt.";
            break;
    }
    error_log("[upload_temp_file] Upload fout: " . $error_message);
    sendJsonResponse(['status' => 'error', 'message' => $error_message]);
}

$file = $_FILES['product_file'];
$original_filename = basename($file['name']); // Gebruik basename voor veiligheid
$temp_file_path = $file['tmp_name'];
$file_size = $file['size'];
$file_type = $file['type'];

// --- Server-side Validatie (basis) ---
// Max file size (10MB) - moet overeenkomen met JS en php.ini
$max_file_size_bytes = 10 * 1024 * 1024;
if ($file_size > $max_file_size_bytes) {
    error_log("[upload_temp_file] Bestand te groot: " . $original_filename . " (" . $file_size . " bytes)");
    sendJsonResponse(['status' => 'error', 'message' => 'Bestand is te groot. Maximaal 10MB.']);
}

// Toegestane bestandstypen (uitgebreider dan alleen accept attribute)
$allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'pdf', 'ai', 'eps', 'svg', 'zip', 'rar'];
$file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    error_log("[upload_temp_file] Ongeldig bestandstype: " . $original_filename . " (ext: " . $file_extension . ")");
    sendJsonResponse(['status' => 'error', 'message' => 'Ongeldig bestandstype. Toegestaan: ' . implode(', ', $allowed_extensions) . '.']);
}

// --- Map Checks ---
if (!is_dir(TEMP_UPLOAD_DIR_ABS)) {
    if (!mkdir(TEMP_UPLOAD_DIR_ABS, 0755, true)) { // Maak map recursief aan
        error_log("[upload_temp_file] Kan tijdelijke upload map niet aanmaken: " . TEMP_UPLOAD_DIR_ABS);
        sendJsonResponse(['status' => 'error', 'message' => 'Serverfout: Kan tijdelijke map niet aanmaken.']);
    }
}
if (!is_writable(TEMP_UPLOAD_DIR_ABS)) {
    error_log("[upload_temp_file] Tijdelijke upload map is niet schrijfbaar: " . TEMP_UPLOAD_DIR_ABS);
    sendJsonResponse(['status' => 'error', 'message' => 'Serverfout: Tijdelijke map is niet schrijfbaar.']);
}

// --- Bestand Verplaatsen naar Tijdelijke Locatie ---
// Genereer een unieke naam voor het tijdelijke bestand
$unique_filename = uniqid('temp_') . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
$destination_path_abs = TEMP_UPLOAD_DIR_ABS . $unique_filename;
$destination_path_rel = TEMP_UPLOAD_DIR_REL . $unique_filename; // Dit is wat we terugsturen

error_log("[upload_temp_file] Poging tot verplaatsen: " . $temp_file_path . " naar " . $destination_path_abs);

if (move_uploaded_file($temp_file_path, $destination_path_abs)) {
    // Optioneel: Zet permissies op het verplaatste bestand
    // chmod($destination_path_abs, 0644);
    error_log("[upload_temp_file] Bestand succesvol verplaatst: " . $destination_path_abs);
    sendJsonResponse([
        'status' => 'success',
        'message' => 'Bestand succesvol geüpload.',
        'temp_file_path' => $destination_path_rel, // Relatief pad voor opslag in localStorage
        'original_filename' => $original_filename,
        'file_size' => $file_size,
        'file_type' => $file_type
    ]);
} else {
    $error = error_get_last();
    error_log("[upload_temp_file] Fout bij move_uploaded_file: " . ($error['message'] ?? 'Onbekende fout') . " (Source: {$temp_file_path}, Dest: {$destination_path_abs})");
    sendJsonResponse(['status' => 'error', 'message' => 'Kon bestand niet opslaan op de server.']);
}
?>