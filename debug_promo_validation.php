<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Promo Code Validation Debug</h1>";

// Test with a known promo code
$testCode = 'TEST10';
echo "<h2>Testing Promo Code: $testCode</h2>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Step 1: Check if promo code exists
    echo "<h3>Step 1: Check if promo code exists</h3>";
    $promoCode = getPromoCode($testCode);
    if ($promoCode) {
        echo "<p style='color: green;'>✅ Found promo code: {$promoCode['name']}</p>";
        echo "<pre>" . print_r($promoCode, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Promo code not found</p>";
        
        // List all available promo codes
        echo "<h3>Available Promo Codes:</h3>";
        $allCodes = getAllPromoCodes();
        foreach ($allCodes as $code) {
            echo "<p><strong>{$code['code']}</strong> - {$code['name']} (Active: " . ($code['is_active'] ? 'Yes' : 'No') . ")</p>";
        }
        exit;
    }
    
    // Step 2: Check cart items
    echo "<h3>Step 2: Check cart items</h3>";
    $cartItems = getCartItems();
    echo "<p>Cart items count: " . count($cartItems) . "</p>";
    
    if (count($cartItems) > 0) {
        echo "<p style='color: green;'>✅ Cart has items</p>";
        foreach ($cartItems as $item) {
            echo "<p>- {$item['name']} (Qty: {$item['quantity']}, Price: {$item['price_formatted']})</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Cart is empty</p>";
    }
    
    // Step 3: Calculate subtotal
    echo "<h3>Step 3: Calculate subtotal</h3>";
    $subtotal = array_sum(array_column($cartItems, 'total_price'));
    echo "<p>Subtotal: " . formatPrice($subtotal) . "</p>";
    
    // Step 4: Test validation
    echo "<h3>Step 4: Test validation</h3>";
    $validation = validatePromoCode($testCode, $cartItems, $subtotal);
    echo "<p>Validation result:</p>";
    echo "<pre>" . print_r($validation, true) . "</pre>";
    
    // Step 5: Test apply
    echo "<h3>Step 5: Test apply</h3>";
    $result = applyPromoCode($testCode, $cartItems);
    echo "<p>Apply result:</p>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    // Step 6: Check applied promo code
    echo "<h3>Step 6: Check applied promo code</h3>";
    $applied = getAppliedPromoCode();
    echo "<p>Applied promo code:</p>";
    echo "<pre>" . print_r($applied, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 