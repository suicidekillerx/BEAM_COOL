<?php
require_once 'includes/functions.php';

echo "<h1>🔍 First Delivery API Integration Test</h1>";
echo "<style>
    .test-box { margin: 15px 0; padding: 15px; border-radius: 5px; }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px; }
    .step { margin: 10px 0; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff; }
</style>";

// Step 1: Check API Token
echo "<div class='step'>";
echo "<h3>Step 1: API Token Check</h3>";
$apiToken = getSiteSetting('first_delivery_token', '');
if (empty($apiToken)) {
    echo "<div class='test-box error'>❌ API token is not configured</div>";
    echo "<p>Please go to <strong>Admin > Settings</strong> and configure your First Delivery API token.</p>";
    exit;
} else {
    echo "<div class='test-box success'>✅ API token is configured</div>";
    echo "<p>Token: " . substr($apiToken, 0, 10) . "...</p>";
}
echo "</div>";

// Step 2: Initialize API
echo "<div class='step'>";
echo "<h3>Step 2: API Initialization</h3>";
try {
    $api = new FirstDeliveryAPI($apiToken);
    echo "<div class='test-box success'>✅ API class initialized successfully</div>";
} catch (Exception $e) {
    echo "<div class='test-box error'>❌ Failed to initialize API: " . $e->getMessage() . "</div>";
    exit;
}
echo "</div>";

