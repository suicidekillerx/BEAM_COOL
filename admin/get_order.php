<?php
require_once '../includes/functions.php';

$orderId = (int)$_GET['id'] ?? 0;

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

$pdo = getDBConnection();

// Get order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'order' => $order]);
?> 