<?php
// logic/dashboard.logic.php

// Zorg ervoor dat sessies gestart zijn (meestal in index.php aan het begin van het script)
// session_start(); // Als dit nog niet gebeurt in je index.php of een ander centraal bestand, moet dit geactiveerd worden.

// Zorg ervoor dat je database.php correct wordt ingeladen
require_once __DIR__ . '/../config/database.php';

// Controleer of de $pdo variabele beschikbaar is na het includen van database.php
if (!isset($pdo)) {
    error_log("FATAL ERROR: Database connection failed in dashboard.logic.php");
    // In een productieomgeving is het beter een algemene foutmelding te tonen en de details te loggen.
    die("Er is een kritieke fout opgetreden. De databaseverbinding kon niet worden gelegd. Controleer de logs en neem contact op met de beheerder.");
}

// --- Beveiliging: Genereer CSRF token ---
// Genereer een nieuw token als er nog geen is of als de sessie net gestart is.
// Deze token kan gebruikt worden voor andere formulieren of als u CSRF-bescherming later (her)activeert.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token']; // Maak de token beschikbaar voor views, indien nodig.


// --- Status Update Logica ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // Optioneel: Voeg hier CSRF check toe voor status updates ook.
    // Als u dit activeert, zorg dan voor een 'csrf_token' veld in het status update formulier.
    /*
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Ongeldig verzoek (CSRF check failed) bij status update.";
        header("Location: index.php?page=dashboard");
        exit();
    }
    */

    // --- Beveiliging: Controleer of de gebruiker een admin is ---
    // Implementeer hier je eigen admin check logica. Bijvoorbeeld:
    /*
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { // Aangepast naar een gangbaardere sessievariabele
        $_SESSION['error_message'] = "Toegang geweigerd. U bent niet geautoriseerd om de status bij te werken.";
        header("Location: index.php?page=dashboard");
        exit();
    }
    */
    // --- Einde Beveiliging ---


    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];

    // Valideer orderId en status
    if (!is_numeric($orderId)) {
        $_SESSION['error_message'] = "Ongeldige bestellings-ID.";
        header("Location: index.php?page=dashboard"); // Redirect naar dashboard, filters gaan verloren
        exit();
    }
    $orderId = (int)$orderId;

    $allowedStatuses = ['nieuw', 'sign', 'in_behandeling', 'wacht', 'print', 'klaar', 'opgehaald'];
    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $pdo->prepare("UPDATE bestellingen SET status = ? WHERE id = ?");
        try {
            $stmt->execute([$newStatus, $orderId]);
            $_SESSION['success_message'] = "Status van bestelling #" . htmlspecialchars($orderId) . " succesvol bijgewerkt naar '" . htmlspecialchars($newStatus) . "'.";
        } catch (PDOException $e) {
            error_log("Error updating order status for ID " . $orderId . ": " . $e->getMessage());
            $_SESSION['error_message'] = "Fout bij het bijwerken van de status van bestelling #" . htmlspecialchars($orderId) . ".";
        }
    } else {
        error_log("Invalid status received for order ID " . $orderId . ": " . $newStatus);
        $_SESSION['error_message'] = "Ongeldige status '" . htmlspecialchars($newStatus) . "' ontvangen voor bestelling #" . htmlspecialchars($orderId) . ".";
    }

    // Redirect om herindiening te voorkomen en de pagina te verversen met behoud van filters
    $redirectUrl = 'index.php?page=dashboard';
    $queryParams = [];
    if (isset($_GET['status_filter']) && $_GET['status_filter'] !== '') { // Check of filter ook daadwerkelijk een waarde heeft
        $queryParams['status_filter'] = $_GET['status_filter'];
    }
    if (isset($_GET['category_filter']) && $_GET['category_filter'] !== '') {
        $queryParams['category_filter'] = $_GET['category_filter'];
    }
    // Er was geen search filter in de POST redirect, maar als je die wilt behouden van de GET:
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $queryParams['search'] = $_GET['search'];
    }
    if (!empty($queryParams)) {
        $redirectUrl .= '&' . http_build_query($queryParams);
    }

    header("Location: " . $redirectUrl);
    exit();
}
// --- Einde Status Update Logica ---


