<?php
session_start();

// --- Error Reporting & Logging ---
error_reporting(E_ALL);
ini_set('display_errors', 0); // Zet UIT voor AJAX response
ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/../../logs/php_errors.log'); // Voorbeeld: specifieke logfile

// --- Configuratie & Includes ---
require_once __DIR__ . '/../../config/database.php';

// --- Definieer Paden ---
// Pas dit pad aan naar de daadwerkelijke root van je project op de server
// Dit pad moet overeenkomen met waar /test_PH/ zich bevindt
// Bijvoorbeeld: /var/www/vhosts/088484.stu.sd-lab.nl/httpdocs/test_PH
// Aangenomen dat /test_PH/ is de root van je webapplicatie
define('PROJECT_ROOT', __DIR__ . '/../..'); // Ga 2 niveaus omhoog vanuit logic/klant/logic

// !! BELANGRIJK: Pad naar de *tijdelijke* map !!
// Deze map wordt gebruikt door de initiële upload (niet getoond in JS),
// maar de checkout JS stuurt de file direct via $_FILES.
// De PHP move_uploaded_file() gebruikt PHP's interne temp dir, niet deze.
// Deze definitie is hier minder relevant voor de checkout move, maar kan elders nodig zijn.
define('TEMP_UPLOAD_DIR_ABS', PROJECT_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR);

// Pad naar de *definitieve* map
// Dit moet wijzen naar /test_PH/Orders/
define('ORDER_UPLOAD_DIR_ABS', PROJECT_ROOT . DIRECTORY_SEPARATOR . 'Orders' . DIRECTORY_SEPARATOR);
// Relatief pad voor DB (vanaf webroot /test_PH/)
define('ORDER_UPLOAD_DIR_REL', 'Orders/');

// --- Helper Functie ---
// Zorg dat deze functie bestaat en JSON correct verstuurt en script stopt
if (!function_exists('sendJsonResponse')) {
    function sendJsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit; // Stop script na versturen response
    }
}


// --- Request Validatie & Logging ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[File Processing] Ongeldige request methode: " . $_SERVER['REQUEST_METHOD']);
    sendJsonResponse(['status' => 'error', 'message' => 'Ongeldige request methode.']);
}
error_log("--- [winkelwagen.logic - File Processing] Nieuwe Checkout Poging ---");
error_log("[File Processing] Script user: " . exec('whoami'));
error_log("[File Processing] POST data: " . print_r($_POST, true));
// $_FILES should contain the re-uploaded files from the checkout form
error_log("[File Processing] FILES data: " . print_r($_FILES, true));


// --- Ontvang en Valideer Data ---
$klant_email = trim($_POST['klant_email'] ?? '');
$cart_metadata_json = $_POST['cart_metadata'] ?? '';
// Validatie (E-mail, Metadata JSON, Lege winkelwagen)
if (empty($klant_email) || !filter_var($klant_email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($klant_email), '@glr.nl')) {
    error_log("[File Processing] Ongeldig of ontbrekend e-mailadres: " . $klant_email);
    sendJsonResponse(['status' => 'error', 'message' => 'Ongeldig e-mailadres. Gebruik een @glr.nl adres.']);
}
if (empty($cart_metadata_json)) {
    error_log("[File Processing] Ontbrekende winkelwagen metadata.");
    sendJsonResponse(['status' => 'error', 'message' => 'Winkelwagen data ontbreekt.']);
}
$cart_metadata = json_decode($cart_metadata_json, true);
if ($cart_metadata === null || json_last_error() !== JSON_ERROR_NONE) {
    error_log("[File Processing] Fout bij parsen winkelwagen metadata JSON: " . json_last_error_msg());
    sendJsonResponse(['status' => 'error', 'message' => 'Ongeldige winkelwagen data ontvangen.']);
}
if (empty($cart_metadata)) {
    error_log("[File Processing] Winkelwagen metadata is leeg.");
    sendJsonResponse(['status' => 'error', 'message' => 'Winkelwagen is leeg.']);
}

// --- Map Checks (Controleer de definitieve map) ---
// De tijdelijke map check is minder kritiek hier omdat move_uploaded_file PHP's interne temp dir gebruikt.
// Maar de ORDERS map moet absoluut schrijfbaar zijn.
error_log("[File Processing] Check ORDERS map (ABS): " . ORDER_UPLOAD_DIR_ABS);
// ** DEZE CHECK IS WAAR DE FOUT OPTREEDT ALS PERMISSIES NIET GOED ZIJN **
if (!is_dir(ORDER_UPLOAD_DIR_ABS) || !is_writable(ORDER_UPLOAD_DIR_ABS)) {
    error_log("[File Processing] !!! FATAL: Orders map niet gevonden of niet schrijfbaar! Pad: " . ORDER_UPLOAD_DIR_ABS);
    // ** DEZE RESPONSE WORDT VERSTUURD **
    sendJsonResponse(['status' => 'error', 'message' => 'Serverfout: Kan bestanden niet opslaan (map permissies).']);
} else { error_log("[File Processing] Orders map OK."); }


