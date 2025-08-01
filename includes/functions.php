<?php
// Prevent double inclusion
if (defined('FUNCTIONS_LOADED')) {
    return;
}
define('FUNCTIONS_LOADED', true);

// Start output buffering to prevent any output before JSON
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function require_admin_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: /admin/index.php');
        exit();
    }
}

// Get site setting
function getSiteSetting($key, $default = '') {
    static $cachedSettings = [];
    
    if (isset($cachedSettings[$key])) {
        return $cachedSettings[$key];
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    $value = $result ? $result['setting_value'] : $default;
    
    $cachedSettings[$key] = $value;
    return $value;
}

// Check if maintenance mode is enabled
function isMaintenanceMode() {
    return getSiteSetting('maintenance_mode', '0') === '1';
}

// Check if current user can bypass maintenance mode
function canBypassMaintenance() {
    $currentPath = $_SERVER['REQUEST_URI'] ?? '';
    
    // Allow admin access
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
        return true;
    }
    
    // Allow access to admin pages
    if (strpos($currentPath, '/admin/') !== false || strpos($currentPath, 'admin/') !== false) {
        return true;
    }
    
    // Allow access to maintenance page itself
    if (strpos($currentPath, 'maintenance.php') !== false) {
        return true;
    }
    
    return false;
}

// Redirect to maintenance page if needed
function checkMaintenanceMode() {
    if (isMaintenanceMode() && !canBypassMaintenance()) {
        // Set maintenance mode session flag
        $_SESSION['maintenance_mode'] = true;
        
        // Get current URL for redirect after password entry
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Get the base path (remove filename from path)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);
        if ($basePath === '.') {
            $basePath = '';
        }
        
        $maintenanceUrl = $basePath . '/maintenance.php?redirect=' . urlencode($currentUrl);
        header('Location: ' . $maintenanceUrl);
        exit();
    }
}

