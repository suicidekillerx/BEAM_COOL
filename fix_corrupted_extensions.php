<?php
// Fix corrupted file extensions in database
require_once 'includes/functions.php';

echo "=== Fixing Corrupted File Extensions ===\n\n";

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
        
        echo "Processing ID $id: $oldPath\n";
        
        // Check for corrupted extensions (duplicate extensions)
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)\1/', $oldPath)) {
            echo "  ❌ Corrupted extension detected\n";
            
            // Fix the extension
            $newPath = preg_replace('/\.(jpg|jpeg|png|gif|webp)\1/', '.$1', $oldPath);
            
            // Check if the fixed file exists
            if (file_exists($newPath)) {
                // Update database
                $updateStmt = $pdo->prepare("UPDATE product_images SET image_path = ? WHERE id = ?");
                $updateStmt->execute([$newPath, $id]);
                
                echo "  ✅ Fixed: $oldPath → $newPath\n";
                $updated++;
            } else {
                echo "  ❌ Fixed file not found: $newPath\n";
                $errors++;
            }
        } else {
            // Check if file exists
            if (file_exists($oldPath)) {
                echo "  ✅ File exists: $oldPath\n";
            } else {
                echo "  ❌ File missing: $oldPath\n";
                $errors++;
            }
        }
        echo "\n";
    }
    
    echo "=== Summary ===\n";
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