<?php
require_once '../includes/functions.php';

$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

$pdo = getDBConnection();

try {
    // Get order items with product information
    $stmt = $pdo->prepare("
        SELECT oi.*, oi.product_name, p.color, pi.image_path
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE oi.order_id = ?
        ORDER BY oi.id
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default image if none exists
    foreach ($items as &$item) {
        if (!$item['image_path']) {
            $item['image_path'] = '../images/placeholder.jpg';
        } else {
            $item['image_path'] = '../' . $item['image_path'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_order_items.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching order items: ' . $e->getMessage()
    ]);
}
?> 