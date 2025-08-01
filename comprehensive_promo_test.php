<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>🔍 Comprehensive Promo Code System Test</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check Promo Codes Table
echo "<h2>2. Promo Codes Table Test</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM promo_codes");
    $result = $stmt->fetch();
    echo "<p>Total promo codes in database: " . $result['count'] . "</p>";
    
    if ($result['count'] > 0) {
        echo "<p style='color: green;'>✅ Promo codes table has data</p>";
        
        // List all promo codes
        $stmt = $pdo->query("SELECT * FROM promo_codes ORDER BY id");
        $promoCodes = $stmt->fetchAll();
        
        echo "<h3>Available Promo Codes:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Type</th><th>Value</th><th>Min Order</th><th>Active</th></tr>";
        foreach ($promoCodes as $code) {
            echo "<tr>";
            echo "<td>{$code['id']}</td>";
            echo "<td><strong>{$code['code']}</strong></td>";
            echo "<td>{$code['name']}</td>";
            echo "<td>{$code['type']}</td>";
            echo "<td>{$code['value']}</td>";
            echo "<td>{$code['min_order_amount']}</td>";
            echo "<td>" . ($code['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No promo codes found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking promo codes table: " . $e->getMessage() . "</p>";
}

// Test 3: Function Tests
echo "<h2>3. Function Tests</h2>";

// Test getPromoCode function
echo "<h3>3.1 getPromoCode Function</h3>";
$testCodes = ['TEST10', 'WELCOME10', 'INVALID'];
foreach ($testCodes as $code) {
    $promoCode = getPromoCode($code);
    if ($promoCode) {
        echo "<p style='color: green;'>✅ Found promo code: {$code} - {$promoCode['name']}</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Not found: {$code}</p>";
    }
}

// Test getAllPromoCodes function
echo "<h3>3.2 getAllPromoCodes Function</h3>";
$allCodes = getAllPromoCodes();
echo "<p>Total codes returned by function: " . count($allCodes) . "</p>";

// Test 4: Validation Tests
echo "<h2>4. Validation Tests</h2>";

// Create test cart items
$testCartItems = [
    [
        'id' => 1,
        'product_id' => 1,
        'name' => 'Test Product 1',
        'size' => 'M',
        'quantity' => 2,
        'price' => 25.00,
        'price_formatted' => '25.00 DTN',
        'total_price' => 50.00
    ],
    [
        'id' => 2,
        'product_id' => 2,
        'name' => 'Test Product 2',
        'size' => 'L',
        'quantity' => 1,
        'price' => 30.00,
        'price_formatted' => '30.00 DTN',
        'total_price' => 30.00
    ]
];

$testSubtotal = array_sum(array_column($testCartItems, 'total_price')); // 80.00

echo "<h3>4.1 Validation with Test Cart (Subtotal: " . formatPrice($testSubtotal) . ")</h3>";

$testCodes = ['TEST10', 'WELCOME10', 'SAVE20', 'FLAT15', 'FREESHIP'];
foreach ($testCodes as $code) {
    $validation = validatePromoCode($code, $testCartItems, $testSubtotal);
    if ($validation['valid']) {
        echo "<p style='color: green;'>✅ {$code} - Valid</p>";
        if (isset($validation['promo_code'])) {
            echo "<p style='margin-left: 20px;'>Name: {$validation['promo_code']['name']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ {$code} - Invalid: {$validation['message']}</p>";
    }
}

// Test 5: Discount Calculation Tests
echo "<h2>5. Discount Calculation Tests</h2>";

foreach ($testCodes as $code) {
    $promoCode = getPromoCode($code);
    if ($promoCode) {
        $discount = calculateDiscount($promoCode, $testSubtotal);
        echo "<p><strong>{$code}</strong> - Discount: " . formatPrice($discount) . "</p>";
    }
}

// Test 6: Session Management Tests
echo "<h2>6. Session Management Tests</h2>";

// Clear any existing applied promo code
unset($_SESSION['applied_promo_code']);

// Test applyPromoCode
echo "<h3>6.1 Apply Promo Code Test</h3>";
$result = applyPromoCode('TEST10', $testCartItems);
if ($result['valid']) {
    echo "<p style='color: green;'>✅ Successfully applied TEST10</p>";
    echo "<p>Discount: " . formatPrice($result['discount_amount']) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to apply TEST10: {$result['message']}</p>";
}

// Test getAppliedPromoCode
echo "<h3>6.2 Get Applied Promo Code Test</h3>";
$applied = getAppliedPromoCode();
if ($applied) {
    echo "<p style='color: green;'>✅ Applied promo code found: {$applied['code']}</p>";
    echo "<p>Discount: " . formatPrice($applied['discount_amount']) . "</p>";
} else {
    echo "<p style='color: red;'>❌ No applied promo code found</p>";
}

// Test removePromoCode
echo "<h3>6.3 Remove Promo Code Test</h3>";
$removeResult = removePromoCode();
if ($removeResult) {
    echo "<p style='color: green;'>✅ Successfully removed promo code</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to remove promo code</p>";
}

// Test 7: AJAX Handler Test
echo "<h2>7. AJAX Handler Test</h2>";

// Simulate AJAX request
$_POST['action'] = 'apply_promo_code';
$_POST['code'] = 'TEST10';

// Capture output
ob_start();
include 'ajax_handler.php';
$ajaxOutput = ob_get_clean();

echo "<h3>7.1 AJAX Response</h3>";
echo "<pre>" . htmlspecialchars($ajaxOutput) . "</pre>";

// Test 8: Cart Integration Test
echo "<h2>8. Cart Integration Test</h2>";

// Check if cart functions exist
if (function_exists('getCartItems')) {
    echo "<p style='color: green;'>✅ getCartItems function exists</p>";
    
    $cartItems = getCartItems();
    echo "<p>Current cart items: " . count($cartItems) . "</p>";
    
    if (count($cartItems) > 0) {
        echo "<h3>Current Cart Items:</h3>";
        foreach ($cartItems as $item) {
            echo "<p>- {$item['name']} (Qty: {$item['quantity']}, Price: {$item['price_formatted']})</p>";
        }
        
        $cartSubtotal = array_sum(array_column($cartItems, 'total_price'));
        echo "<p><strong>Cart Subtotal: " . formatPrice($cartSubtotal) . "</strong></p>";
        
        // Test promo code with real cart
        echo "<h3>Promo Code Test with Real Cart:</h3>";
        $validation = validatePromoCode('TEST10', $cartItems, $cartSubtotal);
        if ($validation['valid']) {
            echo "<p style='color: green;'>✅ TEST10 is valid for current cart</p>";
        } else {
            echo "<p style='color: red;'>❌ TEST10 is invalid for current cart: {$validation['message']}</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Cart is empty - add items to test promo codes</p>";
        echo "<p><a href='add_items_to_current_session.php'>Add Test Items</a></p>";
    }
} else {
    echo "<p style='color: red;'>❌ getCartItems function not found</p>";
}

// Test 9: Error Log Check
echo "<h2>9. Error Log Check</h2>";
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    echo "<p>Error log file: {$logFile}</p>";
    $recentLogs = file_get_contents($logFile);
    if (strpos($recentLogs, 'promo') !== false) {
        echo "<p style='color: orange;'>⚠️ Found promo-related errors in log</p>";
        echo "<pre>" . htmlspecialchars(substr($recentLogs, -1000)) . "</pre>";
    } else {
        echo "<p style='color: green;'>✅ No promo-related errors in recent logs</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Error log not accessible</p>";
}

echo "<h2>🎯 Summary</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If cart is empty, add items using the link above</li>";
echo "<li>Test promo codes on the cart page</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Verify AJAX requests are reaching the server</li>";
echo "</ol>";

echo "<p><a href='view_cart.php' target='_blank'>Go to Cart Page</a></p>";
echo "<p><a href='test_ajax_promo.php' target='_blank'>Test AJAX Promo</a></p>";
?> 