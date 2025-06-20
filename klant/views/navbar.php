<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Navbar</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Navbar CSS -->
    <link rel="stylesheet" href="../../css/navbar.css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" type="image/x-icon" href="../media/logo.png">
</head>
<body class="text-white">
<!-- Navbar -->
<nav class="bg-white shadow-md">
    <input type="checkbox" id="nav-toggle" class="hidden" />
    <div class="logo flex items-center px-6 py-4">
        <a href="index.php">
            <img class="logo-img" src="../media/logo.png" alt="logo" />
        </a>
<!--        <div class="search-container relative">-->
<!--            <input-->
<!--                    type="text"-->
<!--                    placeholder="Search..."-->
<!--                    id="search-bar"-->
<!--                    class="focus-within:outline-green-500 border rounded-xl border-gray-400 text-base px-4 py-2 md:text-sm sm:text-xs w-full"-->
<!--            />-->
<!--            <button type="submit" class="search-btn absolute right-2 top-1/2 transform -translate-y-1/2">-->
<!--                <span class="fa-solid fa-magnifying-glass text-green-500"></span>-->
<!--            </button>-->
<!--            <div-->
<!--                    id="search-suggestions"-->
<!--                    class="hidden absolute bg-white border border-gray-300 shadow-lg w-full mt-2 max-h-60 overflow-y-auto rounded-lg z-10 top-full left-0"-->
<!--            >-->
<!--                <!- Suggestions will appear here -->
<!--            </div>-->
<!--        </div>-->

    </div>
    <ul class="links flex space-x-6 px-6 py-3">
        <li><a href="index.php" class="text-white hover:text-green-500">Home</a></li>
        <li><a href="product.view.php" class="text-white hover:text-green-500">Producten</a></li>
        <li><a href="machine.view.php" class="text-white hover:text-green-500">Machines</a></li>
        <li><a href="techniek.view.php" class="text-white hover:text-green-500">Uitlegvideo’s</a></li>
        <li class="relative">
            <a href="winkelwagen.view.php" class="text-white">
                <span class="fa-solid fa-cart-shopping text-xl text-green-500"></span>
                <span id="cart-count" class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-semibold text-white bg-purple-500 rounded-full -mt-1 -mr-1">
                    0
                </span>
            </a>
        </li>
    </ul>

    <label for="nav-toggle" class="icon-burger cursor-pointer">
        <div class="line bg-white"></div>
        <div class="line bg-white"></div>
        <div class="line bg-white"></div>
    </label>
</nav>

<script>
    // Make updateCartCount function globally accessible
    window.updateCartCount = function() {
        function getCartFromLocalStorage() {
            const cartString = localStorage.getItem('winkelwagen');
            return cartString ? JSON.parse(cartString) : {};
        }

        const cart = getCartFromLocalStorage();
        let totalItems = 0;
        for (const itemKey in cart) {
            totalItems += parseInt(cart[itemKey].quantity); // Ensure quantity is parsed as integer
        }
        document.getElementById('cart-count').textContent = totalItems;
    }

    document.addEventListener('DOMContentLoaded', function() {
        window.updateCartCount(); // Initial update on page load
    });
</script>
</body>
</html>