// --- Bestelling Verwijderen Logica ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {

    // --- Beveiliging: CSRF check ---
    // De volgende CSRF check is uitgecommentarieerd zoals gevraagd om de "Ongeldig verzoek (CSRF check failed)" melding te voorkomen.
    // WAARSCHUWING: Het verwijderen van CSRF-bescherming maakt uw applicatie kwetsbaar voor Cross-Site Request Forgery aanvallen.
    // Overweeg dit alleen als u de implicaties volledig begrijpt en/of alternatieve beveiligingsmaatregelen treft.
    // Als u dit heractiveert, zorg voor een 'csrf_token' veld in het verwijder formulier en gebruik hash_equals voor vergelijking.
    /*
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Ongeldig verzoek (CSRF check failed) bij verwijderen.";
        header("Location: index.php?page=dashboard");
        exit();
    }
    */

    // --- Beveiliging: Controleer of de gebruiker een admin is ---
    // Implementeer hier je eigen admin check logica. Bijvoorbeeld:
    /*
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') { // Aangepast naar een gangbaardere sessievariabele
        $_SESSION['error_message'] = "Toegang geweigerd. U bent niet geautoriseerd om bestellingen te verwijderen.";
        header("Location: index.php?page=dashboard");
        exit();
    }
    */
    // --- Einde Beveiliging ---


    $orderId = $_POST['order_id'];

    // Valideer de order_id
    if (!is_numeric($orderId)) {
        $_SESSION['error_message'] = "Ongeldige bestellings-ID voor verwijdering.";
        header("Location: index.php?page=dashboard"); // Redirect naar dashboard, filters gaan verloren
        exit();
    }

    $orderId = (int)$orderId; // Cast naar integer voor veiligheid

    // Stap 1: Haal het bestandspad op VOORDAT je de bestelling verwijdert
    $filePathToDelete = null;
    $sqlFetchFile = "SELECT bestandspad FROM bestellingen WHERE id = ?";
    try {
        $stmtFetchFile = $pdo->prepare($sqlFetchFile);
        $stmtFetchFile->execute([$orderId]);
        $fileResult = $stmtFetchFile->fetch(PDO::FETCH_ASSOC);
        if ($fileResult && !empty($fileResult['bestandspad'])) {
            // Construct het volledige pad naar het bestand op de server
            // Zorg ervoor dat $fileResult['bestandspad'] correct is relatief aan de project root
            // Voorbeeld: als bestandspad 'uploads/document.pdf' is en dit script in 'logic/' staat,
            // en 'uploads/' in de root:
            $filePathToDelete = dirname(__DIR__) . '/' . $fileResult['bestandspad']; // dirname(__DIR__) gaat 1 level up van /logic
        }
    } catch (PDOException $e) {
        error_log("Error fetching file path before deleting order ID " . $orderId . ": " . $e->getMessage());
        // Zet geen error message hier, want de delete operatie gaat nog door.
        // De gebruiker merkt het pas als het bestand niet verwijderd kon worden.
    }


    // Stap 2: Verwijder de bestelling uit de database
    $sqlDeleteOrder = "DELETE FROM bestellingen WHERE id = ?";
    try {
        $stmtDeleteOrder = $pdo->prepare($sqlDeleteOrder);
        $stmtDeleteOrder->execute([$orderId]);

        if ($stmtDeleteOrder->rowCount() > 0) {
            // Stap 3: Verwijder het bestand van de server als het pad bekend was
            if ($filePathToDelete) { // Controleer of $filePathToDelete is gezet
                if (file_exists($filePathToDelete)) {
                    if (unlink($filePathToDelete)) {
                        $_SESSION['success_message'] = "Bestelling #" . htmlspecialchars($orderId) . " en bijbehorend bestand succesvol verwijderd.";
                    } else {
                        error_log("Error deleting file " . $filePathToDelete . " for order ID " . $orderId . ". Check permissions and path.");
                        $_SESSION['success_message'] = "Bestelling #" . htmlspecialchars($orderId) . " succesvol verwijderd uit database, maar het bijbehorende bestand '" . basename($filePathToDelete) . "' kon niet worden verwijderd. Controleer serverlogs.";
                    }
                } else {
                    // Bestand niet gevonden, maar bestelling wel verwijderd. Kan zijn dat er geen bestand was of pad incorrect.
                    $_SESSION['success_message'] = "Bestelling #" . htmlspecialchars($orderId) . " succesvol verwijderd. Er was geen bestand gekoppeld of het bestand kon niet worden gevonden op: " . htmlspecialchars($filePathToDelete);
                    error_log("File not found at path " . $filePathToDelete . " for deleted order ID " . $orderId . ", but order was deleted from DB.");
                }
            } else {
                $_SESSION['success_message'] = "Bestelling #" . htmlspecialchars($orderId) . " succesvol verwijderd (geen bestand gekoppeld of pad niet gevonden).";
            }

        } else {
            $_SESSION['error_message'] = "Bestelling #" . htmlspecialchars($orderId) . " niet gevonden of kon niet worden verwijderd.";
        }

    } catch (PDOException $e) {
        error_log("Error deleting order ID " . $orderId . ": " . $e->getMessage());
        $_SESSION['error_message'] = "Fout bij het verwijderen van bestelling #" . htmlspecialchars($orderId) . ".";
    }

    // Redirect om herindiening te voorkomen en de pagina te verversen met behoud van filters
    $redirectUrl = 'index.php?page=dashboard';
    $queryParams = [];
    if (isset($_GET['status_filter']) && $_GET['status_filter'] !== '') {
        $queryParams['status_filter'] = $_GET['status_filter'];
    }
    if (isset($_GET['category_filter']) && $_GET['category_filter'] !== '') {
        $queryParams['category_filter'] = $_GET['category_filter'];
    }
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $queryParams['search'] = $_GET['search'];
    }
    if (!empty($queryParams)) {
        $redirectUrl .= '&' . http_build_query($queryParams);
    }

    header("Location: " . $redirectUrl);
    exit();
}
// --- Einde Bestelling Verwijderen Logica ---


