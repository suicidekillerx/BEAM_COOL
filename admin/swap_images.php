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

$imageId1 = $input['image_id_1'] ?? 0;
$imageId2 = $input['image_id_2'] ?? 0;
$productId = $input['product_id'] ?? 0;

if (!$imageId1 || !$imageId2 || !$productId || !is_numeric($imageId1) || !is_numeric($imageId2) || !is_numeric($productId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Verify both images belong to the same product
    $stmt = $pdo->prepare("SELECT id, sort_order FROM product_images WHERE id IN (?, ?) AND product_id = ?");
    $stmt->execute([$imageId1, $imageId2, $productId]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($images) !== 2) {
        http_response_code(400);
        echo json_encode(['error' => 'One or both images not found or do not belong to this product']);
        exit;
    }
    
    // Get the current sort orders
    $sortOrder1 = null;
    $sortOrder2 = null;
    
    foreach ($images as $image) {
        if ($image['id'] == $imageId1) {
            $sortOrder1 = $image['sort_order'];
        } else {
            $sortOrder2 = $image['sort_order'];
        }
    }
    
    // Swap the sort orders
    $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
    $stmt->execute([$sortOrder2, $imageId1]);
    $stmt->execute([$sortOrder1, $imageId2]);
    
    // Update primary image - the image with sort_order = 1 should be primary
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = ?");
    $stmt->execute([$productId]);
    
    $stmt = $pdo->prepare("UPDATE product_images SET is_primary = 1 WHERE product_id = ? AND sort_order = 1");
    $stmt->execute([$productId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Images swapped successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error swapping images: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to swap images']);
}
?> 