<?php
// Tijdelijk upload script voor bestanden van product detail pagina

// --- Error Reporting & Logging ---
error_reporting(E_ALL);
ini_set('display_errors', 0); // Zet UIT voor AJAX response, anders kan JSON breken
ini_set('log_errors', 1);
// ini_set('error_log', dirname(__DIR__, 2) . '/../php_error.log'); // Pas pad aan indien nodig

// --- Configuratie ---
// Geen database nodig hier, alleen paden
define('PROJECT_ROOT', dirname(__DIR__, 3)); // Gaat 3 levels omhoog naar /test_ph/
define('TEMP_UPLOAD_DIR_ABS', PROJECT_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);
define('MAX_FILE_SIZE_TEMP', 10 * 1024 * 1024); // 10 MB limiet (zelfde als frontend)
// Optioneel: Definieer toegestane MIME types of extensies voor extra veiligheid
// $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png', ...];
// $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', ...];

// --- Helper Functie ---
function sendTempJsonResponse($data) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($data);
    exit;
}

// --- Request Validatie ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[upload_temp_file] Ongeldige request methode: " . $_SERVER['REQUEST_METHOD']);
    sendTempJsonResponse(['status' => 'error', 'message' => 'Ongeldige request.']);
}

// --- Map Check ---
if (!is_dir(TEMP_UPLOAD_DIR_ABS) || !is_writable(TEMP_UPLOAD_DIR_ABS)) {
    error_log("[upload_temp_file] FATAL: Tijdelijke upload map niet gevonden of niet schrijfbaar: " . TEMP_UPLOAD_DIR_ABS);
    sendTempJsonResponse(['status' => 'error', 'message' => 'Server configuratiefout (temp map).']);
}

// --- Bestandsverwerking ---
// We verwachten één bestand met de naam 'uploaded_file' (zoals we in JS zullen instellen)
if (!isset($_FILES['uploaded_file'])) {
    error_log("[upload_temp_file] Geen bestand ontvangen in \$_FILES['uploaded_file'].");
    sendTempJsonResponse(['status' => 'error', 'message' => 'Geen bestand ontvangen.']);
}

$file = $_FILES['uploaded_file'];

// 1. Check Upload Error Code
if ($file['error'] !== UPLOAD_ERR_OK) {
    $upload_error_message = match ($file['error']) {
        UPLOAD_ERR_INI_SIZE => 'Bestand te groot (server limiet).',
        UPLOAD_ERR_FORM_SIZE => 'Bestand te groot (formulier limiet).',
        UPLOAD_ERR_PARTIAL => 'Bestand slechts gedeeltelijk geüpload.',
        UPLOAD_ERR_NO_FILE => 'Geen bestand geselecteerd.', // Zou niet moeten gebeuren door JS check
        UPLOAD_ERR_NO_TMP_DIR => 'Server configuratiefout (geen tijdelijke map).',
        UPLOAD_ERR_CANT_WRITE => 'Server configuratiefout (schrijven mislukt).',
        UPLOAD_ERR_EXTENSION => 'Upload gestopt door PHP extensie.',
        default => 'Onbekende uploadfout.',
    };
    error_log("[upload_temp_file] Uploadfout (Code: {$file['error']}): {$upload_error_message}");
    sendTempJsonResponse(['status' => 'error', 'message' => "Uploadfout: {$upload_error_message}"]);
}

// 2. Validatie (Grootte, Type)
if ($file['size'] > MAX_FILE_SIZE_TEMP) {
    error_log("[upload_temp_file] Bestand te groot: {$file['size']} > " . MAX_FILE_SIZE_TEMP);
    sendTempJsonResponse(['status' => 'error', 'message' => 'Bestand is te groot (Max ' . (MAX_FILE_SIZE_TEMP / 1024 / 1024) . 'MB).']);
}

// Optioneel: Type validatie (veiliger)
/*
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file['tmp_name']);
$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($mime_type, $allowed_mime_types) || !in_array($file_ext, $allowed_extensions)) {
    error_log("[upload_temp_file] Ongeldig bestandstype: MIME='{$mime_type}', Ext='{$file_ext}'");
    sendTempJsonResponse(['status' => 'error', 'message' => 'Ongeldig bestandstype.']);
}
*/

// 3. Genereer Unieke Tijdelijke Naam
$original_filename = $file['name'];
$safe_original_name = preg_replace("/[^a-zA-Z0-9_.-]/", "_", $original_filename);
// Voeg random data toe om naamconflicten te voorkomen en het moeilijker te raden te maken
$unique_temp_filename = uniqid('temp_', true) . '_' . bin2hex(random_bytes(8)) . '_' . $safe_original_name;
$absolute_target_path = rtrim(TEMP_UPLOAD_DIR_ABS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $unique_temp_filename;

// 4. Verplaats Bestand naar Tijdelijke Map
error_log("[upload_temp_file] Poging tot move_uploaded_file van '{$file['tmp_name']}' naar '{$absolute_target_path}'");
if (move_uploaded_file($file['tmp_name'], $absolute_target_path)) {
    error_log("[upload_temp_file] SUCCES: Tijdelijk bestand opgeslagen: {$unique_temp_filename}");
    // Stuur de unieke tijdelijke naam en de originele naam terug
    sendTempJsonResponse([
        'status' => 'success',
        'temp_filename' => $unique_temp_filename, // De naam van het bestand op de server
        'original_filename' => $original_filename // De naam die de gebruiker ziet
    ]);
} else {
    $error = error_get_last();
    error_log("[upload_temp_file] !!! MISLUKT: move_uploaded_file() faalde !!! Error: " . ($error['message'] ?? 'Onbekend'));
    sendTempJsonResponse(['status' => 'error', 'message' => 'Kon bestand niet tijdelijk opslaan. Serverfout.']);
}

?>