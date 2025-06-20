<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFest Hero Section</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .oval-image {
            clip-path: ellipse(50% 75% at 50% 25%);
        }
    </style>
</head>
<body class="bg-black text-white">
<div class="relative h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Images -->
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="flex space-x-4">
            <img src="https://via.placeholder.com/200" alt="Space Image 1" class="h-48 w-24 oval-image">
            <img src="https://via.placeholder.com/200" alt="Space Image 2" class="h-64 w-32 oval-image">
            <img src="https://via.placeholder.com/200" alt="Space Image 3" class="h-48 w-24 oval-image">
        </div>
    </div>

    <!-- Text Content -->
    <div class="relative z-10 text-center">
        <h1 class="text-4xl font-bold text-yellow-400">TechFest</h1>
        <h2 class="text-6xl font-bold mb-4">Space : The Timeless Infinity</h2>
        <p class="text-lg mb-8">Explore your favourite events and register now to showcase your talent and win exciting prizes.</p>
        <button class="bg-yellow-500 text-black px-6 py-3 rounded-full hover:bg-yellow-600 transition duration-300">Explore Now</button>
    </div>
</div>
</body>
</html>
