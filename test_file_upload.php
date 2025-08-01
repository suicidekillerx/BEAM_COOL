<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Form Submission Debug</h2>";
    echo "<pre>";
    echo "POST data:\n";
    print_r($_POST);
    echo "\nFILES data:\n";
    print_r($_FILES);
    echo "</pre>";
    
    // Test the exact same logic as in admin/products.php
    $pdo = getDBConnection();
    
    // Handle image uploads
    $imagePaths = [];
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        echo "<h3>Processing Image Uploads</h3>";
        $uploadDir = 'images/products/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            echo "Created upload directory: $uploadDir<br>";
        }
        
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            echo "Processing file $i: {$_FILES['images']['name'][$i]}<br>";
            
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
        echo "<h3>No Images Found</h3>";
        echo "FILES['images'] is not set or not an array<br>";
    }
    
    echo "<h3>Image Paths Array</h3>";
    echo "<pre>";
    print_r($imagePaths);
    echo "</pre>";
    
    // Create a test product
    if (!empty($_POST['name'])) {
        $name = $_POST['name'];
        $slug = createSlug($name);
        $description = $_POST['description'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $costPrice = (float)($_POST['cost_price'] ?? 0);
        
        echo "<h3>Creating Product</h3>";
        echo "Name: $name<br>";
        echo "Slug: $slug<br>";
        echo "Price: $price<br>";
        
        // Insert product
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, cost_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $description, $price, $costPrice]);
        
        $productId = $pdo->lastInsertId();
        echo "✅ Product created with ID: $productId<br>";
        
        // Insert product images
        if (!empty($imagePaths)) {
            echo "<h3>Inserting Images into Database</h3>";
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($imagePaths as $index => $imagePath) {
                $isPrimary = $index === 0 ? 1 : 0;
                $stmt->execute([$productId, $imagePath, $isPrimary, $index]);
                echo "✅ Image $index inserted: $imagePath (Primary: " . ($isPrimary ? 'Yes' : 'No') . ")<br>";
            }
        } else {
            echo "❌ No images to insert<br>";
        }
        
        // Verify the images were inserted
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$productId]);
        $images = $stmt->fetchAll();
        
        echo "<h3>Verification</h3>";
        echo "Found " . count($images) . " image(s) for product $productId:<br>";
        foreach ($images as $image) {
            echo "- ID: {$image['id']}, Path: {$image['image_path']}, Primary: " . ($image['is_primary'] ? 'Yes' : 'No') . "<br>";
        }
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
    <title>File Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #005a87; }
        .debug { background: #f5f5f5; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>File Upload Test</h1>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" required>
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
            <label for="images">Product Images:</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
            <p style="font-size: 12px; color: #666;">Select multiple images. First image will be the primary image.</p>
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