<?php
// Quick database connection test
header('Content-Type: text/html');

echo "<h1>Database Connection Test</h1>";

try {
    $dsn = "mysql:host=localhost;dbname=weultcom_beam;charset=utf8mb4";
    $pdo = new PDO($dsn, 'weultcom_beam', '@J(9yYER6#qIM53]');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ SUCCESS! Connected to database.</p>";
    
    // Test if we can query the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
    
    // Test cart_items table specifically
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM cart_items");
        $count = $stmt->fetchColumn();
        echo "<p style='color: green;'>✅ Cart items table exists with {$count} records</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Cart items table not found: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>✅ Database connection is working!</h2>";
    echo "<p>Your cart functionality should now work properly.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ FAILED: " . $e->getMessage() . "</p>";
}
?> 