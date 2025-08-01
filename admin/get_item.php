<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? '';

if (!$table || !$id) {
    echo json_encode(['success' => false, 'error' => 'Missing table or id parameter']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Validate table name to prevent SQL injection
    $allowedTables = ['footer_sections', 'footer_links', 'social_media', 'video_section'];
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'error' => 'Invalid table name']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        echo json_encode(['success' => true, 'data' => $item]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Item not found']);
    }
} catch (Exception $e) {
    error_log("Error in get_item.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?> 