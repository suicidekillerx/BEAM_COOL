<?php
// Ensure no whitespace before <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON headers immediately
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Include functions
require_once 'includes/functions.php';

// Handle AJAX requests
if ((isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_to_cart':
            $productId = (int)$_POST['product_id'];
            $size = $_POST['size'];
            $quantity = (int)$_POST['quantity'];
            
            try {
                if (addToCart($productId, $size, $quantity)) {
                    // Get auto_open_cart setting
                    $pdo = getDBConnection();
                    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'auto_open_cart'");
                    $stmt->execute();
                    $autoOpenCart = $stmt->fetchColumn();
                    
                    // If setting doesn't exist, default to true (auto open)
                    if ($autoOpenCart === false) {
                        $autoOpenCart = '1';
                    }
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Added to cart',
                        'auto_open_cart' => $autoOpenCart === '1'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            break;
            
        case 'add_to_wishlist':
            $productId = (int)$_POST['product_id'];
            
            if (addToWishlist($productId)) {
                echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
            }
            break;
            
        case 'remove_from_wishlist':
            $productId = (int)$_POST['product_id'];
            
            if (removeFromWishlist($productId)) {
                echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
            }
            break;
            
        case 'get_cart_items':
            $cartItems = getCartItems();
            echo json_encode(['success' => true, 'items' => $cartItems]);
            break;
            
        case 'get_wishlist_items':
            $wishlistItems = getWishlistItems();
            echo json_encode(['success' => true, 'items' => $wishlistItems]);
            break;
            
        case 'remove_from_cart':
            $cartItemId = (int)$_POST['cart_item_id'];
            
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
            if ($stmt->execute([$cartItemId, session_id()])) {
                echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
            }
            break;
            
        case 'update_cart':
            $cartItemId = (int)$_POST['cart_item_id'];
            $quantity = (int)$_POST['quantity'];
            
            if (updateCartItem($cartItemId, $quantity)) {
                echo json_encode(['success' => true, 'message' => 'Cart updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
            break;
            
        case 'live_search':
            $searchQuery = $_POST['query'] ?? '';
            $limit = 6; // Max 6 products
            
            if (!empty($searchQuery)) {
                $filters = ['search' => $searchQuery, 'limit' => $limit];
                $products = getProducts($filters);
                
                $html = '';
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $productImages = getProductImagesForSlider($product['id']);
                        $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                        
                        $html .= '<div class="search-result-item flex items-center p-3 hover:bg-gray-50 rounded-lg cursor-pointer" data-product-id="' . $product['id'] . '">';
                        $html .= '<img src="' . $primaryImage . '" alt="' . htmlspecialchars($product['name']) . '" class="w-12 h-12 object-cover rounded mr-3">';
                        $html .= '<div class="flex-1">';
                        $html .= '<h4 class="font-medium text-gray-900 text-sm">' . htmlspecialchars($product['name']) . '</h4>';
                        $html .= '<p class="text-sm text-gray-600">' . formatPrice($product['sale_price'] ?? $product['price']) . '</p>';
                        $html .= '</div>';
                        $html .= '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>';
                        $html .= '</svg>';
                        $html .= '</div>';
                    }
                    
                    // Add "See All" button if we have more than 6 results
                    $totalResults = count(getProducts(['search' => $searchQuery]));
                    if ($totalResults > $limit) {
                        $html .= '<div class="border-t pt-3 mt-3">';
                        $html .= '<a href="shop.php?search=' . urlencode($searchQuery) . '" class="block text-center text-sm font-medium text-black hover:text-gray-700 transition-colors">';
                        $html .= 'See all ' . $totalResults . ' results';
                        $html .= '</a>';
                        $html .= '</div>';
                    }
                } else {
                    $html = '<div class="text-center py-8 text-gray-500">';
                    $html .= '<svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                    $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>';
                    $html .= '</svg>';
                    $html .= '<p class="text-sm">No products found for "' . htmlspecialchars($searchQuery) . '"</p>';
                    $html .= '</div>';
                }
                
                echo json_encode(['success' => true, 'html' => $html, 'count' => count($products)]);
            } else {
                echo json_encode(['success' => true, 'html' => '', 'count' => 0]);
            }
            break;
            
        case 'filter_products':
            // Get filter parameters
            $filters = [];
            
            if (!empty($_POST['category'])) {
                $filters['category'] = $_POST['category'];
            }
            if (!empty($_POST['collection'])) {
                $filters['collection'] = $_POST['collection'];
            }
            if (!empty($_POST['search'])) {
                $filters['search'] = $_POST['search'];
            }
            if (!empty($_POST['size'])) {
                $filters['size'] = $_POST['size'];
            }
            
            // Get filtered products
            $products = getProducts($filters);
            
            // Get categories and collections for filter display
            $categories = getCategories();
            $collections = getCollections();
            
            // Build product HTML
            $productHtml = '';
            foreach ($products as $product) {
                $productImages = getProductImagesForSlider($product['id']);
                $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                $secondaryImage = $productImages[1]['image_path'] ?? $primaryImage;
                
                $productHtml .= '<div class="bg-white relative group product-card border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300" 
                    data-category="' . $product['category_id'] . '" 
                    data-collection="' . $product['collection_id'] . '"
                    data-price="' . ($product['sale_price'] ?? $product['price']) . '"
                    data-name="' . strtolower($product['name']) . '"
                    data-created-at="' . $product['created_at'] . '">
                    <a href="product-view.php?id=' . $product['id'] . '">
                        <div class="relative aspect-square">';
                
                if ($product['is_on_sale']) {
                    $productHtml .= '<span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2 py-1 z-10 rounded">SALE</span>';
                }
                
                $productHtml .= '<button class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition-colors duration-200 z-10 wishlist-btn" data-product-id="' . $product['id'] . '">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
                <img src="' . $primaryImage . '" alt="' . $product['name'] . '" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0">
                <img src="' . $secondaryImage . '" alt="' . $product['name'] . ' Hover" class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>
            <div class="p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-2 line-clamp-2">' . $product['name'] . '</h4>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg font-bold text-gray-900">' . formatPrice($product['sale_price'] ?? $product['price']) . '</span>';
                
                if ($product['sale_price']) {
                    $productHtml .= '<span class="text-sm text-gray-500 line-through">' . formatPrice($product['price']) . '</span>';
                }
                
                $productHtml .= '</div>
                </div>
            </div>
        </a>
    </div>';
            }
            
            // Build selected filters display
            $selectedFiltersHtml = '';
            $selectedFilters = [];
            
            if (!empty($_POST['category'])) {
                foreach ($_POST['category'] as $categoryId) {
                    foreach ($categories as $category) {
                        if ($category['id'] == $categoryId) {
                            $selectedFilters[] = ['type' => 'category', 'id' => $categoryId, 'name' => $category['name']];
                            break;
                        }
                    }
                }
            }
            
            if (!empty($_POST['collection'])) {
                foreach ($_POST['collection'] as $collectionId) {
                    foreach ($collections as $collection) {
                        if ($collection['id'] == $collectionId) {
                            $selectedFilters[] = ['type' => 'collection', 'id' => $collectionId, 'name' => $collection['name']];
                            break;
                        }
                    }
                }
            }
            
            if (!empty($_POST['size'])) {
                foreach ($_POST['size'] as $size) {
                    $selectedFilters[] = ['type' => 'size', 'id' => $size, 'name' => strtoupper($size)];
                }
            }
            
            if (!empty($_POST['search'])) {
                $selectedFilters[] = ['type' => 'search', 'id' => $_POST['search'], 'name' => 'Search: ' . $_POST['search']];
            }
            
            foreach ($selectedFilters as $filter) {
                $selectedFiltersHtml .= '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-black text-white">
                    ' . htmlspecialchars($filter['name']) . '
                    <button type="button" class="ml-2 text-white hover:text-gray-300 remove-filter" data-type="' . $filter['type'] . '" data-id="' . $filter['id'] . '">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>';
            }
            
            echo json_encode([
                'success' => true,
                'products' => $productHtml,
                'count' => count($products),
                'selectedFilters' => $selectedFiltersHtml,
                'hasResults' => count($products) > 0
            ]);
            break;
            
        case 'apply_promo_code':
            error_log("apply_promo_code action received");
            $code = $_POST['code'] ?? '';
            error_log("Promo code: " . $code);
            
            if ($code) {
                $cartItems = getCartItems();
                $subtotal = array_sum(array_column($cartItems, 'total_price'));
                error_log("Cart subtotal: " . $subtotal);
                error_log("Cart items count: " . count($cartItems));
                
                // Debug cart items
                foreach ($cartItems as $item) {
                    error_log("Cart item: " . $item['name'] . " - Qty: " . $item['quantity'] . " - Price: " . $item['total_price']);
                }
                
                $result = applyPromoCode($code, $cartItems);
                error_log("Apply result: " . json_encode($result));
                
                if ($result['valid']) {
                    echo json_encode([
                        'success' => true,
                        'message' => $result['message'],
                        'promo_code' => $result['promo_code'],
                        'discount_amount' => $result['discount_amount']
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => $result['message']]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Please enter a promo code']);
            }
            break;
            
        case 'remove_promo_code':
            error_log("remove_promo_code action received");
            $result = removePromoCode();
            error_log("Remove result: " . json_encode($result));
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    exit;
}

// Handle GET requests with action parameter
if ((isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'update_content':
            // Get JSON data from request body
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            if (!$data || !isset($data['key']) || !isset($data['value'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid data format']);
                exit;
            }
            
            // Parse the content key (format: section.key)
            $keyParts = explode('.', $data['key']);
            if (count($keyParts) !== 2) {
                echo json_encode(['success' => false, 'message' => 'Invalid content key format']);
                exit;
            }
            
            $section = $keyParts[0];
            $key = $keyParts[1];
            $value = $data['value'];
            
            // Update the content
            if (updateAboutContent($section, $key, $value)) {
                echo json_encode(['success' => true, 'message' => 'Content updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update content']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    exit;
}

// If not a valid request, return error
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>
                    echo json_encode([
                        'success' => true,
                        'message' => $result['message'],
                        'promo_code' => $result['promo_code'],
                        'discount_amount' => $result['discount_amount']
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => $result['message']]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Please enter a promo code']);
            }
            break;
            
        case 'remove_promo_code':
            error_log("remove_promo_code action received");
            $result = removePromoCode();
            error_log("Remove result: " . json_encode($result));
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    exit;
}

// Handle GET requests with action parameter
if ((isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'update_content':
            // Get JSON data from request body
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            if (!$data || !isset($data['key']) || !isset($data['value'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid data format']);
                exit;
            }
            
            // Parse the content key (format: section.key)
            $keyParts = explode('.', $data['key']);
            if (count($keyParts) !== 2) {
                echo json_encode(['success' => false, 'message' => 'Invalid content key format']);
                exit;
            }
            
            $section = $keyParts[0];
            $key = $keyParts[1];
            $value = $data['value'];
            
            // Update the content
            if (updateAboutContent($section, $key, $value)) {
                echo json_encode(['success' => true, 'message' => 'Content updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update content']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    exit;
}

// If not a valid request, return error
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
?>