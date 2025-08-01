<?php
// Test product image display
require_once 'includes/functions.php';

echo "=== Product Image Display Test ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Get a sample product with images
    $stmt = $pdo->query("SELECT p.id, p.name, pi.image_path, pi.is_primary 
                         FROM products p 
                         JOIN product_images pi ON p.id = pi.product_id 
                         ORDER BY p.id, pi.sort_order 
                         LIMIT 10");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Sample Product Images ===\n";
    $currentProduct = null;
    
    foreach ($results as $row) {
        if ($currentProduct !== $row['id']) {
            $currentProduct = $row['id'];
            echo "\n--- Product ID: {$row['id']} - {$row['name']} ---\n";
        }
        
        $path = $row['image_path'];
        $primary = $row['is_primary'] ? ' (Primary)' : '';
        
        if (file_exists($path)) {
            echo "✅ {$path}{$primary}\n";
        } else {
            echo "❌ {$path}{$primary} (File missing)\n";
        }
    }
    
    echo "\n=== Frontend Display Test ===\n";
    echo "To test if images display correctly:\n";
    echo "1. Go to: http://localhost/BEAM_COOL/product-view.php?id=1\n";
    echo "2. Check if product images are visible\n";
    echo "3. Try different product IDs: 2, 3, 4, etc.\n\n";
    
    echo "=== Common Issues & Solutions ===\n";
    echo "• If images don't show: Check browser console for 404 errors\n";
    echo "• If paths are wrong: Run the fix script again\n";
    echo "• If files are missing: Check images/products/ directory\n";
    echo "• If permissions issue: Check file permissions (644)\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 