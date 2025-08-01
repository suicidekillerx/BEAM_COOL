<?php
require_once '../includes/functions.php';

if (isset($_GET['id'])) {
    $productId = (int)$_GET['id'];
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, col.name as collection_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN collections col ON p.collection_id = col.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        // Add primary image
        $productImages = getProductImages($product['id']);
        $product['primary_image'] = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
        
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID required']);
}
?> 