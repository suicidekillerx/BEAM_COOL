<?php
// Force no caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Direct database connection without includes
try {
    $pdo = new PDO("mysql:host=localhost;dbname=beam_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
    
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, col.name as collection_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN collections col ON p.collection_id = col.id 
                          WHERE p.id = ? AND p.is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
} catch (Exception $e) {
    $product = null;
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Isolated Product Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: white; }
        .debug { background: #ffeb3b; padding: 15px; margin: 15px 0; border: 2px solid #f57f17; }
        .product { border: 3px solid #2196f3; padding: 20px; margin: 20px 0; background: #e3f2fd; }
        .error { background: #ffcdd2; padding: 15px; margin: 15px 0; border: 2px solid #f44336; }
        .links { margin: 20px 0; }
        .links a { margin: 0 10px; padding: 10px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Isolated Product Test (No Includes)</h1>
    
    <div class="debug">
        <strong>🔧 DEBUG INFO:</strong><br>
        Requested ID: <?php echo htmlspecialchars($_GET['id'] ?? 'NOT SET'); ?><br>
        Processed ID: <?php echo $productId; ?><br>
        Product Name: <?php echo htmlspecialchars($product['name'] ?? 'NULL'); ?><br>
        Random: <?php echo rand(1, 10000); ?><br>
        Time: <?php echo microtime(true); ?>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="error">
        <strong>❌ Database Error:</strong><br>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($product): ?>
    <div class="product">
        <h2>✅ <?php echo htmlspecialchars($product['name']); ?></h2>
        <p><strong>Price:</strong> <?php echo number_format($product['price'], 3) . ' DTN'; ?></p>
        <p><strong>Sale Price:</strong> <?php echo $product['sale_price'] ? number_format($product['sale_price'], 3) . ' DTN' : 'N/A'; ?></p>
        <p><strong>Color:</strong> <?php echo htmlspecialchars($product['color']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></p>
        <p><strong>Collection:</strong> <?php echo htmlspecialchars($product['collection_name'] ?? 'N/A'); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
    </div>
    <?php else: ?>
    <div class="product">
        <h2>❌ Product Not Found</h2>
        <p>No product found with ID: <?php echo $productId; ?></p>
    </div>
    <?php endif; ?>
    
    <div class="links">
        <h3>🧪 Test Links:</h3>
        <a href="?id=1">Product 1</a>
        <a href="?id=5">Product 5</a>
        <a href="?id=8">Product 8</a>
        <a href="?id=10">Product 10</a>
    </div>
    
    <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
        <h3>📋 What to check:</h3>
        <ul>
            <li>Does the product name change when you click different links?</li>
            <li>Does the random number change on each page refresh?</li>
            <li>Are you seeing the correct product for each ID?</li>
        </ul>
    </div>
</body>
</html> 