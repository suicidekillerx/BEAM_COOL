<?php
// Test dynamic discount calculation
header('Content-Type: text/html');

echo "<h1>Dynamic Discount Test</h1>";

try {
    $dsn = "mysql:host=localhost;dbname=weultcom_beam;charset=utf8mb4";
    $pdo = new PDO($dsn, 'weultcom_beam', '@J(9yYER6#qIM53]');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    // Test 1: Check current cart items
    echo "<h3>Test 1: Current Cart Items</h3>";
    $stmt = $pdo->prepare("
        SELECT ci.*, p.price, p.sale_price, p.name
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.session_id = ?
    ");
    $stmt->execute([session_id()]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($cartItems) > 0) {
        echo "<p>Found " . count($cartItems) . " items in cart:</p>";
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
            $itemTotal = $price * $item['quantity'];
            $subtotal += $itemTotal;
            echo "<p>- {$item['name']}: {$item['quantity']} × {$price} = {$itemTotal}</p>";
        }
        echo "<p><strong>Subtotal: {$subtotal}</strong></p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No items in cart</p>";
    }
    
    // Test 2: Check applied promo code
    echo "<h3>Test 2: Applied Promo Code</h3>";
    session_start();
    if (isset($_SESSION['applied_promo_code'])) {
        $promo = $_SESSION['applied_promo_code'];
        echo "<p>Applied promo: {$promo['code']} ({$promo['name']})</p>";
        echo "<p>Type: {$promo['type']}, Value: {$promo['value']}</p>";
        echo "<p>Current discount: {$promo['discount_amount']}</p>";
        
        // Test 3: Recalculate discount
        echo "<h3>Test 3: Recalculate Discount</h3>";
        
        $newDiscount = 0;
        switch ($promo['type']) {
            case 'percentage':
                $newDiscount = $subtotal * ($promo['value'] / 100);
                if ($promo['max_discount']) {
                    $newDiscount = min($newDiscount, $promo['max_discount']);
                }
                break;
                
            case 'fixed_amount':
                $newDiscount = $promo['value'];
                break;
        }
        
        echo "<p>New calculated discount: {$newDiscount}</p>";
        echo "<p>Difference: " . ($newDiscount - $promo['discount_amount']) . "</p>";
        
        // Update the session
        $_SESSION['applied_promo_code']['discount_amount'] = $newDiscount;
        echo "<p style='color: green;'>✅ Updated session discount</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ No promo code applied</p>";
    }
    
    // Test 4: Simulate AJAX response
    echo "<h3>Test 4: Simulate AJAX Response</h3>";
    $_POST['action'] = 'update_cart';
    $_POST['cart_item_id'] = $cartItems[0]['id'] ?? 1;
    $_POST['quantity'] = ($cartItems[0]['quantity'] ?? 1) + 1;
    
    echo "<p>Simulating quantity update for item {$cartItems[0]['id']} to quantity {$_POST['quantity']}</p>";
    
    // Include the cart handler logic
    require_once 'cart_handler.php';
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 