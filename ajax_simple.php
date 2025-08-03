<?php
// Very simple AJAX test
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode([
        'success' => true,
        'message' => 'Simple AJAX test successful',
        'action' => $_POST['action'] ?? 'none'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests allowed'
    ]);
}
?> 