// --- Categorieën Ophalen ---
$categories = [];
try {
    $stmtCategories = $pdo->query("SELECT id, naam FROM categorieen ORDER BY naam ASC");
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    // Geen $errorMessage hier, de pagina kan nog steeds bestellingen tonen zonder categorieënfilter.
    // Je kunt een melding tonen in de view indien $categories leeg is.
}


// --- Bestellingen Ophalen Logica ---

$sql = "SELECT
            b.id,
            b.klant_email,
            b.gekozen_opties,
            b.bestandspad,
            b.bestand_originele_naam,
            b.aantal,
            b.besteld_op,
            b.status,
            p.naam AS product_naam,
            c.naam AS categorie_naam
        FROM
            bestellingen b
        LEFT JOIN
            producten p ON b.product_id = p.id
        LEFT JOIN
            categorieen c ON p.categorie_id = c.id
        ";

$whereClauses = [];
$params = [];

// Status filter
if (isset($_GET['status_filter']) && $_GET['status_filter'] !== '') {
    $whereClauses[] = "b.status = ?";
    $params[] = $_GET['status_filter'];
}

// Categorie filter
if (isset($_GET['category_filter']) && $_GET['category_filter'] !== '') {
    if (is_numeric($_GET['category_filter'])) {
        $whereClauses[] = "p.categorie_id = ?";
        $params[] = (int)$_GET['category_filter'];
    } else {
        // Ongeldige categorie ID, log dit en negeer het filter
        error_log("Invalid (non-numeric) category_filter received: " . $_GET['category_filter']);
        if (isset($_SESSION)) { // Alleen als sessies werken
            $_SESSION['warning_message'] = "Ongeldige categorie filter waarde genegeerd.";
        }
    }
}

// Search filter (voorbeeld, pas aan naar je databasekolommen)
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $searchTerm = '%' . trim($_GET['search']) . '%';
    // Zoek in meerdere velden, pas dit aan naar je behoeften
    $whereClauses[] = "(b.klant_email LIKE ? OR b.id LIKE ? OR p.naam LIKE ? OR b.bestand_originele_naam LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm; // Voor b.id (als je op ID wilt zoeken als string)
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}


if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

// Sorteervolgorde
$sql .= " ORDER BY FIELD(b.status, 'nieuw', 'sign', 'in_behandeling', 'wacht', 'print', 'klaar', 'opgehaald') ASC, b.besteld_op DESC";


try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bestellingen = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $bestellingen = []; // Zorg voor een lege array zodat de view niet breekt
    // Zet een algemene error message voor de gebruiker als sessies werken.
    if (isset($_SESSION)) {
        $_SESSION['error_message'] = "Er ging iets mis bij het ophalen van de bestellingen. Probeer het later opnieuw.";
    }
}

// --- Einde Bestellingen Ophalen Logica ---


// Laad de view
// Zorg ervoor dat $csrfToken, $bestellingen, $categories, etc. beschikbaar zijn in de view.
// Ook $_SESSION['success_message'], $_SESSION['error_message'] moeten in de view worden getoond en daarna geleegd.
include __DIR__ . '/../views/dashboard.view.php';

// Het is een goede gewoonte om sessieberichten te legen nadat ze zijn getoond,
// om te voorkomen dat ze bij een volgende paginaweergave opnieuw verschijnen.
// Dit kan aan het einde van je view-bestand of hier, maar idealiter in de view zelf.
/*
if (isset($_SESSION['success_message'])) { unset($_SESSION['success_message']); }
if (isset($_SESSION['error_message'])) { unset($_SESSION['error_message']); }
if (isset($_SESSION['warning_message'])) { unset($_SESSION['warning_message']); }
*/

?>