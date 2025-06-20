<?php
// views/dashboard.view.php
// Dit bestand wordt geïncludeerd vanuit logic/dashboard.logic.php
// Variabelen zoals $bestellingen, $errorMessage, $csrfToken, $categories zijn hier beschikbaar.

// Zorg ervoor dat sessies gestart zijn voordat je sessievariabelen leest/schrijft
// Dit moet idealiter in een centrale bootstrap/init-file gebeuren, maar voor deze view geldt:
// session_start(); // Zorg dat dit ÉÉN keer gebeurt per paginarequest, idealiter aan het begin

// CSRF Token handling: Zorg ervoor dat de token gegenereerd wordt in dashboard.logic.php
// en beschikbaar is als $csrfToken.

?>

<?php include __DIR__ . '/header.view.php'; ?>
<div class="flex flex-col md:flex-row">
    <?php include __DIR__ . '/sidebar.view.php'; ?>
    <div class="flex-1 p-4 sm:p-6 bg-gray-100 min-h-screen">
        <h1 class="text-2xl sm:text-3xl font-bold text-green-700 mb-4 sm:mb-6 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 sm:h-6 w-5 sm:w-5 mr-2 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Dashboard
        </h1>

        <!-- Filter en Zoekbalk -->
        <div class="bg-white p-4 shadow-lg rounded-lg mb-6">
            <h2 class="text-lg sm:text-xl font-semibold text-purple-700 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 sm:h-5 w-4 sm:w-5 mr-2 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h16a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM16 9l2 2-2 2M12 9l2 2-2 2M8 9l2 2-2 2" />
                </svg>
                Filter en Zoeken
            </h2>
            <!-- Formulier stuurt naar index.php?page=dashboard met GET parameters -->
            <form action="index.php" method="GET" class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                <input type="hidden" name="page" value="dashboard"> <!-- Zorg dat de 'page' parameter behouden blijft -->

                <!-- Status Filter -->
                <div class="flex-1 w-full sm:w-auto">
                    <label for="status_filter" class="block text-gray-700 font-medium text-sm sm:text-base">Filter op Status:</label>
                    <select id="status_filter" name="status_filter" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-purple-500 dark:focus:ring-purple-500">
                        <option value="">Alle Statussen</option>
                        <option value="nieuw" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'nieuw' ? 'selected' : ''; ?>>Nieuw</option>
                        <option value="in_behandeling" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'in_behandeling' ? 'selected' : ''; ?>>In Behandeling</option>
                        <option value="wacht" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'wacht' ? 'selected' : ''; ?>>Wacht</option>
                        <option value="klaar" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'klaar' ? 'selected' : ''; ?>>Klaar</option>
                        <option value="opgehaald" <?php echo isset($_GET['status_filter']) && $_GET['status_filter'] === 'opgehaald' ? 'selected' : ''; ?>>Opgehaald</option>
                    </select>
                </div>

                <!-- Categorie Filter -->
                <div class="flex-1 w-full sm:w-auto">
                    <label for="category_filter" class="block text-gray-700 font-medium text-sm sm:text-base">Filter op Categorie:</label>
                    <select id="category_filter" name="category_filter" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-purple-500 dark:focus:ring-purple-500">
                        <option value="">Alle Categorieën</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                <?php echo isset($_GET['category_filter']) && (string)$_GET['category_filter'] === (string)$category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['naam']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Zoekbalk - NU ACTIEF -->
                <div class="flex-1 w-full sm:w-auto">
                    <label for="search" class="block text-gray-700 font-medium text-sm sm:text-base">Zoek:</label>
                    <input type="text" id="search" name="search" placeholder="Zoeken..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="border rounded w-full p-2 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <!-- Submit Knop -->
                <div class="flex items-end w-full sm:w-auto">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center justify-center w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 sm:h-5 w-4 sm:w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Toepassen
                    </button>
                </div>
            </form>
        </div>

        <?php
        // Toon eventuele foutmeldingen uit de logic
        if (isset($errorMessage)): ?>
            <p class="text-red-600 bg-red-100 p-3 rounded-lg mb-4"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>
        <?php
        // Toon eventuele succes- of foutmeldingen uit de sessie na een redirect
        // Zorg ervoor dat session_start() bovenaan je index.php of vergelijkbaar staat
        if (isset($_SESSION['success_message'])) {
            echo '<p class="text-green-600 bg-green-100 p-3 rounded-lg mb-4">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
            unset($_SESSION['success_message']); // Verwijder de boodschap na het tonen
        }
        if (isset($_SESSION['error_message'])) {
            echo '<p class="text-red-600 bg-red-100 p-3 rounded-lg mb-4">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
            unset($_SESSION['error_message']); // Verwijder de boodschap na het tonen
        }
        ?>


        <!-- Bestellingen Cards -->
        <?php if (empty($bestellingen)): ?>
            <p class="text-gray-700">Er zijn momenteel geen bestellingen om weer te geven met de geselecteerde filters.</p>
        <?php else: ?>
            <!-- Grid Layout aanpassen voor 1, 2, 3 en 4 kolommen op verschillende breakpoints -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($bestellingen as $bestelling): ?>
                    <div class="bg-white p-4 rounded-lg shadow-lg border border-gray-200 flex flex-col justify-between">
                        <dl>
                            <!-- Overzicht Informatie -->
                            <div class="mb-2 ">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.628m7.32-7.32a2.25 2.25 0 013.182 3.182C21 9.525 22.5 13.5 22.5 18a2.25 2.25 0 01-2.25 2.25H15m.021-2.273 2.536-2.536M3 18V2.25a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 2.25v18m-18 0v-2.25a2.25 2.25 0 012.25-2.25h.937m7.637 2.289-2.549-2.549m-1.026-2.472a2.25 2.25 0 012.91-3.093c.867 1.1.928 2.34.377 3.644Z" /></svg>Order ID:</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-black break-words"><?php echo htmlspecialchars($bestelling['id']); ?></dd>
                            </div>
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.589-7.499-1.632z" /></svg>Klant E-mail:</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-black break-words"><?php echo htmlspecialchars($bestelling['klant_email']); ?></dd>
                            </div>
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V8.25a1.5 1.5 0 011.5-1.5h13.5a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5z" /></svg>Besteld Op:</dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-blackbreak-words"><?php echo date('d-m-Y H:i', strtotime($bestelling['besteld_op'])); ?></dd>
                            </div>
                            <!-- Nieuwe Categorie Weergave -->
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.628m7.32-7.32a2.25 2.25 0 013.182 3.182C21 9.525 22.5 13.5 22.5 18a2.25 2.25 0 01-2.25 2.25H15m.021-2.273 2.536-2.536M3 18V2.25a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 2.25v18m-18 0v-2.25a2.25 2.25 0 012.25-2.25h.937m7.637 2.289-2.549-2.549m-1.026-2.472a2.25 2.25 0 012.91-3.093c.867 1.1.928 2.34.377 3.644Z" />
                                    </svg>
                                    Categorie:
                                </dt>
                                <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-black break-words"><?php echo htmlspecialchars($bestelling['categorie_naam'] ?? 'Onbekend'); ?></dd>
                            </div>

                            <!-- Visuele Status Indicator - Pas tekstgrootte aan voor responsiviteit -->
                            <div class="mb-4 w-full">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Status Voortgang:</dt>
                                <dd class="mt-2 w-full">
                                    <div class="flex items-center relative w-full justify-between px-1">
                                        <div class="flex flex-col items-center z-10 text-center px-1">
                                            <div class="w-11 h-11 <?php echo $bestelling['status'] == 'nieuw' ? 'bg-green-500' : 'bg-gray-300' ?> text-white flex items-center justify-center rounded-full">
                                                <img src="https://icon-library.com/images/order-icon/order-icon-28.jpg" alt="Nieuw" class="w-5 h-5"/>
                                            </div>
                                            <p class="text-gray-600 mt-2 text-xs md:text-sm lg:text-base">Nieuw</p>
                                        </div>
                                        <div class="absolute top-[18px] left-[10%] w-[20%] h-0.5 <?php echo in_array($bestelling['status'], ['sign', 'in_behandeling', 'wacht', 'print', 'klaar', 'opgehaald']) ? 'bg-green-500' : 'bg-gray-300' ?>"></div>
                                        <div class="flex flex-col items-center z-10 text-center px-1">
                                            <div class="w-11 h-11 <?php echo in_array($bestelling['status'], ['sign', 'in_behandeling', 'wacht', 'print']) ? 'bg-yellow-400' : 'bg-gray-300' ?> text-white flex items-center justify-center rounded-full">
                                                <img src="https://static-00.iconduck.com/assets.00/in-progress-icon-2037x2048-us0p278i.png" alt="In Behandeling" class="w-5 h-5"/>
                                            </div>
                                            <p class="text-gray-600 mt-2 text-xs md:text-sm lg:text-base leading-tight">Behandeling</p>
                                        </div>
                                        <div class="absolute top-[18px] left-[40%] w-[20%] h-0.5 <?php echo in_array($bestelling['status'], ['klaar', 'opgehaald']) ? 'bg-green-500' : 'bg-gray-300' ?>"></div>
                                        <div class="flex flex-col items-center z-10 text-center px-1">
                                            <div class="w-11 h-11 <?php echo $bestelling['status'] == 'klaar' ? 'bg-green-400' : 'bg-gray-300' ?> flex items-center justify-center rounded-full">
                                                <img src="https://static.thenounproject.com/png/4927873-200.png" alt="Klaar" class="w-5 h-5"/>
                                            </div>
                                            <p class="text-gray-600 mt-2 text-xs md:text-sm lg:text-base">Klaar</p>
                                        </div>
                                        <div class="absolute top-[18px] left-[70%] w-[20%] h-0.5 <?php echo $bestelling['status'] == 'opgehaald' ? 'bg-green-600' : 'bg-gray-300' ?>"></div>
                                        <div class="flex flex-col items-center z-10 text-center px-1">
                                            <div class="w-11 h-11 <?php echo $bestelling['status'] == 'opgehaald' ? 'bg-green-600' : 'bg-gray-300' ?> text-white flex items-center justify-center rounded-full">
                                                <img src="https://static.thenounproject.com/png/4160044-200.png" alt="Opgehaald" class="w-5 h-5"/>
                                            </div>
                                            <p class="text-gray-600 mt-2 text-xs md:text-sm lg:text-base">Opgehaald</p>
                                        </div>
                                    </div>
                                </dd>
                            </div>
                        </dl>

                        <!-- Details Sectie (initieel verborgen) -->
                        <div id="order-details-<?php echo $bestelling['id']; ?>" class="order-details-section mt-4 pt-4 border-t border-gray-200" style="display: none;">
                            <dl>
                                <div class="mb-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.307 4.308a3 3 0 004.212-4.212l-4.5-4.5" /></svg>Product Naam:</dt>
                                    <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-black break-words">
                                        <?php echo htmlspecialchars($bestelling['product_naam'] ?? 'Onbekend Product'); ?>
                                    </dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>Aantal:</dt>
                                    <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-black"><?php echo htmlspecialchars($bestelling['aantal']); ?></dd>
                                </div>
                                <div class="mb-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M4.5 6H4.5m0 6h9.75M4.5 18h9.75M19.5 6v14.25a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 20.25V6a2.25 2.25 0 012.25-2.25h10.5a2.25 2.25 0 012.25 2.25z" /></svg>Gekozen Opties:</dt>
                                    <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-black break-words">
                                        <?php
                                        // Decodeer de JSON string van gekozen_opties
                                        $options = json_decode($bestelling['gekozen_opties'], true);
                                        if ($options && is_array($options)) {
                                            echo '<ul class="list-disc list-inside text-sm text-gray-700">';
                                            foreach ($options as $optionId => $values) {
                                                $displayValues = is_array($values) ? implode(', ', $values) : htmlspecialchars($values);
                                                echo '<li class="break-words">Optie ID ' . htmlspecialchars($optionId) . ': ' . htmlspecialchars($displayValues) . '</li>';
                                            }
                                            echo '</ul>';
                                        } else {
                                            echo '<span class="text-gray-600 text-sm">Geen specifieke opties</span>';
                                        }
                                        ?>
                                    </dd>
                                </div>
                                <!-- Bestandsnaam en Download Knop -->
                                <div class="mb-4">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 inline-block align-middle"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>Bestand:</dt>
                                    <dd class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                                        <?php if ($bestelling['bestandspad']): ?>
                                            <div class="flex flex-wrap items-center space-x-2 space-y-2 sm:space-y-0">
                                                <span class="text-gray-700 text-sm max-w-[calc(100%-5rem)] sm:max-w-none truncate"><?= htmlspecialchars($bestelling['bestand_originele_naam'] ?? 'Bestand') ?></span>
                                                <a href="download.php?order_id=<?= htmlspecialchars($bestelling['id']) ?>"
                                                   class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l3-3m-3 3l-3-3m2.818-13.19l-2.938 2.939m0 2.828l-2.938 2.939M9.09 17.25L4.69 12.85A2.25 2.25 0 013 11.25V8.563a2.25 2.25 0 01.659-1.591l5.43-5.43a2.25 2.25 0 011.591-.659h4.318c.621 0 1.21.242 1.643.684l5.432 5.432a2.25 2.25 0 01.659 1.591v2.188c0 .621-.504 1.125-1.125 1.125H15.75m-1.5 0a2.25 2.25 0 01-2.25 2.25H9.75a2.25 2.25 0 01-2.25-2.25m7.5-10.5h.008v.008h-.008V12z" />
                                                    </svg>
                                                    Download
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-600 text-sm">Geen bestand geüpload</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <!-- Einde Details Sectie -->


                        <!-- Knoppen sectie - Flexbox aanpassen om op kleinere schermen te stapelen -->
                        <div class="flex flex-col sm:flex-row justify-end gap-2 mt-4">

                            <!-- Status Update Formulier -->
                            <form action="index.php?page=dashboard<?php echo isset($_GET['status_filter']) ? '&status_filter=' . urlencode($_GET['status_filter']) : ''; ?><?php echo isset($_GET['category_filter']) ? '&category_filter=' . urlencode($_GET['category_filter']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" method="POST" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($bestelling['id']); ?>">
                                <select name="status" class="border rounded p-1 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white w-full sm:w-auto text-center">
                                    <option value="nieuw" class="text-center" <?php if ($bestelling['status'] == 'nieuw') echo 'selected'; ?>>Nieuw</option>
                                    <option value="sign" class="text-center" <?php if ($bestelling['status'] == 'sign') echo 'selected'; ?>>Sign</option>
                                    <option value="in_behandeling" class="text-center" <?php if ($bestelling['status'] == 'in_behandeling') echo 'selected'; ?>>In Behandeling</option>
                                    <option value="wacht" class="text-center" <?php if ($bestelling['status'] == 'wacht') echo 'selected'; ?>>Wacht</option>
                                    <option value="print" class="text-center" <?php if ($bestelling['status'] == 'print') echo 'selected'; ?>>Print</option>
                                    <option value="klaar" class="text-center" <?php if ($bestelling['status'] == 'klaar') echo 'selected'; ?>>Klaar</option>
                                    <option value="opgehaald" class="text-center" <?php if ($bestelling['status'] == 'opgehaald') echo 'selected'; ?>>Opgehaald</option>
                                </select>
                                <button type="submit" name="update_status" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm w-full sm:w-auto">
                                    Update
                                </button>
                            </form>

                            <!-- Bestelling Verwijderen Knop - NU MET DATA ATTRIBUTEN -->
                            <button type="button"
                                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm w-full sm:w-auto delete-order-button"
                                    data-order-id="<?= htmlspecialchars($bestelling['id']) ?>"
                                    data-csrf-token="<?= htmlspecialchars($csrfToken) ?>"
                            >
                                Verwijderen
                            </button>

                            <!-- Details Toon/Verberg Knop -->
                            <button type="button" onclick="toggleDetails(<?php echo $bestelling['id']; ?>, this)"
                                    class="inline-flex justify-center items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-purple-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700 w-full sm:w-auto">
                                Details
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- --- MODAL VOOR VERWIJDERBEVESTIGING --- -->
<div id="delete-confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-sm mx-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Bestelling Verwijderen</h3>
        <p class="text-sm text-gray-600 mb-6">
            Weet u zeker dat u bestelling #<span id="modal-order-id" class="font-bold"></span> wilt verwijderen?
            Deze actie kan niet ongedaan gemaakt worden.
        </p>
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
            <button type="button" id="cancel-delete-button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full sm:w-auto">
                Annuleren
            </button>
            <!-- Dit formulier wordt dynamisch gevuld en gesubmit door JavaScript -->
            <form id="confirm-delete-form" action="index.php?page=dashboard<?php echo isset($_GET['status_filter']) ? '&status_filter=' . urlencode($_GET['status_filter']) : ''; ?><?php echo isset($_GET['category_filter']) ? '&category_filter=' . urlencode($_GET['category_filter']) : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" method="POST" class="inline-block w-full sm:w-auto">
                <input type="hidden" name="order_id" id="modal-form-order-id" value="">
                <input type="hidden" name="delete_order" value="1">
                <input type="hidden" name="csrf_token" id="modal-form-csrf-token" value=""> <!-- CSRF token input -->
                <button type="submit" id="confirm-delete-button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full sm:w-auto">
                    Verwijderen
                </button>
            </form>
        </div>
    </div>
