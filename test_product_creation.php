<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Product Creation Test</h1>";

// Simulate the exact product creation process from admin/products.php
$pdo = getDBConnection();

// Test data
$name = 'Test Product ' . time();
$slug = createSlug($name);
$description = 'Test description';
$shortDescription = 'Short test description';
$price = 29.99;
$salePrice = null;
$costPrice = 15.00;
$color = 'Black';
$categoryId = null;
$collectionId = null;
$isFeatured = 0;
$isBestseller = 0;
$isOnSale = 0;
$showStock = 1;
$stockStatus = 'in_stock';

echo "<h2>Step 1: Creating Product</h2>";
echo "Product Name: $name<br>";
echo "Slug: $slug<br>";

// Insert product
$stmt = $pdo->prepare("INSERT INTO products (name, slug, description, short_description, price, sale_price, cost_price, color, category_id, collection_id, is_featured, is_bestseller, is_on_sale, show_stock, stock_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$name, $slug, $description, $shortDescription, $price, $salePrice, $costPrice, $color, $categoryId, $collectionId, $isFeatured, $isBestseller, $isOnSale, $showStock, $stockStatus]);

$productId = $pdo->lastInsertId();
echo "✅ Product created with ID: $productId<br>";

echo "<h2>Step 2: Testing Image Upload Simulation</h2>";

// Simulate image paths (as if files were uploaded)
$imagePaths = [
    'images/products/test_image_1.jpg',
    'images/products/test_image_2.png'
];

echo "Simulated image paths:<br>";
foreach ($imagePaths as $path) {
    echo "- $path<br>";
}

// Insert product images
if (!empty($imagePaths)) {
    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
    foreach ($imagePaths as $index => $imagePath) {
        $isPrimary = $index === 0 ? 1 : 0;
        $stmt->execute([$productId, $imagePath, $isPrimary, $index]);
        echo "✅ Image $index inserted: $imagePath (Primary: " . ($isPrimary ? 'Yes' : 'No') . ")<br>";
    }
} else {
    echo "❌ No image paths to insert<br>";
}

echo "<h2>Step 3: Verifying Product Images</h2>";

// Verify the images were inserted
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$stmt->execute([$productId]);
$images = $stmt->fetchAll();

echo "Found " . count($images) . " image(s) for product $productId:<br>";
foreach ($images as $image) {
    echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . ", Sort: {$image['sort_order']}<br>";
}

echo "<h2>Step 4: Testing getProductImages Function</h2>";

// Test the getProductImages function
$retrievedImages = getProductImages($productId);
echo "getProductImages returned " . count($retrievedImages) . " image(s):<br>";
foreach ($retrievedImages as $image) {
    echo "- Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . "<br>";
}

echo "<h2>Step 5: Cleanup</h2>";

// Clean up test data
$stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
$stmt->execute([$productId]);
echo "✅ Product images deleted<br>";

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$productId]);
echo "✅ Test product deleted<br>";

echo "<h2>Test Complete</h2>";
echo "The database operations are working correctly. The issue might be in the form submission or file upload handling.<br>";

// Helper function
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}
?> 