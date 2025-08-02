<?php
session_start();
require_once '../includes/functions.php';

$currentPage = 'products';
$pageTitle = 'Products';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new product
                $name = $_POST['name'] ?? '';
                $slug = createSlug($name);
                $description = $_POST['description'] ?? '';
                $shortDescription = $_POST['short_description'] ?? '';
                $price = (float)($_POST['price'] ?? 0);
                $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
                $costPrice = (float)($_POST['cost_price'] ?? 0);
                $color = $_POST['color'] ?? '';
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $collectionId = !empty($_POST['collection_id']) ? (int)$_POST['collection_id'] : null;
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
                $isOnSale = isset($_POST['is_on_sale']) ? 1 : 0;
                // For show_stock: checkbox unchecked = 0, checked = 1
                $showStock = isset($_POST['show_stock']) ? 1 : 0;
                $stockStatus = $_POST['stock_status'] ?? 'in_stock';
                
                $pdo = getDBConnection();
                
                // Handle image uploads
                $imagePaths = [];
                if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                    $uploadDir = '../images/products/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                            $fileExtension = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($fileExtension, $allowedExtensions)) {
                                $fileName = 'product_' . time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $fileExtension;
                                $uploadPath = $uploadDir . $fileName;
                                
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadPath)) {
                                    $imagePaths[] = 'images/products/' . $fileName;
                                }
                            }
                        }
                    }
                }
                
                // Insert product
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, short_description, price, sale_price, cost_price, color, category_id, collection_id, is_featured, is_bestseller, is_on_sale, show_stock, stock_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $shortDescription, $price, $salePrice, $costPrice, $color, $categoryId, $collectionId, $isFeatured, $isBestseller, $isOnSale, $showStock, $stockStatus]);
                
                $productId = $pdo->lastInsertId();
                
                // Insert product images
                if (!empty($imagePaths)) {
                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                    foreach ($imagePaths as $index => $imagePath) {
                        $isPrimary = $index === 0 ? 1 : 0;
                        $stmt->execute([$productId, $imagePath, $isPrimary, $index]);
                    }
                }
                
                // Insert product sizes
                $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
                $stmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size, stock_quantity) VALUES (?, ?, ?)");
                foreach ($sizes as $size) {
                    $stockQuantity = isset($_POST['stock_' . $size]) ? (int)$_POST['stock_' . $size] : 0;
                    $stmt->execute([$productId, $size, $stockQuantity]);
                }
                
                $successMessage = "Product added successfully!";
                break;
                
            case 'edit':
                // Edit product
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $slug = createSlug($name);
                $description = $_POST['description'] ?? '';
                $shortDescription = $_POST['short_description'] ?? '';
                $price = (float)($_POST['price'] ?? 0);
                $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
                $costPrice = (float)($_POST['cost_price'] ?? 0);
                $color = $_POST['color'] ?? '';
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
                $collectionId = !empty($_POST['collection_id']) ? (int)$_POST['collection_id'] : null;
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
                $isOnSale = isset($_POST['is_on_sale']) ? 1 : 0;
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                // For show_stock: checkbox unchecked = 0, checked = 1
                $showStock = isset($_POST['show_stock']) ? 1 : 0;
                $stockStatus = $_POST['stock_status'] ?? 'in_stock';
                
                $pdo = getDBConnection();
                
                // Handle image uploads
                $imagePaths = [];
                if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                    $uploadDir = '../images/products/';
                    
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                            $fileExtension = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($fileExtension, $allowedExtensions)) {
                                $fileName = 'product_' . $id . '_' . time() . '_' . $i . '.' . $fileExtension;
                                $uploadPath = $uploadDir . $fileName;
                                
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadPath)) {
                                    $imagePaths[] = 'images/products/' . $fileName;
                                }
                            }
                        }
                    }
                }
                
                // Update product
                $stmt = $pdo->prepare("UPDATE products SET name = ?, slug = ?, description = ?, short_description = ?, price = ?, sale_price = ?, cost_price = ?, color = ?, category_id = ?, collection_id = ?, is_featured = ?, is_bestseller = ?, is_on_sale = ?, is_active = ?, show_stock = ?, stock_status = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $shortDescription, $price, $salePrice, $costPrice, $color, $categoryId, $collectionId, $isFeatured, $isBestseller, $isOnSale, $isActive, $showStock, $stockStatus, $id]);
                
                // Add new images if uploaded
                if (!empty($imagePaths)) {
                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                    foreach ($imagePaths as $index => $imagePath) {
                        $isPrimary = 0; // New images are not primary by default
                        $sortOrder = 100 + $index; // High sort order to place at end
                        $stmt->execute([$id, $imagePath, $isPrimary, $sortOrder]);
                    }
                }
                
                // Update product sizes
                $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
                $stmt = $pdo->prepare("UPDATE product_sizes SET stock_quantity = ? WHERE product_id = ? AND size = ?");
                foreach ($sizes as $size) {
                    $stockQuantity = isset($_POST['stock_' . $size]) ? (int)$_POST['stock_' . $size] : 0;
                    $stmt->execute([$stockQuantity, $id, $size]);
                }
                
                $successMessage = "Product updated successfully!";
                break;
                
            case 'delete':
                // Delete product
                $id = $_POST['id'] ?? 0;
                
                $pdo = getDBConnection();
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
                
                $successMessage = "Product deleted successfully!";
                break;
        }
    }
}

// Get all products with category and collection names
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, col.name as collection_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN collections col ON p.collection_id = col.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$products = $stmt->fetchAll();

// Add primary image to each product
foreach ($products as &$product) {
    $productImages = getProductImages($product['id']);
    $product['primary_image'] = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
}

// Get categories and collections for dropdowns
$categories = getCategories();
$collections = getCollections();

