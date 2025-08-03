<?php
session_start();

// Security headers to prevent caching and ensure secure sessions
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Function to check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true && 
           isset($_SESSION['admin_user_id']) && 
           isset($_SESSION['session_token']);
}

// Function to require authentication with proper session handling
function requireAuth() {
    // Check if session has expired
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > 604800) { // 7 days
        destroySession();
        redirectToLogin('timeout');
    }
    
    if (!isAuthenticated()) {
        // Clear any existing session data
        destroySession();
        redirectToLogin('session_expired');
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration']) || 
        (time() - $_SESSION['last_regeneration']) > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Function to redirect to login with proper error handling
function redirectToLogin($error = 'session_expired') {
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'session_expired',
            'message' => 'Your session has expired. Please log in again.',
            'redirect' => 'login.php?error=' . $error
        ]);
        exit;
    } else {
        // Regular redirect for normal page requests
        header('Location: login.php?error=' . $error);
        exit;
    }
}

// Function to create secure session
function createSecureSession($user) {
    // Clear any existing session
    session_unset();
    
    // Generate a unique session token
    $sessionToken = bin2hex(random_bytes(32));
    
    // Set session variables
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_full_name'] = $user['full_name'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['last_regeneration'] = time();
    
    // Set session cookie parameters for security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
}

// Function to destroy session securely
function destroySession() {
    // Clear all session data
    session_unset();
    
    // Destroy the session
    session_destroy();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}

// Function to check session timeout (7 days)
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > 604800) { // 7 days
        destroySession();
        redirectToLogin('timeout');
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

// Function to validate session token
function validateSessionToken() {
    if (!isset($_SESSION['session_token'])) {
        return false;
    }
    
    // You could add additional token validation here
    // For now, we'll just check if it exists
    return true;
}

// Function to check if session is about to expire (warn user)
function isSessionExpiringSoon() {
    if (isset($_SESSION['last_activity'])) {
        $timeLeft = 604800 - (time() - $_SESSION['last_activity']);
        return $timeLeft <= 86400; // 1 day or less
    }
    return false;
}

// Auto-check session timeout on every request
if (isAuthenticated()) {
    checkSessionTimeout();
    
    if (!validateSessionToken()) {
        destroySession();
        redirectToLogin('invalid_session');
    }
}
?> 