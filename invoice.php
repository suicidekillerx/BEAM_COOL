<?php
session_start();
require_once 'includes/functions.php';

// Get order number from URL
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    die('Order not found');
}

// Get order details
$order = getOrder($orderNumber);

if (!$order) {
    die('Order not found');
}

// Get order items
$orderItems = getOrderItems($order['id']);

// Fetch shipping cost from settings (for display if needed)
$shippingCostSetting = (float)getSiteSetting('shipping_cost', 15.000);
$taxRate = (float)getSiteSetting('tax_rate', 0.19);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #000;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .customer-info, .order-info {
            flex: 1;
        }
        
        .order-info {
            text-align: right;
        }
        
        .info-section h3 {
            margin: 0 0 10px 0;
            color: #000;
            font-size: 16px;
        }
        
        .info-section p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #000;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        .items-table th:nth-child(2),
        .items-table th:nth-child(3),
        .items-table td:nth-child(2),
        .items-table td:nth-child(3) {
            text-align: center;
        }
        
        .items-table th:nth-child(4),
        .items-table th:nth-child(5),
        .items-table td:nth-child(4),
        .items-table td:nth-child(5) {
            text-align: right;
        }
        
        .totals-section {
            margin-left: auto;
            width: 300px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 8px;
            font-size: 14px;
        }
        
        .totals-table td:first-child {
            text-align: right;
        }
        
        .totals-table td:last-child {
            text-align: right;
        }
        
        .totals-table tr:last-child {
            border-top: 2px solid #000;
        }
        
        .totals-table tr:last-child td {
            padding: 12px 8px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        
        .footer p {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .invoice-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <i class="fas fa-print"></i> Print Invoice
    </button>
    
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1><?php echo getSiteSetting('brand_name', 'Beam'); ?></h1>
            <p>INVOICE</p>
            <p>Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
            <p>Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
        </div>
        
        <!-- Customer & Order Info -->
        <div class="info-section">
            <div class="customer-info">
                <h3>Bill To:</h3>
                <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
                <p><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                <p><?php echo htmlspecialchars($order['shipping_city']); ?><?php echo !empty($order['shipping_postal_code']) ? ', ' . htmlspecialchars($order['shipping_postal_code']) : ''; ?></p>
            </div>
            <div class="order-info">
                <h3>Order Details:</h3>
                <p><strong>Order #:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['order_status']); ?></p>
                <p><strong>Payment:</strong> Cash on Delivery</p>
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Size</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                        <?php if ($item['color']): ?>
                        <br><span style="color: #666; font-size: 12px;">Color: <?php echo htmlspecialchars($item['color']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['size']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo formatPrice($item['product_sale_price'] ?: $item['product_price']); ?></td>
                    <td><strong><?php echo formatPrice($item['total_price']); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td><?php echo formatPrice($order['subtotal']); ?></td>
                </tr>
                <tr>
                    <td>Tax (<?php echo ($taxRate * 100); ?>%):</td>
                    <td><?php echo formatPrice($order['tax']); ?></td>
                </tr>
                <tr>
                    <td>Shipping:</td>
                    <td><?php echo $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'FREE'; ?></td>
                </tr>
                <tr>
                    <td>Total:</td>
                    <td><?php echo formatPrice($order['total']); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your order!</p>
            <p>Payment: Cash on Delivery</p>
            <p>For questions, contact: support@beam.com | +216 12 345 678</p>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Small delay to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Close window after printing (optional)
        window.onafterprint = function() {
            // Uncomment the line below if you want the window to close automatically after printing
            // window.close();
        };
    </script>
</body>
</html> 