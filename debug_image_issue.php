<?php
// Debug script to check why images are not being found
require_once 'includes/functions.php';

echo "=== Debug Image Issue ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Check if there are any product images in the database
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM product_images");
    $totalImages = $stmt->fetchColumn();
    echo "Total images in database: $totalImages\n\n";
    
    // Check a specific product
    $productId = 1;
    echo "=== Checking Product ID: $productId ===\n";
    
    // Get product info
    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        echo "Product: {$product['name']}\n";
        
        // Get images for this product
        $stmt = $pdo->prepare("SELECT id, image_path, is_primary, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order");
        $stmt->execute([$productId]);
        $images = $stmt->fetchAll();
        
        echo "Images found: " . count($images) . "\n";
        
        foreach ($images as $image) {
            echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: {$image['is_primary']}, Order: {$image['sort_order']}\n";
            
            // Check if file exists
            if (file_exists($image['image_path'])) {
                echo "  ✅ File exists\n";
            } else {
                echo "  ❌ File missing\n";
            }
        }
        
        // Test the getProductImage function
        echo "\n=== Testing getProductImage() function ===\n";
        $primaryImage = getProductImage($productId, true);
        echo "Primary image returned: $primaryImage\n";
        
        $allImages = getProductImage($productId, false);
        echo "All images returned: $allImages\n";
        
        // Test getProductImages function
        echo "\n=== Testing getProductImages() function ===\n";
        $productImages = getProductImages($productId);
        echo "getProductImages returned: " . count($productImages) . " images\n";
        
        foreach ($productImages as $img) {
            echo "- {$img['image_path']}\n";
        }
        
    } else {
        echo "Product not found!\n";
    }
    
    // Check if placeholder file exists
    echo "\n=== Checking Placeholder ===\n";
    $placeholderPath = 'images/placeholder.jpg';
    if (file_exists($placeholderPath)) {
        echo "✅ Placeholder exists: $placeholderPath\n";
    } else {
        echo "❌ Placeholder missing: $placeholderPath\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 