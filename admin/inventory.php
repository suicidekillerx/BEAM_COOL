<?php
session_start();
require_once '../includes/functions.php';

$currentPage = 'inventory';
$pageTitle = 'Inventory Management';

// Get all products with stock information
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.sale_price, p.is_active, p.show_stock, p.stock_status,
           c.name as category_name, col.name as collection_name
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN collections col ON p.collection_id = col.id 
    ORDER BY p.name ASC
");
$stmt->execute();
$products = $stmt->fetchAll();

// Get stock information for each product
foreach ($products as &$product) {
    $stmt = $pdo->prepare("SELECT size, stock_quantity FROM product_sizes WHERE product_id = ? ORDER BY FIELD(size, 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL')");
    $stmt->execute([$product['id']]);
    $product['sizes'] = $stmt->fetchAll();
    
    // Calculate total stock
    $product['total_stock'] = array_sum(array_column($product['sizes'], 'stock_quantity'));
}
unset($product); // Break the reference

// Handle form submissions for updating stock
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_stock') {
        $productId = $_POST['product_id'] ?? 0;
        $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
        
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE product_sizes SET stock_quantity = ? WHERE product_id = ? AND size = ?");
        
        foreach ($sizes as $size) {
            $stockQuantity = isset($_POST['stock_' . $size]) ? (int)$_POST['stock_' . $size] : 0;
            $stmt->execute([$stockQuantity, $productId, $size]);
        }
        
        // Update product stock settings
        $showStock = isset($_POST['show_stock']) ? 1 : 0;
        $stockStatus = $_POST['stock_status'] ?? 'in_stock';
        
        $stmt = $pdo->prepare("UPDATE products SET show_stock = ?, stock_status = ? WHERE id = ?");
        $stmt->execute([$showStock, $stockStatus, $productId]);
        
        $successMessage = "Inventory updated successfully!";
        
        // Refresh product data
        header("Location: inventory.php?success=1");
        exit;
    }
}

