<?php
require_once 'includes/auth.php';

// Destroy session securely
destroySession();

// Check if this is an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'redirect' => 'login.php?error=logout'
    ]);
    exit;
} else {
    // Regular redirect for normal page requests
    header('Location: login.php?error=logout');
    exit;
}
?> 