<?php
require_once 'includes/functions.php';

echo "<h1>First Delivery API Unit Tests</h1>";
echo "<style>
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-pass { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .test-fail { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .test-info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
    .warning { background-color: #fff3cd; color: #856404; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Test Results Tracking
$testResults = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0
];

function runTest($testName, $testFunction) {
    global $testResults;
    
    echo "<div class='test-section'>";
    echo "<h3>🧪 Test: $testName</h3>";
    
    try {
        $result = $testFunction();
        if ($result === true) {
            echo "<div class='test-result success'>✅ PASSED</div>";
            $testResults['passed']++;
        } else {
            echo "<div class='test-result error'>❌ FAILED</div>";
            echo "<div class='test-result error'>$result</div>";
            $testResults['failed']++;
        }
    } catch (Exception $e) {
        echo "<div class='test-result error'>❌ EXCEPTION</div>";
        echo "<div class='test-result error'>" . $e->getMessage() . "</div>";
        $testResults['failed']++;
    }
    
    echo "</div>";
}

// Test 1: API Token Configuration
runTest("API Token Configuration", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "API token is not configured. Please set it in Admin > Settings.";
    }
    
    if (strlen($apiToken) < 10) {
        return "API token seems too short. Expected at least 10 characters.";
    }
    
    return true;
});

// Test 2: FirstDeliveryAPI Class Initialization
runTest("FirstDeliveryAPI Class Initialization", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test API class without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        // Check if the class has required methods
        $requiredMethods = ['createOrder', 'filterOrders', 'checkOrderStatus'];
        foreach ($requiredMethods as $method) {
            if (!method_exists($api, $method)) {
                return "Missing required method: $method";
            }
        }
        
        return true;
    } catch (Exception $e) {
        return "Failed to initialize API class: " . $e->getMessage();
    }
});

// Test 3: API Connection Test
runTest("API Connection Test", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test connection without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        // Test with minimal filter data
        $filterData = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 1
            ]
        ];
        
        $response = $api->filterOrders($filterData);
        
        if (!isset($response['http_code'])) {
            return "Response missing http_code";
        }
        
        if ($response['http_code'] === 401) {
            return "API token is invalid or expired";
        }
        
        if ($response['http_code'] === 403) {
            return "API token lacks required permissions";
        }
        
        if ($response['http_code'] !== 200) {
            return "API returned HTTP " . $response['http_code'];
        }
        
        return true;
    } catch (Exception $e) {
        return "Connection failed: " . $e->getMessage();
    }
});

// Test 4: Response Structure Validation
runTest("Response Structure Validation", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test response structure without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        $filterData = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 5
            ]
        ];
        
        $response = $api->filterOrders($filterData);
        
        if (!isset($response['data'])) {
            return "Response missing 'data' field";
        }
        
        // Check for different possible response structures
        $hasItems = isset($response['data']['Items']) || isset($response['data']['items']) || isset($response['data']['data']);
        $hasTotal = isset($response['data']['TotalCount']) || isset($response['data']['totalCount']) || isset($response['data']['total']);
        
        if (!$hasItems) {
            return "Response missing orders array (Items/items/data)";
        }
        
        if (!$hasTotal) {
            return "Response missing total count (TotalCount/totalCount/total)";
        }
        
        return true;
    } catch (Exception $e) {
        return "Structure validation failed: " . $e->getMessage();
    }
});

// Test 5: Order Data Structure Test
runTest("Order Data Structure Test", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test order structure without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        $filterData = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 1
            ]
        ];
        
        $response = $api->filterOrders($filterData);
        
        if ($response['http_code'] !== 200) {
            return "API returned HTTP " . $response['http_code'];
        }
        
        $orders = $response['data']['Items'] ?? $response['data']['items'] ?? $response['data']['data'] ?? [];
        
        if (empty($orders)) {
            return "No orders found to test structure";
        }
        
        $order = $orders[0];
        
        // Check required fields
        $requiredFields = ['barCode'];
        foreach ($requiredFields as $field) {
            if (!isset($order[$field])) {
                return "Order missing required field: $field";
            }
        }
        
        // Check optional but expected fields
        $expectedFields = ['Client', 'Product', 'state', 'createdAt'];
        $missingFields = [];
        foreach ($expectedFields as $field) {
            if (!isset($order[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            return "Order missing expected fields: " . implode(', ', $missingFields);
        }
        
        return true;
    } catch (Exception $e) {
        return "Order structure test failed: " . $e->getMessage();
    }
});

