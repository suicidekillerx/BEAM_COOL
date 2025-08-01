<?php
// Test script to check image paths
require_once 'includes/functions.php';

echo "=== Image Path Test ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Check product images in database
    $stmt = $pdo->query("SELECT id, product_id, image_path, is_primary FROM product_images ORDER BY product_id, sort_order LIMIT 10");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Database Image Paths ===\n";
    foreach ($images as $image) {
        echo "ID: {$image['id']}, Product: {$image['product_id']}, Path: {$image['image_path']}, Primary: {$image['is_primary']}\n";
        
        // Check if file exists
        $filePath = $image['image_path'];
        if (file_exists($filePath)) {
            echo "  ✅ File exists: $filePath\n";
        } else {
            echo "  ❌ File missing: $filePath\n";
        }
        echo "\n";
    }
    
    // Check if images/products directory exists
    echo "=== Directory Check ===\n";
    $uploadDir = 'images/products/';
    if (is_dir($uploadDir)) {
        echo "✅ Directory exists: $uploadDir\n";
        $files = scandir($uploadDir);
        echo "Files in directory: " . count($files) . " (including . and ..)\n";
    } else {
        echo "❌ Directory missing: $uploadDir\n";
    }
    
    // Check if images directory exists
    $imagesDir = 'images/';
    if (is_dir($imagesDir)) {
        echo "✅ Directory exists: $imagesDir\n";
        $files = scandir($imagesDir);
        echo "Files in directory: " . count($files) . " (including . and ..)\n";
    } else {
        echo "❌ Directory missing: $imagesDir\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 