<?php
require_once 'config/database.php';

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Check if product_images table exists
echo "<h2>Table Structure Check</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_images'");
    if ($stmt->rowCount() > 0) {
        echo "✅ product_images table exists<br>";
        
        // Check table structure
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

// Check if products table exists
echo "<h2>Products Table Check</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    if ($stmt->rowCount() > 0) {
        echo "✅ products table exists<br>";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table structure:<br>";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}<br>";
        }
    } else {
        echo "❌ products table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
}

// Test file upload directory
echo "<h2>Upload Directory Test</h2>";
$uploadDir = 'images/products/';
if (!is_dir($uploadDir)) {
    echo "❌ Upload directory does not exist: $uploadDir<br>";
    echo "Attempting to create directory...<br>";
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Upload directory created successfully<br>";
    } else {
        echo "❌ Failed to create upload directory<br>";
    }
} else {
    echo "✅ Upload directory exists: $uploadDir<br>";
    
    // Check permissions
    if (is_writable($uploadDir)) {
        echo "✅ Upload directory is writable<br>";
    } else {
        echo "❌ Upload directory is not writable<br>";
    }
}

// Test creating a sample product with images
echo "<h2>Sample Product Creation Test</h2>";
try {
    // Create a test product
    $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, cost_price, color) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test Product', 'test-product', 'Test description', 29.99, 15.00, 'Black']);
    $productId = $pdo->lastInsertId();
    echo "✅ Test product created with ID: $productId<br>";
    
    // Test inserting image records
    $testImagePath = 'images/products/test_image.jpg';
    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$productId, $testImagePath, 1, 0]);
    echo "✅ Test image record inserted<br>";
    
    // Verify the image was inserted
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();
    echo "✅ Found " . count($images) . " image(s) for product $productId<br>";
    
    // Clean up test data
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    echo "✅ Test data cleaned up<br>";
    
} catch (Exception $e) {
    echo "❌ Error in sample product test: " . $e->getMessage() . "<br>";
}

// Check current products and their images
echo "<h2>Current Products and Images</h2>";
try {
    $stmt = $pdo->query("SELECT p.id, p.name, COUNT(pi.id) as image_count FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id GROUP BY p.id ORDER BY p.id DESC LIMIT 10");
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

// Test the getProductImages function
echo "<h2>getProductImages Function Test</h2>";
require_once 'includes/functions.php';

try {
    $stmt = $pdo->query("SELECT id FROM products ORDER BY id DESC LIMIT 1");
    $lastProduct = $stmt->fetch();
    
    if ($lastProduct) {
        $productId = $lastProduct['id'];
        $images = getProductImages($productId);
        echo "✅ getProductImages function works for product $productId<br>";
        echo "Found " . count($images) . " image(s)<br>";
        
        foreach ($images as $image) {
            echo "- Image: {$image['image_path']} (Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . ")<br>";
        }
    } else {
        echo "No products available to test getProductImages function<br>";
    }
} catch (Exception $e) {
    echo "❌ Error testing getProductImages: " . $e->getMessage() . "<br>";
}

echo "<h2>Debug Complete</h2>";
echo "Check the output above to identify any issues with image uploads.<br>";
?> 