// Test 6: Date Range Filter Test
runTest("Date Range Filter Test", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test date filter without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        $filterData = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 10
            ],
            'createdAtFrom' => date('Y-m-d', strtotime('-7 days')),
            'createdAtTo' => date('Y-m-d')
        ];
        
        $response = $api->filterOrders($filterData);
        
        if ($response['http_code'] !== 200) {
            return "Date filter API returned HTTP " . $response['http_code'];
        }
        
        return true;
    } catch (Exception $e) {
        return "Date filter test failed: " . $e->getMessage();
    }
});

// Test 7: Status Check Test
runTest("Status Check Test", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test status check without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        // First get a real barcode from orders
        $filterData = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 1
            ]
        ];
        
        $response = $api->filterOrders($filterData);
        
        if ($response['http_code'] !== 200) {
            return "Cannot get test barcode - API returned HTTP " . $response['http_code'];
        }
        
        $orders = $response['data']['Items'] ?? $response['data']['items'] ?? $response['data']['data'] ?? [];
        
        if (empty($orders)) {
            return "No orders available to test status check";
        }
        
        $testBarcode = $orders[0]['barCode'];
        
        // Test status check
        $statusResponse = $api->checkOrderStatus($testBarcode);
        
        if ($statusResponse['http_code'] !== 200) {
            return "Status check returned HTTP " . $statusResponse['http_code'];
        }
        
        return true;
    } catch (Exception $e) {
        return "Status check test failed: " . $e->getMessage();
    }
});

// Test 8: Pagination Test
runTest("Pagination Test", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test pagination without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        // Test first page
        $filterData1 = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 5
            ]
        ];
        
        $response1 = $api->filterOrders($filterData1);
        
        if ($response1['http_code'] !== 200) {
            return "Page 1 returned HTTP " . $response1['http_code'];
        }
        
        // Test second page
        $filterData2 = [
            'pagination' => [
                'pageNumber' => 2,
                'limit' => 5
            ]
        ];
        
        $response2 = $api->filterOrders($filterData2);
        
        if ($response2['http_code'] !== 200) {
            return "Page 2 returned HTTP " . $response2['http_code'];
        }
        
        return true;
    } catch (Exception $e) {
        return "Pagination test failed: " . $e->getMessage();
    }
});

// Test 9: Error Handling Test
runTest("Error Handling Test", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test error handling without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        // Test with invalid barcode
        $response = $api->checkOrderStatus("INVALID_BARCODE_12345");
        
        // Should return an error, but not crash
        if (!isset($response['http_code'])) {
            return "Invalid barcode test didn't return proper response structure";
        }
        
        return true;
    } catch (Exception $e) {
        return "Error handling test failed: " . $e->getMessage();
    }
});

// Test 10: prepareOrderForDelivery Function Test
runTest("prepareOrderForDelivery Function Test", function() {
    // Mock order data
    $mockOrder = [
        'id' => 1,
        'order_number' => 'TEST-001',
        'customer_name' => 'Test Customer',
        'customer_email' => 'test@example.com',
        'customer_phone' => '+21612345678',
        'shipping_address' => '123 Test Street',
        'shipping_city' => 'Tunis',
        'shipping_governorate' => 'Tunis',
        'total_amount' => 150.50,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $mockOrderItems = [
        [
            'product_name' => 'Test Product',
            'quantity' => 2,
            'price' => 75.25
        ]
    ];
    
    try {
        $result = prepareOrderForDelivery($mockOrder, $mockOrderItems);
        
        if (!is_array($result)) {
            return "Function should return an array";
        }
        
        $requiredFields = ['Client', 'Product'];
        foreach ($requiredFields as $field) {
            if (!isset($result[$field])) {
                return "Missing required field: $field";
            }
        }
        
        // Check Client structure
        $clientFields = ['nom', 'telephone', 'adresse', 'ville', 'gouvernerat'];
        foreach ($clientFields as $field) {
            if (!isset($result['Client'][$field])) {
                return "Client missing field: $field";
            }
        }
        
        // Check Product structure
        $productFields = ['designation', 'nombreArticle', 'prix'];
        foreach ($productFields as $field) {
            if (!isset($result['Product'][$field])) {
                return "Product missing field: $field";
            }
        }
        
        return true;
    } catch (Exception $e) {
        return "prepareOrderForDelivery test failed: " . $e->getMessage();
    }
});

// Test 11: sendOrderToDelivery Function Test
runTest("sendOrderToDelivery Function Test", function() {
    // This test will be skipped if no real order exists
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM orders LIMIT 1");
    $stmt->execute();
    $order = $stmt->fetch();
    
    if (!$order) {
        return "No orders in database to test with";
    }
    
    try {
        $result = sendOrderToDelivery($order['id']);
        
        if (!is_array($result)) {
            return "Function should return an array";
        }
        
        if (!isset($result['success'])) {
            return "Response missing 'success' field";
        }
        
        // If successful, should have barCode
        if ($result['success'] && !isset($result['barCode'])) {
            return "Successful response should include barCode";
        }
        
        return true;
    } catch (Exception $e) {
        return "sendOrderToDelivery test failed: " . $e->getMessage();
    }
});

// Test 12: Database Integration Test
runTest("Database Integration Test", function() {
    try {
        $pdo = getDBConnection();
        
        // Test if we can query orders
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if (!isset($result['count'])) {
            return "Cannot query orders table";
        }
        
        // Test if we can query order items
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if (!isset($result['count'])) {
            return "Cannot query order_items table";
        }
        
        return true;
    } catch (Exception $e) {
        return "Database integration test failed: " . $e->getMessage();
    }
});

// Test 13: Settings Integration Test
runTest("Settings Integration Test", function() {
    try {
        $token = getSiteSetting('first_delivery_token', '');
        
        // Test setting retrieval
        if ($token === null) {
            return "getSiteSetting should return empty string for missing setting";
        }
        
        // Test setting update (we'll just verify the function exists)
        if (!function_exists('updateSiteSetting')) {
            return "updateSiteSetting function not found";
        }
        
        return true;
    } catch (Exception $e) {
        return "Settings integration test failed: " . $e->getMessage();
    }
});

// Test 14: API Response Format Consistency
runTest("API Response Format Consistency", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test response format without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        $filterData = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 3
            ]
        ];
        
        $response = $api->filterOrders($filterData);
        
        if ($response['http_code'] !== 200) {
            return "API returned HTTP " . $response['http_code'];
        }
        
        $orders = $response['data']['Items'] ?? $response['data']['items'] ?? $response['data']['data'] ?? [];
        
        if (empty($orders)) {
            return "No orders to test format consistency";
        }
        
        // Check that all orders have the same structure
        $firstOrderKeys = array_keys($orders[0]);
        
        foreach ($orders as $index => $order) {
            $currentKeys = array_keys($order);
            if ($currentKeys !== $firstOrderKeys) {
                return "Order $index has different structure than first order";
            }
        }
        
        return true;
    } catch (Exception $e) {
        return "Response format consistency test failed: " . $e->getMessage();
    }
});

