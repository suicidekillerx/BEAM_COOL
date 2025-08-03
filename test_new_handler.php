<?php
// Test the new cart handler
header('Content-Type: text/html');

echo "<h1>Testing New Cart Handler</h1>";

// Test database connection
try {
    $dsn = "mysql:host=localhost;dbname=weultcom_beam;charset=utf8mb4";
    $pdo = new PDO($dsn, 'weultcom_beam', '@J(9yYER6#qIM53]');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Test promo code query
    $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1");
    $stmt->execute(['WELCOME10']);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($promo) {
        echo "<p style='color: green;'>✅ Found promo code: " . $promo['code'] . "</p>";
        echo "<p>Promo code details: " . json_encode($promo, JSON_PRETTY_PRINT) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No promo code found</p>";
    }
    
    echo "<h2>Testing New Handler</h2>";
    echo "<p>1. Upload <code>cart_handler_new.php</code> to your server</p>";
    echo "<p>2. Update <code>view_cart.php</code> to use <code>cart_handler_new.php</code></p>";
    echo "<p>3. Test the cart functionality</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 