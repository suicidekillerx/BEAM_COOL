<?php
// Simulate a web request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'apply_promo_code';
$_POST['code'] = 'TEST10';

// Start session
session_start();

// Include the AJAX handler
ob_start();
include 'ajax_handler.php';
$output = ob_get_clean();

echo "<h1>Direct AJAX Handler Test</h1>";
echo "<h2>Request Details:</h2>";
echo "<p>Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>Action: " . $_POST['action'] . "</p>";
echo "<p>Code: " . $_POST['code'] . "</p>";

echo "<h2>Response:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to parse the JSON response
$response = json_decode($output, true);
if ($response) {
    echo "<h2>Parsed Response:</h2>";
    echo "<pre>" . print_r($response, true) . "</pre>";
    
    if ($response['success']) {
        echo "<p style='color: green;'>✅ AJAX handler working correctly!</p>";
        echo "<p>Message: " . $response['message'] . "</p>";
        if (isset($response['discount_amount'])) {
            echo "<p>Discount: " . $response['discount_amount'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ AJAX handler returned error</p>";
        echo "<p>Error: " . $response['message'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Invalid JSON response</p>";
}

// Test with invalid code
echo "<h2>Test with Invalid Code:</h2>";
$_POST['code'] = 'INVALID';
ob_start();
include 'ajax_handler.php';
$output2 = ob_get_clean();

echo "<pre>" . htmlspecialchars($output2) . "</pre>";

$response2 = json_decode($output2, true);
if ($response2 && !$response2['success']) {
    echo "<p style='color: green;'>✅ Invalid code properly rejected</p>";
    echo "<p>Error: " . $response2['message'] . "</p>";
}
?> 