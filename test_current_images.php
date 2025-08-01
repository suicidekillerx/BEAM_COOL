<?php
// Simple test to check current image state
require_once 'includes/functions.php';

echo "=== Current Image State Test ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Get first 5 product images
    $stmt = $pdo->query("SELECT id, product_id, image_path FROM product_images ORDER BY id LIMIT 5");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Database Image Paths ===\n";
    foreach ($images as $image) {
        echo "ID: {$image['id']}, Product: {$image['product_id']}, Path: {$image['image_path']}\n";
        
        // Check if file exists
        if (file_exists($image['image_path'])) {
            echo "  ✅ File exists\n";
        } else {
            echo "  ❌ File missing\n";
        }
        echo "\n";
    }
    
    // Test getProductImage function
    echo "=== Testing getProductImage() ===\n";
    $testProductId = 1;
    $primaryImage = getProductImage($testProductId, true);
    echo "Product $testProductId primary image: $primaryImage\n";
    
    if (file_exists($primaryImage)) {
        echo "✅ Primary image file exists\n";
    } else {
        echo "❌ Primary image file missing\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 