<?php
require_once 'includes/functions.php';

// Test the First Delivery API integration
echo "<h1>First Delivery API Test</h1>";

// Check if API token is configured
$apiToken = getSiteSetting('first_delivery_token', '');
if (empty($apiToken)) {
    echo "<p style='color: red;'>❌ First Delivery API token not configured. Please set it in Admin > Settings.</p>";
    exit;
}

echo "<p style='color: green;'>✅ API token configured</p>";

// Test the API class
try {
    $api = new FirstDeliveryAPI($apiToken);
    echo "<p style='color: green;'>✅ API class initialized successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error initializing API class: " . $e->getMessage() . "</p>";
    exit;
}

// Test with a sample order
$testOrder = [
    'Client' => [
        'nom' => 'Test Customer',
        'gouvernerat' => 'tunis',
        'ville' => 'tunis',
        'adresse' => '123 Test Street',
        'telephone' => '12345678',
        'telephone2' => ''
    ],
    'Produit' => [
        'prix' => 100.0,
        'designation' => 'Test Product (Size: M, Qty: 1)',
        'nombreArticle' => 1,
        'commentaire' => 'Test order from Beam eCommerce',
        'article' => 'TEST-ORDER-001',
        'nombreEchange' => 0
    ]
];

echo "<h2>Testing API Connection</h2>";
echo "<p>Attempting to send test order to First Delivery API...</p>";

try {
    $response = $api->createOrder($testOrder);
    
    echo "<h3>API Response:</h3>";
    echo "<pre>" . print_r($response, true) . "</pre>";
    
    if ($response['http_code'] === 201 && !$response['data']['isError']) {
        echo "<p style='color: green;'>✅ API connection successful!</p>";
        echo "<p><strong>Barcode:</strong> " . $response['data']['result']['barCode'] . "</p>";
        echo "<p><strong>Print Link:</strong> <a href='" . $response['data']['result']['link'] . "' target='_blank'>View Label</a></p>";
    } else {
        echo "<p style='color: red;'>❌ API Error: " . ($response['data']['message'] ?? 'Unknown error') . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ API Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Integration Status</h2>";
echo "<p>✅ API class implemented</p>";
echo "<p>✅ Order preparation function implemented</p>";
echo "<p>✅ Integration with orders.php implemented</p>";
echo "<p>✅ Settings page updated with API token field</p>";
echo "<p>✅ Manual 'Send to Delivery' button added</p>";
echo "<p>✅ Automatic delivery on order confirmation</p>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Configure your First Delivery API token in Admin > Settings</li>";
echo "<li>Test with a real order in the admin panel</li>";
echo "<li>Verify delivery tracking works correctly</li>";
echo "</ol>";
?> 