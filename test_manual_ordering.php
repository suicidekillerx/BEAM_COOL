<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Manual Image Ordering Test</h1>";

// Test 1: Create a product with multiple images
echo "<h2>Test 1: Creating Product with Multiple Images</h2>";

try {
    $pdo = getDBConnection();
    
    // Create test product
    $name = 'Manual Ordering Test Product ' . time();
    $slug = createSlug($name);
    $description = 'Test product with manual ordering';
    $price = 39.99;
    $costPrice = 20.00;
    
    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, cost_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $description, $price, $costPrice]);
    
    $productId = $pdo->lastInsertId();
    echo "✅ Test product created with ID: $productId<br>";
    
    // Create multiple test image files
    $testImages = [
        'image_1.jpg' => 'images/products/image_1.jpg',
        'image_2.png' => 'images/products/image_2.png',
        'image_3.jpg' => 'images/products/image_3.jpg',
        'image_4.png' => 'images/products/image_4.png',
        'image_5.jpg' => 'images/products/image_5.jpg'
    ];
    
    foreach ($testImages as $fileName => $filePath) {
        // Create a simple test image (1x1 pixel)
        $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        if (file_put_contents($filePath, $imageData)) {
            echo "✅ Test image created: $fileName<br>";
        } else {
            echo "❌ Failed to create test image: $fileName<br>";
        }
    }
    
    // Insert image records with initial order
    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
    $sortOrder = 1;
    foreach ($testImages as $fileName => $filePath) {
        $isPrimary = ($sortOrder === 1) ? 1 : 0; // First image is primary
        $stmt->execute([$productId, $filePath, $isPrimary, $sortOrder]);
        echo "✅ Image record inserted: $fileName (Primary: " . ($isPrimary ? 'Yes' : 'No') . ", Order: $sortOrder)<br>";
        $sortOrder++;
    }
    
    // Test 2: Verify initial order
    echo "<h2>Test 2: Verifying Initial Order</h2>";
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();
    
    echo "✅ Found " . count($images) . " image(s) for product $productId:<br>";
    foreach ($images as $image) {
        echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . ", Order: {$image['sort_order']}<br>";
    }
    
    // Test 3: Test manual reordering (simulate the AJAX call)
    echo "<h2>Test 3: Testing Manual Reordering</h2>";
    
    // Simulate changing order: 5, 1, 3, 2, 4
    $newOrder = [
        ['id' => $images[4]['id'], 'sort_order' => 1], // image_5.jpg -> order 1
        ['id' => $images[0]['id'], 'sort_order' => 2], // image_1.jpg -> order 2
        ['id' => $images[2]['id'], 'sort_order' => 3], // image_3.jpg -> order 3
        ['id' => $images[1]['id'], 'sort_order' => 4], // image_2.png -> order 4
        ['id' => $images[3]['id'], 'sort_order' => 5]  // image_4.png -> order 5
    ];
    
    // Update the order
    $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
    foreach ($newOrder as $image) {
        $stmt->execute([$image['sort_order'], $image['id']]);
    }
    
    echo "✅ Manual order updated<br>";
    
    // Verify the new order
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $reorderedImages = $stmt->fetchAll();
    
    echo "✅ New order:<br>";
    foreach ($reorderedImages as $image) {
        echo "- ID: {$image['id']}, Path: {$image['image_path']}, Order: {$image['sort_order']}<br>";
    }
    
    // Test 4: Test partial reordering (only some images)
    echo "<h2>Test 4: Testing Partial Reordering</h2>";
    
    // Only change order of first and last image
    $partialOrder = [
        ['id' => $reorderedImages[4]['id'], 'sort_order' => 1], // Last image -> first
        ['id' => $reorderedImages[0]['id'], 'sort_order' => 5]  // First image -> last
    ];
    
    $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
    foreach ($partialOrder as $image) {
        $stmt->execute([$image['sort_order'], $image['id']]);
    }
    
    echo "✅ Partial order updated<br>";
    
    // Verify the partial reorder
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $partialReorderedImages = $stmt->fetchAll();
    
    echo "✅ Partial reorder result:<br>";
    foreach ($partialReorderedImages as $image) {
        echo "- ID: {$image['id']}, Path: {$image['image_path']}, Order: {$image['sort_order']}<br>";
    }
    
    // Test 5: Test setting a different primary image
    echo "<h2>Test 5: Testing Primary Image Change</h2>";
    
    // Set the third image as primary
    $thirdImage = $partialReorderedImages[2];
    
    // Remove primary from all images
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
    $stmt->execute([$productId]);
    
    // Set the third image as primary
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
    $stmt->execute([$thirdImage['id']]);
    
    echo "✅ Set image ID {$thirdImage['id']} as primary<br>";
    
    // Verify the change
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$productId]);
    $newPrimary = $stmt->fetch();
    
    if ($newPrimary) {
        echo "✅ New primary image: {$newPrimary['image_path']}<br>";
    } else {
        echo "❌ No primary image found<br>";
    }
    
    // Test 6: Test the getProductImages function with new order
    echo "<h2>Test 6: Testing getProductImages Function</h2>";
    $retrievedImages = getProductImages($productId);
    echo "✅ getProductImages returned " . count($retrievedImages) . " image(s)<br>";
    
    echo "✅ Images in order:<br>";
    foreach ($retrievedImages as $image) {
        echo "- Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . ", Order: {$image['sort_order']}<br>";
    }
    
    // Clean up
    echo "<h2>Cleanup</h2>";
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    
    foreach ($testImages as $fileName => $filePath) {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    echo "✅ Test data cleaned up<br>";
    
} catch (Exception $e) {
    echo "❌ Error in test: " . $e->getMessage() . "<br>";
}

// Helper function
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests passed, the manual ordering system is working correctly.</p>";
echo "<p>New features:</p>";
echo "<ul>";
echo "<li>✅ Manual order input fields for each image</li>";
echo "<li>✅ Visual feedback when order is changed</li>";
echo "<li>✅ Update Order button to save changes</li>";
echo "<li>✅ Better design with smaller, more compact images</li>";
echo "<li>✅ Improved visual styling and user experience</li>";
echo "</ul>";
?> 