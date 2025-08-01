<?php
require_once 'includes/functions.php';

echo "<h1>First Delivery API Debug</h1>";

// Check if API token is configured
$apiToken = getSiteSetting('first_delivery_token', '');
echo "<h2>1. API Token Check</h2>";
if (empty($apiToken)) {
    echo "<p style='color: red;'>❌ API token is empty. Please configure it in Admin > Settings.</p>";
    exit;
} else {
    echo "<p style='color: green;'>✅ API token is configured: " . substr($apiToken, 0, 10) . "...</p>";
}

// Test API initialization
echo "<h2>2. API Class Test</h2>";
try {
    $api = new FirstDeliveryAPI($apiToken);
    echo "<p style='color: green;'>✅ API class initialized successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error initializing API: " . $e->getMessage() . "</p>";
    exit;
}

// Test filter orders with minimal parameters
echo "<h2>3. Testing Filter Orders</h2>";
try {
    $filterData = [
        'pagination' => [
            'pageNumber' => 1,
            'limit' => 10
        ]
    ];
    
    echo "<p>Filter data: " . json_encode($filterData) . "</p>";
    
    $response = $api->filterOrders($filterData);
    
    echo "<h3>Raw API Response:</h3>";
    echo "<pre>" . print_r($response, true) . "</pre>";
    
    if ($response['http_code'] === 200) {
        echo "<p style='color: green;'>✅ HTTP 200 - API call successful</p>";
        
        if (!isset($response['data']['isError']) || !$response['data']['isError']) {
            echo "<p style='color: green;'>✅ No API error</p>";
            
            $orders = $response['data']['Items'] ?? [];
            $totalOrders = $response['data']['TotalCount'] ?? 0;
            $currentPage = $response['data']['CurrentPage'] ?? 1;
            $totalPages = $response['data']['TotalPages'] ?? 1;
            
            echo "<h3>Parsed Data:</h3>";
            echo "<p><strong>Total Orders:</strong> " . $totalOrders . "</p>";
            echo "<p><strong>Current Page:</strong> " . $currentPage . "</p>";
            echo "<p><strong>Total Pages:</strong> " . $totalPages . "</p>";
            echo "<p><strong>Orders in Response:</strong> " . count($orders) . "</p>";
            
            if (!empty($orders)) {
                echo "<h3>Sample Order Data:</h3>";
                echo "<pre>" . print_r($orders[0], true) . "</pre>";
            } else {
                echo "<p style='color: orange;'>⚠️ No orders found in response</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ API Error: " . ($response['data']['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ HTTP Error: " . $response['http_code'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
}

// Test with different date range
echo "<h2>4. Testing with Date Range</h2>";
try {
    $filterData = [
        'pagination' => [
            'pageNumber' => 1,
            'limit' => 20
        ],
        'createdAtFrom' => date('Y-m-d', strtotime('-30 days')),
        'createdAtTo' => date('Y-m-d')
    ];
    
    echo "<p>Filter data with date range: " . json_encode($filterData) . "</p>";
    
    $response = $api->filterOrders($filterData);
    
    if ($response['http_code'] === 200 && !$response['data']['isError']) {
        $orders = $response['data']['Items'] ?? [];
        echo "<p style='color: green;'>✅ Found " . count($orders) . " orders with date range</p>";
    } else {
        echo "<p style='color: red;'>❌ Error with date range filter</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception with date range: " . $e->getMessage() . "</p>";
}

// Test status check
echo "<h2>5. Testing Status Check</h2>";
try {
    // Try with a sample barcode (this might fail if no orders exist)
    $testBarcode = "123456789012";
    $response = $api->checkOrderStatus($testBarcode);
    
    echo "<p>Status check response for barcode '$testBarcode':</p>";
    echo "<pre>" . print_r($response, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Status check exception: " . $e->getMessage() . "</p>";
}

echo "<h2>6. Recommendations</h2>";
echo "<ul>";
echo "<li>Check if your API token is correct</li>";
echo "<li>Verify that you have orders in your First Delivery account</li>";
echo "<li>Try different date ranges</li>";
echo "<li>Check the API documentation for any changes</li>";
echo "<li>Contact First Delivery support if the API is not working</li>";
echo "</ul>";

echo "<h2>7. Next Steps</h2>";
echo "<p>If the API is working but showing no results:</p>";
echo "<ol>";
echo "<li>Make sure you have created orders through the orders.php page</li>";
echo "<li>Check if the orders were successfully sent to First Delivery</li>";
echo "<li>Try creating a test order and then checking the history</li>";
echo "<li>Verify your API token has the correct permissions</li>";
echo "</ol>";
?> 