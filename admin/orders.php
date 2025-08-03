<?php
require_once 'includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireAuth();

$currentPage = 'orders';
$pageTitle = 'Orders';



// Function to generate tracking number
function generateTrackingNumber() {
    $prefix = 'BEAM';
    $timestamp = date('Ymd');
    $random = strtoupper(substr(md5(uniqid()), 0, 6));
    return $prefix . $timestamp . $random;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $orderId = (int)$_POST['order_id'];
                $newStatus = $_POST['order_status'];
                $trackingNumber = $_POST['tracking_number'] ?? '';
                $shippingNotes = $_POST['shipping_notes'] ?? '';
                $adminNotes = $_POST['admin_notes'] ?? '';
                
                // Auto-generate tracking number if status is 'shipped' and no tracking number provided
                if ($newStatus === 'shipped' && empty($trackingNumber)) {
                    $trackingNumber = generateTrackingNumber();
                }
                
                $pdo = getDBConnection();
                
                // Get current status for comparison
                $currentStatusStmt = $pdo->prepare("SELECT order_status FROM orders WHERE id = ?");
                $currentStatusStmt->execute([$orderId]);
                $currentStatus = $currentStatusStmt->fetchColumn();
                
                try {
                    // Handle inventory updates based on status changes
                    if ($newStatus === 'confirmed' && $currentStatus !== 'confirmed') {
                        require_once '../includes/inventory.php';
                        if (updateInventoryOnOrderConfirm($orderId)) {
                            // Send order to delivery platform
                            $deliveryResult = sendOrderToDelivery($orderId);
                            
                            if ($deliveryResult['success']) {
                                $successMessage = "Order #" . getOrderNumber($orderId) . " confirmed, inventory updated, and sent to delivery platform successfully!";
                                $successMessage .= " Delivery Barcode: " . $deliveryResult['barCode'];
                            } else {
                                $successMessage = "Order #" . getOrderNumber($orderId) . " confirmed and inventory updated successfully!";
                                $errorMessage = "Warning: Failed to send order to delivery platform: " . $deliveryResult['error'];
                                error_log($errorMessage);
                            }
                        } else {
                            $errorMessage = "Failed to confirm order #" . getOrderNumber($orderId) . " and update inventory.";
                            error_log($errorMessage);
                        }
                    } 
                    // Handle order cancellation
                    elseif ($newStatus === 'cancelled') {
                        require_once '../includes/inventory.php';
                        if (restoreInventoryOnOrderCancel($orderId)) {
                            $successMessage = "Order #" . getOrderNumber($orderId) . " cancelled and inventory restored successfully!";
                        } else {
                            $errorMessage = "Failed to cancel order #" . getOrderNumber($orderId) . ". It may already be cancelled or in a non-cancelable state.";
                            error_log($errorMessage);
                        }
                    }
                    // For other status updates that don't affect inventory
                    else {
                        $pdo->beginTransaction();
                        $stmt = $pdo->prepare("
                            UPDATE orders 
                            SET order_status = ?, tracking_number = ?, shipping_notes = ?, admin_notes = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        
                        if ($stmt->execute([$newStatus, $trackingNumber, $shippingNotes, $adminNotes, $orderId])) {
                            $pdo->commit();
                            $successMessage = "Order #" . getOrderNumber($orderId) . " status updated to " . ucfirst($newStatus) . "!";
                            if ($newStatus === 'shipped' && !empty($trackingNumber)) {
                                $successMessage .= " Tracking number: " . $trackingNumber;
                            }
                        } else {
                            $pdo->rollBack();
                            $errorMessage = "Failed to update order #" . getOrderNumber($orderId) . " status. Database error.";
                            error_log($errorMessage);
                        }
                    }
                } catch (Exception $e) {
                    if (isset($pdo) && $pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $errorMessage = "Error processing order #" . getOrderNumber($orderId) . ": " . $e->getMessage();
                    error_log($errorMessage);
                }
                break;
                
            case 'send_to_delivery':
                $orderId = (int)$_POST['order_id'];
                
                try {
                    $deliveryResult = sendOrderToDelivery($orderId);
                    
                    if ($deliveryResult['success']) {
                        $successMessage = "Order #" . getOrderNumber($orderId) . " successfully sent to delivery platform!";
                        $successMessage .= " Delivery Barcode: " . $deliveryResult['barCode'];
                    } else {
                        $errorMessage = "Failed to send order #" . getOrderNumber($orderId) . " to delivery platform: " . $deliveryResult['error'];
                        error_log($errorMessage);
                    }
                } catch (Exception $e) {
                    $errorMessage = "Error sending order #" . getOrderNumber($orderId) . " to delivery platform: " . $e->getMessage();
                    error_log($errorMessage);
                }
                break;
                
            case 'delete_orders':
                $orderIds = $_POST['order_ids'] ?? [];
                
                if (empty($orderIds)) {
                    $errorMessage = "No orders selected for deletion.";
                } else {
                    $pdo = getDBConnection();
                    
                    try {
                        $pdo->beginTransaction();
                        
                        $deletedCount = 0;
                        $failedCount = 0;
                        
                        foreach ($orderIds as $orderId) {
                            $orderId = (int)$orderId;
                            
                            // Check if order exists and can be deleted
                            $orderStmt = $pdo->prepare("SELECT order_status FROM orders WHERE id = ?");
                            $orderStmt->execute([$orderId]);
                            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($order) {
                                // Delete order items first
                                $deleteItemsStmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                                $deleteItemsStmt->execute([$orderId]);
                                
                                // Delete the order
                                $deleteOrderStmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                                if ($deleteOrderStmt->execute([$orderId])) {
                                    $deletedCount++;
                                } else {
                                    $failedCount++;
                                }
                            } else {
                                $failedCount++;
                            }
                        }
                        
                        $pdo->commit();
                        
                        if ($deletedCount > 0) {
                            $successMessage = "Successfully deleted $deletedCount order(s).";
                            if ($failedCount > 0) {
                                $successMessage .= " $failedCount order(s) could not be deleted.";
                            }
                        } else {
                            $errorMessage = "No orders were deleted.";
                        }
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $errorMessage = "Error deleting orders: " . $e->getMessage();
                        error_log($errorMessage);
                    }
                }
                break;
                
            case 'update_order_items':
                $orderId = (int)$_POST['order_id'];
                $items = $_POST['items'] ?? [];
                $deletedItems = $_POST['deleted_items'] ?? [];
                $newItems = $_POST['new_items'] ?? [];
                
                // Debug logging
                error_log("Update order items - Order ID: " . $orderId);
                error_log("Items to update: " . json_encode($items));
                error_log("Items to delete: " . json_encode($deletedItems));
                error_log("New items: " . json_encode($newItems));
                
                $pdo = getDBConnection();
                
                try {
                    $pdo->beginTransaction();
                    
                    // Delete removed items
                    if (!empty($deletedItems)) {
                        $deleteStmt = $pdo->prepare("DELETE FROM order_items WHERE id = ? AND order_id = ?");
                        foreach ($deletedItems as $itemId) {
                            $deleteStmt->execute([$itemId, $orderId]);
                        }
                    }
                    
                    // Update existing items
                    if (!empty($items)) {
                        $updateStmt = $pdo->prepare("
                            UPDATE order_items 
                            SET quantity = ?, size = ?
                            WHERE id = ? AND order_id = ?
                        ");
                        
                        foreach ($items as $itemId => $itemData) {
                            $quantity = (int)$itemData['quantity'];
                            $size = $itemData['size'];
                            
                            $updateStmt->execute([$quantity, $size, $itemId, $orderId]);
                        }
                    }
                    
                    // Add new items
                    if (!empty($newItems)) {
                        $insertStmt = $pdo->prepare("
                            INSERT INTO order_items (order_id, product_id, product_name, quantity, size, product_price, total_price, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        
                        foreach ($newItems as $newItem) {
                            $productId = (int)$newItem['product_id'];
                            $quantity = (int)$newItem['quantity'];
                            $size = $newItem['size'];
                            $price = (float)$newItem['price'];
                            $totalPrice = $quantity * $price;
                            
                            // Get product name
                            $productStmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
                            $productStmt->execute([$productId]);
                            $productName = $productStmt->fetchColumn() ?: 'Unknown Product';
                            
                            $insertStmt->execute([$orderId, $productId, $productName, $quantity, $size, $price, $totalPrice]);
                        }
                    }
                    
                    // Recalculate order total
                    $totalStmt = $pdo->prepare("
                        SELECT SUM(total_price) as new_total 
                        FROM order_items 
                        WHERE order_id = ?
                    ");
                    $totalStmt->execute([$orderId]);
                    $newTotal = $totalStmt->fetchColumn();
                    
                    // Update order total
                    $orderUpdateStmt = $pdo->prepare("
                        UPDATE orders 
                        SET total = ?, subtotal = ?
                        WHERE id = ?
                    ");
                    $orderUpdateStmt->execute([$newTotal, $newTotal, $orderId]);
                    
                    $pdo->commit();
                    $successMessage = "Order items updated successfully! Total updated to " . formatPrice($newTotal);
                    error_log("Order items update successful - Total: " . $newTotal);
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $errorMessage = "Error updating order items: " . $e->getMessage();
                    error_log("Order items update failed: " . $e->getMessage());
                }
                break;
                
            case 'bulk_update':
                $orderIds = $_POST['order_ids'] ?? [];
                $bulkStatus = $_POST['bulk_status'];
                
                if (empty($orderIds)) {
                    $errorMessage = "No orders selected for bulk update.";
                    error_log($errorMessage);
                    break;
                }
                
                $pdo = getDBConnection();
                require_once '../includes/inventory.php';
                
                // Get current statuses of all orders
                $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
                $statusStmt = $pdo->prepare("SELECT id, order_number, order_status FROM orders WHERE id IN ($placeholders) ORDER BY id");
                
                try {
                    $statusStmt->execute($orderIds);
                    $orders = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($orders) === 0) {
                        throw new Exception("No valid orders found with the provided IDs.");
                    }
                    
                    $successCount = 0;
                    $errorMessages = [];
                    
                    foreach ($orders as $order) {
                        $orderId = $order['id'];
                        $orderNumber = $order['order_number'];
                        $currentStatus = $order['order_status'];
                        
                        try {
                            $pdo->beginTransaction();
                            
                            // Handle confirmed status
                            if ($bulkStatus === 'confirmed' && $currentStatus !== 'confirmed') {
                                if (updateInventoryOnOrderConfirm($orderId)) {
                                    $pdo->commit();
                                    $successCount++;
                                } else {
                                    $pdo->rollBack();
                                    $errorMessages[] = "Order #$orderNumber: Failed to confirm and update inventory";
                                }
                            }
                            // Handle cancelled status
                            elseif ($bulkStatus === 'cancelled') {
                                if (in_array($currentStatus, ['pending', 'confirmed', 'processing'])) {
                                    if (restoreInventoryOnOrderCancel($orderId)) {
                                        $pdo->commit();
                                        $successCount++;
                                    } else {
                                        $pdo->rollBack();
                                        $errorMessages[] = "Order #$orderNumber: Failed to cancel and restore inventory";
                                    }
                                } else {
                                    $errorMessages[] = "Order #$orderNumber: Cannot cancel order with status '$currentStatus'";
                                }
                            }
                            // For other statuses that don't affect inventory
                            else {
                                $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ? AND order_status != ?");
                                if ($stmt->execute([$bulkStatus, $orderId, $bulkStatus])) {
                                    if ($stmt->rowCount() > 0) {
                                        $pdo->commit();
                                        $successCount++;
                                    } else {
                                        $pdo->rollBack();
                                        $errorMessages[] = "Order #$orderNumber: Already has status '$bulkStatus'";
                                    }
                                } else {
                                    $pdo->rollBack();
                                    $errorMessages[] = "Order #$orderNumber: Database update failed";
                                    error_log("Database error updating order #$orderNumber: " . implode(" ", $stmt->errorInfo()));
                                }
                            }
                        } catch (Exception $e) {
                            if ($pdo->inTransaction()) {
                                $pdo->rollBack();
                            }
                            $errorMsg = "Order #$orderNumber: " . $e->getMessage();
                            $errorMessages[] = $errorMsg;
                            error_log("Error processing order #$orderNumber: " . $e->getMessage());
                        }
                    }
                    
                    // Prepare success/error message
                    if ($successCount > 0) {
                        $successMessage = "Successfully updated $successCount order" . ($successCount > 1 ? 's' : '') . " to '$bulkStatus'!";
                    }
                    
                    if (!empty($errorMessages)) {
                        $errorMessage = "Encountered issues with " . count($errorMessages) . " order" . (count($errorMessages) > 1 ? 's' : '') . ":<br>• " . 
                                      implode("<br>• ", $errorMessages);
                    }
                    
                } catch (Exception $e) {
                    $errorMessage = "Error processing bulk update: " . $e->getMessage();
                    error_log($errorMessage);
                }
                break;
        }
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$sortBy = $_GET['sort'] ?? 'created_at';
$sortOrder = $_GET['order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$pdo = getDBConnection();
$whereConditions = [];
$params = [];

if (!empty($statusFilter)) {
    $whereConditions[] = "order_status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $whereConditions[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

if (!empty($dateFrom)) {
    $whereConditions[] = "DATE(created_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $whereConditions[] = "DATE(created_at) <= ?";
    $params[] = $dateTo;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders $whereClause");
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $perPage);

// Get orders
$orderBy = in_array($sortBy, ['order_number', 'customer_name', 'total', 'order_status', 'created_at']) ? $sortBy : 'created_at';
$orderDirection = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

$limit = (int)$perPage;
$offset = (int)$offset;

$stmt = $pdo->prepare("
    SELECT * FROM orders 
    $whereClause 
    ORDER BY $orderBy $orderDirection 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
        SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
        SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN order_status = 'delivered' THEN total ELSE 0 END) as total_revenue
    FROM orders
");
$stats = $statsStmt->fetch();

// Get status counts for filter
$statusCountsStmt = $pdo->query("
    SELECT order_status, COUNT(*) as count 
    FROM orders 
    GROUP BY order_status 
    ORDER BY count DESC
");
$statusCounts = $statusCountsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mobile responsive styles */
        @media (max-width: 1023px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .admin-sidebar.open {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
        
        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-confirmed { background-color: #dbeafe; color: #1e40af; }
        .status-processing { background-color: #f3e8ff; color: #7c3aed; }
        .status-shipped { background-color: #ecfdf5; color: #047857; }
        .status-delivered { background-color: #dcfce7; color: #166534; }
        .status-cancelled { background-color: #fee2e2; color: #dc2626; }
        .status-refunded { background-color: #fef2f2; color: #991b1b; }
        
        .filter-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e5e7eb;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .filter-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .order-row {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .order-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        /* Status-based row colors */
        .order-row.status-pending {
            border-left-color: #f59e0b;
            background-color: #fffbeb;
        }
        
        .order-row.status-confirmed {
            border-left-color: #3b82f6;
            background-color: #eff6ff;
        }
        
        .order-row.status-processing {
            border-left-color: #8b5cf6;
            background-color: #f3f4f6;
        }
        
        .order-row.status-shipped {
            border-left-color: #10b981;
            background-color: #ecfdf5;
        }
        
        .order-row.status-delivered {
            border-left-color: #059669;
            background-color: #f0fdf4;
        }
        
        .order-row.status-cancelled {
            border-left-color: #ef4444;
            background-color: #fef2f2;
        }
        
        .order-row.status-refunded {
            border-left-color: #dc2626;
            background-color: #fef2f2;
        }
        
        .order-row.selected {
            background-color: #e0e7ff !important;
            border-left-color: #6366f1 !important;
        }
        
        .order-row {
            border-left: 3px solid transparent;
        }
        
        .order-row:hover {
            border-left-color: #ef4444;
        }
        
        .bulk-actions {
            transition: all 0.3s ease;
        }
        
        .bulk-actions.has-selection {
            background-color: #f0f9ff;
            border-color: #3b82f6;
        }
        
        /* Mobile-specific styles */
        .mobile-order-card {
            display: none;
        }
        
        @media (max-width: 768px) {
            .desktop-table {
                display: none;
            }
            
            .mobile-order-card {
                display: block;
            }
            
            .stats-card {
                padding: 1rem;
            }
            
            .stats-card .text-3xl {
                font-size: 1.5rem;
            }
            
            .filter-card {
                padding: 1rem;
            }
            
            .bulk-actions {
                padding: 1rem;
            }
            
            .bulk-actions .flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .bulk-actions .flex > div {
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
        
        <?php require_once 'includes/header.php'; ?>
        
        <!-- Content Area -->
        <main class="content-area flex-1 overflow-y-auto p-4 lg:p-6">
                
                <!-- Success/Error Messages -->
                <?php if (isset($successMessage)): ?>
                    <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
                    <div class="stats-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Orders</p>
                                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['total_orders']); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-shopping-bag text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                                <p class="text-3xl font-bold text-gray-900"><?php echo formatPrice($stats['total_revenue']); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Pending Orders</p>
                                <p class="text-3xl font-bold text-yellow-600"><?php echo number_format($stats['pending_orders']); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card rounded-2xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Delivered Orders</p>
                                <p class="text-3xl font-bold text-green-600"><?php echo number_format($stats['delivered_orders']); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters and Search -->
                <div class="filter-card rounded-2xl p-4 lg:p-6 mb-6 lg:mb-8">
                    <form method="GET" class="space-y-4 lg:space-y-6">
                        <!-- Search Bar - Full Width on Mobile -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Orders</label>
                            <div class="relative">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent text-base"
                                       placeholder="Order #, customer name, email, phone">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile: Collapsible Advanced Filters -->
                        <div class="lg:hidden">
                            <button type="button" id="toggleFilters" class="w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-lg flex items-center justify-between hover:bg-gray-200 transition-colors">
                                <span class="font-medium">Advanced Filters</span>
                                <i class="fas fa-chevron-down transition-transform" id="filterIcon"></i>
                            </button>
                            
                            <div id="mobileFilters" class="hidden mt-4 space-y-4 p-4 bg-gray-50 rounded-lg">
                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    </select>
                                </div>
                                
                                <!-- Date Range -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">From</label>
                                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" 
                                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" 
                                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                    </div>
                                </div>
                                
                                <!-- Sort Options -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort by</label>
                                        <select name="sort" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                            <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Date</option>
                                            <option value="order_number" <?php echo $sortBy === 'order_number' ? 'selected' : ''; ?>>Order #</option>
                                            <option value="customer_name" <?php echo $sortBy === 'customer_name' ? 'selected' : ''; ?>>Customer</option>
                                            <option value="total" <?php echo $sortBy === 'total' ? 'selected' : ''; ?>>Total</option>
                                            <option value="order_status" <?php echo $sortBy === 'order_status' ? 'selected' : ''; ?>>Status</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Order</label>
                                        <select name="order" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                            <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                                            <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Desktop: Inline Filters -->
                        <div class="hidden lg:block">
                            <div class="grid grid-cols-4 gap-4">
                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $statusFilter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    </select>
                                </div>
                                
                                <!-- Date From -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                                    <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                </div>
                                
                                <!-- Date To -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                                    <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                </div>
                                
                                <!-- Sort -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort by</label>
                                    <select name="sort" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                        <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Date</option>
                                        <option value="order_number" <?php echo $sortBy === 'order_number' ? 'selected' : ''; ?>>Order #</option>
                                        <option value="customer_name" <?php echo $sortBy === 'customer_name' ? 'selected' : ''; ?>>Customer</option>
                                        <option value="total" <?php echo $sortBy === 'total' ? 'selected' : ''; ?>>Total</option>
                                        <option value="order_status" <?php echo $sortBy === 'order_status' ? 'selected' : ''; ?>>Status</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="submit" class="bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors font-medium flex items-center justify-center">
                                    <i class="fas fa-search mr-2"></i>
                                    Filter Orders
                                </button>
                                <a href="orders.php" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors font-medium flex items-center justify-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Clear Filters
                                </a>
                            </div>
                            
                            <!-- Desktop Sort Order -->
                            <div class="hidden lg:flex items-center space-x-4">
                                <label class="text-sm font-medium text-gray-700">Order:</label>
                                <select name="order" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                    <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                                    <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Bulk Actions -->
                <div class="bulk-actions bg-white rounded-2xl shadow-lg border border-gray-200 p-4 mb-6" id="bulkActions">
                    <form method="POST" id="bulkForm">
                        <input type="hidden" name="action" value="bulk_update">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm font-medium text-gray-700">
                                    <span id="selectedCount">0</span> orders selected
                                </span>
                                <span class="text-sm text-gray-500">|</span>
                                <span class="text-sm text-gray-500">Bulk Actions</span>
                                <select name="bulk_status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                    <option value="">Select Status</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" id="updateSelectedBtn" disabled>
                                    <i class="fas fa-save mr-2"></i>
                                    Update Selected
                                </button>
                                <button type="button" onclick="printSelectedOrders()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" id="printSelectedBtn" disabled>
                                    <i class="fas fa-print mr-2"></i>
                                    Print Selected
                                </button>
                                <button type="button" onclick="exportSelectedOrders()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" id="exportSelectedBtn" disabled>
                                    <i class="fas fa-download mr-2"></i>
                                    Export Selected
                                </button>
                                <button type="button" onclick="deleteSelectedOrders()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" id="deleteSelectedBtn" disabled>
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Selected
                                </button>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button type="button" onclick="selectAllVisible()" class="text-blue-600 hover:text-blue-800 transition-colors text-sm">
                                    <i class="fas fa-check-double mr-1"></i>
                                    Select All Visible
                                </button>
                                <button type="button" onclick="clearSelection()" class="text-gray-600 hover:text-black transition-colors text-sm">
                                    <i class="fas fa-times mr-1"></i>
                                    Clear Selection
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Orders Table -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto desktop-table">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left">
                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-black focus:ring-black">
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        <i class="fas fa-hashtag mr-2"></i>Order #
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        <i class="fas fa-user mr-2"></i>Customer
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        <i class="fas fa-dollar-sign mr-2"></i>Total
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        <i class="fas fa-info-circle mr-2"></i>Status
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        <i class="fas fa-calendar mr-2"></i>Date
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        <i class="fas fa-cogs mr-2"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-4"></i>
                                            <p class="text-lg font-medium">No orders found</p>
                                            <p class="text-sm">Try adjusting your filters or search criteria.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr class="order-row status-<?php echo $order['order_status']; ?>" 
                                            data-order-id="<?php echo $order['id']; ?>"
                                            data-deletable="true">
                                            <td class="px-6 py-4">
                                                <input type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>" 
                                                       class="order-checkbox rounded border-gray-300 text-black focus:ring-black">
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($order['order_number']); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <span class="search-customer cursor-pointer text-blue-700 hover:underline" data-search="<?php echo htmlspecialchars($order['customer_name']); ?>">
                                                            <?php echo htmlspecialchars($order['customer_name']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <span class="search-customer cursor-pointer text-blue-700 hover:underline" data-search="<?php echo htmlspecialchars($order['customer_email']); ?>">
                                                            <?php echo htmlspecialchars($order['customer_email']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <span class="search-customer cursor-pointer text-blue-700 hover:underline" data-search="<?php echo htmlspecialchars($order['customer_phone']); ?>">
                                                            <?php echo htmlspecialchars($order['customer_phone']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo formatPrice($order['total']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo $order['payment_method'] === 'cash_on_delivery' ? 'Cash on Delivery' : ucfirst(str_replace('_', ' ', $order['payment_method'])); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="order-status status-<?php echo $order['order_status']; ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo date('g:i A', strtotime($order['created_at'])); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <button onclick="viewOrder('<?php echo $order['order_number']; ?>')" 
                                                            class="text-blue-600 hover:text-blue-800 transition-colors">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button onclick="editOrder(<?php echo $order['id']; ?>)" 
                                                            class="text-green-600 hover:text-green-800 transition-colors">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="editOrderItems(<?php echo $order['id']; ?>)" 
                                                            class="text-purple-600 hover:text-purple-800 transition-colors"
                                                            title="Edit Order Items">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </button>
                                                    <button onclick="printInvoice('<?php echo $order['order_number']; ?>')" 
                                                            class="text-purple-600 hover:text-purple-800 transition-colors">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                    <button onclick="sendToDelivery(<?php echo $order['id']; ?>)" 
                                                            class="text-orange-600 hover:text-orange-800 transition-colors" 
                                                            title="Send to Delivery Platform">
                                                        <i class="fas fa-truck"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalOrders); ?> of <?php echo number_format($totalOrders); ?> orders
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if ($page > 1): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                            Previous
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm <?php echo $i === $page ? 'bg-black text-white' : 'hover:bg-gray-50'; ?> transition-colors">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                            Next
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Order Cards -->
                <div class="mobile-order-card space-y-4">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                            <p class="text-lg font-medium text-gray-900 mb-2">No orders found</p>
                            <p class="text-sm text-gray-500">Try adjusting your filters or search criteria.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 status-<?php echo $order['order_status']; ?>">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 text-lg"><?php echo htmlspecialchars($order['order_number']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900 text-lg"><?php echo formatPrice($order['total']); ?></p>
                                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-user text-gray-500 mr-2"></i>
                                        <span class="text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-envelope text-gray-500 mr-2"></i>
                                        <span class="text-gray-600"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-phone text-gray-500 mr-2"></i>
                                        <span class="text-gray-600"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <div class="flex items-center space-x-2">
                                        <input type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>" 
                                               class="order-checkbox rounded border-gray-300 text-black focus:ring-black">
                                        <span class="text-xs text-gray-500">Select</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <button onclick="viewOrder('<?php echo $order['order_number']; ?>')" 
                                                class="text-blue-600 hover:text-blue-800 transition-colors p-2">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editOrder(<?php echo $order['id']; ?>)" 
                                                class="text-green-600 hover:text-green-800 transition-colors p-2">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="editOrderItems(<?php echo $order['id']; ?>)" 
                                                class="text-purple-600 hover:text-purple-800 transition-colors p-2"
                                                title="Edit Order Items">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                        <button onclick="printInvoice('<?php echo $order['order_number']; ?>')" 
                                                class="text-purple-600 hover:text-purple-800 transition-colors p-2">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Order Details</h3>
                        <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div id="orderModalContent" class="p-6">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Order Modal -->
    <div id="editOrderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Edit Order</h3>
                        <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <form id="editOrderForm" method="POST" class="p-6">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" id="editOrderId">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Order Status</label>
                            <select name="order_status" id="editOrderStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tracking Number</label>
                            <div class="flex space-x-2">
                                <input type="text" name="tracking_number" id="editTrackingNumber" 
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                                       placeholder="Enter tracking number or leave empty for auto-generation">
                                <button type="button" onclick="generateTrackingNumber()" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    <i class="fas fa-magic mr-1"></i>Generate
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate when status is set to "Shipped"</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shipping Notes</label>
                            <textarea name="shipping_notes" id="editShippingNotes" rows="2" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                                      placeholder="Add or update delivery instructions"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes</label>
                            <textarea name="admin_notes" id="editAdminNotes" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                                      placeholder="Add internal notes about this order"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-4 mt-6">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-black transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                            Update Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Order Items Modal -->
    <div id="editOrderItemsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Edit Order Items</h3>
                        <button onclick="closeEditItemsModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <form id="editOrderItemsForm" method="POST" class="p-6">
                    <input type="hidden" name="action" value="update_order_items">
                    <input type="hidden" name="order_id" id="editItemsOrderId">
                    
                    <!-- Add Item Section -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Add New Item</h4>
                        <div class="flex space-x-4">
                            <div class="flex-1" style="position:relative;">
                                <input type="text" id="productSearch" placeholder="Search for products..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                <div id="searchResults" 
                                     style="position:absolute; left:0; top:100%; width:100%; background:#fff; border:1px solid #ccc; z-index:9999; box-shadow:0 2px 8px rgba(0,0,0,0.08); display:none;">
                                </div>
                            </div>
                           
                        </div>
                    </div>
                    
                    <div id="orderItemsContainer" class="space-y-4">
                        <!-- Order items will be loaded here -->
                    </div>
                    
                    <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                        <div class="text-lg font-semibold text-gray-900">
                            Total: <span id="orderItemsTotal">$0.00</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button type="button" onclick="closeEditItemsModal()" class="px-4 py-2 text-gray-600 hover:text-black transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                                Update Items
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Item Modal -->
    <div id="addItemModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Add Item to Order</h3>
                        <button onclick="closeAddItemModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div id="selectedProductInfo" class="mb-4 hidden">
                        <!-- Selected product info will be shown here -->
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                            <select id="selectedSize" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                <option value="">Select Size</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number" id="selectedQuantity" value="1" min="1" max="99"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-4 mt-6">
                        <button type="button" onclick="closeAddItemModal()" class="px-4 py-2 text-gray-600 hover:text-black transition-colors">
                            Cancel
                        </button>
                        <button type="button" onclick="addItemToOrder()" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                            Add to Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Bulk selection functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.order-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
        updateRowSelection();
    });
    
    document.querySelectorAll('.order-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            updateRowSelection();
        });
    });
    
    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        const selectedCount = selectedCheckboxes.length;
        const bulkActions = document.getElementById('bulkActions');
        
        document.getElementById('selectedCount').textContent = selectedCount;
        
        // Enable/disable buttons based on selection
        const updateBtn = document.getElementById('updateSelectedBtn');
        const printBtn = document.getElementById('printSelectedBtn');
        const exportBtn = document.getElementById('exportSelectedBtn');
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        
        if (selectedCount > 0) {
            bulkActions.classList.add('has-selection');
            updateBtn.disabled = false;
            printBtn.disabled = false;
            exportBtn.disabled = false;
            deleteBtn.disabled = false;
        } else {
            bulkActions.classList.remove('has-selection');
            updateBtn.disabled = true;
            printBtn.disabled = true;
            exportBtn.disabled = true;
            deleteBtn.disabled = true;
        }
    }
    
    function updateRowSelection() {
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            const row = checkbox.closest('.order-row');
            if (checkbox.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        });
    }
    
    function clearSelection() {
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('selectAll').checked = false;
        updateBulkActions();
        updateRowSelection();
    }
    
    function selectAllVisible() {
        document.querySelectorAll('.order-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        document.getElementById('selectAll').checked = true;
        updateBulkActions();
        updateRowSelection();
    }
    
    // Order modal functionality
    function viewOrder(orderNumber) {
        const modal = document.getElementById('orderModal');
        const content = document.getElementById('orderModalContent');
        
        // Show loading
        content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
        modal.classList.remove('hidden');
        
        // Load order details
        fetch(`get_order_details.php?order=${encodeURIComponent(orderNumber)}`)
            .then(response => response.text())
            .then(html => {
                content.innerHTML = html;
            })
            .catch(error => {
                content.innerHTML = '<div class="text-center py-8 text-red-600">Error loading order details</div>';
            });
    }
    
    function closeOrderModal() {
        document.getElementById('orderModal').classList.add('hidden');
    }
    
    // Edit order functionality
    function editOrder(orderId) {
        const modal = document.getElementById('editOrderModal');
        const form = document.getElementById('editOrderForm');
        
        // Load current order data
        fetch(`get_order.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('editOrderId').value = orderId;
                    document.getElementById('editOrderStatus').value = data.order.order_status;
                    document.getElementById('editTrackingNumber').value = data.order.tracking_number || '';
                    document.getElementById('editShippingNotes').value = data.order.shipping_notes || '';
                    document.getElementById('editAdminNotes').value = data.order.admin_notes || '';
                    modal.classList.remove('hidden');
                } else {
                    alert('Error loading order data');
                }
            })
            .catch(error => {
                alert('Error loading order data');
            });
    }
    
    function closeEditModal() {
        document.getElementById('editOrderModal').classList.add('hidden');
    }
    
    // Generate tracking number
    function generateTrackingNumber() {
        const prefix = 'BEAM';
        const timestamp = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        const random = Math.random().toString(36).substring(2, 8).toUpperCase();
        const trackingNumber = prefix + timestamp + random;
        
        document.getElementById('editTrackingNumber').value = trackingNumber;
    }
    
    // Edit order items functionality
    function editOrderItems(orderId) {
        const modal = document.getElementById('editOrderItemsModal');
        const container = document.getElementById('orderItemsContainer');
        
        if (!modal || !container) {
            console.error('Modal or container elements not found');
            alert('Error: Modal elements not found. Please refresh the page and try again.');
            return;
        }
        
        // Show loading
        container.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i></div>';
        modal.classList.remove('hidden');
        
        // Load order items
        fetch(`get_order_items.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const orderIdInput = document.getElementById('editItemsOrderId');
                    if (orderIdInput) {
                        orderIdInput.value = orderId;
                    }
                    renderOrderItems(data.items);
                    updateOrderItemsTotal();
                } else {
                    container.innerHTML = '<div class="text-center py-8 text-red-600">Error loading order items: ' + (data.message || 'Unknown error') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error loading order items:', error);
                container.innerHTML = '<div class="text-center py-8 text-red-600">Error loading order items: ' + error.message + '</div>';
            });
    }
    
    function renderOrderItems(items) {
        const container = document.getElementById('orderItemsContainer');
        if (!container) {
            console.error('Order items container not found');
            return;
        }
        
        let html = '';
        
        items.forEach((item, index) => {
            html += `
                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg" data-item-id="${item.id}">
                    <img src="${item.image_path}" alt="${item.product_name}" class="w-16 h-16 object-cover rounded-lg">
                    <div class="flex-1">
                        <h5 class="font-semibold text-gray-900">${item.product_name}</h5>
                        <p class="text-sm text-gray-600">Color: ${item.color || 'N/A'}</p>
                        <p class="text-sm text-gray-600">Price: $${parseFloat(item.product_price).toFixed(2)}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                            <select name="items[${item.id}][size]" 
                                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                                <option value="XS" ${item.size === 'XS' ? 'selected' : ''}>XS</option>
                                <option value="S" ${item.size === 'S' ? 'selected' : ''}>S</option>
                                <option value="M" ${item.size === 'M' ? 'selected' : ''}>M</option>
                                <option value="L" ${item.size === 'L' ? 'selected' : ''}>L</option>
                                <option value="XL" ${item.size === 'XL' ? 'selected' : ''}>XL</option>
                                <option value="XXL" ${item.size === 'XXL' ? 'selected' : ''}>XXL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input type="number" name="items[${item.id}][quantity]" 
                                   value="${item.quantity}" min="1" max="99"
                                   class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                                   onchange="updateItemTotal(${item.id})">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                            <div class="w-24 px-3 py-2 bg-gray-100 rounded-lg text-sm font-medium text-gray-900">
                                $${(item.quantity * parseFloat(item.product_price)).toFixed(2)}
                            </div>
                        </div>
                        <div>
                            <button type="button" onclick="removeOrderItem(${item.id})" 
                                    class="text-red-600 hover:text-red-800 transition-colors p-2">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function updateItemTotal(itemId) {
        const itemContainer = document.querySelector(`[data-item-id="${itemId}"]`);
        if (!itemContainer) {
            console.error('Item container not found for ID:', itemId);
            return;
        }
        
        const quantityInput = itemContainer.querySelector('input[name*="[quantity]"]');
        const totalDiv = itemContainer.querySelector('.bg-gray-100');
        const priceElement = itemContainer.querySelector('p:last-child');
        
        if (!quantityInput || !totalDiv || !priceElement) {
            console.error('Required elements not found in item container');
            return;
        }
        
        const quantity = parseInt(quantityInput.value) || 0;
        const price = parseFloat(priceElement.textContent.replace('Price: $', '')) || 0;
        const total = quantity * price;
        
        totalDiv.textContent = `$${total.toFixed(2)}`;
        updateOrderItemsTotal();
    }
    
    function removeOrderItem(itemId) {
        if (confirm('Are you sure you want to remove this item from the order?')) {
            const itemContainer = document.querySelector(`[data-item-id="${itemId}"]`);
            if (!itemContainer) {
                console.error('Item container not found for removal:', itemId);
                return;
            }
            itemContainer.remove();
            
            // Add to deleted items hidden input
            const form = document.getElementById('editOrderItemsForm');
            if (!form) {
                console.error('Edit order items form not found');
                return;
            }
            
            let deletedInput = document.querySelector('input[name="deleted_items[]"]');
            if (!deletedInput) {
                deletedInput = document.createElement('input');
                deletedInput.type = 'hidden';
                deletedInput.name = 'deleted_items[]';
                form.appendChild(deletedInput);
            }
            
            const deletedItems = document.querySelectorAll('input[name="deleted_items[]"]');
            const lastDeletedInput = deletedItems[deletedItems.length - 1];
            lastDeletedInput.value = itemId;
            
            updateOrderItemsTotal();
        }
    }
    
    function updateOrderItemsTotal() {
        const itemContainers = document.querySelectorAll('[data-item-id]');
        let total = 0;
        
        itemContainers.forEach(container => {
            const quantityInput = container.querySelector('input[name*="[quantity]"]');
            const priceElement = container.querySelector('p:last-child');
            
            const quantity = parseInt(quantityInput?.value) || 0;
            const price = parseFloat(priceElement?.textContent.replace('Price: $', '')) || 0;
            total += quantity * price;
        });
        
        const totalElement = document.getElementById('orderItemsTotal');
        if (totalElement) {
            totalElement.textContent = `$${total.toFixed(2)}`;
        }
    }
    
    function closeEditItemsModal() {
        document.getElementById('editOrderItemsModal').classList.add('hidden');
    }
    
    // Form submission handler
    document.addEventListener('DOMContentLoaded', function() {
        const editOrderItemsForm = document.getElementById('editOrderItemsForm');
        if (editOrderItemsForm) {
            editOrderItemsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Debug: Log form data
                const formData = new FormData(this);
                console.log('Form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
                
                // Submit the form
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    console.log('Response received');
                    // Show success message
                    alert('Order items updated successfully!');
                    // Reload the page to show updated data
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Form submission error:', error);
                    alert('Error updating order items. Please try again.');
                });
            });
        }
    });
    
    // Product search functionality
    let searchTimeout;
    let selectedProduct = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('productSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    document.getElementById('searchResults').classList.add('hidden');
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    searchProducts(query);
                }, 300);
            });
        }
    });
    
    function searchProducts(query) {
        fetch(`search_products.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.products);
                } else {
                    console.error('Search error:', data.message);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }
    
    function displaySearchResults(products) {
        const resultsContainer = document.getElementById('searchResults');
        if (products.length === 0) {
            resultsContainer.innerHTML = '<p class="text-gray-500 p-2">No products found</p>';
            resultsContainer.style.display = 'block';
            resultsContainer.classList.remove('hidden');
            return;
        }
        let html = '';
        products.forEach(product => {
            html += `
                <div class="flex items-center space-x-3 p-2 hover:bg-gray-100 rounded cursor-pointer" 
                     onclick="selectProduct(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                    <img src="${product.image_path}" alt="${product.name}" class="w-12 h-12 object-cover rounded">
                    <div class="flex-1">
                        <h5 class="font-medium text-gray-900">${product.name}</h5>
                        <p class="text-sm text-gray-600">$${parseFloat(product.price).toFixed(2)}</p>
                    </div>
                </div>
            `;
        });
        resultsContainer.innerHTML = html;
        resultsContainer.style.display = 'block';
        resultsContainer.classList.remove('hidden');
    }
    
    function selectProduct(product) {
        selectedProduct = product;
        document.getElementById('searchResults').classList.add('hidden');
        document.getElementById('productSearch').value = product.name;
        showAddItemForm();
    }
    
    function showAddItemForm() {
        if (!selectedProduct) {
            alert('Please search and select a product first');
            return;
        }
        
        // Display selected product info
        const productInfo = document.getElementById('selectedProductInfo');
        productInfo.innerHTML = `
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded">
                <img src="${selectedProduct.image_path}" alt="${selectedProduct.name}" class="w-16 h-16 object-cover rounded">
                <div>
                    <h5 class="font-semibold text-gray-900">${selectedProduct.name}</h5>
                    <p class="text-sm text-gray-600">$${parseFloat(selectedProduct.price).toFixed(2)}</p>
                </div>
            </div>
        `;
        productInfo.classList.remove('hidden');
        
        // Populate size options
        const sizeSelect = document.getElementById('selectedSize');
        sizeSelect.innerHTML = '<option value="">Select Size</option>';
        
        if (selectedProduct.available_sizes && selectedProduct.available_sizes.length > 0) {
            selectedProduct.available_sizes.forEach(size => {
                sizeSelect.innerHTML += `<option value="${size}">${size}</option>`;
            });
        } else {
            // Default sizes if none available
            ['XS', 'S', 'M', 'L', 'XL', 'XXL'].forEach(size => {
                sizeSelect.innerHTML += `<option value="${size}">${size}</option>`;
            });
        }
        
        document.getElementById('addItemModal').classList.remove('hidden');
    }
    
    function closeAddItemModal() {
        document.getElementById('addItemModal').classList.add('hidden');
        selectedProduct = null;
        document.getElementById('selectedProductInfo').classList.add('hidden');
        document.getElementById('productSearch').value = '';
    }
    
    function addItemToOrder() {
        const size = document.getElementById('selectedSize').value;
        const quantity = parseInt(document.getElementById('selectedQuantity').value) || 1;
        
        if (!size) {
            alert('Please select a size');
            return;
        }
        
        if (!selectedProduct) {
            alert('No product selected');
            return;
        }
        
        // Create new item HTML
        const newItemId = 'new_' + Date.now();
        const container = document.getElementById('orderItemsContainer');
        const newItemHtml = `
            <div class="flex items-center space-x-4 p-4 bg-green-50 rounded-lg border-2 border-green-200" data-item-id="${newItemId}">
                <img src="${selectedProduct.image_path}" alt="${selectedProduct.name}" class="w-16 h-16 object-cover rounded-lg">
                <div class="flex-1">
                    <h5 class="font-semibold text-gray-900">${selectedProduct.name}</h5>
                    <p class="text-sm text-gray-600">Color: ${selectedProduct.color || 'N/A'}</p>
                    <p class="text-sm text-gray-600">Price: $${parseFloat(selectedProduct.price).toFixed(2)}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                        <select name="new_items[${newItemId}][size]" 
                                class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                            <option value="XS" ${size === 'XS' ? 'selected' : ''}>XS</option>
                            <option value="S" ${size === 'S' ? 'selected' : ''}>S</option>
                            <option value="M" ${size === 'M' ? 'selected' : ''}>M</option>
                            <option value="L" ${size === 'L' ? 'selected' : ''}>L</option>
                            <option value="XL" ${size === 'XL' ? 'selected' : ''}>XL</option>
                            <option value="XXL" ${size === 'XXL' ? 'selected' : ''}>XXL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" name="new_items[${newItemId}][quantity]" 
                               value="${quantity}" min="1" max="99"
                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"
                               onchange="updateItemTotal('${newItemId}')">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                        <div class="w-24 px-3 py-2 bg-gray-100 rounded-lg text-sm font-medium text-gray-900">
                            $${(quantity * parseFloat(selectedProduct.price)).toFixed(2)}
                        </div>
                    </div>
                    <div>
                        <button type="button" onclick="removeOrderItem('${newItemId}')" 
                                class="text-red-600 hover:text-red-800 transition-colors p-2">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" name="new_items[${newItemId}][product_id]" value="${selectedProduct.id}">
                <input type="hidden" name="new_items[${newItemId}][price]" value="${selectedProduct.price}">
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', newItemHtml);
        
        closeAddItemModal();
        updateOrderItemsTotal();
    }
    
    // Print invoice functionality
    function printInvoice(orderNumber) {
        const printWindow = window.open(`../invoice.php?order=${encodeURIComponent(orderNumber)}`, '_blank');
        if (printWindow) {
            printWindow.focus();
        } else {
            alert('Please allow pop-ups for this site to print the invoice.');
        }
    }
    
    // Send to delivery functionality
    function sendToDelivery(orderId) {
        if (!confirm('Are you sure you want to send this order to the delivery platform?')) {
            return;
        }
        
        // Create a form to submit the delivery request
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="send_to_delivery">
            <input type="hidden" name="order_id" value="${orderId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
    
    // Print selected orders
    function printSelectedOrders() {
        const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Please select orders to print.');
            return;
        }
        
        const orderNumbers = Array.from(selectedCheckboxes).map(checkbox => {
            const row = checkbox.closest('.order-row');
            const orderNumberCell = row.querySelector('td:nth-child(2) .text-sm.font-medium');
            return orderNumberCell.textContent.trim();
        });
        
        // Open each order in a new window for printing
        orderNumbers.forEach((orderNumber, index) => {
            setTimeout(() => {
                const printWindow = window.open(`../invoice.php?order=${encodeURIComponent(orderNumber)}`, `print_${index}`);
                if (printWindow) {
                    printWindow.focus();
                }
            }, index * 500); // Delay each window by 500ms
        });
    }
    
    // Export selected orders
    function exportSelectedOrders() {
        const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Please select orders to export.');
            return;
        }
        
        const orderIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
        
        // Create a form to submit the export request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'export_orders.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'export';
        form.appendChild(actionInput);
        
        orderIds.forEach(orderId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = orderId;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
    
    // Delete selected orders
    function deleteSelectedOrders() {
        const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Please select orders to delete.');
            return;
        }
        
        const orderIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
        
        // Show confirmation dialog
        const confirmMessage = `Are you sure you want to delete ${selectedCheckboxes.length} selected order(s)?\n\nThis action cannot be undone and will permanently remove the orders from the system.`;
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_orders';
        form.appendChild(actionInput);
        
        orderIds.forEach(orderId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = orderId;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
    
    // Close modals when clicking outside
    document.getElementById('orderModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeOrderModal();
        }
    });
    
    document.getElementById('editOrderModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });
    
            // Mobile sidebar functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('open');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('open');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) { // Only on mobile
                    const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
                    const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggle && sidebar && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('open');
                    }
                }
            });
        
        // Mobile filter toggle functionality
        const toggleFiltersBtn = document.getElementById('toggleFilters');
        const mobileFilters = document.getElementById('mobileFilters');
        const filterIcon = document.getElementById('filterIcon');
        
        if (toggleFiltersBtn && mobileFilters) {
            toggleFiltersBtn.addEventListener('click', function() {
                const isHidden = mobileFilters.classList.contains('hidden');
                
                if (isHidden) {
                    mobileFilters.classList.remove('hidden');
                    filterIcon.style.transform = 'rotate(180deg)';
                } else {
                    mobileFilters.classList.add('hidden');
                    filterIcon.style.transform = 'rotate(0deg)';
                }
            });
        }
        
        // Auto-generate tracking number when status changes to shipped
        const statusSelect = document.getElementById('editOrderStatus');
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                const trackingInput = document.getElementById('editTrackingNumber');
                if (this.value === 'shipped' && !trackingInput.value.trim()) {
                    generateTrackingNumber();
                }
            });
        }
    });
    
    // Auto-hide success messages
    setTimeout(function() {
        const successMessage = document.querySelector('.bg-green-50');
        if (successMessage) {
            successMessage.style.transition = 'opacity 0.5s ease';
            successMessage.style.opacity = '0';
            setTimeout(() => successMessage.remove(), 500);
        }
    }, 3000);
    
    document.addEventListener('click', function(e) {
        const searchInput = document.getElementById('productSearch');
        const resultsContainer = document.getElementById('searchResults');
        if (resultsContainer && !searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
            resultsContainer.classList.add('hidden');
        }
    });
    </script>
    <script>
    // Fast search by clicking customer name/email/phone
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.search-customer').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                const value = this.getAttribute('data-search');
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) {
                    searchInput.value = value;
                    // Find the filter form and submit it
                    const filterForm = searchInput.closest('form');
                    if (filterForm) {
                        filterForm.submit();
                    }
                }
            });
        });
    });
    </script>
</body>
</html> 