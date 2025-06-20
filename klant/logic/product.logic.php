<?php

require_once __DIR__ . '/../../config/database.php'; // Correct path to database.php

function getAlleProducten() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT p.*,
                   GROUP_CONCAT(pi.filename) AS image_filenames
            FROM producten p
            LEFT JOIN product_images pi ON p.id = pi.product_id
            GROUP BY p.id
        ");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Process image filenames into array
        foreach ($products as &$product) {
            $product['images'] = !empty($product['image_filenames']) ? explode(',', $product['image_filenames']) : [];
            unset($product['image_filenames']); // Clean up temporary field
        }
        return $products;
    } catch (PDOException $e) {
        error_log("Database error in getAlleProducten: " . $e->getMessage());
        return [];
    }
}

function getCategorieen() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM categorieen");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getCategorieen: " . $e->getMessage());
        return [];
    }
}

function getProductenPerCategorie($categorie_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT p.*,
                   GROUP_CONCAT(pi.filename) AS image_filenames
            FROM producten p
            LEFT JOIN product_images pi ON p.id = pi.product_id
            WHERE p.categorie_id = ?
            GROUP BY p.id
        ");
        $stmt->execute([$categorie_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Process image filenames into array
        foreach ($products as &$product) {
            $product['images'] = !empty($product['image_filenames']) ? explode(',', $product['image_filenames']) : [];
            unset($product['image_filenames']); // Clean up temporary field
        }
        return $products;
    } catch (PDOException $e) {
        error_log("Database error in getProductenPerCategorie: " . $e->getMessage());
        return [];
    }
}

// Function to generate the full URL to the image
function getAfbeeldingUrl($filename) {
    // Base URL should be set to the root of your project
    $base_url = '/test_ph/'; // Adjust this if necessary
    $upload_dir = 'uploads/'; // Directory where media files are stored

    // Check if $filename is not null or empty
    if (!empty($filename)) {
        return $base_url . $upload_dir . ltrim($filename, '/'); // Construct full URL
    }

    // Return a default image URL or handle the case where $filename is null
    return $base_url . $upload_dir . 'default_image.jpg'; // Default image path
}

// Function to get filtered products based on category
function getFilteredProducten() {
    if (isset($_GET['categorie']) && $_GET['categorie'] !== '') {
        $categorie_id = $_GET['categorie'];
        return getProductenPerCategorie($categorie_id);
    }
    return getAlleProducten();
}
function getTechniekVideosForProduct($product_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT v.id, v.name, v.link
            FROM videos v
            INNER JOIN product_videos pv ON v.id = pv.video_id
            WHERE pv.product_id = ?
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getTechniekVideosForProduct: " . $e->getMessage());
        return [];
    }
}
?>