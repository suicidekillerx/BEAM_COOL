<?php
require_once '../includes/functions.php';

$search = $_GET['q'] ?? '';
$limit = 10;

if (empty($search)) {
    echo json_encode(['success' => false, 'message' => 'Search query required']);
    exit;
}

$pdo = getDBConnection();

try {
    // Search for products with images
    $sql = "
        SELECT p.*, pi.image_path, c.name as category_name
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE (p.name LIKE ? OR p.description LIKE ?)
        AND p.is_active = 1
        ORDER BY p.name
        LIMIT $limit
    ";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default image if none exists
    foreach ($products as &$product) {
        if (!$product['image_path']) {
            $product['image_path'] = '../images/placeholder.jpg';
        } else {
            $product['image_path'] = '../' . $product['image_path'];
        }
        
        // Get available sizes
        $sizeStmt = $pdo->prepare("
            SELECT DISTINCT size FROM product_sizes 
            WHERE product_id = ? AND stock_quantity > 0 
            ORDER BY size
        ");
        $sizeStmt->execute([$product['id']]);
        $product['available_sizes'] = $sizeStmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error searching products: ' . $e->getMessage()
    ]);
}
?> 