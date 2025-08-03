<?php
// Database connection test
header('Content-Type: text/html');

echo "<h1>Database Connection Test</h1>";

// Common hosting database configurations to test
$configs = [
    ['name' => 'Your Hosting DB (from SQL)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => ''],
    ['name' => 'Your Hosting DB (root)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'root', 'pass' => ''],
    ['name' => 'Local XAMPP', 'host' => 'localhost', 'dbname' => 'beam_ecommerce', 'user' => 'root', 'pass' => ''],
    ['name' => 'Hosting - weult_beam', 'host' => 'localhost', 'dbname' => 'weult_beam', 'user' => 'root', 'pass' => ''],
    ['name' => 'Hosting - weult_beam user', 'host' => 'localhost', 'dbname' => 'weult_beam', 'user' => 'weult_beam', 'pass' => ''],
    ['name' => 'Hosting - beam_ecommerce', 'host' => 'localhost', 'dbname' => 'beam_ecommerce', 'user' => 'weult_beam', 'pass' => ''],
    ['name' => 'Hosting - weult_beam_ecommerce', 'host' => 'localhost', 'dbname' => 'weult_beam_ecommerce', 'user' => 'root', 'pass' => ''],
    ['name' => 'Hosting - weult_beam_ecommerce user', 'host' => 'localhost', 'dbname' => 'weult_beam_ecommerce', 'user' => 'weult_beam', 'pass' => ''],
];

foreach ($configs as $config) {
    echo "<h3>Testing: {$config['name']}</h3>";
    echo "<p>Host: {$config['host']}, Database: {$config['dbname']}, User: {$config['user']}</p>";
    
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test if we can query the database
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p style='color: green;'>✅ SUCCESS! Connected to database.</p>";
        echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
        
        // Test cart_items table specifically
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM cart_items");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✅ Cart items table exists with {$count} records</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Cart items table not found: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ FAILED: " . $e->getMessage() . "</p>";
        echo "<hr>";
    }
}

echo "<h2>Next Steps:</h2>";
echo "<p>1. Look for the configuration that shows 'SUCCESS'</p>";
echo "<p>2. Update db_config.php with those credentials</p>";
echo "<p>3. Test cart_handler.php again</p>";
?> 