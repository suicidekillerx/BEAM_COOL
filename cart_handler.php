<?php
// Simple cart handler - minimal dependencies
session_start();

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Basic error handling
try {
    // Database connection - try different configurations
    $dbConfigs = [
        // Try custom config first
        file_exists('db_config.php') ? include('db_config.php') : null,
        // Your hosting database (from config file)
        ['host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'weultcom_beam', 'pass' => '@J(9yYER6#qIM53]'],
        ['host' => 'localhost', 'dbname' => 'weultcom_beam', 'user' => 'root', 'pass' => ''],
        // Common hosting configurations
        ['host' => 'localhost', 'dbname' => 'beam_ecommerce', 'user' => 'root', 'pass' => ''],
        ['host' => 'localhost', 'dbname' => 'weult_beam', 'user' => 'root', 'pass' => ''],
        ['host' => 'localhost', 'dbname' => 'beam_ecommerce', 'user' => 'weultcom_beam', 'pass' => '@J(9yYER6#qIM53]'],
        ['host' => 'localhost', 'dbname' => 'weult_beam', 'user' => 'weultcom_beam', 'pass' => '@J(9yYER6#qIM53]'],
        // Add your actual database credentials here
    ];
    
    // Filter out null values
    $dbConfigs = array_filter($dbConfigs);
    
    $pdo = null;
    $lastError = '';
    
    foreach ($dbConfigs as $config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['user'], $config['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            break; // Success, exit loop
        } catch (PDOException $e) {
            $lastError = $e->getMessage();
            continue; // Try next configuration
        }
    }
    
    if (!$pdo) {
        throw new Exception("Database connection failed. Last error: " . $lastError);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_cart':
                $cartId = (int)$_POST['cart_item_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity <= 0) {
                    // Remove item
                    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
                    $stmt->execute([$cartId, session_id()]);
                    
                    // Recalculate promo code discount after removal
                    $promoInfo = recalculatePromoCodeDiscount($pdo);
                    error_log("Cart removal - Promo info: " . json_encode($promoInfo));
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Item removed',
                        'promo_info' => $promoInfo
                    ]);
                } else {
                    // Update quantity
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND session_id = ?");
                    $stmt->execute([$quantity, $cartId, session_id()]);
                    
                    // Recalculate promo code discount after update
                    $promoInfo = recalculatePromoCodeDiscount($pdo);
                    error_log("Cart update - Promo info: " . json_encode($promoInfo));
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Cart updated',
                        'promo_info' => $promoInfo
                    ]);
                }
                break;
                
            case 'remove_from_cart':
                $cartId = (int)$_POST['cart_item_id'];
                $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
                $stmt->execute([$cartId, session_id()]);
                echo json_encode(['success' => true, 'message' => 'Item removed']);
                break;
                
            case 'apply_promo_code':
                $code = trim($_POST['code']);
                
                // Simple promo code check - NO expires_at
                $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1");
                $stmt->execute([$code]);
                $promoCode = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($promoCode) {
                    $_SESSION['applied_promo_code'] = $promoCode;
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Promo code applied',
                        'promo_code' => $promoCode,
                        'discount_amount' => $promoCode['value']
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid promo code']);
                }
                break;
                
            case 'remove_promo_code':
                unset($_SESSION['applied_promo_code']);
                echo json_encode(['success' => true, 'message' => 'Promo code removed']);
                break;
                
            case 'test':
                echo json_encode(['success' => true, 'message' => 'Database connection successful']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// Function to recalculate promo code discount
function recalculatePromoCodeDiscount($pdo) {
    $promoInfo = ['discount_updated' => false];
    
    // Check if there's an applied promo code
    if (isset($_SESSION['applied_promo_code'])) {
        $appliedPromo = $_SESSION['applied_promo_code'];
        
        // Get current cart items and calculate subtotal
        $stmt = $pdo->prepare("
            SELECT ci.*, p.price, p.sale_price 
            FROM cart_items ci 
            JOIN products p ON ci.product_id = p.id 
            WHERE ci.session_id = ?
        ");
        $stmt->execute([session_id()]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
            $subtotal += $price * $item['quantity'];
        }
        
        // Recalculate discount based on promo code type
        $newDiscount = 0;
        switch ($appliedPromo['type']) {
            case 'percentage':
                $newDiscount = $subtotal * ($appliedPromo['value'] / 100);
                if ($appliedPromo['max_discount']) {
                    $newDiscount = min($newDiscount, $appliedPromo['max_discount']);
                }
                break;
                
            case 'fixed_amount':
                $newDiscount = $appliedPromo['value'];
                break;
                
            case 'free_shipping':
                $newDiscount = 0; // Will be handled separately
                break;
        }
        
        // Update the applied promo code with new discount
        $_SESSION['applied_promo_code']['discount_amount'] = $newDiscount;
        
        $promoInfo = [
            'discount_updated' => true,
            'new_discount' => $newDiscount,
            'subtotal' => $subtotal
        ];
        
        error_log("Recalculated discount: {$newDiscount} for subtotal: {$subtotal}");
    }
    
    return $promoInfo;
}
?> 