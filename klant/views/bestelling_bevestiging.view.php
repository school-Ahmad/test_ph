<?php
// Haal de bestelling ID(s) uit de URL parameter 'ids'
$bestelling_ids_string = $_GET['ids'] ?? '';
$bestelling_ids = !empty($bestelling_ids_string) ? explode(',', $bestelling_ids_string) : [];

// Basisvalidatie: check of de IDs numeriek zijn (optioneel)
$valid_ids = array_filter($bestelling_ids, 'is_numeric');

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestelling Bevestigd</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="../media/logo.png"> <!-- Pas pad aan indien nodig -->
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans text-gray-800 dark:text-gray-200">
<?php include 'navbar.php'; ?>
<div class="container mx-auto mt-10 p-4 md:p-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 max-w-2xl mx-auto text-center">
        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 mb-6">
            <i class="fas fa-check-circle fa-3x text-green-600 dark:text-green-400"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Bedankt voor je bestelling!</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Je bestelling is succesvol ontvangen en wordt zo spoedig mogelijk verwerkt.
        </p>

        <?php if (!empty($valid_ids)): ?>
            <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md border dark:border-gray-600">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Je bestelnummer(s):</p>
                <ul class="space-y-1">
                    <?php foreach ($valid_ids as $id): ?>
                        <li class="text-lg font-semibold text-gray-900 dark:text-white">#<?= htmlspecialchars($id) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Bewaar deze nummers goed voor eventuele vragen.</p>
            </div>
        <?php else: ?>
            <p class="text-sm text-red-600 dark:text-red-400">Kon bestelnummer niet weergeven.</p>
        <?php endif; ?>

        <div class="mt-8">
            <a href="index.php" class="px-6 py-2 bg-[#8fe507] text-white font-semibold rounded-lg hover:bg-[#7bc906] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8fe507] dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out">
                Terug naar home
            </a>
        </div>
    </div>
</div>
</body>
</html>