<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelwagen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="../media/logo.png">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accentGreen: '#34D399', // Tailwind green-400
                        accentPurple: '#A78BFA', // Tailwind purple-400
                    }
                }
            }
        }
    </script>

    <style>
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border-left-color: #34D399;
            animation: spin 1s ease infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="bg-white text-gray-900 font-sans">
<?php include 'navbar.php'; ?>

<div class="container mx-auto mt-10 p-4 md:p-8" id="winkelwagen-app">
    <h1 class="text-3xl font-bold text-accentPurple mb-6">Uw Winkelwagen</h1>

    <div id="winkelwagen-container">
        <div class="text-center p-8">
            <i class="fas fa-spinner fa-spin fa-3x text-accentPurple"></i>
            <p class="mt-4 text-gray-600">Winkelwagen laden...</p>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="cartErrorModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm mx-auto">
            <div class="flex items-start space-x-4">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-circle fa-lg text-red-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-900">Fout</h3>
                    <p class="mt-2 text-sm text-gray-600" id="cartErrorModalText"></p>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="document.getElementById('cartErrorModal').classList.add('hidden')"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-accentPurple text-white hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:w-auto text-sm">
                    Sluiten
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="cartSuccessModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm mx-auto text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <i class="fas fa-check-circle fa-lg text-accentGreen"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Gelukt!</h3>
            <p class="mt-2 text-sm text-gray-600" id="cartSuccessModalText"></p>
            <div class="mt-4 spinner mx-auto"></div>
        </div>
    </div>

    <!-- Remove Confirmation Modal -->
    <div id="removeConfirmationModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-auto">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="fas fa-exclamation-triangle fa-lg text-red-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Item Verwijderen
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Weet u zeker dat u dit product uit de winkelwagen wilt verwijderen?
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button"
                        class="remove-confirm-button w-full inline-flex justify-center rounded-md shadow-sm px-4 py-2 bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto text-sm">
                    Ja, verwijder
                </button>
                <button type="button"
                        class="remove-cancel-button mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-accentPurple text-white hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:w-auto text-sm">
                    Annuleren
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../js/winkelwagen.js" defer></script>
</body>
</html>