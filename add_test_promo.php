<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Add Test Promo Code</h1>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Add a test promo code with no minimum order
    $testPromoData = [
        'code' => 'TEST10',
        'name' => 'Test Discount',
        'description' => '10% off for testing (no minimum)',
        'type' => 'percentage',
        'value' => 10.000,
        'min_order_amount' => 0.000,
        'max_discount' => 50.000,
        'usage_limit' => 1000,
        'user_limit' => null,
        'applies_to' => 'all',
        'category_ids' => null,
        'product_ids' => null,
        'excluded_categories' => null,
        'excluded_products' => null,
        'start_date' => null,
        'end_date' => null,
        'is_active' => 1,
        'is_first_time_only' => 0,
        'is_single_use' => 0
    ];
    
    if (createPromoCode($testPromoData)) {
        echo "<p style='color: green;'>✅ Test promo code TEST10 created successfully</p>";
        echo "<p><strong>TEST10</strong> - 10% off with no minimum order amount</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create test promo code</p>";
    }
    
    echo "<h2>Available Test Codes</h2>";
    echo "<ul>";
    echo "<li><strong>TEST10</strong> - 10% off (no minimum)</li>";
    echo "<li><strong>WELCOME10</strong> - 10% off (min 50 DTN)</li>";
    echo "<li><strong>SAVE20</strong> - 20% off (min 100 DTN)</li>";
    echo "<li><strong>FREESHIP</strong> - Free shipping (min 50 DTN)</li>";
    echo "<li><strong>FLAT15</strong> - $15 off (min 75 DTN)</li>";
    echo "</ul>";
    
    echo "<h2>Test Steps</h2>";
    echo "<ol>";
    echo "<li><a href='view_cart.php' target='_blank'>Go to Cart Page</a></li>";
    echo "<li>Try <strong>TEST10</strong> first (should work with any cart)</li>";
    echo "<li>Then try <strong>WELCOME10</strong> (should work with current cart)</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 