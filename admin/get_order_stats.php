<?php
require_once '../includes/functions.php';

$pdo = getDBConnection();

// Get today's stats
$todayStmt = $pdo->query("
    SELECT 
        COUNT(*) as today_orders,
        SUM(total) as today_revenue
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
");
$todayStats = $todayStmt->fetch();

// Get status counts
$statusCountsStmt = $pdo->query("
    SELECT order_status, COUNT(*) as count 
    FROM orders 
    GROUP BY order_status
");
$statusCounts = $statusCountsStmt->fetchAll();

$statusCountsMap = [];
foreach ($statusCounts as $statusCount) {
    $statusCountsMap[$statusCount['order_status']] = $statusCount['count'];
}

// Get total orders count
$totalStmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $totalStmt->fetch()['total'];

$stats = [
    'today_orders' => $todayStats['today_orders'] ?? 0,
    'today_revenue' => $todayStats['today_revenue'] ?? 0,
    'pending_orders' => $statusCountsMap['pending'] ?? 0,
    'total_orders' => $totalOrders,
    'status_counts' => $statusCountsMap
];

header('Content-Type: application/json');
echo json_encode($stats);
?> 