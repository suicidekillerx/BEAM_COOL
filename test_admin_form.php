<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Simulate the exact admin form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Admin Form Simulation</h2>";
    
    // Log all data
    echo "<h3>POST Data:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>FILES Data:</h3>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    // Simulate the exact logic from admin/products.php
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        echo "<h3>Processing Add Action</h3>";
        
        $pdo = getDBConnection();
        
        // Extract form data
        $name = $_POST['name'] ?? '';
        $slug = createSlug($name);
        $description = $_POST['description'] ?? '';
        $shortDescription = $_POST['short_description'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $costPrice = (float)($_POST['cost_price'] ?? 0);
        $color = $_POST['color'] ?? '';
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $collectionId = !empty($_POST['collection_id']) ? (int)$_POST['collection_id'] : null;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
        $isOnSale = isset($_POST['is_on_sale']) ? 1 : 0;
        $showStock = isset($_POST['show_stock']) ? 1 : 0;
        $stockStatus = $_POST['stock_status'] ?? 'in_stock';
        
        echo "Product Name: $name<br>";
        echo "Slug: $slug<br>";
        echo "Price: $price<br>";
        echo "Cost Price: $costPrice<br>";
        
        // Handle image uploads - EXACT same logic as admin/products.php
        $imagePaths = [];
        echo "<h4>Processing Images</h4>";
        
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $uploadDir = 'images/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
                echo "Created upload directory: $uploadDir<br>";
            }
            
            echo "Processing " . count($_FILES['images']['name']) . " files<br>";
            
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                echo "File $i: {$_FILES['images']['name'][$i]} - Error: {$_FILES['images']['error'][$i]}<br>";
                
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileExtension = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $fileName = 'product_' . time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        echo "Uploading to: $uploadPath<br>";
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadPath)) {
                            $imagePaths[] = 'images/products/' . $fileName;
                            echo "✅ File uploaded successfully: $fileName<br>";
                        } else {
                            echo "❌ Failed to move uploaded file<br>";
                        }
                    } else {
                        echo "❌ Invalid file extension: $fileExtension<br>";
                    }
                } else {
                    echo "❌ Upload error: {$_FILES['images']['error'][$i]}<br>";
                }
            }
        } else {
            echo "❌ No images found in FILES array<br>";
        }
        
        echo "<h4>Image Paths Array</h4>";
        echo "<pre>";
        print_r($imagePaths);
        echo "</pre>";
        
        // Insert product
        echo "<h4>Creating Product</h4>";
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, short_description, price, sale_price, cost_price, color, category_id, collection_id, is_featured, is_bestseller, is_on_sale, show_stock, stock_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $description, $shortDescription, $price, $salePrice, $costPrice, $color, $categoryId, $collectionId, $isFeatured, $isBestseller, $isOnSale, $showStock, $stockStatus]);
        
        $productId = $pdo->lastInsertId();
        echo "✅ Product created with ID: $productId<br>";
        
        // Insert product images
        echo "<h4>Inserting Images</h4>";
        if (!empty($imagePaths)) {
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($imagePaths as $index => $imagePath) {
                $isPrimary = $index === 0 ? 1 : 0;
                $stmt->execute([$productId, $imagePath, $isPrimary, $index]);
                echo "✅ Image $index inserted: $imagePath (Primary: " . ($isPrimary ? 'Yes' : 'No') . ")<br>";
            }
        } else {
            echo "❌ No images to insert<br>";
        }
        
        // Insert product sizes
        echo "<h4>Inserting Sizes</h4>";
        $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
        $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size, stock_quantity) VALUES (?, ?, ?)");
        foreach ($sizes as $size) {
            $stockQuantity = isset($_POST['stock_' . $size]) ? (int)$_POST['stock_' . $size] : 0;
            $stmt->execute([$productId, $size, $stockQuantity]);
            echo "✅ Size $size: $stockQuantity<br>";
        }
        
        // Verify the product was created with images
        echo "<h4>Verification</h4>";
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$productId]);
        $images = $stmt->fetchAll();
        
        echo "Found " . count($images) . " image(s) for product $productId:<br>";
        foreach ($images as $image) {
            echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . "<br>";
        }
        
        echo "<h3>✅ Test Complete - Product Created Successfully!</h3>";
    }
}

// Helper function
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Form Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .debug { background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Admin Form Test</h1>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="short_description">Short Description:</label>
            <textarea id="short_description" name="short_description" rows="2"></textarea>
        </div>
        
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        
        <div class="form-group">
            <label for="cost_price">Cost Price:</label>
            <input type="number" id="cost_price" name="cost_price" step="0.01" required>
        </div>
        
        <div class="form-group">
            <label for="color">Color:</label>
            <input type="text" id="color" name="color">
        </div>
        
        <div class="form-group">
            <label for="images">Product Images:</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
            <p style="font-size: 12px; color: #666;">Select multiple images. First image will be the primary image.</p>
        </div>
        
        <div class="form-group">
            <label for="stock_status">Stock Status:</label>
            <select id="stock_status" name="stock_status">
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_featured" value="1"> Featured
            </label>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_bestseller" value="1"> Best Seller
            </label>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_on_sale" value="1"> On Sale
            </label>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="show_stock" value="1" checked> Show Stock Numbers
            </label>
        </div>
        
        <div class="form-group">
            <label>Stock Quantities:</label>
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px;">
                <?php 
                $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
                foreach ($sizes as $size): 
                ?>
                <div>
                    <label for="stock_<?php echo $size; ?>" style="font-size: 12px;"><?php echo $size; ?></label>
                    <input type="number" id="stock_<?php echo $size; ?>" name="stock_<?php echo $size; ?>" min="0" value="0" style="width: 100%;">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button type="submit">Create Product</button>
    </form>
    
    <div class="debug">
        <h3>Debug Information</h3>
        <p><strong>Upload Directory:</strong> images/products/</p>
        <p><strong>Max File Size:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
        <p><strong>Post Max Size:</strong> <?php echo ini_get('post_max_size'); ?></p>
        <p><strong>Max File Uploads:</strong> <?php echo ini_get('max_file_uploads'); ?></p>
    </div>
</body>
</html> 