<?php
// Test to identify the expires_at error
header('Content-Type: text/html');

echo "<h1>Promo Code Error Test</h1>";

try {
    $dsn = "mysql:host=localhost;dbname=weultcom_beam;charset=utf8mb4";
    $pdo = new PDO($dsn, 'weultcom_beam', '@J(9yYER6#qIM53]');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connected to database.</p>";
    
    // Test 1: Direct query without expires_at
    echo "<h3>Test 1: Direct query without expires_at</h3>";
    try {
        $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1");
        $stmt->execute(['WELCOME10']);
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($promo) {
            echo "<p style='color: green;'>✅ Found promo code: " . $promo['code'] . "</p>";
            echo "<p>Columns: " . implode(', ', array_keys($promo)) . "</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No promo code found</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    // Test 2: Query with expires_at (this should fail)
    echo "<h3>Test 2: Query with expires_at (should fail)</h3>";
    try {
        $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1 AND expires_at > NOW()");
        $stmt->execute(['WELCOME10']);
        $promo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($promo) {
            echo "<p style='color: green;'>✅ Found promo code with expires_at</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No promo code found with expires_at</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Expected error: " . $e->getMessage() . "</p>";
    }
    
    // Test 3: Check table structure
    echo "<h3>Test 3: Table structure</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE promo_codes");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Available columns: " . implode(', ', $columns) . "</p>";
        
        if (in_array('expires_at', $columns)) {
            echo "<p style='color: green;'>✅ expires_at column exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ expires_at column does not exist</p>";
        }
        
        if (in_array('end_date', $columns)) {
            echo "<p style='color: green;'>✅ end_date column exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ end_date column does not exist</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}
?> 