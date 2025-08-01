<?php
// Fix image paths in database to match actual file locations
require_once 'includes/functions.php';

echo "=== Fixing Image Paths ===\n\n";

try {
    $pdo = getDBConnection();
    
    // Get all product images
    $stmt = $pdo->query("SELECT id, image_path FROM product_images");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0;
    $errors = 0;
    
    foreach ($images as $image) {
        $oldPath = $image['image_path'];
        $id = $image['id'];
        
        // Check if path already has 'products/' in it
        if (strpos($oldPath, 'images/products/') === 0) {
            echo "✅ Path already correct: $oldPath\n";
            continue;
        }
        
        // Convert from 'images/filename.png' to 'images/products/filename.png'
        if (strpos($oldPath, 'images/') === 0) {
            $newPath = str_replace('images/', 'images/products/', $oldPath);
            
            // Check if the new path file exists
            if (file_exists($newPath)) {
                // Update database
                $updateStmt = $pdo->prepare("UPDATE product_images SET image_path = ? WHERE id = ?");
                $updateStmt->execute([$newPath, $id]);
                
                echo "✅ Updated: $oldPath → $newPath\n";
                $updated++;
            } else {
                echo "❌ File not found: $newPath\n";
                $errors++;
            }
        } else {
            echo "⚠️  Unexpected path format: $oldPath\n";
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Updated: $updated paths\n";
    echo "Errors: $errors paths\n";
    echo "Total processed: " . count($images) . " images\n";
    
    // Verify the fix
    echo "\n=== Verification ===\n";
    $stmt = $pdo->query("SELECT id, image_path FROM product_images LIMIT 5");
    $sampleImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sampleImages as $image) {
        $path = $image['image_path'];
        if (file_exists($path)) {
            echo "✅ {$image['id']}: $path (exists)\n";
        } else {
            echo "❌ {$image['id']}: $path (missing)\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 