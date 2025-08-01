<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Final Image Upload Test</h1>";

// Test 1: Check database connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check upload directory
echo "<h2>Test 2: Upload Directory</h2>";
$uploadDir = 'images/products/';
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Upload directory created: $uploadDir<br>";
    } else {
        echo "❌ Failed to create upload directory<br>";
    }
} else {
    echo "✅ Upload directory exists: $uploadDir<br>";
    if (is_writable($uploadDir)) {
        echo "✅ Upload directory is writable<br>";
    } else {
        echo "❌ Upload directory is not writable<br>";
    }
}

// Test 3: Check table structure
echo "<h2>Test 3: Database Tables</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_images'");
    if ($stmt->rowCount() > 0) {
        echo "✅ product_images table exists<br>";
        
        $stmt = $pdo->query("DESCRIBE product_images");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table structure:<br>";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}<br>";
        }
    } else {
        echo "❌ product_images table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
}

// Test 4: Create a test product with images
echo "<h2>Test 4: Product Creation with Images</h2>";
try {
    // Create test product
    $name = 'Test Product ' . time();
    $slug = createSlug($name);
    $description = 'Test description';
    $price = 29.99;
    $costPrice = 15.00;
    
    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, cost_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $description, $price, $costPrice]);
    
    $productId = $pdo->lastInsertId();
    echo "✅ Test product created with ID: $productId<br>";
    
    // Create test image files
    $testImages = [
        'test_image_1.jpg' => 'images/products/test_image_1.jpg',
        'test_image_2.png' => 'images/products/test_image_2.png'
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
    
    // Insert image records
    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
    foreach ($testImages as $fileName => $filePath) {
        $isPrimary = ($fileName === 'test_image_1.jpg') ? 1 : 0;
        $sortOrder = ($fileName === 'test_image_1.jpg') ? 0 : 1;
        $stmt->execute([$productId, $filePath, $isPrimary, $sortOrder]);
        echo "✅ Image record inserted: $fileName (Primary: " . ($isPrimary ? 'Yes' : 'No') . ")<br>";
    }
    
    // Verify images
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();
    
    echo "✅ Found " . count($images) . " image(s) for product $productId:<br>";
    foreach ($images as $image) {
        echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . "<br>";
    }
    
    // Test getProductImages function
    $retrievedImages = getProductImages($productId);
    echo "✅ getProductImages returned " . count($retrievedImages) . " image(s)<br>";
    
    // Clean up
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
    echo "❌ Error in product creation test: " . $e->getMessage() . "<br>";
}

// Test 5: Check current products and their images
echo "<h2>Test 5: Current Products Status</h2>";
try {
    $stmt = $pdo->query("SELECT p.id, p.name, COUNT(pi.id) as image_count FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id GROUP BY p.id ORDER BY p.id DESC LIMIT 5");
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "No products found in database<br>";
    } else {
        echo "Recent products:<br>";
        foreach ($products as $product) {
            echo "- Product #{$product['id']}: {$product['name']} - {$product['image_count']} image(s)<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking products: " . $e->getMessage() . "<br>";
}

// Helper function
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests passed, the image upload system should be working correctly.</p>";
echo "<p>To test the admin interface:</p>";
echo "<ol>";
echo "<li>Go to the admin panel</li>";
echo "<li>Create a new product</li>";
echo "<li>Upload images</li>";
echo "<li>Check if images appear in the product list</li>";
echo "</ol>";
echo "<p>If images still don't upload, check the error logs for debugging information.</p>";
?> 