// --- Database Connectie Check ---
global $pdo;
if (!$pdo) {
    error_log("[File Processing] !!! FATAL: Database connectie mislukt.");
    sendJsonResponse(['status' => 'error', 'message' => 'Database connectie mislukt.']);
}


// --- Verwerk Bestanden en Database ---
$bestelling_ids = [];

try {
    $pdo->beginTransaction();
    error_log("[File Processing] DB Transactie gestart.");

    // Haal de geüploade bestanden op uit $_FILES['cart_files']
    $uploaded_files = $_FILES['cart_files'] ?? [];
    error_log("[File Processing] Aantal items in \$uploaded_files: " . count($uploaded_files['name'] ?? []));


    foreach ($cart_metadata as $itemKey => $item) {
        error_log("--- [File Processing] Verwerken item: {$itemKey} ---");

        // Item data validatie uit metadata
        $product_id = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($item['quantity'] ?? 1, FILTER_VALIDATE_INT);
        $options = $item['options'] ?? [];
        // Haal de originele naam op uit metadata (voor DB, kan afwijken van geüploade naam)
        $original_filename_from_meta = $item['original_filename'] ?? null;

        if (!$product_id || $quantity < 1) {
            error_log("[Item {$itemKey}] Ongeldige data: pid={$product_id}, qty={$quantity}");
            throw new Exception("Ongeldige data voor item: $itemKey");
        }
        error_log("[Item {$itemKey}] Data: pid={$product_id}, qty={$quantity}, orig_name_meta='{$original_filename_from_meta}'");

        // Reset variabelen voor dit item
        $bestandspad_relatief = null; // Definitief pad voor DB
        $bestand_originele_naam_db = $original_filename_from_meta; // Originele naam voor DB (voorkeur uit metadata)

        // --- Bestandsverwerking: Verplaats geüploade file uit $_FILES ---
        // Check of er een bestand is geüpload voor dit specifieke itemKey
        // $_FILES['cart_files'] is een array met 'name', 'type', 'tmp_name', 'error', 'size'
        // Elk van deze is een array met de itemKeys als sleutels.
        $item_file_info = [
            'name' => $uploaded_files['name'][$itemKey] ?? null,
            'type' => $uploaded_files['type'][$itemKey] ?? null,
            'tmp_name' => $uploaded_files['tmp_name'][$itemKey] ?? null,
            'error' => $uploaded_files['error'][$itemKey] ?? UPLOAD_ERR_NO_FILE, // Default naar geen bestand
            'size' => $uploaded_files['size'][$itemKey] ?? 0,
        ];

        // Check of er een bestand is geüpload ZONDER fouten
        if ($item_file_info['error'] === UPLOAD_ERR_OK && !empty($item_file_info['tmp_name'])) {
            error_log("[Item {$itemKey}] Bestand gevonden in \$_FILES. Temp naam: " . $item_file_info['tmp_name']);

            // Gebruik de originele naam uit $_FILES als fallback/zekerheid voor DB naam
            if (empty($bestand_originele_naam_db)) {
                $bestand_originele_naam_db = $item_file_info['name'];
                error_log("[Item {$itemKey}] Gebruik originele naam uit \$_FILES: " . $bestand_originele_naam_db);
            }

            // **Security Check:** Zorg dat de originele naam geen paden bevat!
            $safe_original_name = basename($bestand_originele_naam_db); // Strip paden
            $safe_original_name = preg_replace("/[^a-zA-Z0-9_.-]/", "_", $safe_original_name ?: 'bestand'); // Verwijder onveilige karakters

            // Genereer een nieuwe unieke naam voor de Orders map
            $final_unique_suffix = time() . '_' . bin2hex(random_bytes(4));
            $final_unique_filename = $final_unique_suffix . '_' . $safe_original_name;

            // Bouw paden
            $absolute_target_path = rtrim(ORDER_UPLOAD_DIR_ABS, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $final_unique_filename;
            $current_relative_path = rtrim(ORDER_UPLOAD_DIR_REL, '/') . '/' . $final_unique_filename; // Pad voor DB

            error_log("[Item {$itemKey}] Voorbereiden verplaatsing (via move_uploaded_file):");
            error_log("  Bron (temp \$_FILES): {$item_file_info['tmp_name']}");
            error_log("  Doel (final): {$absolute_target_path}");
            error_log("  Pad voor DB (rel): {$current_relative_path}");

            // --- Poging tot Verplaatsen (move_uploaded_file) ---
            error_log("[Item {$itemKey}] Poging tot move_uploaded_file()...");
            if (move_uploaded_file($item_file_info['tmp_name'], $absolute_target_path)) {
                // --- SUCCES ---
                error_log("[Item {$itemKey}] +++ SUCCES: Bestand succesvol verplaatst naar {$absolute_target_path}");
                $bestandspad_relatief = $current_relative_path; // Zet pad voor DB
                // Optioneel: Zet permissies op definitief bestand (kan afhangen van server config)
                // chmod($absolute_target_path, 0644);
            } else {
                // --- MISLUKT ---
                $error = error_get_last();
                error_log("[Item {$itemKey}] !!! MISLUKT: move_uploaded_file() faalde !!!");
                error_log("  PHP Error: " . ($error['message'] ?? 'Onbekende fout') . " - Controleer permissies op de Orders map en PHP upload instellingen.");
                // Gooi exception als move faalt
                throw new Exception("Kon bestand '{$safe_original_name}' niet definitief opslaan.");
            }
        } elseif ($item_file_info['error'] !== UPLOAD_ERR_NO_FILE) {
            // Er was een upload poging, maar met een fout
            $upload_error_message = "Upload fout voor bestand '{$bestand_originele_naam_db}': ";
            switch ($item_file_info['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $upload_error_message .= "Bestand is te groot."; break;
                case UPLOAD_ERR_PARTIAL:
                    $upload_error_message .= "Bestand is maar gedeeltelijk geüpload."; break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $upload_error_message .= "Tijdelijke map ontbreekt op server."; break;
                case UPLOAD_ERR_CANT_WRITE:
                    $upload_error_message .= "Kan niet schrijven naar schijf."; break;
                case UPLOAD_ERR_EXTENSION:
                    $upload_error_message .= "PHP extensie stopte de upload."; break;
                default:
                    $upload_error_message .= "Onbekende upload fout ({$item_file_info['error']})."; break;
            }
            error_log("[Item {$itemKey}] !!! UPLOAD FOUT: " . $upload_error_message);
            throw new Exception($upload_error_message);

        } else {
            error_log("[Item {$itemKey}] Geen bestand geüpload voor dit item (UPLOAD_ERR_NO_FILE).");
            // $bestandspad_relatief blijft NULL, $bestand_originele_naam_db blijft uit metadata
        }

        // --- Database Opslag ---
        $gekozen_opties_json = json_encode($options);
        if ($gekozen_opties_json === false) {
            error_log("[Item {$itemKey}] Fout bij JSON encoding opties: " . json_last_error_msg());
            throw new Exception("Kon opties niet verwerken (JSON encoding fout).");
        }
        $sql = "INSERT INTO bestellingen (klant_email, product_id, gekozen_opties, bestandspad, bestand_originele_naam, aantal, status)
                VALUES (:email, :pid, :opties, :pad, :orig_naam, :aantal, 'nieuw')";
        $stmt = $pdo->prepare($sql);
        // Bind $bestandspad_relatief (is NULL als geen bestand geüpload/verwerkt)
        // Bind $bestand_originele_naam_db (is NULL als geen bestand en geen naam in metadata)
        $stmt->bindParam(':email', $klant_email);
        $stmt->bindParam(':pid', $product_id);
        $stmt->bindParam(':opties', $gekozen_opties_json);
        $stmt->bindParam(':pad', $bestandspad_relatief);
        $stmt->bindParam(':orig_naam', $bestand_originele_naam_db);
        $stmt->bindParam(':aantal', $quantity);

        error_log("[Item {$itemKey}] DB Insert parameters: email='{$klant_email}', pid={$product_id}, opties='{$gekozen_opties_json}', pad='{$bestandspad_relatief}', orig_naam='{$bestand_originele_naam_db}', aantal={$quantity}");

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            error_log("[Item {$itemKey}] !!! DB INSERT FOUT: " . print_r($errorInfo, true));
            throw new Exception("Database fout bij opslaan van item {$itemKey}.");
        } else {
            $lastId = $pdo->lastInsertId();
            $bestelling_ids[] = $lastId;
            error_log("[Item {$itemKey}] DB insert SUCCES. Bestelling ID: " . $lastId);
        }
        error_log("--- [File Processing] Einde verwerking item: {$itemKey} ---");
    } // Einde foreach

    // Commit transactie
    $pdo->commit();
    error_log("[File Processing] DB Transactie succesvol gecommit.");

    // Stuur succes response
    sendJsonResponse(['status' => 'success', 'message' => 'Bestelling succesvol ontvangen!', 'bestelling_ids' => $bestelling_ids]);

} catch (PDOException $e) {
    // Rollback transactie bij DB fout
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        error_log("[File Processing] DB Transactie gerollback wegens PDO fout.");
    }
    error_log("[File Processing] !!! FATALE PDO FOUT: " . $e->getMessage());
    sendJsonResponse(['status' => 'error', 'message' => 'Er ging iets mis met de database. Probeer opnieuw.']);

} catch (Exception $e) {
    // Rollback transactie bij algemene fout
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        error_log("[File Processing] DB Transactie gerollback wegens algemene fout.");
    }
    error_log("[File Processing] !!! FATALE FOUT: " . $e->getMessage());
    sendJsonResponse(['status' => 'error', 'message' => 'Er ging iets mis: ' . $e->getMessage()]);

} finally {
    error_log("--- [File Processing] Einde Checkout Poging Verwerking ---");
}

?>