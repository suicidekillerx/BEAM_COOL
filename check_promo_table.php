<?php
// Check promo_codes table structure
header('Content-Type: text/html');

echo "<h1>Promo Codes Table Structure</h1>";

try {
    $dsn = "mysql:host=localhost;dbname=weultcom_beam;charset=utf8mb4";
    $pdo = new PDO($dsn, 'weultcom_beam', '@J(9yYER6#qIM53]');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connected to database.</p>";
    
    // Check if promo_codes table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'promo_codes'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ promo_codes table exists</p>";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE promo_codes");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data
        $stmt = $pdo->query("SELECT * FROM promo_codes LIMIT 5");
        $promoCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Sample Data:</h3>";
        if (count($promoCodes) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr>";
            foreach (array_keys($promoCodes[0]) as $header) {
                echo "<th>{$header}</th>";
            }
            echo "</tr>";
            
            foreach ($promoCodes as $promo) {
                echo "<tr>";
                foreach ($promo as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No promo codes found in table.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ promo_codes table does not exist</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}
?> 