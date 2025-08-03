<?php
session_start();
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$imageId = $input['image_id'] ?? 0;
$productId = $input['product_id'] ?? 0;

if (!$imageId || !is_numeric($imageId) || !$productId || !is_numeric($productId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid image ID or product ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get image information before deleting
    $stmt = $pdo->prepare("SELECT image_path, is_primary FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->execute([$imageId, $productId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$image) {
        http_response_code(404);
        echo json_encode(['error' => 'Image not found']);
        exit;
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Delete the image record
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->execute([$imageId, $productId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to delete image record');
    }
    
    // If this was the primary image, set the next image as primary
    if ($image['is_primary']) {
        // First, clear all primary flags for this product
        $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        // Then set the image with the lowest sort_order as primary
        $stmt = $pdo->prepare("
            UPDATE product_images 
            SET is_primary = 1 
            WHERE product_id = ? 
            ORDER BY sort_order ASC, id ASC 
            LIMIT 1
        ");         
        $stmt->execute([$productId]);
    }
    
    // Delete the physical file
    $filePath = '../' . $image['image_path'];
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            error_log("Warning: Could not delete physical file: $filePath");
        }
    }
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Image deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error deleting product image: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete image']);
}
?> 