</div>
<!-- --- EINDE MODAL --- -->


<!-- JavaScript om details te tonen/verbergen en modal te beheren -->
<script>
    // Functie om details te tonen/verbergen (bestaand)
    function toggleDetails(orderId, buttonElement) {
        const detailsElement = document.getElementById('order-details-' + orderId);
        if (detailsElement.style.display === 'none' || detailsElement.style.display === '') {
            detailsElement.style.display = 'block';
            buttonElement.textContent = 'Verberg Details'; // Verander tekst van de knop
        } else {
            detailsElement.style.display = 'none';
            buttonElement.textContent = 'Details'; // Verander tekst terug
        }
    }

    // --- JavaScript voor de Verwijder Modal ---

    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = document.getElementById('delete-confirm-modal');
        const modalOrderIdSpan = document.getElementById('modal-order-id');
        const modalForm = document.getElementById('confirm-delete-form'); // Dit is het formulier in de modal
        const modalFormOrderIdInput = document.getElementById('modal-form-order-id');
        const modalFormCsrfInput = document.getElementById('modal-form-csrf-token');
        const cancelButton = document.getElementById('cancel-delete-button');

        // Event listener voor alle "Verwijderen" knoppen
        // Gebruik event delegation op de body voor efficiëntie
        document.body.addEventListener('click', function(event) {
            const targetButton = event.target.closest('.delete-order-button');

            if (targetButton) {
                event.preventDefault(); // Voorkom standaard formulier indiening

                // Haal de orderId en csrfToken op uit de data-attributen van de geklikte knop
                const orderId = targetButton.dataset.orderId;
                const csrfToken = targetButton.dataset.csrfToken; // Haal de token op

                // Vul de modal met de juiste bestellings-ID
                modalOrderIdSpan.textContent = orderId;

                // Vul de hidden velden in het modal formulier
                modalFormOrderIdInput.value = orderId;
                modalFormCsrfInput.value = csrfToken; // Zet CSRF token in het formulier

                // Toon de modal
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex'); // Gebruik flex om te centreren
            }
        });

        // Event listener voor de "Annuleren" knop in de modal
        cancelButton.addEventListener('click', function() {
            // Verberg de modal
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        });

        // Optioneel: Verberg de modal als er buiten de modal wordt geklikt
        deleteModal.addEventListener('click', function(event) {
            // Controleer of de klik direct op de modal achtergrond was (niet op de inhoud)
            if (event.target === deleteModal) {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
            }
        });

        // Optioneel: Verberg de modal met de ESC-toets
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
            }
        });

    });
</script>

</body>
</html>