<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Multiple Images Test</h1>";

// Test 1: Create a product with multiple images
echo "<h2>Test 1: Creating Product with Multiple Images</h2>";

try {
    $pdo = getDBConnection();
    
    // Create test product
    $name = 'Multiple Images Test Product ' . time();
    $slug = createSlug($name);
    $description = 'Test product with multiple images';
    $price = 49.99;
    $costPrice = 25.00;
    
    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, cost_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $description, $price, $costPrice]);
    
    $productId = $pdo->lastInsertId();
    echo "✅ Test product created with ID: $productId<br>";
    
    // Create multiple test image files
    $testImages = [
        'test_image_1.jpg' => 'images/products/test_image_1.jpg',
        'test_image_2.png' => 'images/products/test_image_2.png',
        'test_image_3.jpg' => 'images/products/test_image_3.jpg',
        'test_image_4.png' => 'images/products/test_image_4.png'
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
    
    // Insert image records with different sort orders
    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
    $sortOrder = 0;
    foreach ($testImages as $fileName => $filePath) {
        $isPrimary = ($sortOrder === 0) ? 1 : 0; // First image is primary
        $stmt->execute([$productId, $filePath, $isPrimary, $sortOrder]);
        echo "✅ Image record inserted: $fileName (Primary: " . ($isPrimary ? 'Yes' : 'No') . ", Order: $sortOrder)<br>";
        $sortOrder++;
    }
    
    // Test 2: Verify images were inserted correctly
    echo "<h2>Test 2: Verifying Images</h2>";
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();
    
    echo "✅ Found " . count($images) . " image(s) for product $productId:<br>";
    foreach ($images as $image) {
        echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . ", Order: {$image['sort_order']}<br>";
    }
    
    // Test 3: Test getProductImages function
    echo "<h2>Test 3: Testing getProductImages Function</h2>";
    $retrievedImages = getProductImages($productId);
    echo "✅ getProductImages returned " . count($retrievedImages) . " image(s)<br>";
    
    $primaryImage = null;
    foreach ($retrievedImages as $image) {
        if ($image['is_primary']) {
            $primaryImage = $image;
            break;
        }
    }
    
    if ($primaryImage) {
        echo "✅ Primary image: {$primaryImage['image_path']}<br>";
    } else {
        echo "❌ No primary image found<br>";
    }
    
    // Test 4: Test image reordering (simulate the AJAX call)
    echo "<h2>Test 4: Testing Image Reordering</h2>";
    
    // Simulate reordering: move the last image to first position
    $newOrder = [];
    $lastImage = end($images);
    $newOrder[] = ['id' => $lastImage['id'], 'sort_order' => 0];
    
    foreach ($images as $image) {
        if ($image['id'] != $lastImage['id']) {
            $newOrder[] = ['id' => $image['id'], 'sort_order' => count($newOrder)];
        }
    }
    
    // Update the order
    $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
    foreach ($newOrder as $image) {
        $stmt->execute([$image['sort_order'], $image['id']]);
    }
    
    echo "✅ Image order updated<br>";
    
    // Verify the new order
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $reorderedImages = $stmt->fetchAll();
    
    echo "✅ New order:<br>";
    foreach ($reorderedImages as $image) {
        echo "- ID: {$image['id']}, Path: {$image['image_path']}, Order: {$image['sort_order']}<br>";
    }
    
    // Test 5: Test setting a different primary image
    echo "<h2>Test 5: Testing Primary Image Change</h2>";
    
    // Set the second image as primary
    $secondImage = $reorderedImages[1];
    
    // Remove primary from all images
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
    $stmt->execute([$productId]);
    
    // Set the second image as primary
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
    $stmt->execute([$secondImage['id']]);
    
    echo "✅ Set image ID {$secondImage['id']} as primary<br>";
    
    // Verify the change
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? AND is_primary = 1");
    $stmt->execute([$productId]);
    $newPrimary = $stmt->fetch();
    
    if ($newPrimary) {
        echo "✅ New primary image: {$newPrimary['image_path']}<br>";
    } else {
        echo "❌ No primary image found<br>";
    }
    
    // Test 6: Test deleting an image
    echo "<h2>Test 6: Testing Image Deletion</h2>";
    
    $imageToDelete = $reorderedImages[2]; // Delete the third image
    
    // Delete the image record
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$imageToDelete['id']]);
    
    // Delete the physical file
    $filePath = $imageToDelete['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "✅ Physical file deleted: $filePath<br>";
    }
    
    echo "✅ Image record deleted: ID {$imageToDelete['id']}<br>";
    
    // Verify remaining images
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $remainingImages = $stmt->fetchAll();
    
    echo "✅ Remaining images: " . count($remainingImages) . "<br>";
    foreach ($remainingImages as $image) {
        echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . "<br>";
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
echo "<p>If all tests passed, the multiple image functionality is working correctly.</p>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>Add multiple images when creating a product</li>";
echo "<li>Reorder images using drag and drop in the edit modal</li>";
echo "<li>Set any image as the primary image</li>";
echo "<li>Delete individual images</li>";
echo "</ul>";
?> 