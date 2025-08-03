<?php
// New cart handler - completely rewritten to avoid cache issues
session_start();

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    // Direct database connection
    $dsn = "mysql:host=localhost;dbname=weultcom_beam;charset=utf8mb4";
    $pdo = new PDO($dsn, 'weultcom_beam', '@J(9yYER6#qIM53]');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
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
                
            case 'update_cart':
                $cartId = (int)$_POST['cart_item_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity <= 0) {
                    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
                    $stmt->execute([$cartId, session_id()]);
                    echo json_encode(['success' => true, 'message' => 'Item removed']);
                } else {
                    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND session_id = ?");
                    $stmt->execute([$quantity, $cartId, session_id()]);
                    echo json_encode(['success' => true, 'message' => 'Cart updated']);
                }
                break;
                
            case 'remove_from_cart':
                $cartId = (int)$_POST['cart_item_id'];
                $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
                $stmt->execute([$cartId, session_id()]);
                echo json_encode(['success' => true, 'message' => 'Item removed']);
                break;
                
            case 'remove_promo_code':
                unset($_SESSION['applied_promo_code']);
                echo json_encode(['success' => true, 'message' => 'Promo code removed']);
                break;
                
            case 'test':
                echo json_encode(['success' => true, 'message' => 'New cart handler working']);
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
?> 