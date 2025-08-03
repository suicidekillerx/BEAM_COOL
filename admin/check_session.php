<?php
require_once 'includes/auth.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is authenticated
if (isAuthenticated()) {
    // Check if session is about to expire
    $timeLeft = 604800 - (time() - $_SESSION['last_activity']);
    $isExpiringSoon = $timeLeft <= 86400; // 1 day or less
    
    echo json_encode([
        'valid' => true,
        'timeLeft' => $timeLeft,
        'expiringSoon' => $isExpiringSoon,
        'message' => $isExpiringSoon ? 'Session will expire soon' : 'Session is valid'
    ]);
} else {
    echo json_encode([
        'valid' => false,
        'error' => 'session_expired',
        'message' => 'Session has expired',
        'redirect' => 'login.php?error=session_expired'
    ]);
}
?> 