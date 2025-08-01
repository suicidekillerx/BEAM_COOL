<?php
session_start();
require_once '../includes/functions.php';

// Direct print without login check

// Get all products with stock information
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.sale_price, p.is_active, p.show_stock, p.stock_status,
           c.name as category_name, col.name as collection_name
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN collections col ON p.collection_id = col.id 
    ORDER BY p.name ASC
");
$stmt->execute();
$products = $stmt->fetchAll();

// Get stock information for each product
foreach ($products as &$product) {
    $stmt = $pdo->prepare("SELECT size, stock_quantity FROM product_sizes WHERE product_id = ? ORDER BY FIELD(size, 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL')");
    $stmt->execute([$product['id']]);
    $product['sizes'] = $stmt->fetchAll();
    
    // Calculate total stock
    $product['total_stock'] = array_sum(array_column($product['sizes'], 'stock_quantity'));
}
unset($product); // Break the reference

// Get current date and time for the report
$reportDate = date('F j, Y, g:i a');
$totalProducts = count($products);

// Calculate total stock, low stock, and out of stock counts
$totalStock = 0;
$lowStockCount = 0;
$outOfStockCount = 0;
$lowStockThreshold = 5; // Define what "low stock" means

foreach ($products as $product) {
    $totalStock += $product['total_stock'];
    
    if ($product['total_stock'] == 0) {
        $outOfStockCount++;
    } elseif ($product['total_stock'] <= $lowStockThreshold) {
        $lowStockCount++;
    }
}

// Set the content type to HTML
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Report - <?php echo date('Y-m-d'); ?></title>
    <style>
        @media print {
            @page {
                size: portrait;
                margin: 0.5in;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #f9f9f9;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .print-only {
                display: block;
            }
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .header h1 {
            margin: 0;
            color: #000;
            font-size: 28px;
        }
        
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 14px;
        }
        
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .summary-card {
            flex: 1;
            min-width: 200px;
            margin: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .summary-card h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #666;
        }
        
        .summary-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            position: sticky;
            top: 0;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .stock-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .in-stock {
            background-color: #d4edda;
            color: #155724;
        }
        
        .low-stock {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .size-details {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            color: #666;
            font-size: 12px;
        }
        
        .actions {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #000;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 0 5px;
            cursor: pointer;
            border: none;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .category-section {
            margin-bottom: 40px;
        }
        
        .category-header {
            background: #f0f0f0;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="actions no-print">
            <button onclick="window.print()" class="btn">Print Report</button>
            <a href="inventory.php" class="btn btn-secondary">Back to Inventory</a>
        </div>
        
        <div class="header">
            <h1>Stock Inventory Report</h1>
            <p>Generated on: <?php echo $reportDate; ?></p>
        </div>
        
        <div class="summary">
            <div class="summary-card">
                <h3>Total Products</h3>
                <p><?php echo $totalProducts; ?></p>
            </div>
            
            <div class="summary-card">
                <h3>Total Stock</h3>
                <p><?php echo $totalStock; ?> units</p>
            </div>
            
            <div class="summary-card">
                <h3>Low Stock Products</h3>
                <p><?php echo $lowStockCount; ?></p>
            </div>
            
            <div class="summary-card">
                <h3>Out of Stock Products</h3>
                <p><?php echo $outOfStockCount; ?></p>
            </div>
        </div>
        
        <!-- Stock Status Summary -->
        <h2>Stock Status Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <span class="stock-status in-stock">In Stock</span>
                    </td>
                    <td><?php echo ($totalProducts - $lowStockCount - $outOfStockCount); ?></td>
                    <td><?php echo round((($totalProducts - $lowStockCount - $outOfStockCount) / $totalProducts) * 100, 1); ?>%</td>
                </tr>
                <tr>
                    <td>
                        <span class="stock-status low-stock">Low Stock</span>
                    </td>
                    <td><?php echo $lowStockCount; ?></td>
                    <td><?php echo round(($lowStockCount / $totalProducts) * 100, 1); ?>%</td>
                </tr>
                <tr>
                    <td>
                        <span class="stock-status out-of-stock">Out of Stock</span>
                    </td>
                    <td><?php echo $outOfStockCount; ?></td>
                    <td><?php echo round(($outOfStockCount / $totalProducts) * 100, 1); ?>%</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Detailed Product Inventory -->
        <h2>Detailed Product Inventory</h2>
        
        <?php
        // Group products by category
        $productsByCategory = [];
        foreach ($products as $product) {
            $category = $product['category_name'] ?? 'Uncategorized';
            if (!isset($productsByCategory[$category])) {
                $productsByCategory[$category] = [];
            }
            $productsByCategory[$category][] = $product;
        }
        
        // Sort categories alphabetically
        ksort($productsByCategory);
        
        foreach ($productsByCategory as $category => $categoryProducts):
        ?>
        <div class="category-section">
            <div class="category-header"><?php echo htmlspecialchars($category); ?></div>
            
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Collection</th>
                        <th>Price</th>
                        <th>Total Stock</th>
                        <th>Size Breakdown</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoryProducts as $product): ?>
                    <tr>
                        <td>
                            <div><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="text-sm text-gray-500">ID: <?php echo $product['id']; ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($product['collection_name'] ?? 'None'); ?></td>
                        <td>
                            <?php if ($product['sale_price']): ?>
                            <div><?php echo formatPrice($product['sale_price']); ?></div>
                            <div style="text-decoration: line-through; color: #999;"><?php echo formatPrice($product['price']); ?></div>
                            <?php else: ?>
                            <div><?php echo formatPrice($product['price']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo $product['total_stock']; ?> units</strong>
                        </td>
                        <td>
                            <div class="size-details">
                                <?php 
                                $sizeInfo = [];
                                foreach ($product['sizes'] as $size) {
                                    $sizeInfo[] = $size['size'] . ': ' . $size['stock_quantity'];
                                }
                                echo implode(', ', $sizeInfo);
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($product['total_stock'] == 0): ?>
                            <span class="stock-status out-of-stock">Out of Stock</span>
                            <?php elseif ($product['total_stock'] <= 5): ?>
                            <span class="stock-status low-stock">Low Stock</span>
                            <?php else: ?>
                            <span class="stock-status in-stock">In Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
        
        <div class="footer">
            <p>This report was generated from the Beam Admin Panel on <?php echo $reportDate; ?>.</p>
            <p class="print-only">© <?php echo date('Y'); ?> Beam Clothing. All rights reserved.</p>
        </div>
    </div>
</body>
</html>