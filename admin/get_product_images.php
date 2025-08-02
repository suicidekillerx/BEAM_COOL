<?php
session_start();
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$productId = $_GET['product_id'] ?? 0;

if (!$productId || !is_numeric($productId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get product images ordered by sort_order
    $stmt = $pdo->prepare("
        SELECT id, image_path, sort_order, is_primary 
        FROM product_images 
        WHERE product_id = ? 
        ORDER BY sort_order ASC
    ");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formattedImages = [];
    foreach ($images as $image) {
        $formattedImages[] = [
            'id' => $image['id'],
            'path' => $image['image_path'],
            'sort_order' => $image['sort_order'],
            'is_primary' => (bool)$image['is_primary'],
            'full_url' => '../' . $image['image_path']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'images' => $formattedImages
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching product images: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch product images']);
}
?> 