// Helper function to create slug
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Products page specific styles */
        .product-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .modal {
            transition: all 0.3s ease;
        }
        
        .modal.show {
            opacity: 1;
            pointer-events: auto;
        }
        
        .modal-content {
            transform: scale(0.7);
            transition: all 0.3s ease;
        }
        
        .modal.show .modal-content {
            transform: scale(1);
        }
        
        .size-input {
            width: 60px;
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
        
        /* Mobile product card styles */
        .mobile-product-card {
            transition: all 0.2s ease;
        }
        
        .mobile-product-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        /* Ensure proper visibility on different screen sizes */
        @media (min-width: 1024px) {
            #mobileProducts {
                display: none !important;
            }
        }
        
        /* Mobile-specific styles */
        @media (max-width: 640px) {
            .stats-card {
                padding: 0.75rem;
            }
            
            .product-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-['Inter']">
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
                <!-- Success Message -->
                <?php if (isset($successMessage)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if (isset($errorMessage)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Header Section -->
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-4 lg:mb-6 space-y-4 lg:space-y-0">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Products</h1>
                        <p class="text-sm lg:text-base text-gray-600">Manage your product catalog</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="openAddModal()" class="bg-black text-white px-4 py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200 flex items-center justify-center space-x-2 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add Product</span>
                        </button>
                        <button onclick="toggleFilters()" class="bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center justify-center space-x-2 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span>Filters</span>
                        </button>
                    </div>
                </div>

                <!-- Search and Filters Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4 lg:mb-6">
                    <!-- Mobile: Collapsible Filters -->
                    <div class="lg:hidden">
                        <!-- Search Bar - Full Width on Mobile -->
                        <div class="relative mb-4">
                            <input type="text" id="searchInput" placeholder="Search products..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-base">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        
                        <!-- Mobile Filter Toggle -->
                        <button type="button" id="mobileFilterToggle" class="w-full bg-gray-100 text-gray-700 px-4 py-3 rounded-lg flex items-center justify-between hover:bg-gray-200 transition-colors mb-4">
                            <span class="font-medium">Advanced Filters</span>
                            <i class="fas fa-chevron-down transition-transform" id="mobileFilterIcon"></i>
                        </button>
                        
                        <!-- Mobile Filter Options -->
                        <div id="mobileFilterOptions" class="hidden space-y-4 p-4 bg-gray-50 rounded-lg">
                            <!-- Category Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="categoryFilter" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select id="statusFilter" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="featured">Featured</option>
                                    <option value="bestseller">Best Seller</option>
                                    <option value="sale">On Sale</option>
                                </select>
                            </div>
                            
                            <!-- Sort By -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sort by</label>
                                <select id="sortBy" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent text-base">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="name_asc">Name A-Z</option>
                                    <option value="name_desc">Name Z-A</option>
                                    <option value="price_asc">Price Low-High</option>
                                    <option value="price_desc">Price High-Low</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Desktop: Inline Filters -->
                    <div class="hidden lg:block">
                        <div class="grid grid-cols-4 gap-4">
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" id="searchInputDesktop" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            
                            <!-- Category Filter -->
                            <div>
                                <select id="categoryFilterDesktop" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Status Filter -->
                            <div>
                                <select id="statusFilterDesktop" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="featured">Featured</option>
                                    <option value="bestseller">Best Seller</option>
                                    <option value="sale">On Sale</option>
                                </select>
                            </div>
                            
                            <!-- Sort By -->
                            <div>
                                <select id="sortByDesktop" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="name_asc">Name A-Z</option>
                                    <option value="name_desc">Name Z-A</option>
                                    <option value="price_asc">Price Low-High</option>
                                    <option value="price_desc">Price High-Low</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Filters -->
                    <div id="activeFilters" class="mt-4 flex flex-wrap gap-2 hidden">
                        <span class="text-sm text-gray-600">Active filters:</span>
                        <button onclick="clearAllFilters()" class="text-sm text-red-600 hover:text-red-800">Clear All</button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-4 lg:mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="ml-3 lg:ml-4">
                                <p class="text-xs lg:text-sm font-medium text-gray-600">Total Products</p>
                                <p class="text-lg lg:text-2xl font-bold text-gray-900" id="totalProducts"><?php echo count($products); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 lg:ml-4">
                                <p class="text-xs lg:text-sm font-medium text-gray-600">Active</p>
                                <p class="text-lg lg:text-2xl font-bold text-gray-900" id="activeProducts"><?php echo count(array_filter($products, fn($p) => $p['is_active'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 lg:ml-4">
                                <p class="text-xs lg:text-sm font-medium text-gray-600">Featured</p>
                                <p class="text-lg lg:text-2xl font-bold text-gray-900" id="featuredProducts"><?php echo count(array_filter($products, fn($p) => $p['is_featured'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3 lg:ml-4">
                                <p class="text-xs lg:text-sm font-medium text-gray-600">On Sale</p>
                                <p class="text-lg lg:text-2xl font-bold text-gray-900" id="saleProducts"><?php echo count(array_filter($products, fn($p) => $p['is_on_sale'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Grid Header -->
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <h3 class="text-lg font-semibold text-gray-900">Products</h3>
                            <div class="flex items-center justify-between sm:justify-end space-x-4">
                                <span class="text-sm text-gray-600" id="resultsCount">Showing <?php echo count($products); ?> products</span>
                                <!-- View Toggle -->
                                <div class="flex items-center space-x-2">
                                    <button onclick="toggleViewMode('grid')" id="gridViewBtn" class="p-2 text-black bg-white border border-gray-300 rounded hover:bg-gray-50">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="toggleViewMode('list')" id="listViewBtn" class="p-2 text-gray-400 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products Container -->
                    <div id="productsContainer" class="p-4 lg:p-6">
                        <!-- Desktop Grid View -->
                        <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                            <?php foreach ($products as $product): 
                                $productImages = getProductImages($product['id']);
                                $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                            ?>
                            <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200" data-product='<?php echo htmlspecialchars(json_encode($product)); ?>'>
                                <div class="relative">
                                    <img src="../<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-40 lg:h-48 object-cover">
                                    <div class="absolute top-2 right-2 flex space-x-1">
                                        <?php if ($product['is_featured']): ?>
                                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">★</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_on_sale']): ?>
                                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">SALE</span>
                                        <?php endif; ?>
                                        <span class="w-2 h-2 rounded-full <?php echo $product['is_active'] ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                    </div>
                                </div>
                                
                                <div class="p-3 lg:p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="text-base lg:text-lg font-semibold text-gray-900 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                                        <span class="text-xs text-gray-500 ml-2">#<?php echo $product['id']; ?></span>
                                    </div>
                                    
                                    <div class="space-y-1 mb-3">
                                        <div class="flex justify-between text-xs lg:text-sm">
                                            <span class="text-gray-600">Price:</span>
                                            <span class="font-semibold"><?php echo formatPrice($product['price']); ?></span>
                                        </div>
                                        <?php if ($product['sale_price']): ?>
                                        <div class="flex justify-between text-xs lg:text-sm">
                                            <span class="text-gray-600">Sale:</span>
                                            <span class="font-semibold text-red-600"><?php echo formatPrice($product['sale_price']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex justify-between text-xs lg:text-sm">
                                            <span class="text-gray-600">Color:</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($product['color'] ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-1 mb-3">
                                        <?php if ($product['category_name']): ?>
                                        <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($product['collection_name']): ?>
                                        <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded"><?php echo htmlspecialchars($product['collection_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded text-xs lg:text-sm hover:bg-gray-200 transition-colors duration-200">
                                            Edit
                                        </button>
                                        <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded text-xs lg:text-sm hover:bg-red-200 transition-colors duration-200">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Mobile Product Cards (Hidden on Desktop) -->
                        <div id="mobileProducts" class="block lg:hidden space-y-3">
                            <?php foreach ($products as $product): 
                                $productImages = getProductImages($product['id']);
                                $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                            ?>
                            <div class="mobile-product-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" data-product='<?php echo htmlspecialchars(json_encode($product)); ?>'>
                                <div class="flex">
                                    <!-- Product Image -->
                                    <div class="relative w-24 h-24 flex-shrink-0">
                                        <img src="../<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                                        <div class="absolute top-1 right-1 flex space-x-1">
                                            <?php if ($product['is_featured']): ?>
                                            <span class="bg-blue-500 text-white text-xs px-1 py-0.5 rounded-full">★</span>
                                            <?php endif; ?>
                                            <?php if ($product['is_on_sale']): ?>
                                            <span class="bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">SALE</span>
                                            <?php endif; ?>
                                            <span class="w-1.5 h-1.5 rounded-full <?php echo $product['is_active'] ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Product Info -->
                                    <div class="flex-1 p-3">
                                        <div class="flex items-start justify-between mb-2">
                                            <h3 class="text-sm font-semibold text-gray-900 leading-tight"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <span class="text-xs text-gray-500 ml-2">#<?php echo $product['id']; ?></span>
                                        </div>
                                        
                                        <div class="space-y-1 mb-2">
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">Price:</span>
                                                <span class="font-semibold"><?php echo formatPrice($product['price']); ?></span>
                                            </div>
                                            <?php if ($product['sale_price']): ?>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">Sale:</span>
                                                <span class="font-semibold text-red-600"><?php echo formatPrice($product['sale_price']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <div class="flex justify-between text-xs">
                                                <span class="text-gray-600">Color:</span>
                                                <span class="font-medium"><?php echo htmlspecialchars($product['color'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-1 mb-3">
                                            <?php if ($product['category_name']): ?>
                                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($product['collection_name']): ?>
                                            <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded"><?php echo htmlspecialchars($product['collection_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="flex-1 bg-gray-100 text-gray-700 px-2 py-1.5 rounded text-xs hover:bg-gray-200 transition-colors duration-200">
                                                Edit
                                            </button>
                                            <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="flex-1 bg-red-100 text-red-700 px-2 py-1.5 rounded text-xs hover:bg-red-200 transition-colors duration-200">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Desktop List View (Hidden by default) -->
                        <div id="productsList" class="hidden space-y-4">
                            <?php foreach ($products as $product): 
                                $productImages = getProductImages($product['id']);
                                $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                            ?>
                            <div class="product-list-item bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200" data-product='<?php echo htmlspecialchars(json_encode($product)); ?>'>
                                <div class="flex items-center space-x-4">
                                    <img src="../<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-16 h-16 object-cover rounded">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-gray-500">#<?php echo $product['id']; ?></span>
                                                <span class="w-2 h-2 rounded-full <?php echo $product['is_active'] ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                                            <span>Price: <?php echo formatPrice($product['price']); ?></span>
                                            <?php if ($product['sale_price']): ?>
                                            <span class="text-red-600">Sale: <?php echo formatPrice($product['sale_price']); ?></span>
                                            <?php endif; ?>
                                            <span>Color: <?php echo htmlspecialchars($product['color'] ?? 'N/A'); ?></span>
                                            <?php if ($product['category_name']): ?>
                                            <span>Category: <?php echo htmlspecialchars($product['category_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <?php if ($product['is_featured']): ?>
                                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Featured</span>
                                            <?php endif; ?>
                                            <?php if ($product['is_bestseller']): ?>
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Best Seller</span>
                                            <?php endif; ?>
                                            <?php if ($product['is_on_sale']): ?>
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">On Sale</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" class="bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-200 transition-colors duration-200">
                                            Edit
                                        </button>
                                        <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="bg-red-100 text-red-700 px-3 py-2 rounded text-sm hover:bg-red-200 transition-colors duration-200">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <?php if (empty($products)): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No products yet</h3>
                    <p class="text-gray-600 mb-4">Get started by creating your first product</p>
                    <button onclick="openAddModal()" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors duration-200">
                        Add Product
                    </button>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div id="addModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-5xl w-full mx-4 max-h-[95vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Add New Product</h3>
                        <p class="text-sm text-gray-600 mt-1">Create a new product for your catalog</p>
                    </div>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="addProductForm">
                    <input type="hidden" name="action" value="add">
                    
                    <!-- Progress Steps -->
                    <div class="mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-black text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                                <span class="ml-2 text-sm font-medium text-gray-900">Basic Info</span>
                            </div>
                            <div class="flex-1 h-px bg-gray-300"></div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                                <span class="ml-2 text-sm font-medium text-gray-500">Pricing & Categories</span>
                            </div>
                            <div class="flex-1 h-px bg-gray-300"></div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                                <span class="ml-2 text-sm font-medium text-gray-500">Images & Stock</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 1: Basic Information -->
                    <div id="step1" class="step-content">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-blue-800">Basic Product Information</span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Product Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" required 
                                           placeholder="Enter product name..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                    <p class="text-xs text-gray-500 mt-1">This will be displayed to customers</p>
                                </div>
                                
                                <div>
                                    <label for="short_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                                    <textarea id="short_description" name="short_description" rows="3" 
                                              placeholder="Brief description for product cards and previews..."
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200"></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Keep it concise for product cards</p>
                                </div>
                                
                                <div>
                                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                    <div class="relative">
                                        <input type="text" id="color" name="color" 
                                               placeholder="e.g., Red, Blue, Black..."
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <div class="w-4 h-4 rounded-full bg-gray-300" id="colorPreview"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                                    <textarea id="description" name="description" rows="8" 
                                              placeholder="Detailed product description with features, materials, care instructions..."
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200"></textarea>
                                    <p class="text-xs text-gray-500 mt-1">This will be shown on the product detail page</p>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_active" name="is_active" checked 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="is_active" class="ml-2 text-sm text-gray-700">Active Product</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_featured" name="is_featured" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="is_featured" class="ml-2 text-sm text-gray-700">Featured</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="button" onclick="nextStep(2)" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors duration-200 flex items-center">
                                Next Step
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 2: Pricing and Categories -->
                    <div id="step2" class="step-content hidden">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                <span class="text-sm font-medium text-green-800">Pricing & Categorization</span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                            Regular Price <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" id="price" name="price" step="0.01" required 
                                                   placeholder="0.00"
                                                   class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">TND</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price</label>
                                        <div class="relative">
                                            <input type="number" id="sale_price" name="sale_price" step="0.01" 
                                                   placeholder="0.00"
                                                   class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">TND</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-1">
                                        Cost Price <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" id="cost_price" name="cost_price" step="0.01" required 
                                               placeholder="0.00"
                                               class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">TND</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Your purchase cost for profit calculation</p>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_on_sale" name="is_on_sale" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="is_on_sale" class="ml-2 text-sm text-gray-700">On Sale</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_bestseller" name="is_bestseller" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="is_bestseller" class="ml-2 text-sm text-gray-700">Best Seller</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select id="category_id" name="category_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="collection_id" class="block text-sm font-medium text-gray-700 mb-1">Collection</label>
                                    <select id="collection_id" name="collection_id" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                        <option value="">Select Collection</option>
                                        <?php foreach ($collections as $collection): ?>
                                        <option value="<?php echo $collection['id']; ?>"><?php echo htmlspecialchars($collection['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-800">Pricing Tips</p>
                                            <p class="text-xs text-yellow-700 mt-1">Set sale price lower than regular price to enable discounts. Cost price helps calculate profit margins.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between mt-6">
                            <button type="button" onclick="prevStep(1)" class="px-6 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Previous
                            </button>
                            <button type="button" onclick="nextStep(3)" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors duration-200 flex items-center">
                                Next Step
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Step 3: Images and Stock -->
                    <div id="step3" class="step-content hidden">
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium text-purple-800">Images & Inventory</span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <?php include 'advanced_image_uploader.php'; ?>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantities</label>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-7 gap-3">
                                        <?php 
                                        $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
                                        foreach ($sizes as $size): 
                                        ?>
                                        <div class="text-center">
                                            <label for="stock_<?php echo $size; ?>" class="block text-xs font-medium text-gray-700 mb-1"><?php echo $size; ?></label>
                                            <input type="number" id="stock_<?php echo $size; ?>" name="stock_<?php echo $size; ?>" min="0" value="0" 
                                                   class="w-full px-2 py-2 border border-gray-300 rounded text-center focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Total Stock:</span>
                                        <span class="font-medium" id="totalStock">0</span>
                                    </div>
                                </div>
                                
                                <!-- Stock Display Options -->
                                <div class="mt-6 space-y-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="show_stock" name="show_stock" checked
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="show_stock" class="ml-2 text-sm font-medium text-gray-700">Show Stock Numbers</label>
                                    </div>
                                    <p class="text-xs text-gray-500">When enabled, customers can see exact stock quantities. When disabled, they'll see general stock status.</p>
                                    
                                    <div id="stockStatusSection" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status (when stock numbers are hidden)</label>
                                        <select id="stock_status" name="stock_status" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <option value="in_stock">In Stock</option>
                                            <option value="low_stock">Low Stock</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">This status will be shown to customers when stock numbers are hidden</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between mt-6">
                            <button type="button" onclick="prevStep(2)" class="px-6 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Previous
                            </button>
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Create Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div id="editModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-5xl w-full mx-4 max-h-[95vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Edit Product</h3>
                        <p class="text-sm text-gray-600 mt-1">Update product information and settings</p>
                    </div>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form method="POST" enctype="multipart/form-data" id="editProductForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <!-- Product Status Banner -->
                    <div id="editStatusBanner" class="mb-6 p-4 rounded-lg border">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div id="editStatusIndicator" class="w-3 h-3 rounded-full mr-3"></div>
                                <span id="editStatusText" class="text-sm font-medium"></span>
                            </div>
                            <button type="button" id="editToggleStatus" class="text-sm px-3 py-1 rounded-full border transition-colors duration-200">
                                Toggle Status
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Basic Information -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-800 mb-4">Basic Information</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">
                                            Product Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="edit_name" name="name" required 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                    </div>
                                    
                                    <div>
                                        <label for="edit_short_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                                        <textarea id="edit_short_description" name="short_description" rows="3" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200"></textarea>
                                    </div>
                                    
                                    <div>
                                        <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                                        <textarea id="edit_description" name="description" rows="6" 
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200"></textarea>
                                    </div>
                                    
                                    <div>
                                        <label for="edit_color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                                        <div class="relative">
                                            <input type="text" id="edit_color" name="color" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <div class="w-4 h-4 rounded-full bg-gray-300" id="editColorPreview"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pricing Information -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-green-800 mb-4">Pricing & Categories</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="edit_price" class="block text-sm font-medium text-gray-700 mb-1">
                                            Regular Price <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" id="edit_price" name="price" step="0.01" required 
                                                   class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">TND</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="edit_sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price</label>
                                        <div class="relative">
                                            <input type="number" id="edit_sale_price" name="sale_price" step="0.01" 
                                                   class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">TND</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="edit_cost_price" class="block text-sm font-medium text-gray-700 mb-1">
                                            Cost Price <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" id="edit_cost_price" name="cost_price" step="0.01" required 
                                                   class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">TND</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="edit_category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                        <select id="edit_category_id" name="category_id" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="edit_collection_id" class="block text-sm font-medium text-gray-700 mb-1">Collection</label>
                                        <select id="edit_collection_id" name="collection_id" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <option value="">Select Collection</option>
                                            <?php foreach ($collections as $collection): ?>
                                            <option value="<?php echo $collection['id']; ?>"><?php echo htmlspecialchars($collection['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stock Management -->
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-purple-800 mb-4">Stock Management</h4>
                                <div class="grid grid-cols-7 gap-3 mb-4">
                                    <?php 
                                    $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
                                    foreach ($sizes as $size): 
                                    ?>
                                    <div class="text-center">
                                        <label for="edit_stock_<?php echo $size; ?>" class="block text-xs font-medium text-gray-700 mb-1"><?php echo $size; ?></label>
                                        <input type="number" id="edit_stock_<?php echo $size; ?>" name="stock_<?php echo $size; ?>" min="0" value="0" 
                                               class="w-full px-2 py-2 border border-gray-300 rounded text-center focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Total Stock:</span>
                                    <span class="font-medium" id="editTotalStock">0</span>
                                </div>
                                
                                <!-- Stock Display Options -->
                                <div class="mt-4 space-y-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="edit_show_stock" name="show_stock" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="edit_show_stock" class="ml-2 text-sm font-medium text-gray-700">Show Stock Numbers</label>
                                    </div>
                                    <p class="text-xs text-gray-500">When enabled, customers can see exact stock quantities. When disabled, they'll see general stock status.</p>
                                    
                                    <!-- Current Status Preview -->
                                    <div id="editStockStatusPreview" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-blue-800">Current Status Preview:</span>
                                            </div>
                                            <button type="button" onclick="updateEditStatusPreview()" class="text-xs text-blue-600 hover:text-blue-800 underline">
                                                Refresh
                                            </button>
                                        </div>
                                        <p class="text-xs text-blue-700 mt-1" id="editStatusPreviewText">
                                            Customers will see: Real stock numbers (exact quantities per size and total)
                                        </p>
                                    </div>
                                    
                                    <div id="editStockStatusSection" class="hidden">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stock Status (when stock numbers are hidden)</label>
                                        <select id="edit_stock_status" name="stock_status" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent transition-colors duration-200">
                                            <option value="in_stock">In Stock</option>
                                            <option value="low_stock">Low Stock</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">This status will be shown to customers when stock numbers are hidden</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sidebar -->
                        <div class="space-y-6">
                            <!-- Product Flags -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-800 mb-4">Product Settings</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="edit_is_active" name="is_active" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="edit_is_active" class="ml-2 text-sm text-gray-700">Active Product</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="edit_is_featured" name="is_featured" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="edit_is_featured" class="ml-2 text-sm text-gray-700">Featured</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="edit_is_bestseller" name="is_bestseller" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="edit_is_bestseller" class="ml-2 text-sm text-gray-700">Best Seller</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="edit_is_on_sale" name="is_on_sale" 
                                               class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black">
                                        <label for="edit_is_on_sale" class="ml-2 text-sm text-gray-700">On Sale</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Current Images -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-800 mb-4">Current Images</h4>
                                <div id="editCurrentImages" class="grid grid-cols-2 gap-2">
                                    <!-- Current images will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Add New Images -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-800 mb-4">Add New Images</h4>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-gray-400 transition-colors duration-200 relative">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <input type="file" name="images[]" multiple accept="image/*" 
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <p class="mt-2 text-xs text-gray-600">Click to add images</p>
                                </div>
                                <div id="editImagePreview" class="mt-4 grid grid-cols-2 gap-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()" class="px-6 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 pointer-events-none">
        <div class="modal-content bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Delete Product</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <p class="text-gray-600 mb-6">Are you sure you want to delete this product? This action cannot be undone.</p>
                
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_id" name="id">
                    
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                            Delete Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Global variables
        let allProducts = <?php echo json_encode($products); ?>;
        let filteredProducts = [...allProducts];
        let currentViewMode = 'grid';
        
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.add('show');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }
        
        function openEditModal(product) {
            // Get fresh product data from server to ensure we have the latest values
            fetch(`get_product.php?id=${product.id}`)
                .then(response => response.json())
                .then(freshProduct => {
                    console.log('Fresh product data:', freshProduct);
                    populateEditModal(freshProduct);
                })
                .catch(error => {
                    console.log('Error fetching fresh data, using provided data:', error);
                    populateEditModal(product);
                });
        }
        
        function populateEditModal(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description || '';
            document.getElementById('edit_short_description').value = product.short_description || '';
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_sale_price').value = product.sale_price || '';
            document.getElementById('edit_cost_price').value = product.cost_price;
            document.getElementById('edit_color').value = product.color || '';
            document.getElementById('edit_category_id').value = product.category_id || '';
            document.getElementById('edit_collection_id').value = product.collection_id || '';
            document.getElementById('edit_is_featured').checked = product.is_featured == 1;
            document.getElementById('edit_is_bestseller').checked = product.is_bestseller == 1;
            document.getElementById('edit_is_on_sale').checked = product.is_on_sale == 1;
            document.getElementById('edit_is_active').checked = product.is_active == 1;
            
            console.log('Product data for edit:', { 
                show_stock: product.show_stock, 
                stock_status: product.stock_status,
                show_stock_bool: product.show_stock == 1 
            });
            
            // Set stock display options
            const showStockCheckbox = document.getElementById('edit_show_stock');
            const stockStatusSelect = document.getElementById('edit_stock_status');
            
            showStockCheckbox.checked = product.show_stock == 1;
            stockStatusSelect.value = product.stock_status || 'in_stock';
            
            console.log('Form elements after setting:', {
                checkbox_checked: showStockCheckbox.checked,
                select_value: stockStatusSelect.value
            });
            
            // Update preview immediately after setting form values
            updateEditStatusPreview();
            
            // Toggle stock status section visibility
            const stockStatusSection = document.getElementById('editStockStatusSection');
            if (stockStatusSection) {
                if (product.show_stock == 1) {
                    stockStatusSection.classList.add('hidden');
                } else {
                    stockStatusSection.classList.remove('hidden');
                }
            }
            
            // Load stock quantities
            loadProductStock(product.id);
            
            document.getElementById('editModal').classList.add('show');
            
            // Update status preview after modal is shown
            setTimeout(() => {
                console.log('Updating preview after modal open...');
                updateEditStatusPreview();
            }, 100);
            
            // Also update after a longer delay to ensure everything is loaded
            setTimeout(() => {
                console.log('Final preview update...');
                updateEditStatusPreview();
            }, 500);
            
            // Update status banner
            updateEditStatusBanner(product.is_active == 1);
            
            // Load current images
            loadProductImages(product.id);
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        function deleteProduct(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        
        // Load product stock quantities
        function loadProductStock(productId) {
            // This would typically be an AJAX call to get current stock quantities
            // For now, we'll set default values
            const sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
            sizes.forEach(size => {
                const input = document.getElementById('edit_stock_' + size);
                if (input) {
                    input.value = '0'; // Default value, should be loaded from database
                }
            });
        }
        
        // Filter and search functionality
        function filterProducts() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const categoryFilter = document.getElementById('categoryFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const sortBy = document.getElementById('sortBy').value;
            
            // Filter products
            filteredProducts = allProducts.filter(product => {
                const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                                    (product.description && product.description.toLowerCase().includes(searchTerm)) ||
                                    (product.color && product.color.toLowerCase().includes(searchTerm));
                
                const matchesCategory = !categoryFilter || product.category_id == categoryFilter;
                
                let matchesStatus = true;
                if (statusFilter) {
                    switch(statusFilter) {
                        case 'active':
                            matchesStatus = product.is_active == 1;
                            break;
                        case 'inactive':
                            matchesStatus = product.is_active == 0;
                            break;
                        case 'featured':
                            matchesStatus = product.is_featured == 1;
                            break;
                        case 'bestseller':
                            matchesStatus = product.is_bestseller == 1;
                            break;
                        case 'sale':
                            matchesStatus = product.is_on_sale == 1;
                            break;
                    }
                }
                
                return matchesSearch && matchesCategory && matchesStatus;
            });
            
            // Sort products
            filteredProducts.sort((a, b) => {
                switch(sortBy) {
                    case 'newest':
                        return new Date(b.created_at) - new Date(a.created_at);
                    case 'oldest':
                        return new Date(a.created_at) - new Date(b.created_at);
                    case 'name_asc':
                        return a.name.localeCompare(b.name);
                    case 'name_desc':
                        return b.name.localeCompare(a.name);
                    case 'price_asc':
                        return parseFloat(a.price) - parseFloat(b.price);
                    case 'price_desc':
                        return parseFloat(b.price) - parseFloat(a.price);
                    default:
                        return 0;
                }
            });
            
            // Update display
            updateProductsDisplay();
            updateStats();
            updateActiveFilters();
        }
        
        // Update products display
        function updateProductsDisplay() {
            const gridContainer = document.getElementById('productsGrid');
            const listContainer = document.getElementById('productsList');
            const mobileContainer = document.getElementById('mobileProducts');
            const resultsCount = document.getElementById('resultsCount');
            
            // Update results count
            resultsCount.textContent = `Showing ${filteredProducts.length} products`;
            
            // Clear containers
            gridContainer.innerHTML = '';
            listContainer.innerHTML = '';
            mobileContainer.innerHTML = '';
            
            // Generate product HTML
            filteredProducts.forEach(product => {
                const productImages = product.primary_image || 'images/placeholder.jpg';
                
                // Grid view HTML
                const gridHTML = `
                    <div class="product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200" data-product='${JSON.stringify(product)}'>
                        <div class="relative">
                            <img src="../${productImages}" alt="${product.name}" class="w-full h-48 object-cover">
                            <div class="absolute top-2 right-2 flex space-x-1">
                                ${product.is_featured ? '<span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">★</span>' : ''}
                                ${product.is_on_sale ? '<span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">SALE</span>' : ''}
                                <span class="w-2 h-2 rounded-full ${product.is_active ? 'bg-green-500' : 'bg-red-500'}"></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">${product.name}</h3>
                                <span class="text-xs text-gray-500 ml-2">#${product.id}</span>
                            </div>
                            <div class="space-y-1 mb-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Price:</span>
                                    <span class="font-semibold">${formatPrice(product.price)}</span>
                                </div>
                                ${product.sale_price ? `
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Sale:</span>
                                    <span class="font-semibold text-red-600">${formatPrice(product.sale_price)}</span>
                                </div>
                                ` : ''}
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Color:</span>
                                    <span class="font-medium">${product.color || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1 mb-3">
                                ${product.category_name ? `<span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">${product.category_name}</span>` : ''}
                                ${product.collection_name ? `<span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded">${product.collection_name}</span>` : ''}
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditModal(${JSON.stringify(product)})" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-200 transition-colors duration-200">
                                    Edit
                                </button>
                                <button onclick="deleteProduct(${product.id})" class="flex-1 bg-red-100 text-red-700 px-3 py-2 rounded text-sm hover:bg-red-200 transition-colors duration-200">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                // List view HTML
                const listHTML = `
                    <div class="product-list-item bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200" data-product='${JSON.stringify(product)}'>
                        <div class="flex items-center space-x-4">
                            <img src="../${productImages}" alt="${product.name}" class="w-16 h-16 object-cover rounded">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">${product.name}</h3>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-500">#${product.id}</span>
                                        <span class="w-2 h-2 rounded-full ${product.is_active ? 'bg-green-500' : 'bg-red-500'}"></span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                                    <span>Price: ${formatPrice(product.price)}</span>
                                    ${product.sale_price ? `<span class="text-red-600">Sale: ${formatPrice(product.sale_price)}</span>` : ''}
                                    <span>Color: ${product.color || 'N/A'}</span>
                                    ${product.category_name ? `<span>Category: ${product.category_name}</span>` : ''}
                                </div>
                                <div class="flex items-center space-x-2 mt-2">
                                    ${product.is_featured ? '<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Featured</span>' : ''}
                                    ${product.is_bestseller ? '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Best Seller</span>' : ''}
                                    ${product.is_on_sale ? '<span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">On Sale</span>' : ''}
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="openEditModal(${JSON.stringify(product)})" class="bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-200 transition-colors duration-200">
                                    Edit
                                </button>
                                <button onclick="deleteProduct(${product.id})" class="bg-red-100 text-red-700 px-3 py-2 rounded text-sm hover:bg-red-200 transition-colors duration-200">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                // Mobile view HTML
                const mobileHTML = `
                    <div class="mobile-product-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" data-product='${JSON.stringify(product)}'>
                        <div class="flex">
                            <!-- Product Image -->
                            <div class="relative w-24 h-24 flex-shrink-0">
                                <img src="../${productImages}" alt="${product.name}" class="w-full h-full object-cover">
                                <div class="absolute top-1 right-1 flex space-x-1">
                                    ${product.is_featured ? '<span class="bg-blue-500 text-white text-xs px-1 py-0.5 rounded-full">★</span>' : ''}
                                    ${product.is_on_sale ? '<span class="bg-red-500 text-white text-xs px-1 py-0.5 rounded-full">SALE</span>' : ''}
                                    <span class="w-1.5 h-1.5 rounded-full ${product.is_active ? 'bg-green-500' : 'bg-red-500'}"></span>
                                </div>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="flex-1 p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-sm font-semibold text-gray-900 leading-tight">${product.name}</h3>
                                    <span class="text-xs text-gray-500 ml-2">#${product.id}</span>
                                </div>
                                
                                <div class="space-y-1 mb-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Price:</span>
                                        <span class="font-semibold">${formatPrice(product.price)}</span>
                                    </div>
                                    ${product.sale_price ? `
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Sale:</span>
                                        <span class="font-semibold text-red-600">${formatPrice(product.sale_price)}</span>
                                    </div>
                                    ` : ''}
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">Color:</span>
                                        <span class="font-medium">${product.color || 'N/A'}</span>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap gap-1 mb-3">
                                    ${product.category_name ? `<span class="bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded">${product.category_name}</span>` : ''}
                                    ${product.collection_name ? `<span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded">${product.collection_name}</span>` : ''}
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal(${JSON.stringify(product)})" class="flex-1 bg-gray-100 text-gray-700 px-2 py-1.5 rounded text-xs hover:bg-gray-200 transition-colors duration-200">
                                        Edit
                                    </button>
                                    <button onclick="deleteProduct(${product.id})" class="flex-1 bg-red-100 text-red-700 px-2 py-1.5 rounded text-xs hover:bg-red-200 transition-colors duration-200">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                gridContainer.insertAdjacentHTML('beforeend', gridHTML);
                listContainer.insertAdjacentHTML('beforeend', listHTML);
                mobileContainer.insertAdjacentHTML('beforeend', mobileHTML);
            });
        }
        
        // Update statistics
        function updateStats() {
            document.getElementById('totalProducts').textContent = allProducts.length;
            document.getElementById('activeProducts').textContent = allProducts.filter(p => p.is_active == 1).length;
            document.getElementById('featuredProducts').textContent = allProducts.filter(p => p.is_featured == 1).length;
            document.getElementById('saleProducts').textContent = allProducts.filter(p => p.is_on_sale == 1).length;
        }
        
        // Update active filters display
        function updateActiveFilters() {
            const activeFilters = document.getElementById('activeFilters');
            const searchTerm = document.getElementById('searchInput').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            const hasFilters = searchTerm || categoryFilter || statusFilter;
            
            if (hasFilters) {
                activeFilters.classList.remove('hidden');
            } else {
                activeFilters.classList.add('hidden');
            }
        }
        
        // Clear all filters
        function clearAllFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('sortBy').value = 'newest';
            filterProducts();
        }
        
        // Toggle view mode
        function toggleViewMode(mode) {
            currentViewMode = mode;
            const gridView = document.getElementById('productsGrid');
            const listView = document.getElementById('productsList');
            const mobileView = document.getElementById('mobileProducts');
            const gridBtn = document.getElementById('gridViewBtn');
            const listBtn = document.getElementById('listViewBtn');
            
            if (mode === 'grid') {
                // Show appropriate view based on screen size
                if (window.innerWidth < 1024) {
                    // Mobile: show mobile cards, hide others
                    mobileView.classList.remove('hidden');
                    listView.classList.add('hidden');
                    gridView.classList.add('hidden');
                } else {
                    // Desktop: show grid, hide others
                    gridView.classList.remove('hidden');
                    listView.classList.add('hidden');
                    mobileView.classList.add('hidden');
                }
                gridBtn.classList.remove('text-gray-400');
                gridBtn.classList.add('text-black');
                listBtn.classList.remove('text-black');
                listBtn.classList.add('text-gray-400');
            } else {
                // List view - only show on desktop
                if (window.innerWidth >= 1024) {
                    gridView.classList.add('hidden');
                    listView.classList.remove('hidden');
                    mobileView.classList.add('hidden');
                } else {
                    // On mobile, stay with mobile cards for list view
                    mobileView.classList.remove('hidden');
                    listView.classList.add('hidden');
                    gridView.classList.add('hidden');
                }
                listBtn.classList.remove('text-gray-400');
                listBtn.classList.add('text-black');
                gridBtn.classList.remove('text-black');
                gridBtn.classList.add('text-gray-400');
            }
        }
        
        // Toggle filters panel
        function toggleFilters() {
            const filtersBar = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-4.mb-6');
            filtersBar.classList.toggle('hidden');
        }
        
        // Format price helper function
        function formatPrice(price) {
            return parseFloat(price).toFixed(3) + ' TND';
        }
        
        // Step navigation functions
        function nextStep(step) {
            // Hide current step
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show next step
            document.getElementById('step' + step).classList.remove('hidden');
            
            // Update progress indicators
            updateProgressSteps(step);
        }
        
        function prevStep(step) {
            // Hide current step
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show previous step
            document.getElementById('step' + step).classList.remove('hidden');
            
            // Update progress indicators
            updateProgressSteps(step);
        }
        
        function updateProgressSteps(currentStep) {
            const steps = document.querySelectorAll('.flex.items-center');
            steps.forEach((step, index) => {
                const stepNumber = index + 1;
                const circle = step.querySelector('div');
                const text = step.querySelector('span');
                
                if (stepNumber < currentStep) {
                    // Completed step
                    circle.className = 'w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-medium';
                    text.className = 'ml-2 text-sm font-medium text-green-600';
                } else if (stepNumber === currentStep) {
                    // Current step
                    circle.className = 'w-8 h-8 bg-black text-white rounded-full flex items-center justify-center text-sm font-medium';
                    text.className = 'ml-2 text-sm font-medium text-gray-900';
                } else {
                    // Future step
                    circle.className = 'w-8 h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium';
                    text.className = 'ml-2 text-sm font-medium text-gray-500';
                }
            });
        }
        
        // Enhanced modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.add('show');
            // Reset form
            document.getElementById('addProductForm').reset();
            // Reset steps
            nextStep(1);
            // Reset progress
            updateProgressSteps(1);
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }
        

        
        function updateEditStatusBanner(isActive) {
            const banner = document.getElementById('editStatusBanner');
            const indicator = document.getElementById('editStatusIndicator');
            const text = document.getElementById('editStatusText');
            const toggleBtn = document.getElementById('editToggleStatus');
            
            if (isActive) {
                banner.className = 'mb-6 p-4 rounded-lg border border-green-200 bg-green-50';
                indicator.className = 'w-3 h-3 rounded-full mr-3 bg-green-500';
                text.textContent = 'Product is Active';
                text.className = 'text-sm font-medium text-green-800';
                toggleBtn.textContent = 'Deactivate';
                toggleBtn.className = 'text-sm px-3 py-1 rounded-full border border-red-300 text-red-700 hover:bg-red-50 transition-colors duration-200';
            } else {
                banner.className = 'mb-6 p-4 rounded-lg border border-red-200 bg-red-50';
                indicator.className = 'w-3 h-3 rounded-full mr-3 bg-red-500';
                text.textContent = 'Product is Inactive';
                text.className = 'text-sm font-medium text-red-800';
                toggleBtn.textContent = 'Activate';
                toggleBtn.className = 'text-sm px-3 py-1 rounded-full border border-green-300 text-green-700 hover:bg-green-50 transition-colors duration-200';
            }
        }
        
        // Toggle product status
        document.getElementById('editToggleStatus').addEventListener('click', function() {
            const isActiveCheckbox = document.getElementById('edit_is_active');
            isActiveCheckbox.checked = !isActiveCheckbox.checked;
            updateEditStatusBanner(isActiveCheckbox.checked);
        });
        
        // Load product images for edit modal
        function loadProductImages(productId) {
            const container = document.getElementById('editCurrentImages');
            container.innerHTML = '<div class="text-center p-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 mx-auto"></div><p class="text-xs text-gray-500 mt-2">Loading images...</p></div>';
            
            fetch(`get_product_images.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.images.length > 0) {
                        let imagesHTML = '';
                        data.images.forEach((image, index) => {
                            imagesHTML += `
                                <div class="relative group">
                                    <img src="${image.full_url}" alt="Product image ${index + 1}" 
                                         class="w-full h-24 object-cover rounded-lg border border-gray-200">
                                    <div class="absolute top-1 right-1 bg-black bg-opacity-50 text-white text-xs px-1 py-0.5 rounded">
                                        ${image.sort_order}
                                    </div>
                                    ${image.is_primary ? '<div class="absolute top-1 left-1 bg-yellow-500 text-white text-xs px-1 py-0.5 rounded">★</div>' : ''}
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <button onclick="deleteProductImage(${image.id}, ${productId})" 
                                                class="bg-red-500 text-white p-1 rounded-full hover:bg-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = imagesHTML;
                    } else {
                        container.innerHTML = `
                            <div class="text-center p-4 bg-gray-100 rounded">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-xs text-gray-500">No images found</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading product images:', error);
                    container.innerHTML = `
                        <div class="text-center p-4 bg-red-50 rounded">
                            <svg class="w-8 h-8 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <p class="text-xs text-red-500">Error loading images</p>
                        </div>
                    `;
                });
        }
        
        // Delete product image
        function deleteProductImage(imageId, productId) {
            if (confirm('Are you sure you want to delete this image?')) {
                fetch('delete_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image_id: imageId,
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadProductImages(productId); // Reload images
                    } else {
                        alert('Error deleting image: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting image:', error);
                    alert('Error deleting image');
                });
            }
        }
        
        // Enhanced stock loading with total calculation
        function loadProductStock(productId) {
            const sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
            
            fetch(`get_product_stock.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stock inputs
                        sizes.forEach(size => {
                            const input = document.getElementById('edit_stock_' + size);
                            if (input) {
                                input.value = data.stock[size] || 0;
                            }
                        });
                        
                        // Update total stock
                        document.getElementById('editTotalStock').textContent = data.total_stock;
                        
                        // Update stock settings
                        const showStockCheckbox = document.getElementById('edit_show_stock');
                        const stockStatusSelect = document.getElementById('edit_stock_status');
                        
                        if (showStockCheckbox) {
                            showStockCheckbox.checked = data.show_stock;
                        }
                        
                        if (stockStatusSelect) {
                            stockStatusSelect.value = data.stock_status;
                        }
                        
                        // Update status preview
                        updateEditStatusPreview();
                    } else {
                        console.error('Error loading stock data:', data.error);
                        // Fallback to default values
                        sizes.forEach(size => {
                            const input = document.getElementById('edit_stock_' + size);
                            if (input) {
                                input.value = 0;
                            }
                        });
                        document.getElementById('editTotalStock').textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error loading product stock:', error);
                    // Fallback to default values
                    sizes.forEach(size => {
                        const input = document.getElementById('edit_stock_' + size);
                        if (input) {
                            input.value = 0;
                        }
                    });
                    document.getElementById('editTotalStock').textContent = '0';
                });
        }
        
        // Calculate total stock for add modal
        function calculateTotalStock() {
            const sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
            let total = 0;
            
            sizes.forEach(size => {
                const input = document.getElementById('stock_' + size);
                if (input) {
                    total += parseInt(input.value) || 0;
                }
            });
            
            document.getElementById('totalStock').textContent = total;
        }
        
        // Color preview functionality
        function updateColorPreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            
            if (input && preview) {
                input.addEventListener('input', function() {
                    const color = this.value.toLowerCase();
                    const colorMap = {
                        'red': '#ef4444',
                        'blue': '#3b82f6',
                        'green': '#10b981',
                        'yellow': '#f59e0b',
                        'purple': '#8b5cf6',
                        'pink': '#ec4899',
                        'black': '#000000',
                        'white': '#ffffff',
                        'gray': '#6b7280',
                        'brown': '#a16207'
                    };
                    
                    preview.style.backgroundColor = colorMap[color] || '#d1d5db';
                });
            }
        }
        
        // Show stock toggle functionality
        function setupShowStockToggle(checkboxId, statusSectionId) {
            const checkbox = document.getElementById(checkboxId);
            const statusSection = document.getElementById(statusSectionId);
            
            if (checkbox && statusSection) {
                // Set initial state based on checkbox
                if (checkbox.checked) {
                    statusSection.classList.add('hidden');
                } else {
                    statusSection.classList.remove('hidden');
                }
                
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        statusSection.classList.add('hidden');
                    } else {
                        statusSection.classList.remove('hidden');
                    }
                    
                    // Update preview if this is the edit modal
                    if (checkboxId === 'edit_show_stock') {
                        updateEditStatusPreview();
                    }
                });
            }
        }
        
        // Update status preview in edit modal
        function updateEditStatusPreview() {
            const showStockCheckbox = document.getElementById('edit_show_stock');
            const stockStatusSelect = document.getElementById('edit_stock_status');
            const previewText = document.getElementById('editStatusPreviewText');
            
            if (!showStockCheckbox || !previewText) {
                console.log('Elements not found:', { showStockCheckbox: !!showStockCheckbox, previewText: !!previewText });
                return;
            }
            
            // Get the actual current values from the form elements
            const isShowStockEnabled = showStockCheckbox.checked;
            const currentStockStatus = stockStatusSelect ? stockStatusSelect.value : 'in_stock';
            
            console.log('updateEditStatusPreview called with:', { 
                isShowStockEnabled, 
                currentStockStatus,
                checkbox_checked: showStockCheckbox.checked,
                select_value: stockStatusSelect ? stockStatusSelect.value : 'undefined'
            });
            
            if (isShowStockEnabled) {
                previewText.textContent = "Customers will see: Real stock numbers (exact quantities per size and total)";
                console.log('Setting preview to: Real stock numbers');
            } else {
                let statusText = '';
                
                switch (currentStockStatus) {
                    case 'in_stock':
                        statusText = 'In Stock';
                        break;
                    case 'low_stock':
                        statusText = 'Low Stock';
                        break;
                    default:
                        statusText = 'In Stock';
                }
                
                const previewMessage = `Customers will see: ${statusText} (unless total stock is 0, then automatically shows "Out of Stock")`;
                previewText.textContent = previewMessage;
                console.log('Setting preview to:', previewMessage);
            }
        }
        
        // Ensure checkbox state is properly tracked in forms
        function setupStockCheckboxTracking() {
            const addForm = document.getElementById('addProductForm');
            const editForm = document.getElementById('editProductForm');
            
            if (addForm) {
                addForm.addEventListener('submit', function() {
                    const checkbox = document.getElementById('show_stock');
                    if (!checkbox.checked) {
                        // If checkbox is unchecked, add a hidden input to ensure the state is sent
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'show_stock_unchecked';
                        hiddenInput.value = '1';
                        addForm.appendChild(hiddenInput);
                    }
                });
            }
            
            if (editForm) {
                editForm.addEventListener('submit', function() {
                    const checkbox = document.getElementById('edit_show_stock');
                    if (!checkbox.checked) {
                        // If checkbox is unchecked, add a hidden input to ensure the state is sent
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'show_stock_unchecked';
                        hiddenInput.value = '1';
                        editForm.appendChild(hiddenInput);
                    }
                });
            }
        }
        
        // Advanced image uploader functionality
        function setupAdvancedImageUploader() {
            // The advanced image uploader is handled by the AdvancedImageUploader class
            // This function can be used for any additional setup if needed
            console.log('Advanced image uploader initialized');
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile sidebar functionality
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
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
            
            // Mobile filter toggle functionality
            const mobileFilterToggle = document.getElementById('mobileFilterToggle');
            const mobileFilterOptions = document.getElementById('mobileFilterOptions');
            const mobileFilterIcon = document.getElementById('mobileFilterIcon');
            
            if (mobileFilterToggle && mobileFilterOptions) {
                mobileFilterToggle.addEventListener('click', function() {
                    const isHidden = mobileFilterOptions.classList.contains('hidden');
                    
                    if (isHidden) {
                        mobileFilterOptions.classList.remove('hidden');
                        mobileFilterIcon.style.transform = 'rotate(180deg)';
                    } else {
                        mobileFilterOptions.classList.add('hidden');
                        mobileFilterIcon.style.transform = 'rotate(0deg)';
                    }
                });
            }
            
            // Search and filter event listeners
            document.getElementById('searchInput').addEventListener('input', debounce(filterProducts, 300));
            document.getElementById('categoryFilter').addEventListener('change', filterProducts);
            document.getElementById('statusFilter').addEventListener('change', filterProducts);
            document.getElementById('sortBy').addEventListener('change', filterProducts);
            
            // Stock calculation listeners
            const stockInputs = document.querySelectorAll('input[id^="stock_"], input[id^="edit_stock_"]');
            stockInputs.forEach(input => {
                input.addEventListener('input', calculateTotalStock);
            });
            
            // Color preview setup
            updateColorPreview('color', 'colorPreview');
            updateColorPreview('edit_color', 'editColorPreview');
            
            // Advanced image uploader setup
            setupAdvancedImageUploader();
            
            // Show stock checkbox functionality
            setupShowStockToggle('show_stock', 'stockStatusSection');
            setupShowStockToggle('edit_show_stock', 'editStockStatusSection');
            setupStockCheckboxTracking();
            
            // Add event listener for stock status dropdown in edit modal
            document.addEventListener('change', function(e) {
                if (e.target.id === 'edit_stock_status') {
                    updateEditStatusPreview();
                }
            });
            
            // Initialize
            updateStats();
        });
        
        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        });
        
        // Handle window resize for view switching
        window.addEventListener('resize', function() {
            if (currentViewMode === 'grid') {
                toggleViewMode('grid');
            } else if (currentViewMode === 'list') {
                toggleViewMode('list');
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html> 