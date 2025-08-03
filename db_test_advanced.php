<?php
// Advanced database connection test with passwords
header('Content-Type: text/html');

echo "<h1>Advanced Database Connection Test</h1>";

// Common hosting database configurations with passwords
$configs = [
    // Your hosting database with common passwords
    ['name' => 'weultcom_beam (no pass)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => ''],
    ['name' => 'weultcom_beam (pass: weultcom_beam)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => 'weultcom_beam'],
    ['name' => 'weultcom_beam (pass: beam)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => 'beam'],
    ['name' => 'weultcom_beam (pass: 123456)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => '123456'],
    ['name' => 'weultcom_beam (pass: password)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => 'password'],
    ['name' => 'weultcom_beam (pass: weult)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => 'weult'],
    
    // Root user with passwords
    ['name' => 'weultcom_beam root (no pass)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'root', 'pass' => ''],
    ['name' => 'weultcom_beam root (pass: root)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'root', 'pass' => 'root'],
    ['name' => 'weultcom_beam root (pass: 123456)', 'host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'root', 'pass' => '123456'],
    
    // Try different database names
    ['name' => 'weult_beam (pass: weult_beam)', 'host' => 'localhost', 'dbname' => 'weult_beam', 'user' => 'weult_beam', 'pass' => 'weult_beam'],
    ['name' => 'weult_beam (pass: beam)', 'host' => 'localhost', 'dbname' => 'weult_beam', 'user' => 'weult_beam', 'pass' => 'beam'],
    ['name' => 'weult_beam (pass: weult)', 'host' => 'localhost', 'dbname' => 'weult_beam', 'user' => 'weult_beam', 'pass' => 'weult'],
];

foreach ($configs as $config) {
    echo "<h3>Testing: {$config['name']}</h3>";
    echo "<p>Host: {$config['host']}, Database: {$config['dbname']}, User: {$config['user']}, Pass: " . (empty($config['pass']) ? 'empty' : '***') . "</p>";
    
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

echo "<h2>How to Find Your Database Password:</h2>";
echo "<p>1. Check your hosting control panel (cPanel, Plesk, etc.)</p>";
echo "<p>2. Look for 'MySQL Databases' or 'Database Management'</p>";
echo "<p>3. Check your hosting provider's welcome email</p>";
echo "<p>4. Contact your hosting provider for database credentials</p>";
echo "<p>5. Common passwords: database name, 'password', '123456', 'root'</p>";

echo "<h2>Next Steps:</h2>";
echo "<p>1. Look for the configuration that shows 'SUCCESS'</p>";
echo "<p>2. Update db_config.php with those credentials</p>";
echo "<p>3. Test cart_handler.php again</p>";
?> 