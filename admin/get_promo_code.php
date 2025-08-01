<?php
session_start();
require_once '../includes/functions.php';

// Check admin authentication
require_admin_auth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$id = $_GET['id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Missing ID parameter']);
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE id = ?");
    $stmt->execute([$id]);
    $promoCode = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($promoCode) {
        // Format dates for datetime-local inputs
        if ($promoCode['start_date']) {
            $promoCode['start_date'] = date('Y-m-d\TH:i', strtotime($promoCode['start_date']));
        }
        if ($promoCode['end_date']) {
            $promoCode['end_date'] = date('Y-m-d\TH:i', strtotime($promoCode['end_date']));
        }
        
        echo json_encode(['success' => true, 'promo_code' => $promoCode]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Promo code not found']);
    }
} catch (Exception $e) {
    error_log("Error in get_promo_code.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 