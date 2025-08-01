<?php
// Fix corrupted image paths in database
require_once 'includes/functions.php';

echo "=== Fixing Corrupted Image Paths ===\n\n";

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
        
        // Check if path is corrupted (has duplicate extensions)
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)\1/', $oldPath)) {
            echo "  ❌ Corrupted path detected\n";
            
            // Try to find the correct file
            $possiblePaths = [
                // Try with correct extension
                preg_replace('/\.(jpg|jpeg|png|gif|webp)\1/', '.$1', $oldPath),
                // Try in products folder
                str_replace('images/', 'images/products/', $oldPath),
                // Try both
                str_replace('images/', 'images/products/', preg_replace('/\.(jpg|jpeg|png|gif|webp)\1/', '.$1', $oldPath))
            ];
            
            $fixed = false;
            foreach ($possiblePaths as $newPath) {
                if (file_exists($newPath)) {
                    // Update database
                    $updateStmt = $pdo->prepare("UPDATE product_images SET image_path = ? WHERE id = ?");
                    $updateStmt->execute([$newPath, $id]);
                    
                    echo "  ✅ Fixed: $oldPath → $newPath\n";
                    $updated++;
                    $fixed = true;
                    break;
                }
            }
            
            if (!$fixed) {
                echo "  ❌ Could not find correct file for: $oldPath\n";
                $errors++;
            }
        } else {
            // Check if file exists
            if (file_exists($oldPath)) {
                echo "  ✅ File exists: $oldPath\n";
            } else {
                echo "  ❌ File missing: $oldPath\n";
                
                // Try to find it in products folder
                $newPath = str_replace('images/', 'images/products/', $oldPath);
                if (file_exists($newPath)) {
                    $updateStmt = $pdo->prepare("UPDATE product_images SET image_path = ? WHERE id = ?");
                    $updateStmt->execute([$newPath, $id]);
                    
                    echo "  ✅ Fixed path: $oldPath → $newPath\n";
                    $updated++;
                } else {
                    echo "  ❌ Could not find file in products folder either\n";
                    $errors++;
                }
            }
        }
        echo "\n";
    }
    
    echo "=== Summary ===\n";
    echo "Updated: $updated paths\n";
    echo "Errors: $errors paths\n";
    echo "Total processed: " . count($images) . " images\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 