// Success message handling
if (isset($_GET['success'])) {
    $successMessage = "Inventory updated successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - <?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
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
            transition: all 0.3s ease;
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
        
        .stock-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .stock-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .stock-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php require_once 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <?php require_once 'includes/header.php'; ?>
            
            <!-- Content Area -->
            <main class="content-area flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Page Title and Actions -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900"><?php echo $pageTitle; ?></h1>
                        <p class="mt-1 text-sm text-gray-600">Manage your product inventory and stock levels</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <button id="print-stock-report" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Stock Report
                        </button>
                        <a href="products.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add New Product
                        </a>
                    </div>
                </div>
                
                <!-- Success Message -->
                <?php if (isset($successMessage)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?php echo $successMessage; ?></p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button" class="close-alert inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <span class="sr-only">Dismiss</span>
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Inventory Overview Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6">
                    <!-- Total Products -->
                    <div class="stats-card rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-50 text-blue-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-sm font-medium text-gray-600">Total Products</h2>
                                <p class="text-2xl font-bold text-gray-900"><?php echo count($products); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Stock -->
                    <?php 
                    $totalStock = 0;
                    foreach ($products as $product) {
                        $totalStock += $product['total_stock'];
                    }
                    ?>
                    <div class="stats-card rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-50 text-green-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-sm font-medium text-gray-600">Total Stock</h2>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $totalStock; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Low Stock Products -->
                    <?php 
                    $lowStockCount = 0;
                    $lowStockThreshold = 5; // Define what "low stock" means
                    foreach ($products as $product) {
                        if ($product['total_stock'] > 0 && $product['total_stock'] <= $lowStockThreshold) {
                            $lowStockCount++;
                        }
                    }
                    ?>
                    <div class="stats-card rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-50 text-yellow-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-sm font-medium text-gray-600">Low Stock Products</h2>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $lowStockCount; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Out of Stock Products -->
                    <?php 
                    $outOfStockCount = 0;
                    foreach ($products as $product) {
                        if ($product['total_stock'] == 0) {
                            $outOfStockCount++;
                        }
                    }
                    ?>
                    <div class="stats-card rounded-xl shadow-sm p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-50 text-red-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-sm font-medium text-gray-600">Out of Stock</h2>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $outOfStockCount; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Inventory Table -->
                <div class="bg-white shadow-sm rounded-xl overflow-hidden mb-8">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Inventory List</h2>
                    </div>
                    
                    <!-- Search and Filter - Mobile Friendly -->
                    <div class="p-4 sm:p-6 border-b border-gray-200 bg-gray-50">
                        <!-- Search Bar - Always Visible -->
                        <div class="w-full mb-4">
                            <label for="inventory-search" class="sr-only">Search</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" id="inventory-search" class="focus:ring-black focus:border-black block w-full pl-10 pr-12 py-3 border-gray-300 rounded-md" placeholder="Search products...">
                            </div>
                        </div>
                        
                        <!-- Filter Toggle Button (Mobile Only) -->
                        <div class="sm:hidden mb-4">
                            <button type="button" id="filter-toggle" class="w-full flex justify-between items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                                <span>Filters</span>
                                <svg id="filter-chevron-down" class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                <svg id="filter-chevron-up" class="h-5 w-5 text-gray-500 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Filter Options - Hidden on Mobile by Default -->
                        <div id="filter-options" class="hidden sm:flex flex-col sm:flex-row sm:space-y-0 sm:space-x-4">
                            <div class="w-full sm:w-40 mb-3 sm:mb-0">
                                <label for="stock-filter" class="block text-xs font-medium text-gray-700 mb-1 sm:sr-only">Filter by Stock</label>
                                <select id="stock-filter" class="block w-full py-3 pl-3 pr-10 border-gray-300 focus:outline-none focus:ring-black focus:border-black rounded-md">
                                    <option value="all">All Stock</option>
                                    <option value="in-stock">In Stock</option>
                                    <option value="low-stock">Low Stock</option>
                                    <option value="out-of-stock">Out of Stock</option>
                                </select>
                            </div>
                            
                            <div class="w-full sm:w-40">
                                <label for="category-filter" class="block text-xs font-medium text-gray-700 mb-1 sm:sr-only">Filter by Category</label>
                                <select id="category-filter" class="block w-full py-3 pl-3 pr-10 border-gray-300 focus:outline-none focus:ring-black focus:border-black rounded-md">
                                    <option value="all">All Categories</option>
                                    <?php 
                                    $categories = getCategories();
                                    foreach ($categories as $category): 
                                        ?>
                                        <option value="<?php echo htmlspecialchars($category['name']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table - Responsive Design -->
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <!-- Mobile Card View (visible on small screens only) -->
                        <div class="block sm:hidden">
                            <div class="space-y-3 p-2" id="inventory-cards-body">
                                <!-- Mobile cards will be populated by JavaScript -->
                            </div>
                        </div>
                        
                        <!-- Desktop Table View (hidden on small screens) -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collection</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Worth</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="inventory-table-body">
                                <?php foreach ($products as $product): 
                                    // Determine stock status class
                                    $stockStatusClass = '';
                                    if ($product['total_stock'] == 0) {
                                        $stockStatusClass = 'stock-danger';
                                    } elseif ($product['total_stock'] <= 5) {
                                        $stockStatusClass = 'stock-warning';
                                    } else {
                                        $stockStatusClass = 'stock-success';
                                    }
                                ?>
                                <tr class="inventory-row" 
                                    data-product-name="<?php echo strtolower(htmlspecialchars($product['name'])); ?>"
                                    data-category="<?php echo strtolower(htmlspecialchars($product['category_name'] ?? '')); ?>"
                                    data-stock-status="<?php echo $product['total_stock'] == 0 ? 'out-of-stock' : ($product['total_stock'] <= 5 ? 'low-stock' : 'in-stock'); ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-md flex items-center justify-center">
                                                <?php 
                                                // Get product image
                                                $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
                                                $stmt->execute([$product['id']]);
                                                $image = $stmt->fetch();
                                                if ($image): 
                                                ?>
                                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-10 w-10 object-cover rounded-md">
                                                <?php else: ?>
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <div class="text-sm text-gray-500">ID: <?php echo $product['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($product['collection_name'] ?? 'None'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($product['sale_price']): ?>
                                        <div class="text-sm font-medium text-red-600"><?php echo formatPrice($product['sale_price']); ?></div>
                                        <div class="text-xs text-gray-500 line-through"><?php echo formatPrice($product['price']); ?></div>
                                        <?php else: ?>
                                        <div class="text-sm font-medium text-gray-900"><?php echo formatPrice($product['price']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium <?php echo $stockStatusClass; ?> px-2 py-1 rounded-full text-center">
                                            <?php echo $product['total_stock']; ?> units
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?php 
                                            $sizeInfo = [];
                                            foreach ($product['sizes'] as $size) {
                                                if ($size['stock_quantity'] > 0) {
                                                    $sizeInfo[] = $size['size'] . ': ' . $size['stock_quantity'];
                                                }
                                            }
                                            echo implode(', ', $sizeInfo);
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($product['total_stock'] == 0): ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                                        <?php elseif ($product['total_stock'] <= 5): ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>
                                        <?php else: ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php 
                                        $price = $product['sale_price'] ?: $product['price'];
                                        $stockWorth = $price * $product['total_stock'];
                                        echo formatPrice($stockWorth);
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button type="button" class="text-blue-600 hover:text-blue-900 update-stock-btn" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                            Update Stock
                                        </button>
                                        <a href="products.php?edit=<?php echo $product['id']; ?>" class="text-gray-600 hover:text-gray-900">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty State - Mobile Friendly -->
                    <div id="empty-state" class="hidden p-6 sm:p-12 text-center bg-white rounded-lg shadow-sm my-4">
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <div class="bg-gray-100 p-4 rounded-full">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-medium text-gray-900">No products found</h3>
                                <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">Try adjusting your search or filter to find what you're looking for.</p>
                            </div>
                            <button type="button" id="reset-filters" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Reset Filters
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Update Stock Modal - Mobile Friendly -->
    <div id="update-stock-modal" class="fixed inset-0 overflow-y-auto hidden z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Modal positioning helper -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal content -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full max-w-[95%] sm:w-full">
                <!-- Close button for mobile -->
                <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-500 sm:hidden" id="mobile-close-modal">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <form id="update-stock-form" method="POST" action="inventory.php">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="product_id" id="modal-product-id">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <!-- Icon -->
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </div>
                            
                            <!-- Content -->
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Update Stock</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4" id="modal-product-name">Update stock quantities for Product Name</p>
                                    
                                    <!-- Stock Quantities - Mobile Friendly Grid -->
                                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Stock Quantities</h4>
                                        
                                        <!-- Mobile view: 4 columns grid -->
                                        <div class="grid grid-cols-4 gap-2 sm:hidden">
                                            <?php 
                                            $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
                                            foreach ($sizes as $size): 
                                            ?>
                                            <div class="text-center">
                                                <label for="stock_<?php echo $size; ?>" class="block text-xs font-medium text-gray-700 mb-1"><?php echo $size; ?></label>
                                                <input type="number" id="stock_<?php echo $size; ?>" name="stock_<?php echo $size; ?>" min="0" value="0" 
                                                       class="w-full px-2 py-1 border border-gray-300 rounded text-center focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- Desktop view: 7 columns grid -->
                                        <div class="hidden sm:grid sm:grid-cols-7 sm:gap-2">
                                            <?php foreach ($sizes as $size): ?>
                                            <div class="text-center">
                                                <label for="stock_desktop_<?php echo $size; ?>" class="block text-xs font-medium text-gray-700 mb-1"><?php echo $size; ?></label>
                                                <input type="number" id="stock_desktop_<?php echo $size; ?>" data-size="<?php echo $size; ?>" min="0" value="0" 
                                                       class="stock-sync w-full px-2 py-1 border border-gray-300 rounded text-center focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="mt-3 flex items-center justify-between text-sm">
                                            <span class="text-gray-600">Total Stock:</span>
                                            <span class="font-medium" id="modal-total-stock">0</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Stock Display Options -->
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="show_stock" name="show_stock" checked
                                                   class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                            <label for="show_stock" class="ml-2 text-sm font-medium text-gray-700">Show Stock Numbers</label>
                                        </div>
                                        <p class="text-xs text-gray-500">When enabled, customers can see exact stock quantities. When disabled, they'll see general stock status.</p>
                                        
                                        <div id="stockStatusSection">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status (when stock numbers are hidden)</label>
                                            <select id="stock_status" name="stock_status" 
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                                <option value="in_stock">In Stock</option>
                                                <option value="low_stock">Low Stock</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action buttons - Mobile friendly layout -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:w-auto sm:text-sm" id="cancel-update">
                            Cancel
                        </button>
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-black text-base font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black sm:w-auto sm:text-sm">
                            Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Close alert message
        const closeAlertButtons = document.querySelectorAll('.close-alert');
        closeAlertButtons.forEach(button => {
            button.addEventListener('click', function() {
                this.closest('div.bg-green-50').remove();
            });
        });
        
        // Search functionality
        const searchInput = document.getElementById('inventory-search');
        const inventoryRows = document.querySelectorAll('.inventory-row');
        const emptyState = document.getElementById('empty-state');
        const inventoryCardsContainer = document.getElementById('inventory-cards-body');
        
        // Create mobile cards for each product
        createMobileCards();
        
        searchInput.addEventListener('input', filterInventory);
        
        // Stock filter
        const stockFilter = document.getElementById('stock-filter');
        stockFilter.addEventListener('change', filterInventory);
        
        // Category filter
        const categoryFilter = document.getElementById('category-filter');
        categoryFilter.addEventListener('change', filterInventory);
        
        // Create mobile cards for responsive view
        function createMobileCards() {
            inventoryRows.forEach(row => {
                // Extract data from the row
                const productId = row.querySelector('button.update-stock-btn').dataset.productId;
                const productName = row.querySelector('button.update-stock-btn').dataset.productName;
                const productImage = row.querySelector('.flex-shrink-0 img') ? 
                                    row.querySelector('.flex-shrink-0 img').src : null;
                const productCategory = row.querySelector('td:nth-child(2) .text-sm').textContent;
                const productCollection = row.querySelector('td:nth-child(3) .text-sm').textContent;
                const productPrice = row.querySelector('td:nth-child(4)').innerHTML;
                const productStock = row.querySelector('td:nth-child(5)').innerHTML;
                const productStatus = row.querySelector('td:nth-child(6) span').outerHTML;
                const stockStatus = row.dataset.stockStatus;
                
                // Create a card for mobile view
                const card = document.createElement('div');
                card.className = `mobile-inventory-card bg-white rounded-lg shadow p-4 ${row.classList.contains('hidden') ? 'hidden' : ''}`;
                card.dataset.productName = row.dataset.productName;
                card.dataset.category = row.dataset.category;
                card.dataset.stockStatus = stockStatus;
                
                // Card content
                card.innerHTML = `
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12 bg-gray-200 rounded-md flex items-center justify-center mr-3">
                                ${productImage ? `<img src="${productImage}" alt="${productName}" class="h-12 w-12 object-cover rounded-md">` : 
                                `<svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>`}
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-900">${productName}</h3>
                                <div class="text-xs text-gray-500">ID: ${productId}</div>
                            </div>
                        </div>
                        <div>
                            ${productStatus}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <div class="text-xs text-gray-500">Category</div>
                            <div class="text-sm">${productCategory}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Collection</div>
                            <div class="text-sm">${productCollection}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Price</div>
                            <div class="text-sm">${productPrice}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Stock</div>
                            <div class="text-sm">${productStock}</div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" 
                            class="update-stock-btn-mobile inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            data-product-id="${productId}"
                            data-product-name="${productName}">
                            Update Stock
                        </button>
                    </div>
                `;
                
                // Add the card to the container
                inventoryCardsContainer.appendChild(card);
            });
            
            // Add event listeners to mobile update buttons
            const mobileUpdateButtons = document.querySelectorAll('.update-stock-btn-mobile');
            mobileUpdateButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    
                    const modalProductName = document.getElementById('modal-product-name');
                    const modalProductId = document.getElementById('modal-product-id');
                    
                    modalProductName.textContent = `Update stock quantities for ${productName}`;
                    modalProductId.value = productId;
                    
                    // Fetch current stock quantities
                    fetchProductStock(productId);
                    
                    document.getElementById('update-stock-modal').classList.remove('hidden');
                });
            });
        }
        
        // Reset filters button
        const resetFiltersButton = document.getElementById('reset-filters');
        if (resetFiltersButton) {
            resetFiltersButton.addEventListener('click', function() {
                // Reset search input
                searchInput.value = '';
                
                // Reset filter dropdowns
                stockFilter.value = 'all';
                categoryFilter.value = 'all';
                
                // Apply the reset filters
                filterInventory();
            });
        }
        
        function filterInventory() {
            const searchTerm = searchInput.value.toLowerCase();
            const stockFilterValue = stockFilter.value;
            const categoryFilterValue = categoryFilter.value.toLowerCase();
            
            let visibleCount = 0;
            
            // Filter table rows
            inventoryRows.forEach(row => {
                const productName = row.dataset.productName;
                const stockStatus = row.dataset.stockStatus;
                const category = row.dataset.category;
                
                let showRow = true;
                
                // Apply search filter
                if (searchTerm && !productName.includes(searchTerm)) {
                    showRow = false;
                }
                
                // Apply stock filter
                if (stockFilterValue !== 'all' && stockStatus !== stockFilterValue) {
                    showRow = false;
                }
                
                // Apply category filter
                if (categoryFilterValue !== 'all' && category !== categoryFilterValue) {
                    showRow = false;
                }
                
                if (showRow) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });
            
            // Filter mobile cards
            const mobileCards = document.querySelectorAll('.mobile-inventory-card');
            mobileCards.forEach(card => {
                const productName = card.dataset.productName;
                const stockStatus = card.dataset.stockStatus;
                const category = card.dataset.category;
                
                let showCard = true;
                
                // Apply search filter
                if (searchTerm && !productName.includes(searchTerm)) {
                    showCard = false;
                }
                
                // Apply stock filter
                if (stockFilterValue !== 'all' && stockStatus !== stockFilterValue) {
                    showCard = false;
                }
                
                // Apply category filter
                if (categoryFilterValue !== 'all' && category !== categoryFilterValue) {
                    showCard = false;
                }
                
                if (showCard) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
            
            // Show/hide empty state
            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }
        
        // Update Stock Modal
        const updateStockButtons = document.querySelectorAll('.update-stock-btn');
        const updateStockModal = document.getElementById('update-stock-modal');
        const cancelUpdateButton = document.getElementById('cancel-update');
        const mobileCloseButton = document.getElementById('mobile-close-modal');
        const modalProductName = document.getElementById('modal-product-name');
        const modalProductId = document.getElementById('modal-product-id');
        
        updateStockButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                
                modalProductName.textContent = `Update stock quantities for ${productName}`;
                modalProductId.value = productId;
                
                // Fetch current stock quantities
                fetchProductStock(productId);
                
                updateStockModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden'); // Prevent background scrolling
            });
        });
        
        // Close modal handlers
        function closeModal() {
            updateStockModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden'); // Re-enable scrolling
        }
        
        cancelUpdateButton.addEventListener('click', closeModal);
        
        // Mobile close button
        if (mobileCloseButton) {
            mobileCloseButton.addEventListener('click', closeModal);
        }
        
        // Close modal when clicking outside
        updateStockModal.addEventListener('click', function(e) {
            if (e.target === updateStockModal) {
                closeModal();
            }
        });
        
        // Calculate total stock in modal
        const stockInputs = document.querySelectorAll('input[name^="stock_"]');
        const stockSyncInputs = document.querySelectorAll('.stock-sync');
        const modalTotalStock = document.getElementById('modal-total-stock');
        
        // Sync between mobile and desktop inputs
        stockSyncInputs.forEach(input => {
            input.addEventListener('input', function() {
                const size = this.dataset.size;
                const mobileInput = document.getElementById('stock_' + size);
                if (mobileInput) {
                    mobileInput.value = this.value;
                }
                calculateTotalStock();
            });
        });
        
        stockInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Extract size from input ID (e.g., stock_XS -> XS)
                const size = this.id.replace('stock_', '');
                const desktopInput = document.getElementById('stock_desktop_' + size);
                if (desktopInput) {
                    desktopInput.value = this.value;
                }
                calculateTotalStock();
            });
        });
        
        function calculateTotalStock() {
            let total = 0;
            stockInputs.forEach(input => {
                total += parseInt(input.value) || 0;
            });
            modalTotalStock.textContent = total;
        }
        
        // Toggle stock status section based on show_stock checkbox
        const showStockCheckbox = document.getElementById('show_stock');
        const stockStatusSection = document.getElementById('stockStatusSection');
        
        showStockCheckbox.addEventListener('change', function() {
            if (this.checked) {
                stockStatusSection.classList.add('hidden');
            } else {
                stockStatusSection.classList.remove('hidden');
            }
        });
        
        // Initial check
        if (showStockCheckbox.checked) {
            stockStatusSection.classList.add('hidden');
        }
        
        // Fetch product stock from server
        function fetchProductStock(productId) {
            // In a real implementation, this would be an AJAX call to get the current stock
            // For now, we'll simulate it with random values
            const sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
            let total = 0;
            
            sizes.forEach(size => {
                // This would be replaced with actual data from the server
                const stock = Math.floor(Math.random() * 10);
                
                // Update mobile input
                const mobileInput = document.getElementById('stock_' + size);
                if (mobileInput) {
                    mobileInput.value = stock;
                }
                
                // Update desktop input
                const desktopInput = document.getElementById('stock_desktop_' + size);
                if (desktopInput) {
                    desktopInput.value = stock;
                }
                
                total += stock;
            });
            
            modalTotalStock.textContent = total;
            
            // Also set the show_stock and stock_status values based on product settings
            // This would also come from the server in a real implementation
            showStockCheckbox.checked = Math.random() > 0.5;
            document.getElementById('stock_status').value = Math.random() > 0.5 ? 'in_stock' : 'low_stock';
            
            // Update visibility of stock status section
            if (showStockCheckbox.checked) {
                stockStatusSection.classList.add('hidden');
            } else {
                stockStatusSection.classList.remove('hidden');
            }
        }
    });
    </script>
    
    <!-- Sidebar and Filter Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Print Stock Report Button
            const printStockReportBtn = document.getElementById('print-stock-report');
            if (printStockReportBtn) {
                printStockReportBtn.addEventListener('click', function() {
                    window.location.href = 'print-stock-report.php';
                });
            }
            
            // Sidebar toggle functionality
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('open');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('open');
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) { // Only on mobile
                    const isClickInsideSidebar = sidebar && sidebar.contains(event.target);
                    const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickOnToggle && sidebar && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('open');
                    }
                }
            });
            
            // Filter toggle functionality for mobile
            const filterToggle = document.getElementById('filter-toggle');
            const filterOptions = document.getElementById('filter-options');
            const filterChevronDown = document.getElementById('filter-chevron-down');
            const filterChevronUp = document.getElementById('filter-chevron-up');
            
            if (filterToggle && filterOptions) {
                filterToggle.addEventListener('click', function() {
                    filterOptions.classList.toggle('hidden');
                    filterChevronDown.classList.toggle('hidden');
                    filterChevronUp.classList.toggle('hidden');
                });
                
                // Show filter options by default on larger screens
                function handleResize() {
                    if (window.innerWidth >= 640) { // sm breakpoint
                        filterOptions.classList.remove('hidden');
                    } else {
                        filterOptions.classList.add('hidden');
                        filterChevronDown.classList.remove('hidden');
                        filterChevronUp.classList.add('hidden');
                    }
                }
                
                // Initial check and listen for window resize
                handleResize();
                window.addEventListener('resize', handleResize);
            }
        });
        
        // Add active class to current menu item
        const currentPage = '<?php echo $currentPage; ?>';
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            if (item.getAttribute('href') && item.getAttribute('href').includes(currentPage)) {
                item.classList.add('active');
            }
        });
    </script>
</body>
</html>