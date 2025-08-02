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
    
    // Get product stock information
    $stmt = $pdo->prepare("
        SELECT size, stock_quantity 
        FROM product_sizes 
        WHERE product_id = ? 
        ORDER BY FIELD(size, 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL')
    ");
    $stmt->execute([$productId]);
    $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product stock settings
    $stmt = $pdo->prepare("
        SELECT show_stock, stock_status 
        FROM products 
        WHERE id = ?
    ");
    $stmt->execute([$productId]);
    $productSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format stock data by size
    $stockBySize = [];
    $totalStock = 0;
    
    foreach ($stockData as $stock) {
        $stockBySize[$stock['size']] = (int)$stock['stock_quantity'];
        $totalStock += (int)$stock['stock_quantity'];
    }
    
    // Ensure all sizes are present (default to 0 if missing)
    $allSizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
    foreach ($allSizes as $size) {
        if (!isset($stockBySize[$size])) {
            $stockBySize[$size] = 0;
        }
    }
    
    echo json_encode([
        'success' => true,
        'stock' => $stockBySize,
        'total_stock' => $totalStock,
        'show_stock' => (bool)($productSettings['show_stock'] ?? false),
        'stock_status' => $productSettings['stock_status'] ?? 'in_stock'
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching product stock: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch product stock']);
}
?> 