// Step 3: Test Basic API Call
echo "<div class='step'>";
echo "<h3>Step 3: Basic API Call Test</h3>";
try {
    $filterData = [
        'pagination' => [
            'pageNumber' => 1,
            'limit' => 5
        ]
    ];
    
    echo "<p>Making API call with data:</p>";
    echo "<pre>" . json_encode($filterData, JSON_PRETTY_PRINT) . "</pre>";
    
    $response = $api->filterOrders($filterData);
    
    echo "<p><strong>HTTP Status:</strong> " . ($response['http_code'] ?? 'Unknown') . "</p>";
    
    if ($response['http_code'] === 200) {
        echo "<div class='test-box success'>✅ API call successful</div>";
    } else {
        echo "<div class='test-box error'>❌ API call failed with HTTP " . $response['http_code'] . "</div>";
    }
    
    echo "<p><strong>Raw Response:</strong></p>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "<div class='test-box error'>❌ API call exception: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Step 4: Parse Response
echo "<div class='step'>";
echo "<h3>Step 4: Response Parsing</h3>";
if (isset($response) && $response['http_code'] === 200) {
    $data = $response['data'] ?? [];
    
    echo "<p><strong>Response Data Keys:</strong></p>";
    echo "<pre>" . json_encode(array_keys($data), JSON_PRETTY_PRINT) . "</pre>";
    
    // Try different possible structures
    $orders = $data['Items'] ?? $data['items'] ?? $data['data'] ?? [];
    $totalCount = $data['TotalCount'] ?? $data['totalCount'] ?? $data['total'] ?? 0;
    $currentPage = $data['CurrentPage'] ?? $data['currentPage'] ?? $data['page'] ?? 1;
    $totalPages = $data['TotalPages'] ?? $data['totalPages'] ?? $data['pages'] ?? 1;
    
    echo "<p><strong>Parsed Data:</strong></p>";
    echo "<ul>";
    echo "<li>Orders found: " . count($orders) . "</li>";
    echo "<li>Total count: " . $totalCount . "</li>";
    echo "<li>Current page: " . $currentPage . "</li>";
    echo "<li>Total pages: " . $totalPages . "</li>";
    echo "</ul>";
    
    if (empty($orders)) {
        echo "<div class='test-box warning'>⚠️ No orders found in response</div>";
        echo "<p>This could mean:</p>";
        echo "<ul>";
        echo "<li>No orders have been sent to First Delivery yet</li>";
        echo "<li>The API token doesn't have access to orders</li>";
        echo "<li>Orders are in a different date range</li>";
        echo "<li>The API response format has changed</li>";
        echo "</ul>";
    } else {
        echo "<div class='test-box success'>✅ Found " . count($orders) . " orders</div>";
        
        // Show first order structure
        echo "<p><strong>First Order Structure:</strong></p>";
        echo "<pre>" . json_encode($orders[0], JSON_PRETTY_PRINT) . "</pre>";
    }
} else {
    echo "<div class='test-box error'>❌ Cannot parse response - API call failed</div>";
}
echo "</div>";

// Step 5: Test with Date Range
echo "<div class='step'>";
echo "<h3>Step 5: Date Range Test</h3>";
try {
    $filterData = [
        'pagination' => [
            'pageNumber' => 1,
            'limit' => 10
        ],
        'createdAtFrom' => date('Y-m-d', strtotime('-30 days')),
        'createdAtTo' => date('Y-m-d')
    ];
    
    echo "<p>Testing with date range (last 30 days):</p>";
    echo "<pre>" . json_encode($filterData, JSON_PRETTY_PRINT) . "</pre>";
    
    $response = $api->filterOrders($filterData);
    
    if ($response['http_code'] === 200) {
        $orders = $response['data']['Items'] ?? $response['data']['items'] ?? $response['data']['data'] ?? [];
        echo "<div class='test-box success'>✅ Date range test successful - Found " . count($orders) . " orders</div>";
    } else {
        echo "<div class='test-box error'>❌ Date range test failed - HTTP " . $response['http_code'] . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-box error'>❌ Date range test exception: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Step 6: Check Database Orders
echo "<div class='step'>";
echo "<h3>Step 6: Database Orders Check</h3>";
try {
    $pdo = getDBConnection();
    
    // Check total orders
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
    $stmt->execute();
    $result = $stmt->fetch();
    $totalOrders = $result['count'];
    
    echo "<p><strong>Total orders in database:</strong> " . $totalOrders . "</p>";
    
    if ($totalOrders > 0) {
        // Check recent orders
        $stmt = $pdo->prepare("SELECT id, order_number, customer_name, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $recentOrders = $stmt->fetchAll();
        
        echo "<p><strong>Recent orders:</strong></p>";
        echo "<ul>";
        foreach ($recentOrders as $order) {
            echo "<li>Order #" . $order['order_number'] . " - " . $order['customer_name'] . " (" . $order['created_at'] . ")</li>";
        }
        echo "</ul>";
        
        // Check if any orders have been sent to delivery (using order_status column)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE order_status = 'confirmed'");
        $stmt->execute();
        $result = $stmt->fetch();
        $confirmedOrders = $result['count'];
        
        echo "<p><strong>Confirmed orders (should be sent to delivery):</strong> " . $confirmedOrders . "</p>";
        
        if ($confirmedOrders == 0) {
            echo "<div class='test-box warning'>⚠️ No confirmed orders found</div>";
            echo "<p>Orders need to be confirmed to be sent to First Delivery. Try:</p>";
            echo "<ol>";
            echo "<li>Go to Admin > Orders</li>";
            echo "<li>Change an order status to 'confirmed'</li>";
            echo "<li>Check if it appears in Orders History</li>";
            echo "</ol>";
        }
    } else {
        echo "<div class='test-box warning'>⚠️ No orders in database</div>";
        echo "<p>Create some test orders first to test the delivery integration.</p>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-box error'>❌ Database check failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Step 7: Test Order Creation
echo "<div class='step'>";
echo "<h3>Step 7: Test Order Creation</h3>";
try {
    // Get a sample order from database
    $stmt = $pdo->prepare("SELECT id FROM orders LIMIT 1");
    $stmt->execute();
    $order = $stmt->fetch();
    
    if ($order) {
        echo "<p>Testing order preparation for order ID: " . $order['id'] . "</p>";
        
        $result = sendOrderToDelivery($order['id']);
        
        echo "<p><strong>Send Order Result:</strong></p>";
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        
        if ($result['success']) {
            echo "<div class='test-box success'>✅ Order sent successfully to First Delivery</div>";
            echo "<p>Barcode: " . ($result['barCode'] ?? 'N/A') . "</p>";
        } else {
            echo "<div class='test-box error'>❌ Failed to send order: " . ($result['error'] ?? 'Unknown error') . "</div>";
        }
    } else {
        echo "<div class='test-box warning'>⚠️ No orders available to test</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-box error'>❌ Order creation test failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Summary
echo "<div class='step'>";
echo "<h3>📋 Summary & Next Steps</h3>";
echo "<div class='test-box info'>";
echo "<h4>If you're seeing 'No orders found':</h4>";
echo "<ol>";
echo "<li><strong>Check API Token:</strong> Make sure it's correct in Admin > Settings</li>";
echo "<li><strong>Create Test Orders:</strong> Add some orders through the admin panel</li>";
echo "<li><strong>Confirm Orders:</strong> Change order status to 'confirmed' to send to delivery</li>";
echo "<li><strong>Check Date Range:</strong> Try different date filters</li>";
echo "<li><strong>Contact First Delivery:</strong> Verify your account has orders</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

// Cleanup reminder
echo "<div class='test-box info'>";
echo "<h4>🧹 Cleanup</h4>";
echo "<p>After testing, you can delete this file: <code>test_delivery_integration.php</code></p>";
echo "</div>";
?> 