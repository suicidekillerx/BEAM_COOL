<?php
// Test promo code AJAX functionality
header('Content-Type: text/html');

echo "<h1>Promo Code AJAX Test</h1>";

// Simulate the AJAX request
$_POST['action'] = 'apply_promo_code';
$_POST['code'] = 'WELCOME10';

// Start session
session_start();

try {
    // Include functions
    require_once 'includes/functions.php';
    
    echo "<p style='color: green;'>✅ Functions loaded successfully.</p>";
    
    // Test the applyPromoCode function directly
    echo "<h3>Testing applyPromoCode function</h3>";
    
    // Get cart items (empty for test)
    $cartItems = [];
    
    $result = applyPromoCode('WELCOME10', $cartItems);
    
    echo "<p>Result: " . json_encode($result, JSON_PRETTY_PRINT) . "</p>";
    
    if ($result['valid']) {
        echo "<p style='color: green;'>✅ Promo code applied successfully!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Promo code validation failed: " . $result['message'] . "</p>";
    }
    
    // Test database connection
    echo "<h3>Testing database connection</h3>";
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful.</p>";
    
    // Test promo code query
    echo "<h3>Testing promo code query</h3>";
    $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1");
    $stmt->execute(['WELCOME10']);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($promo) {
        echo "<p style='color: green;'>✅ Found promo code: " . $promo['code'] . "</p>";
        echo "<p>Promo code details: " . json_encode($promo, JSON_PRETTY_PRINT) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No promo code found</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error trace: " . $e->getTraceAsString() . "</p>";
}
?> 