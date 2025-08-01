<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Session and Cart Check</h1>";

echo "<h2>Session Info</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";

echo "<h2>Cart Items</h2>";
$cartItems = getCartItems();
echo "<p>Cart items count: " . count($cartItems) . "</p>";

if (count($cartItems) > 0) {
    echo "<h3>Cart Items:</h3>";
    foreach ($cartItems as $item) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<p><strong>Product:</strong> {$item['name']}</p>";
        echo "<p><strong>Size:</strong> {$item['size']}</p>";
        echo "<p><strong>Quantity:</strong> {$item['quantity']}</p>";
        echo "<p><strong>Price:</strong> {$item['price_formatted']}</p>";
        echo "<p><strong>Total:</strong> " . formatPrice($item['total_price']) . "</p>";
        echo "</div>";
    }
    
    $subtotal = array_sum(array_column($cartItems, 'total_price'));
    echo "<h3>Cart Summary</h3>";
    echo "<p><strong>Subtotal:</strong> " . formatPrice($subtotal) . "</p>";
    
    echo "<h2>Test Promo Codes</h2>";
    echo "<p>Try these codes:</p>";
    echo "<ul>";
    echo "<li><strong>TEST10</strong> - 10% off (no minimum)</li>";
    echo "<li><strong>WELCOME10</strong> - 10% off (min 50 DTN)</li>";
    echo "</ul>";
    
    echo "<p><a href='view_cart.php' target='_blank'>Go to Cart Page</a></p>";
    echo "<p><a href='test_ajax_promo.php' target='_blank'>Test AJAX Promo</a></p>";
    
} else {
    echo "<p style='color: red;'>❌ No items in cart</p>";
    echo "<p><a href='add_items_to_current_session.php'>Add Test Items</a></p>";
}

echo "<h2>Database Check</h2>";
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE session_id = ?");
    $stmt->execute([session_id()]);
    $dbCartItems = $stmt->fetchAll();
    
    echo "<p>Items in database for this session: " . count($dbCartItems) . "</p>";
    
    if (count($dbCartItems) > 0) {
        echo "<h3>Database Cart Items:</h3>";
        foreach ($dbCartItems as $item) {
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<p><strong>ID:</strong> {$item['id']}</p>";
            echo "<p><strong>Product ID:</strong> {$item['product_id']}</p>";
            echo "<p><strong>Size:</strong> {$item['size']}</p>";
            echo "<p><strong>Quantity:</strong> {$item['quantity']}</p>";
            echo "<p><strong>Session ID:</strong> {$item['session_id']}</p>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?> 