// Get all categories
function getCategories() {
    static $cachedCategories = null;
    
    if ($cachedCategories !== null) {
        return $cachedCategories;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $cachedCategories = $stmt->fetchAll();
    
    return $cachedCategories;
}

// Get all collections (for frontend - excludes secret collections)
function getCollections() {
    static $cachedCollections = null;
    
    if ($cachedCollections !== null) {
        return $cachedCollections;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE is_active = 1 AND secret = 0 ORDER BY sort_order ASC");
    $stmt->execute();
    $cachedCollections = $stmt->fetchAll();
    
    return $cachedCollections;
}

// Get all collections for admin (includes secret collections)
function getAllCollectionsForAdmin() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM collections ORDER BY sort_order ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get secret collections only
function getSecretCollections() {
    static $cachedSecretCollections = null;
    
    if ($cachedSecretCollections !== null) {
        return $cachedSecretCollections;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE is_active = 1 AND secret = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $cachedSecretCollections = $stmt->fetchAll();
    
    return $cachedSecretCollections;
}

// Get secret categories only
function getSecretCategories() {
    static $cachedSecretCategories = null;
    
    if ($cachedSecretCategories !== null) {
        return $cachedSecretCategories;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT DISTINCT c.* FROM categories c 
                           INNER JOIN products p ON c.id = p.category_id 
                           INNER JOIN collections col ON p.collection_id = col.id 
                           WHERE c.is_active = 1 AND col.secret = 1 
                           ORDER BY c.sort_order ASC");
    $stmt->execute();
    $cachedSecretCategories = $stmt->fetchAll();
    
    return $cachedSecretCategories;
}

// Get secret products
function getSecretProducts($filters = []) {
    $pdo = getDBConnection();
    
    $sql = "SELECT p.*, c.name as category_name, col.name as collection_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN collections col ON p.collection_id = col.id 
            WHERE p.is_active = 1 AND col.secret = 1";
    
    $params = [];
    
    // Category filter - handle both single value and array
    if (!empty($filters['category'])) {
        if (is_array($filters['category'])) {
            $placeholders = str_repeat('?,', count($filters['category']) - 1) . '?';
            $sql .= " AND p.category_id IN ($placeholders)";
            $params = array_merge($params, $filters['category']);
        } else {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category'];
        }
    }
    
    // Collection filter - handle both single value and array
    if (!empty($filters['collection'])) {
        if (is_array($filters['collection'])) {
            $placeholders = str_repeat('?,', count($filters['collection']) - 1) . '?';
            $sql .= " AND p.collection_id IN ($placeholders)";
            $params = array_merge($params, $filters['collection']);
        } else {
            $sql .= " AND p.collection_id = ?";
            $params[] = $filters['collection'];
        }
    }
    
    if (!empty($filters['featured'])) {
        $sql .= " AND p.is_featured = 1";
    }
    
    if (!empty($filters['bestseller'])) {
        $sql .= " AND p.is_bestseller = 1";
    }
    
    if (!empty($filters['on_sale'])) {
        $sql .= " AND p.is_on_sale = 1";
    }
    
    // Search filter
    if (!empty($filters['search'])) {
        $searchTerm = '%' . $filters['search'] . '%';
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ? OR col.name LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Size filter
    if (!empty($filters['size'])) {
        $sizes = is_array($filters['size']) ? $filters['size'] : [$filters['size']];
        $sizePlaceholders = str_repeat('?,', count($sizes) - 1) . '?';
        $sql .= " AND EXISTS (SELECT 1 FROM product_sizes ps WHERE ps.product_id = p.id AND ps.size IN ($sizePlaceholders))";
        $params = array_merge($params, $sizes);
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    // Limit filter
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . (int)$filters['limit'];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Calculate total price for each product
    foreach ($products as &$product) {
        $product['total_price'] = ($product['sale_price'] ?? $product['price']) * 1; // Default quantity 1
    }
    
    return $products;
}

// Get products with filters
function getProducts($filters = []) {
    $pdo = getDBConnection();
    
    $sql = "SELECT p.*, c.name as category_name, col.name as collection_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN collections col ON p.collection_id = col.id 
            WHERE p.is_active = 1 AND (col.secret = 0 OR col.secret IS NULL)";
    
    $params = [];
    
    // Category filter - handle both single value and array
    if (!empty($filters['category'])) {
        if (is_array($filters['category'])) {
            $placeholders = str_repeat('?,', count($filters['category']) - 1) . '?';
            $sql .= " AND p.category_id IN ($placeholders)";
            $params = array_merge($params, $filters['category']);
        } else {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category'];
        }
    }
    
    // Collection filter - handle both single value and array
    if (!empty($filters['collection'])) {
        if (is_array($filters['collection'])) {
            $placeholders = str_repeat('?,', count($filters['collection']) - 1) . '?';
            $sql .= " AND p.collection_id IN ($placeholders)";
            $params = array_merge($params, $filters['collection']);
        } else {
            $sql .= " AND p.collection_id = ?";
            $params[] = $filters['collection'];
        }
    }
    
    if (!empty($filters['featured'])) {
        $sql .= " AND p.is_featured = 1";
    }
    
    if (!empty($filters['bestseller'])) {
        $sql .= " AND p.is_bestseller = 1";
    }
    
    if (!empty($filters['on_sale'])) {
        $sql .= " AND p.is_on_sale = 1";
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Size filter - handle both single value and array
    if (!empty($filters['size'])) {
        if (is_array($filters['size'])) {
            $placeholders = str_repeat('?,', count($filters['size']) - 1) . '?';
            $sql .= " AND EXISTS (
                SELECT 1 FROM product_sizes ps 
                WHERE ps.product_id = p.id 
                AND LOWER(ps.size) IN ($placeholders)
                AND ps.stock_quantity > 0
            )";
            $sizeParams = array_map('strtolower', $filters['size']);
            $params = array_merge($params, $sizeParams);
        } else {
            $sql .= " AND EXISTS (
                SELECT 1 FROM product_sizes ps 
                WHERE ps.product_id = p.id 
                AND LOWER(ps.size) = ? 
                AND ps.stock_quantity > 0
            )";
            $params[] = strtolower($filters['size']);
        }
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . (int)$filters['limit'];
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get single product by ID
function getProduct($id) {
    static $cachedProducts = [];
    
    if (isset($cachedProducts[$id])) {
        return $cachedProducts[$id];
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, col.name as collection_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN collections col ON p.collection_id = col.id 
                          WHERE p.id = ? AND p.is_active = 1");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    $cachedProducts[$id] = $product;
    return $product;
}

// Get product images
function getProductImages($productId) {
    static $cachedProductImages = [];
    
    if (isset($cachedProductImages[$productId])) {
        return $cachedProductImages[$productId];
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();
    
    $cachedProductImages[$productId] = $images;
    return $images;
}

// Get product sizes and stock
function getProductSizes($productId) {
    static $cachedSizes = [];
    
    if (isset($cachedSizes[$productId])) {
        return $cachedSizes[$productId];
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM product_sizes WHERE product_id = ? AND stock_quantity > 0 ORDER BY FIELD(size, 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL')");
    $stmt->execute([$productId]);
    $sizes = $stmt->fetchAll();
    
    $cachedSizes[$productId] = $sizes;
    return $sizes;
}

// Get social media links
function getSocialMedia() {
    static $cachedSocialMedia = null;
    
    if ($cachedSocialMedia !== null) {
        return $cachedSocialMedia;
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM social_media WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $cachedSocialMedia = $stmt->fetchAll();
    
    return $cachedSocialMedia;
}

// Get footer sections and links
function getFooterData() {
    static $cachedFooterData = null;
    
    if ($cachedFooterData !== null) {
        return $cachedFooterData;
    }
    
    $pdo = getDBConnection();
    
    // Get sections
    $stmt = $pdo->prepare("SELECT * FROM footer_sections WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $sections = $stmt->fetchAll();
    
    // Get links for each section
    foreach ($sections as &$section) {
        $stmt = $pdo->prepare("SELECT * FROM footer_links WHERE section_id = ? AND is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute([$section['id']]);
        $section['links'] = $stmt->fetchAll();
    }
    
    $cachedFooterData = $sections;
    return $cachedFooterData;
}

// Get video section data
function getVideoSection() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM video_section WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    return $stmt->fetch();
}

// Cart functions
function addToCart($productId, $size, $quantity = 1) {
    try {
        $pdo = getDBConnection();
        $sessionId = session_id();
        
        // Check if item already exists in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_id = ? AND size = ?");
        $stmt->execute([$sessionId, $productId, $size]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newQuantity, $existing['id']]);
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO cart_items (session_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sessionId, $productId, $size, $quantity]);
        }
        
        return true;
    } catch (Exception $e) {
        throw $e;
    }
}

function getCartItems() {
    $pdo = getDBConnection();
    $sessionId = session_id();
    
    $stmt = $pdo->prepare("SELECT ci.*, p.name, p.price, p.sale_price, p.color 
                          FROM cart_items ci 
                          JOIN products p ON ci.product_id = p.id 
                          WHERE ci.session_id = ?");
    $stmt->execute([$sessionId]);
    $items = $stmt->fetchAll();
    
    // Add formatted price and image to each item
    foreach ($items as &$item) {
        $item['price_formatted'] = formatPrice($item['sale_price'] ?? $item['price']);
        $item['total_price'] = ($item['sale_price'] ?? $item['price']) * $item['quantity'];
        $item['image'] = getProductImage($item['product_id']);
    }
    
    return $items;
}

function updateCartItem($cartItemId, $quantity) {
    $pdo = getDBConnection();
    $sessionId = session_id();
    
    if ($quantity <= 0) {
        // Remove item
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
        $stmt->execute([$cartItemId, $sessionId]);
    } else {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ? AND session_id = ?");
        $stmt->execute([$quantity, $cartItemId, $sessionId]);
    }
    
    return true;
}

function clearCart() {
    $pdo = getDBConnection();
    $sessionId = session_id();
    
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    
    return true;
}

// Wishlist functions (temporarily hidden via CSS - code preserved for future use)
function addToWishlist($productId) {
    $pdo = getDBConnection();
    $sessionId = session_id();
    
    // Check if already in wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist_items WHERE session_id = ? AND product_id = ?");
    $stmt->execute([$sessionId, $productId]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO wishlist_items (session_id, product_id) VALUES (?, ?)");
        $stmt->execute([$sessionId, $productId]);
    }
    
    return true;
}

function removeFromWishlist($productId) {
    $pdo = getDBConnection();
    $sessionId = session_id();
    
    $stmt = $pdo->prepare("DELETE FROM wishlist_items WHERE session_id = ? AND product_id = ?");
    $stmt->execute([$sessionId, $productId]);
    
    return true;
}

function getWishlistItems() {
    $pdo = getDBConnection();
    $sessionId = session_id();
    
    $stmt = $pdo->prepare("SELECT wi.*, p.name, p.price, p.sale_price, p.color 
                          FROM wishlist_items wi 
                          JOIN products p ON wi.product_id = p.id 
                          WHERE wi.session_id = ?");
    $stmt->execute([$sessionId]);
    return $stmt->fetchAll();
}

function isInWishlist($productId) {
    $pdo = getDBConnection();
    $sessionId = session_id();
    
    $stmt = $pdo->prepare("SELECT id FROM wishlist_items WHERE session_id = ? AND product_id = ?");
    $stmt->execute([$sessionId, $productId]);
    
    return $stmt->fetch() ? true : false;
}

// Utility functions
function formatPrice($price) {
    return number_format($price, 3) . ' DTN';
}

function getProductImage($productId, $primary = true) {
    static $cachedImages = [];
    
    $cacheKey = $productId . '_' . ($primary ? 'primary' : 'all');
    
    if (isset($cachedImages[$cacheKey])) {
        return $cachedImages[$cacheKey];
    }
    
    $pdo = getDBConnection();
    $sql = "SELECT image_path FROM product_images WHERE product_id = ?";
    if ($primary) {
        $sql .= " AND is_primary = 1";
    }
    $sql .= " ORDER BY is_primary DESC, sort_order ASC LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    $result = $stmt->fetch();
    
    $imagePath = $result ? $result['image_path'] : 'images/placeholder.jpg';
    $cachedImages[$cacheKey] = $imagePath;
    
    return $imagePath;
}

function getProductImagesForSlider($productId) {
    static $cachedSliderImages = [];
    
    if (isset($cachedSliderImages[$productId])) {
        return $cachedSliderImages[$productId];
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 2");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();
    
    $cachedSliderImages[$productId] = $images;
    return $images;
}

// Get related products based on category and collection
function getRelatedProducts($currentProductId, $categoryId, $collectionId, $limit = 4) {
    $pdo = getDBConnection();
    
    // Build query to get products from same category or collection, excluding current product
    $sql = "SELECT p.*, c.name as category_name, col.name as collection_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN collections col ON p.collection_id = col.id 
            WHERE p.is_active = 1 AND p.id != ? AND (col.secret = 0 OR col.secret IS NULL)";
    
    $params = [$currentProductId];
    
    // Add category and collection conditions
    $conditions = [];
    if ($categoryId) {
        $conditions[] = "p.category_id = ?";
        $params[] = $categoryId;
    }
    if ($collectionId) {
        $conditions[] = "p.collection_id = ?";
        $params[] = $collectionId;
    }
    
    // If we have conditions, add them to the query
    if (!empty($conditions)) {
        $sql .= " AND (" . implode(" OR ", $conditions) . ")";
    }
    
    $sql .= " ORDER BY p.is_featured DESC, p.is_bestseller DESC, p.created_at DESC LIMIT " . (int)$limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $relatedProducts = $stmt->fetchAll();
    
    // If we don't have enough related products, fill with other products
    if (count($relatedProducts) < $limit) {
        $remaining = $limit - count($relatedProducts);
        $existingIds = array_merge([$currentProductId], array_column($relatedProducts, 'id'));
        
        if (!empty($existingIds)) {
            $placeholders = str_repeat('?,', count($existingIds) - 1) . '?';
            
            $sql = "SELECT p.*, c.name as category_name, col.name as collection_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN collections col ON p.collection_id = col.id 
                    WHERE p.is_active = 1 AND p.id NOT IN ($placeholders) AND (col.secret = 0 OR col.secret IS NULL)
                    ORDER BY p.is_featured DESC, p.is_bestseller DESC, p.created_at DESC 
                    LIMIT " . (int)$remaining;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($existingIds);
            $additionalProducts = $stmt->fetchAll();
            
            $relatedProducts = array_merge($relatedProducts, $additionalProducts);
        }
    }
    
    return array_slice($relatedProducts, 0, $limit);
}

// AJAX handlers moved to ajax_handler.php for better session management

// Generate unique order number
function generateOrderNumber() {
    $prefix = 'BEAM';
    $timestamp = date('YmdHis');
    $random = strtoupper(substr(md5(uniqid()), 0, 4));
    return $prefix . $timestamp . $random;
}

// Get order number by order ID
function getOrderNumber($orderId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT order_number FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $result = $stmt->fetch();
    return $result ? $result['order_number'] : 'Unknown';
}

/**
 * Create a new order with transaction handling and retry logic
 * @param array $orderData Order data array
 * @return string Order number
 * @throws Exception If order creation fails after retries
 */
function createOrder($orderData) {
    $maxRetries = 3;
    $retryCount = 0;
    $lastException = null;
    
    while ($retryCount < $maxRetries) {
        $pdo = getDBConnection();
        
        try {
            // Set transaction isolation level and lock wait timeout (5 seconds)
            $pdo->exec("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
            $pdo->exec("SET SESSION innodb_lock_wait_timeout = 5");
            
            $pdo->beginTransaction();
            
            try {
                // Create order
                $orderNumber = generateOrderNumber();
                $stmt = $pdo->prepare("
                    INSERT INTO orders (
                        order_number, session_id, customer_name, customer_email, customer_phone,
                        shipping_address, shipping_city, shipping_postal_code, shipping_notes,
                        subtotal, tax, shipping_cost, discount, promo_code_id, promo_code, total, payment_method, notes,
                        created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                $stmt->execute([
                    $orderNumber,
                    session_id(),
                    $orderData['customer_name'],
                    $orderData['customer_email'],
                    $orderData['customer_phone'],
                    $orderData['shipping_address'],
                    $orderData['shipping_city'],
                    $orderData['shipping_postal_code'],
                    $orderData['shipping_notes'],
                    $orderData['subtotal'],
                    $orderData['tax'],
                    $orderData['shipping_cost'],
                    $orderData['discount'] ?? 0,
                    $orderData['promo_code_id'] ?? null,
                    $orderData['promo_code'] ?? null,
                    $orderData['total'],
                    'cash_on_delivery',
                    $orderData['notes'] ?? null
                ]);
                
                $orderId = $pdo->lastInsertId();
                
                // Get cart items and create order items (batch insert for better performance)
                $cartItems = getCartItems();
                $orderItemValues = [];
                $placeholders = [];
                $insertData = [];
                
                foreach ($cartItems as $item) {
                    $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
                    $insertData = array_merge($insertData, [
                        $orderId,
                        $item['product_id'],
                        $item['name'],
                        $item['price'],
                        $item['sale_price'] ?? null,
                        $item['size'],
                        $item['quantity'],
                        $item['total_price']
                    ]);
                }
                
                if (!empty($cartItems)) {
                    $sql = "INSERT INTO order_items (
                        order_id, product_id, product_name, product_price, product_sale_price,
                        size, quantity, total_price
                    ) VALUES " . implode(', ', $placeholders);
                    
                    $pdo->prepare($sql)->execute($insertData);
                }
                
                // Record promo code usage if applied (outside transaction if possible)
                if (!empty($orderData['promo_code_id'])) {
                    // Store promo code data to use after commit
                    $promoData = [
                        'promo_code_id' => $orderData['promo_code_id'],
                        'discount' => $orderData['discount'],
                        'order_id' => $orderId
                    ];
                }
                
                $pdo->commit();
                
                // Clear cart and record promo usage after successful commit
                clearCart();
                removePromoCode();
                
                if (isset($promoData)) {
                    try {
                        recordPromoCodeUsage(
                            $promoData['promo_code_id'], 
                            $promoData['discount'], 
                            $promoData['order_id']
                        );
                    } catch (Exception $e) {
                        // Log but don't fail the order if promo code recording fails
                        error_log("Failed to record promo code usage: " . $e->getMessage());
                    }
                }
                
                return $orderNumber;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            
        } catch (PDOException $e) {
            $lastException = $e;
            $retryCount++;
            
            // Log the error for debugging
            error_log(sprintf(
                "Order creation attempt %d failed: %s",
                $retryCount,
                $e->getMessage()
            ));
            
            // Exponential backoff before retry
            if ($retryCount < $maxRetries) {
                usleep(100000 * $retryCount); // 100ms, 200ms, 300ms
            }
            
        } finally {
            // Ensure connection is closed
            $pdo = null;
        }
    }
    
    // If we get here, all retries failed
    throw new Exception("Failed to create order after $maxRetries attempts: " . $lastException->getMessage(), 0, $lastException);
}

// Get order by order number
function getOrder($orderNumber) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM orders WHERE order_number = ? AND session_id = ?
    ");
    $stmt->execute([$orderNumber, session_id()]);
    return $stmt->fetch();
}

// Get order items
function getOrderItems($orderId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.color 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    // Add product images to each item
    foreach ($items as &$item) {
        if ($item['product_id']) {
            $item['image'] = getProductImage($item['product_id']);
        } else {
            $item['image'] = 'images/placeholder.jpg';
        }
    }
    
    return $items;
}

// Get Tunisia cities/governorates
function getTunisiaCities() {
    return [
        'Tunis', 'Sfax', 'Sousse', 'Kairouan', 'Bizerte', 'Gabès', 'Ariana', 'Gafsa', 'Monastir', 'La Marsa',
        'Ben Arous', 'Nabeul', 'Hammamet', 'Tozeur', 'Mahdia', 'Zaghouan', 'Siliana', 'Le Kef', 'Jendouba', 'Béja',
        'Kasserine', 'Sidi Bouzid', 'Kebili', 'Tataouine', 'Médenine', 'Tataouine', 'Zarzis', 'Djerba', 'Tabarka', 'Hammam-Lif'
    ];
}

// Get about page content by section and key
function getAboutContent($section, $key = null) {
    static $cachedContent = [];
    
    $cacheKey = $section . ($key ? '_' . $key : '');
    
    if (isset($cachedContent[$cacheKey])) {
        return $cachedContent[$cacheKey];
    }
    
    $pdo = getDBConnection();
    
    if ($key) {
        // Get specific content
        $stmt = $pdo->prepare("SELECT content_value, content_type FROM aboutus WHERE section_name = ? AND content_key = ? AND is_active = 1");
        $stmt->execute([$section, $key]);
        $result = $stmt->fetch();
        
        if ($result) {
            $cachedContent[$cacheKey] = $result['content_value'];
            return $result['content_value'];
        }
        return '';
    } else {
        // Get all content for a section
        $stmt = $pdo->prepare("SELECT content_key, content_value, content_type FROM aboutus WHERE section_name = ? AND is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute([$section]);
        $results = $stmt->fetchAll();
        
        $sectionContent = [];
        foreach ($results as $row) {
            $sectionContent[$row['content_key']] = $row['content_value'];
        }
        
        $cachedContent[$cacheKey] = $sectionContent;
        return $sectionContent;
    }
}

// Get all about page content organized by sections
function getAllAboutContent($editable = false) {
    static $cachedAllContent = [];
    
    $cacheKey = $editable ? 'editable' : 'regular';
    
    if (isset($cachedAllContent[$cacheKey])) {
        return $cachedAllContent[$cacheKey];
    }
    
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT section_name, content_key, content_value, content_type, sort_order FROM aboutus WHERE is_active = 1 ORDER BY section_name, sort_order ASC");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    $allContent = [];
    foreach ($results as $row) {
        if (!isset($allContent[$row['section_name']])) {
            $allContent[$row['section_name']] = [];
        }
        
        // For editable mode, we store the full content information
        if ($editable) {
            if (!isset($allContent[$row['section_name']][$row['content_key']])) {
                $allContent[$row['section_name']][$row['content_key']] = $row['content_value'];
            }
        } else {
            $allContent[$row['section_name']][$row['content_key']] = $row['content_value'];
        }
    }
    
    $cachedAllContent[$cacheKey] = $allContent;
    return $allContent;
}

// Update about page content
function updateAboutContent($section, $key, $value) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE aboutus SET content_value = ?, updated_at = CURRENT_TIMESTAMP WHERE section_name = ? AND content_key = ?");
    $result = $stmt->execute([$value, $section, $key]);
    
    // Clear cache
    static $cachedContent = [];
    $cacheKey = $section . '_' . $key;
    unset($cachedContent[$cacheKey]);
    unset($cachedContent[$section]);
    
    return $result;
}

// Insert new about page content
function insertAboutContent($section, $key, $type, $value, $sortOrder = 0) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO aboutus (section_name, content_key, content_type, content_value, sort_order) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), content_type = VALUES(content_type), sort_order = VALUES(sort_order), updated_at = CURRENT_TIMESTAMP");
    $result = $stmt->execute([$section, $key, $type, $value, $sortOrder]);
    
    // Clear cache
    static $cachedContent = [];
    $cacheKey = $section . '_' . $key;
    unset($cachedContent[$cacheKey]);
    unset($cachedContent[$section]);
    
    return $result;
}

// ==================== PROMO CODE FUNCTIONS ====================

/**
 * Get promo code by code
 */
function getPromoCode($code) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = 1");
    $stmt->execute([strtoupper($code)]);
    return $stmt->fetch();
}

/**
 * Validate promo code
 */
function validatePromoCode($code, $cartItems = [], $subtotal = 0) {
    $promoCode = getPromoCode($code);
    
    if (!$promoCode) {
        return ['valid' => false, 'message' => 'Invalid promo code'];
    }
    
    // Check if code is expired
    if ($promoCode['end_date'] && strtotime($promoCode['end_date']) < time()) {
        return ['valid' => false, 'message' => 'Promo code has expired'];
    }
    
    // Check if code hasn't started yet
    if ($promoCode['start_date'] && strtotime($promoCode['start_date']) > time()) {
        return ['valid' => false, 'message' => 'Promo code is not active yet'];
    }
    
    // Check usage limit
    if ($promoCode['usage_limit'] && $promoCode['used_count'] >= $promoCode['usage_limit']) {
        return ['valid' => false, 'message' => 'Promo code usage limit reached'];
    }
    
    // Check minimum order amount
    if ($promoCode['min_order_amount'] > $subtotal) {
        return ['valid' => false, 'message' => 'Minimum order amount not met'];
    }
    
    // Check if user has already used this code (for single use codes)
    if ($promoCode['is_single_use']) {
        $sessionId = session_id();
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM promo_code_usage WHERE promo_code_id = ? AND session_id = ?");
        $stmt->execute([$promoCode['id'], $sessionId]);
        if ($stmt->fetchColumn() > 0) {
            return ['valid' => false, 'message' => 'You have already used this promo code'];
        }
    }
    
    // Check if code applies to cart items
    if ($promoCode['applies_to'] !== 'all') {
        $applies = false;
        foreach ($cartItems as $item) {
            if ($promoCode['applies_to'] === 'categories') {
                $categoryIds = json_decode($promoCode['category_ids'], true);
                if (in_array($item['category_id'], $categoryIds)) {
                    $applies = true;
                    break;
                }
            } elseif ($promoCode['applies_to'] === 'products') {
                $productIds = json_decode($promoCode['product_ids'], true);
                if (in_array($item['product_id'], $productIds)) {
                    $applies = true;
                    break;
                }
            }
        }
        
        if (!$applies) {
            return ['valid' => false, 'message' => 'Promo code does not apply to items in your cart'];
        }
    }
    
    return ['valid' => true, 'promo_code' => $promoCode];
}

/**
 * Calculate discount amount
 */
function calculateDiscount($promoCode, $subtotal) {
    $discount = 0;
    
    switch ($promoCode['type']) {
        case 'percentage':
            $discount = $subtotal * ($promoCode['value'] / 100);
            if ($promoCode['max_discount']) {
                $discount = min($discount, $promoCode['max_discount']);
            }
            break;
            
        case 'fixed_amount':
            $discount = $promoCode['value'];
            break;
            
        case 'free_shipping':
            $discount = 0; // Will be handled separately
            break;
    }
    
    return $discount;
}

/**
 * Apply promo code to cart
 */
function applyPromoCode($code, $cartItems = []) {
    error_log("applyPromoCode called with code: " . $code);
    $subtotal = array_sum(array_column($cartItems, 'total_price'));
    error_log("Subtotal calculated: " . $subtotal);
    
    $validation = validatePromoCode($code, $cartItems, $subtotal);
    error_log("Validation result: " . json_encode($validation));
    
    if (!$validation['valid']) {
        return $validation;
    }
    
    $promoCode = $validation['promo_code'];
    $discount = calculateDiscount($promoCode, $subtotal);
    error_log("Discount calculated: " . $discount);
    
    // Store promo code in session
    $_SESSION['applied_promo_code'] = [
        'id' => $promoCode['id'],
        'code' => $promoCode['code'],
        'name' => $promoCode['name'],
        'type' => $promoCode['type'],
        'value' => $promoCode['value'],
        'discount_amount' => $discount,
        'max_discount' => $promoCode['max_discount']
    ];
    
    error_log("Promo code stored in session: " . json_encode($_SESSION['applied_promo_code']));
    
    return [
        'valid' => true,
        'promo_code' => $promoCode,
        'discount_amount' => $discount,
        'message' => 'Promo code applied successfully!'
    ];
}

/**
 * Remove promo code from cart
 */
function removePromoCode() {
    unset($_SESSION['applied_promo_code']);
    return ['valid' => true, 'message' => 'Promo code removed'];
}

/**
 * Get applied promo code
 */
function getAppliedPromoCode() {
    return $_SESSION['applied_promo_code'] ?? null;
}

/**
 * Record promo code usage
 */
function recordPromoCodeUsage($promoCodeId, $discountAmount, $orderId = null) {
    $pdo = getDBConnection();
    
    // Update usage count
    $stmt = $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?");
    $stmt->execute([$promoCodeId]);
    
    // Record usage
    $stmt = $pdo->prepare("INSERT INTO promo_code_usage (promo_code_id, session_id, order_id, discount_amount) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$promoCodeId, session_id(), $orderId, $discountAmount]);
}

/**
 * Get all active promo codes (for admin)
 */
function getAllPromoCodes() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM promo_codes ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Create new promo code
 */
function createPromoCode($data) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        INSERT INTO promo_codes (
            code, name, description, type, value, min_order_amount, 
            max_discount, usage_limit, user_limit, applies_to, 
            category_ids, product_ids, excluded_categories, excluded_products,
            start_date, end_date, is_active, is_first_time_only, is_single_use
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([
        strtoupper($data['code']),
        $data['name'],
        $data['description'],
        $data['type'],
        $data['value'],
        $data['min_order_amount'],
        $data['max_discount'],
        $data['usage_limit'],
        $data['user_limit'],
        $data['applies_to'],
        $data['category_ids'],
        $data['product_ids'],
        $data['excluded_categories'],
        $data['excluded_products'],
        $data['start_date'],
        $data['end_date'],
        $data['is_active'],
        $data['is_first_time_only'],
        $data['is_single_use']
    ]);
}

/**
 * Update promo code
 */
function updatePromoCode($id, $data) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        UPDATE promo_codes SET 
            code = ?, name = ?, description = ?, type = ?, value = ?, 
            min_order_amount = ?, max_discount = ?, usage_limit = ?, 
            user_limit = ?, applies_to = ?, category_ids = ?, product_ids = ?, 
            excluded_categories = ?, excluded_products = ?, start_date = ?, 
            end_date = ?, is_active = ?, is_first_time_only = ?, is_single_use = ?
        WHERE id = ?
    ");
    
    return $stmt->execute([
        strtoupper($data['code']),
        $data['name'],
        $data['description'],
        $data['type'],
        $data['value'],
        $data['min_order_amount'],
        $data['max_discount'],
        $data['usage_limit'],
        $data['user_limit'],
        $data['applies_to'],
        $data['category_ids'],
        $data['product_ids'],
        $data['excluded_categories'],
        $data['excluded_products'],
        $data['start_date'],
        $data['end_date'],
        $data['is_active'],
        $data['is_first_time_only'],
        $data['is_single_use'],
        $id
    ]);
}

/**
 * Delete promo code
 */
function deletePromoCode($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
    return $stmt->execute([$id]);
}

// Password session management functions
function createPasswordSession($passwordId, $sessionId, $userIp = null, $userAgent = null, $expiresAt = null) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO password_sessions (password_id, session_id, user_ip, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$passwordId, $sessionId, $userIp, $userAgent, $expiresAt]);
}

function getPasswordSession($sessionId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT ps.*, p.password, p.name as password_name FROM password_sessions ps 
                           INNER JOIN passwords p ON ps.password_id = p.id 
                           WHERE ps.session_id = ? AND ps.is_active = 1 
                           AND (ps.expires_at IS NULL OR ps.expires_at > NOW())");
    $stmt->execute([$sessionId]);
    return $stmt->fetch();
}

function deactivatePasswordSession($sessionId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE password_sessions SET is_active = 0 WHERE session_id = ?");
    return $stmt->execute([$sessionId]);
}

function deactivateAllPasswordSessions() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE password_sessions SET is_active = 0");
    return $stmt->execute();
}

function deactivatePasswordSessionsByPasswordId($passwordId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE password_sessions SET is_active = 0 WHERE password_id = ?");
    return $stmt->execute([$passwordId]);
}

function getAllPasswordSessions() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT ps.*, p.password, p.name as password_name 
                           FROM password_sessions ps 
                           INNER JOIN passwords p ON ps.password_id = p.id 
                           ORDER BY ps.accessed_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getActivePasswordSessions() {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT ps.*, p.password, p.name as password_name 
                           FROM password_sessions ps 
                           INNER JOIN passwords p ON ps.password_id = p.id 
                           WHERE ps.is_active = 1 
                           AND (ps.expires_at IS NULL OR ps.expires_at > NOW())
                           ORDER BY ps.accessed_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function checkPasswordSession($sessionId) {
    $session = getPasswordSession($sessionId);
    return $session !== false;
}

function restorePasswordSessionByIp($userIp) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM password_sessions WHERE user_ip = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY accessed_at DESC LIMIT 1");
    $stmt->execute([$userIp]);
    $recentSession = $stmt->fetch();
    
    if ($recentSession) {
        // Update session ID to current one
        $stmt = $pdo->prepare("UPDATE password_sessions SET session_id = ? WHERE id = ?");
        $stmt->execute([session_id(), $recentSession['id']]);
        
        return $recentSession;
    }
    
    return false;
}

// First Delivery Group API Configuration
class FirstDeliveryAPI {
    private $baseUrl = 'https://www.firstdeliverygroup.com/api/v2';
    private $token;
    
    public function __construct($token) {
        $this->token = $token;
    }
    
    private function apiPost($endpoint, $data) {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->token,
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . $response);
        }
        
        return [
            'http_code' => $httpCode,
            'data' => $result
        ];
    }
    
    public function createOrder($orderData) {
        return $this->apiPost('/create', $orderData);
    }
    
    public function bulkCreateOrders($ordersData) {
        return $this->apiPost('/bulk-create', $ordersData);
    }
    
    public function checkOrderStatus($barCode) {
        return $this->apiPost('/etat', ['barCode' => $barCode]);
    }
    
    public function filterOrders($filterData) {
        return $this->apiPost('/filter', $filterData);
    }
    
    public function cancelOrders($barCodes) {
        return $this->apiPost('/cancel-orders', ['barCodes' => $barCodes]);
    }
}

// Function to prepare order data for First Delivery API
function prepareOrderForDelivery($order, $orderItems) {
    // Map Tunisian cities to governorates
    $cityToGovernorate = [
        'tunis' => 'tunis',
        'sousse' => 'sousse',
        'sfax' => 'sfax',
        'monastir' => 'monastir',
        'mahdia' => 'mahdia',
        'gafsa' => 'gafsa',
        'gabes' => 'gabes',
        'medenine' => 'medenine',
        'zarzis' => 'medenine',
        'djerba' => 'medenine',
        'kairouan' => 'kairouan',
        'kasserine' => 'kasserine',
        'beja' => 'beja',
        'jendouba' => 'jendouba',
        'le kef' => 'le kef',
        'siliana' => 'siliana',
        'zaghouan' => 'zaghouan',
        'nabeul' => 'nabeul',
        'hammamet' => 'nabeul',
        'bizerte' => 'bizerte',
        'beja' => 'beja',
        'jendouba' => 'jendouba',
        'le kef' => 'le kef',
        'siliana' => 'siliana',
        'zaghouan' => 'zaghouan',
        'nabeul' => 'nabeul',
        'hammamet' => 'nabeul',
        'bizerte' => 'bizerte'
    ];
    
    // Determine governorate from city
    $city = strtolower(trim($order['shipping_city']));
    $governorate = $cityToGovernorate[$city] ?? 'tunis'; // Default to Tunis if city not found
    
    // Prepare product description
    $productDescriptions = [];
    foreach ($orderItems as $item) {
        $price = $item['product_sale_price'] ?? $item['product_price'];
        $productDescriptions[] = $item['product_name'] . ' (Size: ' . $item['size'] . ', Qty: ' . $item['quantity'] . ')';
    }
    $productDescription = implode(' | ', $productDescriptions);
    
    // Calculate total price (excluding shipping cost for product price)
    $productPrice = $order['subtotal'] + $order['tax'];
    
    return [
        'Client' => [
            'nom' => $order['customer_name'],
            'gouvernerat' => $governorate,
            'ville' => $city,
            'adresse' => $order['shipping_address'],
            'telephone' => $order['customer_phone'],
            'telephone2' => '' // Optional second phone
        ],
        'Produit' => [
            'prix' => (float)$productPrice,
            'designation' => $productDescription,
            'nombreArticle' => 1, // Always 1 as we're sending one package
            'commentaire' => $order['shipping_notes'] ?? '',
            'article' => $order['order_number'], // Use order number as article reference
            'nombreEchange' => 0
        ]
    ];
}

// Function to send order to delivery platform
function sendOrderToDelivery($orderId) {
    try {
        $pdo = getDBConnection();
        
        // Get order details
        $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception("Order not found");
        }
        
        // Get order items
        $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemsStmt->execute([$orderId]);
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($orderItems)) {
            throw new Exception("No items found for order");
        }
        
        // Get API token from site settings
        $apiToken = getSiteSetting('first_delivery_token', '');
        if (empty($apiToken)) {
            throw new Exception("First Delivery API token not configured");
        }
        
        // Initialize API
        $api = new FirstDeliveryAPI($apiToken);
        
        // Prepare order data
        $orderData = prepareOrderForDelivery($order, $orderItems);
        
        // Send to delivery platform
        $response = $api->createOrder($orderData);
        
        if ($response['http_code'] === 201 && !$response['data']['isError']) {
            // Update order with delivery tracking info
            $barCode = $response['data']['result']['barCode'];
            $printLink = $response['data']['result']['link'];
            
            $updateStmt = $pdo->prepare("
                UPDATE orders 
                SET tracking_number = ?, admin_notes = CONCAT(COALESCE(admin_notes, ''), '\nDelivery Barcode: ', ?, '\nPrint Link: ', ?)
                WHERE id = ?
            ");
            $updateStmt->execute([$barCode, $barCode, $printLink, $orderId]);
            
            return [
                'success' => true,
                'barCode' => $barCode,
                'printLink' => $printLink,
                'message' => 'Order successfully sent to delivery platform'
            ];
        } else {
            throw new Exception("API Error: " . ($response['data']['message'] ?? 'Unknown error'));
        }
        
    } catch (Exception $e) {
        error_log("First Delivery API Error for Order #$orderId: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

?>