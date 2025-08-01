<?php
require_once 'includes/functions.php';

try {
    $pdo = getDBConnection();
    
    echo "<h1>Adding Discount Fields to Orders Table</h1>";
    
    // Check if fields already exist
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('discount', $columns)) {
        echo "<p>Adding discount column...</p>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN discount decimal(10,3) DEFAULT 0.000 AFTER shipping_cost");
        echo "<p style='color: green;'>✅ Discount column added successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Discount column already exists</p>";
    }
    
    if (!in_array('promo_code_id', $columns)) {
        echo "<p>Adding promo_code_id column...</p>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN promo_code_id int(11) DEFAULT NULL AFTER discount");
        echo "<p style='color: green;'>✅ Promo code ID column added successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Promo code ID column already exists</p>";
    }
    
    if (!in_array('promo_code', $columns)) {
        echo "<p>Adding promo_code column...</p>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN promo_code varchar(50) DEFAULT NULL AFTER promo_code_id");
        echo "<p style='color: green;'>✅ Promo code column added successfully</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Promo code column already exists</p>";
    }
    
    // Try to add foreign key constraint
    try {
        $pdo->exec("ALTER TABLE orders ADD CONSTRAINT fk_orders_promo_code FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE SET NULL ON UPDATE CASCADE");
        echo "<p style='color: green;'>✅ Foreign key constraint added successfully</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Foreign key constraint already exists or failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>✅ Database Update Complete!</h2>";
    echo "<p><a href='checkout.php'>Test Checkout with Promo Code</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 