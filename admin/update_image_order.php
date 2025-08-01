<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['images']) || !is_array($input['images'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
    
    foreach ($input['images'] as $image) {
        if (!isset($image['id']) || !isset($image['sort_order'])) {
            throw new Exception('Invalid image data');
        }
        
        $stmt->execute([(int)$image['sort_order'], (int)$image['id']]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 