<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['image_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing image ID']);
    exit;
}

$imageId = (int)$input['image_id'];

try {
    $pdo = getDBConnection();
    
    // Get image information before deleting
    $stmt = $pdo->prepare("SELECT image_path, product_id, is_primary FROM product_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $image = $stmt->fetch();
    
    if (!$image) {
        echo json_encode(['success' => false, 'error' => 'Image not found']);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete the image record from database
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$imageId]);
    
    // If this was the primary image, set the first remaining image as primary
    if ($image['is_primary']) {
        $stmt = $pdo->prepare("SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1");
        $stmt->execute([$image['product_id']]);
        $newPrimary = $stmt->fetch();
        
        if ($newPrimary) {
            $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
            $stmt->execute([$newPrimary['id']]);
        }
    }
    
    // Delete the physical file
    $filePath = '../' . $image['image_path'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 