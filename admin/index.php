<?php
require_once 'includes/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in
requireAuth();

$currentPage = 'dashboard';
$pageTitle = 'Dashboard';

// Check if maintenance mode is enabled
$maintenanceMode = isMaintenanceMode();

// Get real statistics
$pdo = getDBConnection();

// Get total products
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$stmt->execute();
$totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total orders
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total revenue
$stmt = $pdo->prepare("SELECT SUM(total) as total FROM orders WHERE order_status = 'delivered'");
$stmt->execute();
$totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Get low stock products
$stmt = $pdo->prepare("
    SELECT p.name, ps.size, ps.stock_quantity 
    FROM product_sizes ps 
    JOIN products p ON ps.product_id = p.id 
    WHERE ps.stock_quantity <= 5 AND ps.stock_quantity > 0 
    ORDER BY ps.stock_quantity ASC 
    LIMIT 5
");
$stmt->execute();
$lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent orders
$stmt = $pdo->prepare("
    SELECT o.*, o.customer_name 
    FROM orders o 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top selling products
$stmt = $pdo->prepare("
    SELECT p.name, COUNT(oi.id) as sales_count 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.order_status = 'delivered' 
    GROUP BY p.id 
    ORDER BY sales_count DESC 
    LIMIT 5
");
$stmt->execute();
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get monthly revenue data for the last month only
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m-%d') as day,
        SUM(total) as revenue,
        COUNT(*) as orders
    FROM orders 
    WHERE order_status = 'delivered' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
    ORDER BY day ASC
");
$stmt->execute();
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order status distribution
$stmt = $pdo->prepare("
    SELECT 
        order_status,
        COUNT(*) as count
    FROM orders 
    GROUP BY order_status
");
$stmt->execute();
$orderStatusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top cities by orders
$stmt = $pdo->prepare("
    SELECT 
        shipping_city,
        COUNT(*) as order_count
    FROM orders 
    GROUP BY shipping_city 
    ORDER BY order_count DESC 
    LIMIT 5
");
$stmt->execute();
$topCities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily orders for the last 30 days
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as orders
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute();
$dailyOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get real monthly visitors data
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as visitors
    FROM visitors 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthlyVisitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get real daily visitors data for the last 30 days
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as visitors
    FROM visitors 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute();
$dailyVisitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get today's visitors count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as today_visitors
    FROM visitors 
    WHERE DATE(created_at) = CURDATE()
");
$stmt->execute();
$todayVisitors = $stmt->fetch(PDO::FETCH_ASSOC)['today_visitors'] ?? 0;

// Get financial data
$stmt = $pdo->query("SELECT SUM(total) as total_revenue FROM orders WHERE order_status = 'delivered'");
$totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

$stmt = $pdo->query("SELECT AVG(total) as avg_order_value FROM orders WHERE order_status = 'delivered'");
$avgOrderValue = $stmt->fetch(PDO::FETCH_ASSOC)['avg_order_value'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders WHERE order_status = 'delivered'");
$deliveredOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;

// Get monthly revenue for the last 6 months
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total) as revenue,
        COUNT(*) as orders
    FROM orders 
    WHERE order_status = 'delivered' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get revenue by day for the last 30 days
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        SUM(total) as revenue,
        COUNT(*) as orders
    FROM orders 
    WHERE order_status = 'delivered' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$stmt->execute();
$dailyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get comprehensive financial data including costs and profit
// Calculate total revenue (all orders, not just delivered)
$stmt = $pdo->query("SELECT SUM(total) as total_revenue_all FROM orders");
$totalRevenueAll = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue_all'] ?? 0;

// Calculate total product costs for delivered orders
$stmt = $pdo->query("
    SELECT SUM(oi.quantity * p.cost_price) as total_cost
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.order_status = 'delivered'
");
$totalProductCost = $stmt->fetch(PDO::FETCH_ASSOC)['total_cost'] ?? 0;

// Data integrity check - verify we have real data
$dataIntegrityCheck = true;
$dataIssues = [];

// Check if we have delivered orders
if ($deliveredOrders == 0) {
    $dataIntegrityCheck = false;
    $dataIssues[] = "No delivered orders found";
}

// Check if we have product costs
if ($totalProductCost == 0) {
    $dataIntegrityCheck = false;
    $dataIssues[] = "No product cost data found";
}

// Check if revenue is realistic
if ($totalRevenue < 0) {
    $dataIntegrityCheck = false;
    $dataIssues[] = "Invalid revenue data detected";
}

// Calculate gross profit (revenue - product costs)
$grossProfit = $totalRevenue - $totalProductCost;

// Calculate profit margin percentage
$profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

// Get monthly financial data for charts
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') as month,
        SUM(o.total) as revenue,
        SUM(oi.quantity * p.cost_price) as costs,
        SUM(o.total - (oi.quantity * p.cost_price)) as profit,
        COUNT(*) as orders
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.order_status = 'delivered' 
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthlyFinancial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily financial data for charts
$stmt = $pdo->prepare("
    SELECT 
        DATE(o.created_at) as date,
        SUM(o.total) as revenue,
        SUM(oi.quantity * p.cost_price) as costs,
        SUM(o.total - (oi.quantity * p.cost_price)) as profit,
        COUNT(*) as orders
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.order_status = 'delivered' 
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(o.created_at)
    ORDER BY date ASC
");
$stmt->execute();
$dailyFinancial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate average cost per order
$avgCostPerOrder = $deliveredOrders > 0 ? $totalProductCost / $deliveredOrders : 0;

// Calculate total expenses (product costs + other expenses)
$totalExpenses = $totalProductCost; // You can add other expenses here later

// Calculate net profit
$netProfit = $totalRevenue - $totalExpenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <!-- Chart.js for beautiful graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- ApexCharts for advanced charts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        /* Admin specific styles */
        .admin-sidebar {
            background: linear-gradient(180deg, #000000 0%, #1a1a1a 100%);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .menu-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .menu-item:hover::before {
            left: 100%;
        }
        
        .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #ffffff;
        }
        
        .menu-item:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }
        
        .menu-icon {
            transition: all 0.3s ease;
        }
        
        .menu-item:hover .menu-icon {
            transform: scale(1.1);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e5e7eb;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .admin-header {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Enhanced chart styles */
        .chart-container {
            position: relative;
            height: 300px;
            transition: all 0.3s ease;
        }
        
        .chart-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Performance metrics hover effects */
        .performance-metric {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .performance-metric:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        /* Animated gradients */
        .animated-gradient {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Pulse animation for alerts */
        .pulse-alert {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Enhanced card shadows */
        .enhanced-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        
        .enhanced-shadow:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .notification-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .sidebar-toggle {
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            transform: rotate(180deg);
        }
        
        .content-area {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        
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
        
        /* Mobile-specific styles */
        @media (max-width: 640px) {
            .stats-card {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']" data-current-page="dashboard">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>
            
        <!-- Content Area -->
        <main class="content-area flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Maintenance Mode Alert -->
                <?php if ($maintenanceMode): ?>
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Maintenance Mode is Active
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Your website is currently in maintenance mode. Visitors will see a maintenance page instead of your regular website.</p>
                            </div>
                            <div class="mt-4">
                                <div class="-mx-2 -my-1.5 flex">
                                    <a href="setting.php" class="bg-yellow-50 px-2 py-1.5 rounded-md text-sm font-medium text-yellow-800 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-yellow-50 focus:ring-yellow-600">
                                        Manage Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 mb-6 lg:mb-8">
                    <div class="stats-card rounded-lg p-3 lg:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs lg:text-sm font-medium">Total Products</p>
                                <p class="text-lg lg:text-3xl font-bold text-gray-900 counter" data-target="<?php echo $totalProducts; ?>">0</p>
                                <p class="text-green-600 text-xs lg:text-sm">Active products</p>
                            </div>
                            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card rounded-lg p-3 lg:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs lg:text-sm font-medium">Total Orders</p>
                                <p class="text-lg lg:text-3xl font-bold text-gray-900 counter" data-target="<?php echo $totalOrders; ?>">0</p>
                                <p class="text-green-600 text-xs lg:text-sm">All time orders</p>
                            </div>
                            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card rounded-lg p-3 lg:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs lg:text-sm font-medium">Revenue</p>
                                <p class="text-lg lg:text-3xl font-bold text-gray-900 counter" data-target="<?php echo $totalRevenue; ?>" data-prefix="" data-suffix=" DTN">0 DTN</p>
                                <p class="text-green-600 text-xs lg:text-sm">Completed orders</p>
                            </div>
                            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="stats-card rounded-lg p-3 lg:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs lg:text-sm font-medium">Low Stock Items</p>
                                <p class="text-lg lg:text-3xl font-bold text-gray-900 counter" data-target="<?php echo count($lowStockProducts); ?>">0</p>
                                <p class="text-red-600 text-xs lg:text-sm">Need attention</p>
                            </div>
                            <div class="w-10 h-10 lg:w-12 lg:h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php if (empty($recentOrders)): ?>
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        <p class="text-gray-500 text-sm">No orders yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                                    <p class="font-medium text-gray-900">Order #<?php echo $order['id']; ?></p>
                                                    <p class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($order['customer_name']); ?> - 
                                                        <?php echo number_format($order['total'], 2); ?> DTN
                                                    </p>
                                        </div>
                                    </div>
                                            <?php
                                            $statusColor = 'gray';
                                            switch ($order['order_status']) {
                                                case 'delivered':
                                                    $statusColor = 'green';
                                                    break;
                                                case 'pending':
                                                    $statusColor = 'yellow';
                                                    break;
                                                case 'processing':
                                                    $statusColor = 'blue';
                                                    break;
                                                case 'shipped':
                                                    $statusColor = 'purple';
                                                    break;
                                                case 'cancelled':
                                                    $statusColor = 'red';
                                                    break;
                                            }
                                            ?>
                                            <span class="bg-<?php echo $statusColor; ?>-100 text-<?php echo $statusColor; ?>-800 text-xs px-2 py-1 rounded-full capitalize">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                                </div>
                                
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Low Stock Alerts</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php if (empty($lowStockProducts)): ?>
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500 text-sm">All products well stocked</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 <?php echo $product['stock_quantity'] <= 2 ? 'bg-red-100' : 'bg-yellow-100'; ?> rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 <?php echo $product['stock_quantity'] <= 2 ? 'text-red-600' : 'text-yellow-600'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                                    <p class="text-sm text-gray-500">Size <?php echo $product['size']; ?> - <?php echo $product['stock_quantity']; ?> units left</p>
                                        </div>
                                    </div>
                                            <span class="<?php echo $product['stock_quantity'] <= 2 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?> text-xs px-2 py-1 rounded-full">
                                                <?php echo $product['stock_quantity'] <= 2 ? 'Critical' : 'Low'; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                                </div>
                                
                <!-- Additional Dashboard Sections -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                    <!-- Top Selling Products -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:col-span-2">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Top Selling Products</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php if (empty($topProducts)): ?>
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        <p class="text-gray-500 text-sm">No sales data yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($topProducts as $index => $product): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-bold text-blue-600"><?php echo $index + 1; ?></span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                                    <p class="text-sm text-gray-500"><?php echo $product['sales_count']; ?> sales</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm font-medium text-gray-900">#<?php echo $index + 1; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <a href="products.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                        <div>
                                        <p class="font-medium text-gray-900">Add Product</p>
                                        <p class="text-sm text-gray-500">Create new product</p>
                                        </div>
                                </a>
                                
                                <a href="orders.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                    </div>
                                        <div>
                                        <p class="font-medium text-gray-900">View Orders</p>
                                        <p class="text-sm text-gray-500">Manage orders</p>
                                </div>
                                </a>
                                
                                <a href="inventory.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                            </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Inventory</p>
                                        <p class="text-sm text-gray-500">Check stock levels</p>
                        </div>
                                </a>
                                
                                                                <a href="financial.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Financial</p>
                                        <p class="text-sm text-gray-500">Financial reports</p>
                                    </div>
                                </a>
                                
                                <a href="setting.php" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Settings</p>
                                        <p class="text-sm text-gray-500">Configure store</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts and Analytics Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Revenue Trend Chart -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Revenue Trend</h3>
                            <p class="text-sm text-gray-500">Last month revenue by day</p>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="revenueChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Status Distribution -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Order Status Distribution</h3>
                            <p class="text-sm text-gray-500">Current order status breakdown</p>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="orderStatusChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Analytics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                    <!-- Daily Orders Chart -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:col-span-2">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daily Orders</h3>
                            <p class="text-sm text-gray-500">Last 30 days order activity</p>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="dailyOrdersChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Cities -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Top Cities</h3>
                            <p class="text-sm text-gray-500">Most active shipping cities</p>
                        </div>
                        <div class="p-6">
                            <?php if (empty($topCities)): ?>
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <p class="text-gray-500 text-sm">No city data yet</p>
                                </div>
                            <?php else: ?>
                            <div class="space-y-4">
                                    <?php foreach ($topCities as $index => $city): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-bold text-blue-600"><?php echo $index + 1; ?></span>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($city['shipping_city']); ?></p>
                                                    <p class="text-sm text-gray-500"><?php echo $city['order_count']; ?> orders</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm font-medium text-gray-900">#<?php echo $index + 1; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Monthly Visitors Chart -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Monthly Visitors</h3>
                            <p class="text-sm text-gray-500">Website traffic over the last 6 months</p>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="monthlyVisitorsChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daily Visitors</h3>
                            <p class="text-sm text-gray-500">Last 30 days website traffic</p>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="dailyVisitorsChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Data Integrity Warning -->
                <?php if (!$dataIntegrityCheck): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Data Integrity Warning</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>The following issues were detected with your financial data:</p>
                                <ul class="list-disc list-inside mt-1">
                                    <?php foreach ($dataIssues as $issue): ?>
                                        <li><?php echo htmlspecialchars($issue); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p class="mt-2">Please ensure you have real customer orders and product cost data for accurate profit & loss calculations.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Financial Analytics Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Monthly Revenue Chart -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Monthly Revenue</h3>
                            <p class="text-sm text-gray-500">Last 6 months revenue trend</p>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="monthlyRevenueChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Daily Revenue Chart -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daily Revenue</h3>
                            <p class="text-sm text-gray-500">Last 30 days revenue</p>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="dailyRevenueChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profit & Loss Analytics -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Monthly Profit & Loss Chart -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Monthly Profit & Loss</h3>
                                    <p class="text-sm text-gray-500">Revenue vs Costs vs Profit (6 months)</p>
                                </div>
                                <?php if ($dataIntegrityCheck): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Real Data
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="monthlyProfitLossChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Daily Profit & Loss Chart -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Daily Profit & Loss</h3>
                                    <p class="text-sm text-gray-500">Revenue vs Costs vs Profit (30 days)</p>
                                </div>
                                <?php if ($dataIntegrityCheck): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Real Data
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="chart-container">
                                <canvas id="dailyProfitLossChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                

                
                <!-- Performance Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-6">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Average Order Value</p>
                                <p class="text-2xl font-bold"><?php echo $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : '0.00'; ?> DTN</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                        </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                        <div class="flex items-center justify-between">
                                        <div>
                                <p class="text-green-100 text-sm font-medium">Today's Visitors</p>
                                <p class="text-2xl font-bold"><?php echo number_format($todayVisitors); ?></p>
                                        </div>
                            <div class="w-12 h-12 bg-green-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                    </div>
                        </div>
                                </div>
                                
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                                <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100 text-sm font-medium">Active Products</p>
                                <p class="text-2xl font-bold"><?php echo number_format($totalProducts); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100 text-sm font-medium">Stock Alerts</p>
                                <p class="text-2xl font-bold"><?php echo count($lowStockProducts); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-orange-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-6">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                        <div class="flex items-center justify-between">
                                        <div>
                                <p class="text-emerald-100 text-sm font-medium">Total Revenue</p>
                                <p class="text-2xl font-bold counter" data-target="<?php echo $totalRevenue; ?>" data-prefix="" data-suffix=" DTN">0 DTN</p>
                                        </div>
                            <div class="w-12 h-12 bg-emerald-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                    </div>
                        </div>
                                </div>
                                
                    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                                <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Total Costs</p>
                                <p class="text-2xl font-bold counter" data-target="<?php echo $totalProductCost; ?>" data-prefix="" data-suffix=" DTN">0 DTN</p>
                            </div>
                            <div class="w-12 h-12 bg-red-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                        <div class="flex items-center justify-between">
                                        <div>
                                <p class="text-teal-100 text-sm font-medium">Gross Profit</p>
                                <p class="text-2xl font-bold counter" data-target="<?php echo $grossProfit; ?>" data-prefix="" data-suffix=" DTN">0 DTN</p>
                                        </div>
                            <div class="w-12 h-12 bg-teal-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                    </div>
                                </div>
                            </div>
                    
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg p-6 text-white performance-metric enhanced-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm font-medium">Profit Margin</p>
                                <p class="text-2xl font-bold counter" data-target="<?php echo $profitMargin; ?>" data-prefix="" data-suffix="%">0%</p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-400 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="js/admin-common.js"></script>
    
    <script>
        // Chart.js configuration
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#6B7280';
        
        // Animated Counter Function
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const prefix = element.getAttribute('data-prefix') || '';
            const suffix = element.getAttribute('data-suffix') || '';
            const duration = 2000; // 2 seconds
            const step = target / (duration / 16); // 60fps
            let current = 0;
            
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                if (suffix === ' DTN') {
                    element.textContent = prefix + current.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + suffix;
                } else if (suffix === '%') {
                    element.textContent = prefix + current.toFixed(1) + suffix;
                } else {
                    element.textContent = prefix + Math.floor(current).toLocaleString() + suffix;
                }
            }, 16);
        }
        
        // Animate counters when they come into view
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        // Observe all counter elements
        document.querySelectorAll('.counter').forEach(counter => {
            counterObserver.observe(counter);
        });
        
        // Monthly Revenue Chart
        const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        const monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyRevenue, 'month')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($monthlyRevenue, 'revenue')); ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' DTN';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        
        // Daily Revenue Chart
        const dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
        const dailyRevenueChart = new Chart(dailyRevenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($dailyRevenue, 'date')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($dailyRevenue, 'revenue')); ?>,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    borderColor: '#8B5CF6',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' DTN';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Revenue Trend Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyData, 'day')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($monthlyData, 'revenue')); ?>,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' DTN';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        
        // Order Status Distribution Chart
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        const orderStatusChart = new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($orderStatusDistribution, 'order_status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($orderStatusDistribution, 'count')); ?>,
                    backgroundColor: [
                        '#10B981', // Green for delivered
                        '#F59E0B', // Yellow for pending
                        '#3B82F6', // Blue for processing
                        '#8B5CF6', // Purple for shipped
                        '#EF4444', // Red for cancelled
                        '#6B7280'  // Gray for others
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 2,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                cutout: '60%'
            }
        });
        
        // Daily Orders Chart
        const dailyOrdersCtx = document.getElementById('dailyOrdersChart').getContext('2d');
        const dailyOrdersChart = new Chart(dailyOrdersCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($dailyOrders, 'date')); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_column($dailyOrders, 'orders')); ?>,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    borderColor: '#8B5CF6',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                    }
                }
            });
        
        // Monthly Visitors Chart
        const monthlyVisitorsCtx = document.getElementById('monthlyVisitorsChart').getContext('2d');
        const monthlyVisitorsChart = new Chart(monthlyVisitorsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyVisitors, 'month')); ?>,
                datasets: [{
                    label: 'Visitors',
                    data: <?php echo json_encode(array_column($monthlyVisitors, 'visitors')); ?>,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        
        // Daily Visitors Chart
        const dailyVisitorsCtx = document.getElementById('dailyVisitorsChart').getContext('2d');
        const dailyVisitorsChart = new Chart(dailyVisitorsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($dailyVisitors, 'date')); ?>,
                datasets: [{
                    label: 'Visitors',
                    data: <?php echo json_encode(array_column($dailyVisitors, 'visitors')); ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10B981',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Monthly Profit & Loss Chart
        const monthlyProfitLossCtx = document.getElementById('monthlyProfitLossChart').getContext('2d');
        const monthlyProfitLossChart = new Chart(monthlyProfitLossCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($monthlyFinancial, 'month')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($monthlyFinancial, 'revenue')); ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10B981',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }, {
                    label: 'Costs',
                    data: <?php echo json_encode(array_column($monthlyFinancial, 'costs')); ?>,
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: '#EF4444',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }, {
                    label: 'Profit',
                    data: <?php echo json_encode(array_column($monthlyFinancial, 'profit')); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: '#3B82F6',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' DTN';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Daily Profit & Loss Chart
        const dailyProfitLossCtx = document.getElementById('dailyProfitLossChart').getContext('2d');
        const dailyProfitLossChart = new Chart(dailyProfitLossCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($dailyFinancial, 'date')); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode(array_column($dailyFinancial, 'revenue')); ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: '#10B981',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }, {
                    label: 'Costs',
                    data: <?php echo json_encode(array_column($dailyFinancial, 'costs')); ?>,
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: '#EF4444',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }, {
                    label: 'Profit',
                    data: <?php echo json_encode(array_column($dailyFinancial, 'profit')); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: '#3B82F6',
                    borderWidth: 0,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' DTN';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Animate charts on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe chart containers
        document.querySelectorAll('.bg-white.rounded-lg').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease-out';
            observer.observe(card);
        });
        
        // Performance metrics animation
        const metrics = document.querySelectorAll('.bg-gradient-to-r');
        metrics.forEach((metric, index) => {
            metric.style.opacity = '0';
            metric.style.transform = 'translateY(20px)';
            metric.style.transition = `all 0.6s ease-out ${index * 0.1}s`;
            
            setTimeout(() => {
                metric.style.opacity = '1';
                metric.style.transform = 'translateY(0)';
            }, 500 + (index * 100));
        });
        
        // Add hover effects to performance metrics
        metrics.forEach(metric => {
            metric.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            metric.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
    
    <!-- Security Script -->
    <script src="js/security.js"></script>
</body>
</html> 