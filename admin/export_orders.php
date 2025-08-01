<?php
session_start();
require_once '../includes/functions.php';

// Check if admin is logged in (you can add proper authentication later)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'export') {
    header('Location: orders.php');
    exit;
}

$orderIds = $_POST['order_ids'] ?? [];

if (empty($orderIds)) {
    header('Location: orders.php');
    exit;
}

$pdo = getDBConnection();

// Get orders data
$placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
$stmt = $pdo->prepare("
    SELECT 
        o.order_number,
        o.customer_name,
        o.customer_email,
        o.customer_phone,
        o.shipping_address,
        o.shipping_city,
        o.shipping_postal_code,
        o.subtotal,
        o.tax,
        o.shipping_cost,
        o.total,
        o.order_status,
        o.payment_method,
        o.tracking_number,
        o.notes,
        o.admin_notes,
        o.created_at,
        o.updated_at,
        GROUP_CONCAT(
            CONCAT(oi.product_name, ' (', oi.size, ') x', oi.quantity, ' - ', oi.total_price, ' DTN')
            SEPARATOR '; '
        ) as order_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id IN ($placeholders)
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

$stmt->execute($orderIds);
$orders = $stmt->fetchAll();

// Set headers for CSV download
$filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// CSV Headers
$headers = [
    'Order Number',
    'Customer Name',
    'Customer Email',
    'Customer Phone',
    'Shipping Address',
    'City',
    'Postal Code',
    'Subtotal (DTN)',
    'Tax (DTN)',
    'Shipping Cost (DTN)',
    'Total (DTN)',
    'Order Status',
    'Payment Method',
    'Tracking Number',
    'Customer Notes',
    'Admin Notes',
    'Order Items',
    'Order Date',
    'Last Updated'
];

fputcsv($output, $headers);

// Add data rows
foreach ($orders as $order) {
    $row = [
        $order['order_number'],
        $order['customer_name'],
        $order['customer_email'],
        $order['customer_phone'],
        str_replace(["\r", "\n"], ' ', $order['shipping_address']), // Remove line breaks
        $order['shipping_city'],
        $order['shipping_postal_code'],
        $order['subtotal'],
        $order['tax'],
        $order['shipping_cost'],
        $order['total'],
        ucfirst($order['order_status']),
        ucfirst(str_replace('_', ' ', $order['payment_method'])),
        $order['tracking_number'] ?? '',
        str_replace(["\r", "\n"], ' ', $order['notes'] ?? ''), // Remove line breaks
        str_replace(["\r", "\n"], ' ', $order['admin_notes'] ?? ''), // Remove line breaks
        $order['order_items'] ?? '',
        date('Y-m-d H:i:s', strtotime($order['created_at'])),
        date('Y-m-d H:i:s', strtotime($order['updated_at']))
    ];
    
    fputcsv($output, $row);
}

fclose($output);
exit;
?> 