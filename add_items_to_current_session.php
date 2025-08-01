<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Add Items to Current Session Cart</h1>";
echo "<p>Current Session ID: " . session_id() . "</p>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Get some products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 LIMIT 2");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    if (count($products) > 0) {
        echo "<p>Found " . count($products) . " products</p>";
        
        foreach ($products as $product) {
            echo "<p>Adding product: {$product['name']} (ID: {$product['id']})</p>";
            
            // Add to cart using the current session
            $result = addToCart($product['id'], 'M', 2); // Add 2 items of size M
            
            if ($result) {
                echo "<p style='color: green;'>✅ Added {$product['name']} to cart</p>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add {$product['name']} to cart</p>";
            }
        }
        
        // Check cart total
        $cartItems = getCartItems();
        $subtotal = array_sum(array_column($cartItems, 'total_price'));
        
        echo "<h2>Cart Summary</h2>";
        echo "<p>Items in cart: " . count($cartItems) . "</p>";
        echo "<p>Subtotal: " . formatPrice($subtotal) . "</p>";
        
        if ($subtotal >= 50) {
            echo "<p style='color: green;'>✅ Cart total meets minimum for WELCOME10 promo code</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Cart total too low for WELCOME10 (need 50.000 DTN)</p>";
        }
        
        echo "<h2>Test Promo Codes</h2>";
        echo "<p>Now try these codes on the cart page:</p>";
        echo "<ul>";
        echo "<li><strong>TEST10</strong> - 10% off (no minimum)</li>";
        echo "<li><strong>WELCOME10</strong> - 10% off (min 50 DTN)</li>";
        echo "</ul>";
        
        echo "<p><a href='view_cart.php' target='_blank'>Go to Cart Page</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ No products found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 