<?php
session_start();
require_once '../includes/functions.php';

$currentPage = 'orders_history';
$pageTitle = 'Orders History';

// Get API token
$apiToken = getSiteSetting('first_delivery_token', '');
$api = null;

if (!empty($apiToken)) {
    try {
        $api = new FirstDeliveryAPI($apiToken);
    } catch (Exception $e) {
        $errorMessage = "Error initializing API: " . $e->getMessage();
    }
}

// Handle API requests
$orders = [];
$totalOrders = 0;
$currentPageNum = 1;
$totalPages = 1;
$errorMessage = '';
$successMessage = '';

if ($api) {
    try {
        // Get filter parameters
        $barCode = $_GET['barcode'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $state = $_GET['state'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20; // Items per page
        
        // Prepare filter data
        $filterData = [
            'pagination' => [
                'pageNumber' => $page,
                'limit' => $limit
            ]
        ];
        
        if (!empty($barCode)) {
            $filterData['barCode'] = $barCode;
        }
        
        if (!empty($dateFrom)) {
            $filterData['createdAtFrom'] = $dateFrom;
        }
        
        if (!empty($dateTo)) {
            $filterData['createdAtTo'] = $dateTo;
        }
        
        if (!empty($state)) {
            $filterData['state'] = intval($state);
        }
        
        // Fetch orders from API
        $response = $api->filterOrders($filterData);
        
        // Debug: Log the response
        error_log("First Delivery API Response: " . json_encode($response));
        
        if ($response['http_code'] === 200 && !$response['data']['isError']) {
            // Try different possible response structures
            $orders = $response['data']['Items'] ?? $response['data']['items'] ?? $response['data']['data'] ?? [];
            $totalOrders = $response['data']['TotalCount'] ?? $response['data']['totalCount'] ?? $response['data']['total'] ?? 0;
            $currentPageNum = $response['data']['CurrentPage'] ?? $response['data']['currentPage'] ?? $response['data']['page'] ?? 1;
            $totalPages = $response['data']['TotalPages'] ?? $response['data']['totalPages'] ?? $response['data']['pages'] ?? 1;
            
            // Debug: Log the parsed data
            error_log("Parsed orders count: " . count($orders));
            error_log("Total orders: " . $totalOrders);
            error_log("Response structure: " . json_encode(array_keys($response['data'])));
        } else {
            $errorMessage = "API Error: " . ($response['data']['message'] ?? 'Unknown error');
            error_log("First Delivery API Error: " . $errorMessage);
        }
        
    } catch (Exception $e) {
        $errorMessage = "Error fetching orders: " . $e->getMessage();
    }
}

// Handle status check
if (isset($_POST['action']) && $_POST['action'] === 'check_status') {
    $barCode = $_POST['barcode'] ?? '';
    if (!empty($barCode) && $api) {
        try {
            $response = $api->checkOrderStatus($barCode);
            if ($response['http_code'] === 200 && !$response['data']['isError']) {
                $successMessage = "Status for barcode $barCode: " . $response['data']['result']['state'];
            } else {
                $errorMessage = "Error checking status: " . ($response['data']['message'] ?? 'Unknown error');
            }
        } catch (Exception $e) {
            $errorMessage = "Error checking status: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - Orders History</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mobile responsive styles */
        @media (max-width: 1023px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 280px;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .admin-sidebar.open {
                left: 0;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
        }
        
        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-en-attente { background-color: #fef3c7; color: #92400e; }
        .status-en-cours { background-color: #dbeafe; color: #1e40af; }
        .status-livre { background-color: #ecfdf5; color: #047857; }
        .status-demande-enlevement { background-color: #fef3c7; color: #92400e; }
        .status-retour { background-color: #fef2f2; color: #dc2626; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    
    <div class="flex h-screen bg-gray-50">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Main content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="container mx-auto px-6 py-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Orders History</h1>
                        <p class="text-gray-600">View and manage delivery orders from First Delivery Group</p>
                    </div>
                    
                    <?php if (!empty($apiToken)): ?>
                        <!-- Filters -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
                                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                                        <input type="text" name="barcode" value="<?= htmlspecialchars($_GET['barcode'] ?? '') ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black"
                                               placeholder="Enter barcode">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                        <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                        <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select name="state" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                                            <option value="">All Statuses</option>
                                            <option value="0" <?= ($_GET['state'] ?? '') === '0' ? 'selected' : '' ?>>En attente</option>
                                            <option value="1" <?= ($_GET['state'] ?? '') === '1' ? 'selected' : '' ?>>En cours</option>
                                            <option value="2" <?= ($_GET['state'] ?? '') === '2' ? 'selected' : '' ?>>Livré</option>
                                            <option value="100" <?= ($_GET['state'] ?? '') === '100' ? 'selected' : '' ?>>Demande d'enlèvement</option>
                                            <option value="200" <?= ($_GET['state'] ?? '') === '200' ? 'selected' : '' ?>>Retour</option>
                                        </select>
                                    </div>
                                    
                                    <div class="flex items-end">
                                        <button type="submit" class="w-full bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 transition-colors">
                                            <i class="fas fa-search mr-2"></i>Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Status Check -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Check Order Status</h3>
                                <form method="POST" class="flex space-x-4">
                                    <input type="hidden" name="action" value="check_status">
                                    <input type="text" name="barcode" placeholder="Enter barcode to check status" 
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-black focus:border-black">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-search mr-2"></i>Check Status
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Messages -->
                        <?php if (!empty($successMessage)): ?>
                            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md mb-6">
                                <?= htmlspecialchars($successMessage) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errorMessage)): ?>
                            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md mb-6">
                                <?= htmlspecialchars($errorMessage) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Orders Table -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900">Delivery Orders</h3>
                                    <div class="text-sm text-gray-500">
                                        Showing <?= count($orders) ?> of <?= number_format($totalOrders) ?> orders
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-12">
                                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">No orders found</p>
                                    <p class="text-sm text-gray-500">Try adjusting your filters or check your API connection.</p>
                                </div>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach ($orders as $order): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?= htmlspecialchars($order['barCode']) ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">
                                                            <?= htmlspecialchars($order['Client']['nom'] ?? 'N/A') ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?= htmlspecialchars($order['Client']['telephone'] ?? 'N/A') ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="text-sm text-gray-900">
                                                            <?= htmlspecialchars($order['Client']['adresse'] ?? 'N/A') ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?= htmlspecialchars($order['Client']['ville'] ?? '') ?>, 
                                                            <?= htmlspecialchars($order['Client']['gouvernerat'] ?? '') ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="text-sm text-gray-900">
                                                            <?= htmlspecialchars($order['Product']['designation'] ?? 'N/A') ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            Qty: <?= htmlspecialchars($order['Product']['nombreArticle'] ?? '1') ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?= number_format($order['Product']['prix'] ?? 0, 3) ?> TND
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="order-status status-<?= strtolower(str_replace(' ', '-', $order['state'] ?? '')) ?>">
                                                            <?= htmlspecialchars($order['state'] ?? 'Unknown') ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm text-gray-900">
                                                            <?= date('M j, Y', strtotime($order['createdAt'] ?? '')) ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?= date('g:i A', strtotime($order['createdAt'] ?? '')) ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button onclick="checkOrderStatus('<?= htmlspecialchars($order['barCode']) ?>')" 
                                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                        <?php if (!empty($order['pickupAt'])): ?>
                                                            <span class="text-green-600" title="Picked up on <?= date('M j, Y g:i A', strtotime($order['pickupAt'])) ?>">
                                                                <i class="fas fa-truck"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($order['deliveredAt'])): ?>
                                                            <span class="text-green-600" title="Delivered on <?= date('M j, Y g:i A', strtotime($order['deliveredAt'])) ?>">
                                                                <i class="fas fa-check-circle"></i>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm text-gray-700">
                                                Page <?= $currentPageNum ?> of <?= $totalPages ?>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <?php if ($currentPageNum > 1): ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPageNum - 1])) ?>" 
                                                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                                        Previous
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php for ($i = max(1, $currentPageNum - 2); $i <= min($totalPages, $currentPageNum + 2); $i++): ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm <?= $i === $currentPageNum ? 'bg-black text-white' : 'hover:bg-gray-50'; ?> transition-colors">
                                                        <?= $i ?>
                                                    </a>
                                                <?php endfor; ?>
                                                
                                                <?php if ($currentPageNum < $totalPages): ?>
                                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPageNum + 1])) ?>" 
                                                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                                                        Next
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium">API Token Not Configured</h3>
                                    <p class="text-sm mt-1">Please configure your First Delivery API token in <a href="setting.php" class="underline">Admin Settings</a> to view orders history.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        function checkOrderStatus(barcode) {
            if (confirm('Check status for barcode: ' + barcode + '?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="check_status">
                    <input type="hidden" name="barcode" value="${barcode}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.admin-sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    overlay.classList.toggle('open');
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('open');
                });
            }
        });
    </script>
</body>
</html> 