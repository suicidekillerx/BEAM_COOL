<?php
/**
 * Inventory management functions
 */

/**
 * Update inventory when an order is confirmed
 * @param int $orderId The ID of the order
 * @return bool True on success, false on failure
 */
function updateInventoryOnOrderConfirm($orderId) {
    $pdo = getDBConnection();
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Get all items in the order
        $stmt = $pdo->prepare("
            SELECT oi.product_id, oi.size, oi.quantity 
            FROM order_items oi 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Update inventory for each item
        $updateStmt = $pdo->prepare("
            UPDATE product_sizes 
            SET stock_quantity = stock_quantity - ? 
            WHERE product_id = ? AND size = ? AND stock_quantity >= ?
        ");
        
        foreach ($orderItems as $item) {
            // Update the stock quantity
            $updateStmt->execute([
                $item['quantity'],
                $item['product_id'],
                $item['size'],
                $item['quantity'] // Ensures we don't go below 0
            ]);
            
            // Check if any rows were affected
            if ($updateStmt->rowCount() === 0) {
                throw new Exception("Insufficient stock for product ID: {$item['product_id']} size: {$item['size']}");
            }
            
            // Update product stock status if needed
            updateProductStockStatus($item['product_id']);
        }
        
        // Update order status to confirmed
        $updateOrderStmt = $pdo->prepare("
            UPDATE orders 
            SET order_status = 'confirmed', updated_at = NOW() 
            WHERE id = ? AND order_status = 'pending'
        ");
        $updateOrderStmt->execute([$orderId]);
        
        if ($updateOrderStmt->rowCount() === 0) {
            $stmt = $pdo->prepare("SELECT order_status FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $currentStatus = $stmt->fetchColumn();
            throw new Exception("Failed to confirm order. Current status: " . ($currentStatus ?: 'unknown'));
        }
        
        // Commit transaction
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error updating inventory for order {$orderId}: " . $e->getMessage());
        return false;
    }
}

/**
 * Restore inventory when an order is cancelled
 * @param int $orderId The ID of the order
 * @return bool True on success, false on failure
 */
function restoreInventoryOnOrderCancel($orderId) {
    $pdo = getDBConnection();
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Get current order status
        $stmt = $pdo->prepare("SELECT order_status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception("Order not found");
        }
        
        // Only process if order is in a cancelable state
        if (!in_array($order['order_status'], ['pending', 'confirmed', 'processing'])) {
            throw new Exception("Cannot cancel order with status: {$order['order_status']}. Only pending, confirmed, or processing orders can be cancelled.");
        }
        
        // Only restore inventory if the order was previously 'processing'
        $shouldRestoreInventory = ($order['order_status'] === 'processing');
        
        // Get all items in the order
        $stmt = $pdo->prepare("
            SELECT oi.product_id, oi.size, oi.quantity 
            FROM order_items oi 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restore inventory for each item only if the order was previously 'confirmed'
        if ($shouldRestoreInventory) {
            $updateStmt = $pdo->prepare("
                UPDATE product_sizes 
                SET stock_quantity = stock_quantity + ? 
                WHERE product_id = ? AND size = ?
            ");
            
            foreach ($orderItems as $item) {
                $updateStmt->execute([
                    $item['quantity'],
                    $item['product_id'],
                    $item['size']
                ]);
                
                // Update product stock status
                updateProductStockStatus($item['product_id']);
            }
        }
        
        // Update order status to cancelled
        $updateOrderStmt = $pdo->prepare("
            UPDATE orders 
            SET order_status = 'cancelled', updated_at = NOW() 
            WHERE id = ? AND order_status IN ('confirmed', 'processing')
        ");
        $updateOrderStmt->execute([$orderId]);
        
        if ($updateOrderStmt->rowCount() === 0) {
            throw new Exception("Failed to update order status");
        }
        
        // Commit transaction
        $pdo->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error restoring inventory for order {$orderId}: " . $e->getMessage());
        return false;
    }
}

/**
 * Update product stock status based on available sizes
 * @param int $productId The ID of the product
 */
function updateProductStockStatus($productId) {
    $pdo = getDBConnection();
    
    // Get total available stock across all sizes
    $stmt = $pdo->prepare("
        SELECT SUM(stock_quantity) as total_stock
        FROM product_sizes 
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalStock = (int)$result['total_stock'];
    
    // Update product stock status
    $stockStatus = ($totalStock > 0) ? 'in_stock' : 'out_of_stock';
    
    $updateStmt = $pdo->prepare("
        UPDATE products 
        SET stock_status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([$stockStatus, $productId]);
}

/**
 * Get available stock for a product size
 * @param int $productId The ID of the product
 * @param string $size The size to check
 * @return int Available quantity
 */
function getProductStock($productId, $size) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        SELECT stock_quantity 
        FROM product_sizes 
        WHERE product_id = ? AND size = ?
    ");
    $stmt->execute([$productId, $size]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? (int)$result['stock_quantity'] : 0;
}
?>
