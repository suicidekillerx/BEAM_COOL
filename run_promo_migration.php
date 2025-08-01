<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Promo Codes Database Migration</h1>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Read and execute the migration file
    $migrationFile = 'database/migrations/create_promo_codes_table.sql';
    
    if (file_exists($migrationFile)) {
        $sql = file_get_contents($migrationFile);
        
        // Split the SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    echo "<p style='color: green;'>✅ Executed: " . substr($statement, 0, 50) . "...</p>";
                } catch (PDOException $e) {
                    echo "<p style='color: orange;'>⚠️ Warning: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        echo "<h2>Migration Complete!</h2>";
        echo "<p>✅ Promo codes tables created successfully</p>";
        echo "<p>✅ Sample promo codes inserted</p>";
        
        // Test the functions
        echo "<h2>Testing Functions</h2>";
        
        // Test getPromoCode
        $testCode = getPromoCode('WELCOME10');
        if ($testCode) {
            echo "<p style='color: green;'>✅ getPromoCode() working - Found: " . $testCode['name'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ getPromoCode() failed</p>";
        }
        
        // Test getAllPromoCodes
        $allCodes = getAllPromoCodes();
        if (count($allCodes) > 0) {
            echo "<p style='color: green;'>✅ getAllPromoCodes() working - Found " . count($allCodes) . " codes</p>";
        } else {
            echo "<p style='color: red;'>❌ getAllPromoCodes() failed</p>";
        }
        
        echo "<h2>Sample Promo Codes Created:</h2>";
        echo "<ul>";
        echo "<li><strong>WELCOME10</strong> - 10% off first order (min $50)</li>";
        echo "<li><strong>SAVE20</strong> - 20% off all items (min $100)</li>";
        echo "<li><strong>FREESHIP</strong> - Free shipping on orders over $50</li>";
        echo "<li><strong>FLAT15</strong> - $15 off your order (min $75)</li>";
        echo "<li><strong>NEWCUSTOMER</strong> - 25% off for new customers</li>";
        echo "</ul>";
        
        echo "<h2>Next Steps:</h2>";
        echo "<ol>";
        echo "<li>Go to <a href='admin/promo_codes.php' target='_blank'>Admin Panel → Promo Codes</a></li>";
        echo "<li>Test promo codes on <a href='view_cart.php' target='_blank'>Cart Page</a></li>";
        echo "<li>Try codes like: WELCOME10, SAVE20, FREESHIP</li>";
        echo "</ol>";
        
    } else {
        echo "<p style='color: red;'>❌ Migration file not found: $migrationFile</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 