<?php
require_once 'includes/functions.php';

echo "<h1>Promo Code Test</h1>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Test if promo_codes table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'promo_codes'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✅ promo_codes table exists</p>";
    } else {
        echo "<p style='color: red;'>❌ promo_codes table does not exist</p>";
    }
    
    // Test if promo_code_usage table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'promo_code_usage'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✅ promo_code_usage table exists</p>";
    } else {
        echo "<p style='color: red;'>❌ promo_code_usage table does not exist</p>";
    }
    
    // Test getPromoCode function
    echo "<h2>Testing getPromoCode()</h2>";
    $testCode = getPromoCode('WELCOME10');
    if ($testCode) {
        echo "<p style='color: green;'>✅ getPromoCode('WELCOME10') working - Found: " . $testCode['name'] . "</p>";
        echo "<pre>" . print_r($testCode, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ getPromoCode('WELCOME10') failed</p>";
    }
    
    // Test getAllPromoCodes function
    echo "<h2>Testing getAllPromoCodes()</h2>";
    $allCodes = getAllPromoCodes();
    if (count($allCodes) > 0) {
        echo "<p style='color: green;'>✅ getAllPromoCodes() working - Found " . count($allCodes) . " codes</p>";
        foreach ($allCodes as $code) {
            echo "<p><strong>{$code['code']}</strong> - {$code['name']} ({$code['type']})</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ getAllPromoCodes() failed</p>";
    }
    
    // Test validatePromoCode function
    echo "<h2>Testing validatePromoCode()</h2>";
    $cartItems = [
        [
            'product_id' => 1,
            'category_id' => 1,
            'total_price' => 100.00
        ]
    ];
    $validation = validatePromoCode('WELCOME10', $cartItems, 100.00);
    echo "<p>Validation result for WELCOME10:</p>";
    echo "<pre>" . print_r($validation, true) . "</pre>";
    
    // Test applyPromoCode function
    echo "<h2>Testing applyPromoCode()</h2>";
    $result = applyPromoCode('WELCOME10', $cartItems);
    echo "<p>Apply result for WELCOME10:</p>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    // Test getAppliedPromoCode function
    echo "<h2>Testing getAppliedPromoCode()</h2>";
    $applied = getAppliedPromoCode();
    echo "<p>Applied promo code:</p>";
    echo "<pre>" . print_r($applied, true) . "</pre>";
    
    // Test removePromoCode function
    echo "<h2>Testing removePromoCode()</h2>";
    $removeResult = removePromoCode();
    echo "<p>Remove result:</p>";
    echo "<pre>" . print_r($removeResult, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 