// Test 15: Performance Test
runTest("Performance Test", function() {
    $apiToken = getSiteSetting('first_delivery_token', '');
    
    if (empty($apiToken)) {
        return "Cannot test performance without token";
    }
    
    try {
        $api = new FirstDeliveryAPI($apiToken);
        
        $startTime = microtime(true);
        
        $filterData = [
            'pagination' => [
                'pageNumber' => 1,
                'limit' => 10
            ]
        ];
        
        $response = $api->filterOrders($filterData);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        if ($response['http_code'] !== 200) {
            return "Performance test API returned HTTP " . $response['http_code'];
        }
        
        if ($executionTime > 10) {
            return "API call took too long: " . round($executionTime, 2) . " seconds";
        }
        
        return true;
    } catch (Exception $e) {
        return "Performance test failed: " . $e->getMessage();
    }
});

// Display Results Summary
echo "<div class='test-section test-info'>";
echo "<h2>📊 Test Results Summary</h2>";
echo "<p><strong>Passed:</strong> {$testResults['passed']}</p>";
echo "<p><strong>Failed:</strong> {$testResults['failed']}</p>";
echo "<p><strong>Warnings:</strong> {$testResults['warnings']}</p>";

$totalTests = $testResults['passed'] + $testResults['failed'] + $testResults['warnings'];
$successRate = $totalTests > 0 ? round(($testResults['passed'] / $totalTests) * 100, 1) : 0;

echo "<p><strong>Success Rate:</strong> {$successRate}%</p>";

if ($testResults['failed'] === 0) {
    echo "<p style='color: green; font-weight: bold;'>🎉 All critical tests passed!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Some tests failed. Please review the results above.</p>";
}

echo "</div>";

// Recommendations
echo "<div class='test-section test-info'>";
echo "<h2>🔧 Recommendations</h2>";
echo "<ul>";
echo "<li>If API token tests fail: Configure your API token in Admin > Settings</li>";
echo "<li>If connection tests fail: Check your internet connection and API endpoint</li>";
echo "<li>If structure tests fail: The API response format may have changed</li>";
echo "<li>If performance tests fail: Consider implementing caching</li>";
echo "<li>If database tests fail: Check your database connection</li>";
echo "</ul>";
echo "</div>";

// Cleanup
echo "<div class='test-section test-info'>";
echo "<h2>🧹 Cleanup</h2>";
echo "<p>This test file can be safely deleted after testing:</p>";
echo "<code>test_delivery_api_unit.php</code>";
echo "</div>";
?> 