<?php
// Simple cart handler - no database required for testing
session_start();

// Set JSON headers
header('Content-Type: application/json');
header('Cache-Control: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_cart':
            echo json_encode(['success' => true, 'message' => 'Cart updated (test mode)']);
            break;
            
        case 'remove_from_cart':
            echo json_encode(['success' => true, 'message' => 'Item removed (test mode)']);
            break;
            
        case 'apply_promo_code':
            $code = trim($_POST['code']);
            if ($code === 'WELCOME10') {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Promo code applied (test mode)',
                    'promo_code' => ['name' => 'WELCOME10', 'value' => 10],
                    'discount_amount' => 10
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid promo code']);
            }
            break;
            
        case 'remove_promo_code':
            echo json_encode(['success' => true, 'message' => 'Promo code removed (test mode)']);
            break;
            
        case 'test':
            echo json_encode(['success' => true, 'message' => 'Simple cart handler working']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?> 