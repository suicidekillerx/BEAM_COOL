<?php
session_start();
require_once 'includes/functions.php';

// Check maintenance mode before accessing cart
checkMaintenanceMode();

// Handle remove from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $cartItemId = (int)$_GET['remove'];
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
    if ($stmt->execute([$cartItemId, session_id()])) {
        $_SESSION['cart_message'] = 'Item removed from cart.';
    } else {
        $_SESSION['cart_message'] = 'Failed to remove item from cart.';
    }
    header('Location: view_cart.php');
    exit;
}

// Handle quantity updates
if (isset($_POST['update_quantity'])) {
    $cartItemId = (int)$_POST['cart_item_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        // Remove item
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE id = ? AND session_id = ?");
        $stmt->execute([$cartItemId, session_id()]);
        $_SESSION['cart_message'] = 'Item removed from cart.';
    } else {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ? AND session_id = ?");
        $stmt->execute([$quantity, $cartItemId, session_id()]);
        $_SESSION['cart_message'] = 'Cart updated successfully.';
    }
    header('Location: view_cart.php');
    exit;
}

// Get cart items
$cartItems = getCartItems();
$totalItems = array_sum(array_column($cartItems, 'quantity'));
$subtotal = array_sum(array_column($cartItems, 'total_price'));
$shipping = $totalItems > 0 ? (float)getSiteSetting('shipping_cost', 15.000) : 0; // Free shipping over 100 DTN
$taxRate = (float)getSiteSetting('tax_rate', 0.19);
$tax = $subtotal * $taxRate;
$total = $subtotal + $shipping + $tax;

// Calculate savings
$originalTotal = 0;
foreach ($cartItems as $item) {
    $originalPrice = $item['price'] * $item['quantity'];
    $salePrice = ($item['sale_price'] ?? $item['price']) * $item['quantity'];
    $originalTotal += $originalPrice;
}
$savings = $originalTotal - $subtotal;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Cart - <?php echo getSiteSetting('brand_name', 'Beam'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-item-enter { animation: slideInRight 0.3s ease-out; }
        .cart-item-exit { animation: slideOutLeft 0.3s ease-in; }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutLeft {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(-100%); opacity: 0; }
        }
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .quantity-input {
            -moz-appearance: textfield;
        }
        .floating-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        /* Account for header height */
        .sticky-cart {
            top: 120px; /* Adjust based on your header height */
        }
        @media (max-width: 768px) {
            .sticky-cart {
                top: 100px; /* Smaller header on mobile */
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <?php require_once 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Smart Cart Header -->
           
            <?php if (isset($_SESSION['cart_message'])): ?>
                <div class="bg-white border border-gray-300 text-gray-800 px-4 py-3 rounded-lg mb-6 flex items-center shadow-sm">
                    <i class="fas fa-check-circle mr-2 text-green-600"></i>
                    <?php echo htmlspecialchars($_SESSION['cart_message']); ?>
                </div>
                <?php unset($_SESSION['cart_message']); ?>
            <?php endif; ?>
            
            <?php if (empty($cartItems)): ?>
                <!-- Empty Cart - Smart Design -->
                <div class="text-center py-16">
                    <div class="mb-8">
                        <i class="fas fa-shopping-bag text-8xl text-gray-400 mb-6"></i>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Your cart is empty</h2>
                        <p class="text-gray-600 text-lg mb-8">Discover amazing products and start shopping!</p>
                    </div>
                    
                   
                    
                    <div class="mt-8">
                        <a href="index.php" class="bg-black text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-800 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Start Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Smart Cart Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Cart Items - Left Column -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                            
                            
                            <div class="p-6">
                                <div class="space-y-6">
                                    <?php foreach ($cartItems as $item): ?>
                                    <div class="cart-item-enter bg-gray-50 rounded-xl p-6 border border-gray-200 hover:shadow-md transition-all duration-300">
                                        <div class="flex items-start space-x-4">
                                            <!-- Product Image -->
                                            <div class="relative">
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="w-24 h-24 object-cover rounded-lg shadow-sm">
                                                <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                                <div class="absolute -top-2 -right-2 bg-black text-white text-xs font-bold px-2 py-1 rounded-full">
                                                    SALE
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Product Details -->
                                            <div class="flex-1">
                                                <h3 class="font-bold text-lg text-gray-900 mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                                                <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                                                    <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded">Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                                    <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded">Color: <?php echo htmlspecialchars($item['color'] ?? 'Default'); ?></span>
                                                </div>
                                                
                                                <!-- Smart Price Display -->
                                                <div class="flex items-center space-x-3 mb-4">
                                                    <span class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($item['price_formatted']); ?></span>
                                                    <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                                    <span class="text-lg text-gray-500 line-through"><?php echo formatPrice($item['price']); ?></span>
                                                    <span class="bg-gray-200 text-gray-800 px-2 py-1 rounded text-sm font-semibold">
                                                        Save <?php echo formatPrice($item['price'] - $item['sale_price']); ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Smart Quantity Control -->
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-2">
                                                        <button class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center transition-colors" 
                                                                data-cart-id="<?php echo $item['id']; ?>" 
                                                                data-action="decrease">
                                                            <i class="fas fa-minus text-xs"></i>
                                                        </button>
                                                        <input type="number" value="<?php echo $item['quantity']; ?>" 
                                                               class="quantity-input w-16 text-center border border-gray-300 rounded-lg py-1 text-lg font-semibold" 
                                                               data-cart-id="<?php echo $item['id']; ?>" 
                                                               min="1" max="99">
                                                        <button class="quantity-btn bg-gray-200 hover:bg-gray-300 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center transition-colors" 
                                                                data-cart-id="<?php echo $item['id']; ?>" 
                                                                data-action="increase">
                                                            <i class="fas fa-plus text-xs"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="text-right">
                                                        <div class="text-lg font-bold text-gray-900">
                                                            <?php echo formatPrice($item['total_price']); ?>
                                                        </div>
                                                        <button class="remove-btn text-gray-600 hover:text-black text-sm font-medium transition-colors" 
                                                                data-cart-id="<?php echo $item['id']; ?>">
                                                            <i class="fas fa-trash mr-1"></i>
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                
                            </div>
                        </div>
                    </div>
                    
                    <!-- Smart Summary - Right Column -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden sticky-cart">
                            
                            
                            <div class="p-6">
                                <!-- Savings Alert -->
                                <?php if ($savings > 0): ?>
                                <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 mb-6">
                                    <div class="flex items-center">
                                        <i class="fas fa-gift text-gray-700 text-xl mr-3"></i>
                                        <div>
                                            <h4 class="font-bold text-gray-800">You're Saving!</h4>
                                            <p class="text-gray-700 text-sm">Total savings: <span class="font-bold"><?php echo formatPrice($savings); ?></span></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Price Breakdown -->
                                <div class="space-y-3 mb-6">
                                    <div class="flex justify-between text-gray-600">
                                        <span>Subtotal (<?php echo $totalItems; ?> items)</span>
                                        <span><?php echo formatPrice($subtotal); ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-600">
                                        <span>Tax (<?php echo ($taxRate * 100); ?>%)</span>
                                        <span><?php echo formatPrice($tax); ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-600">
                                        <span>Shipping</span>
                                        <span class="<?php echo $shipping > 0 ? 'text-gray-800' : 'text-green-600'; ?>">
                                            <?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?>
                                        </span>
                                    </div>
                                    <?php if ($savings > 0): ?>
                                    <div class="flex justify-between text-gray-800 font-semibold">
                                        <span>Total Savings</span>
                                        <span>-<?php echo formatPrice($savings); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Promo Code Discount -->
                                    <div id="promoDiscountRow" class="hidden flex justify-between text-green-600 font-semibold">
                                        <span>Promo Code Discount</span>
                                        <span id="promoDiscountAmount">-0.000 DTN</span>
                                    </div>
                                    
                                    <div class="border-t border-gray-200 pt-3">
                                        <div class="flex justify-between text-xl font-bold text-gray-900">
                                            <span>Total</span>
                                            <span id="finalTotal"><?php echo formatPrice($total); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                             
                                
                                <!-- Promo Code Section -->
                                <div class="border-t border-gray-200 pt-6 mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Have a Promo Code?</h3>
                                    <div id="promoCodeSection">
                                        <div class="flex space-x-2">
                                            <input type="text" id="promoCodeInput" placeholder="Enter promo code" 
                                                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
                                            <button id="applyPromoBtn" 
                                                    class="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-black transition-colors">
                                                Apply
                                            </button>
                                        </div>
                                        <div id="promoCodeMessage" class="mt-2 text-sm"></div>
                                    </div>
                                    
                                    <!-- Applied Promo Code Display -->
                                    <div id="appliedPromoCode" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="font-semibold text-green-800" id="appliedPromoName"></span>
                                                <span class="text-green-600 text-sm ml-2" id="appliedPromoDiscount"></span>
                                            </div>
                                            <button onclick="removePromoCode()" class="text-green-600 hover:text-green-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Checkout Button -->
                                <a href="checkout.php" class="block w-full bg-black text-white py-4 rounded-xl font-bold text-lg hover:bg-gray-800 transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                                     Checkout
                                </a>
                                
                                
                                
                                <!-- Continue Shopping -->
                                <div class="mt-6 text-center">
                                    <a href="shop.php" class="text-gray-600 hover:text-black font-medium transition-colors">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>

    <!-- Smart Cart JavaScript -->
    <script>
    var SHIPPING_COST = <?php echo json_encode((float)getSiteSetting('shipping_cost', 15.000)); ?>;
    var TAX_RATE = <?php echo json_encode($taxRate); ?>;
    document.addEventListener('DOMContentLoaded', function() {
        // Quantity update functionality
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.dataset.action;
                const cartId = this.dataset.cartId;
                const input = document.querySelector(`input[data-cart-id="${cartId}"]`);
                let currentQty = parseInt(input.value);
                
                if (action === 'decrease' && currentQty > 1) {
                    currentQty--;
                } else if (action === 'increase' && currentQty < 99) {
                    currentQty++;
                } else if (action === 'decrease' && currentQty <= 1) {
                    // Remove item if quantity becomes 0
                    removeCartItem(cartId);
                    return;
                }
                
                input.value = currentQty;
                updateCartItemQuantity(cartId, currentQty);
            });
        });
        
        // Direct quantity input
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const cartId = this.dataset.cartId;
                let quantity = parseInt(this.value);
                
                // Validate quantity
                if (quantity < 1) {
                    quantity = 1;
                    this.value = 1;
                } else if (quantity > 99) {
                    quantity = 99;
                    this.value = 99;
                }
                
                updateCartItemQuantity(cartId, quantity);
            });
        });
        
        // Remove item functionality
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartId = this.dataset.cartId;
                removeCartItem(cartId);
            });
        });
        
        // Update cart item quantity via AJAX
        function updateCartItemQuantity(cartId, quantity) {
            const formData = new FormData();
            formData.append('action', 'update_cart');
            formData.append('cart_item_id', cartId);
            formData.append('quantity', quantity);
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the item's total price display
                    updateItemTotal(cartId, quantity);
                    // Update cart totals
                    updateCartTotals();
                    // Update cart count in header
                    updateHeaderCartCount();
                    
                    // Show success feedback
                    showNotification('Cart updated successfully!', 'success');
                } else {
                    showNotification(data.message || 'Failed to update cart', 'error');
                    // Revert the quantity input
                    const input = document.querySelector(`input[data-cart-id="${cartId}"]`);
                    if (input) {
                        input.value = quantity;
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                showNotification('Network error. Please try again.', 'error');
            });
        }
        
        // Remove cart item via AJAX
        function removeCartItem(cartId) {
            const formData = new FormData();
            formData.append('action', 'remove_from_cart');
            formData.append('cart_item_id', cartId);
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Animate item removal
                    const itemElement = document.querySelector(`[data-cart-id="${cartId}"]`).closest('.cart-item-enter');
                    if (itemElement) {
                        itemElement.classList.add('cart-item-exit');
                        setTimeout(() => {
                            itemElement.remove();
                            updateCartTotals();
                            updateHeaderCartCount();
                            
                            // Check if cart is empty
                            const remainingItems = document.querySelectorAll('.cart-item-enter');
                            if (remainingItems.length === 0) {
                                showEmptyCart();
                            }
                        }, 300);
                    }
                    
                    showNotification('Item removed from cart', 'success');
                } else {
                    showNotification(data.message || 'Failed to remove item', 'error');
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
                showNotification('Network error. Please try again.', 'error');
            });
        }
        
        // Update individual item total
        function updateItemTotal(cartId, quantity) {
            const itemElement = document.querySelector(`[data-cart-id="${cartId}"]`).closest('.cart-item-enter');
            if (itemElement) {
                const priceElement = itemElement.querySelector('.text-lg.font-bold.text-gray-900');
                
                // Get the original price from the sale price or regular price
                let price = 0;
                const salePriceElement = itemElement.querySelector('.text-2xl.font-bold.text-gray-900');
                const originalPriceElement = itemElement.querySelector('.text-lg.text-gray-500.line-through');
                
                if (salePriceElement) {
                    // Use sale price if available
                    const salePriceText = salePriceElement.textContent;
                    price = parseFloat(salePriceText.replace(/[^0-9.]/g, ''));
                } else if (originalPriceElement) {
                    // Use original price if no sale price
                    const originalPriceText = originalPriceElement.textContent;
                    price = parseFloat(originalPriceText.replace(/[^0-9.]/g, ''));
                }
                
                if (price > 0) {
                const total = price * quantity;
                priceElement.textContent = formatPrice(total);
                }
            }
        }
        
        // Update header cart count
        function updateHeaderCartCount() {
            const cartCountElements = document.querySelectorAll('.cart-count');
            let totalItems = 0;
            
            document.querySelectorAll('.quantity-input').forEach(input => {
                totalItems += parseInt(input.value) || 0;
            });
            
            cartCountElements.forEach(element => {
                element.textContent = totalItems;
                
                // Animate the count change
                element.classList.add('scale-125', 'bg-green-500');
                setTimeout(() => {
                    element.classList.remove('scale-125', 'bg-green-500');
                }, 300);
            });
        }
        
        // Show empty cart message
        function showEmptyCart() {
            const cartContainer = document.querySelector('.lg\\:col-span-2');
            if (cartContainer) {
                cartContainer.innerHTML = `
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
                        <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
                        <a href="shop.php" class="bg-black text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                            Start Shopping
                        </a>
                    </div>
                `;
            }
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            document.querySelectorAll('.notification').forEach(n => n.remove());
            
            const bgColor = type === 'success' ? 'bg-green-500' : 
                           type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            const notification = document.createElement('div');
            notification.className = 'notification fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300';
            notification.innerHTML = `
                <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        ${type === 'success' ? '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' :
                          type === 'error' ? '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>' :
                          '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>'}
                    </svg>
                    <span>${message}</span>
                    <button class="notification-close ml-2 hover:opacity-75">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Slide in animation
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
            
            // Manual close
            notification.querySelector('.notification-close').addEventListener('click', function() {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            });
        }
        
        function updateCartTotals() {
            // Recalculate totals based on remaining items
            const items = document.querySelectorAll('.cart-item-enter');
            let totalItems = 0;
            let subtotal = 0;
            
            items.forEach(item => {
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                
                // Get the correct price (sale price if available, otherwise regular price)
                let price = 0;
                const salePriceElement = item.querySelector('.text-2xl.font-bold.text-gray-900');
                const originalPriceElement = item.querySelector('.text-lg.text-gray-500.line-through');
                
                if (salePriceElement) {
                    // Use sale price if available
                    const salePriceText = salePriceElement.textContent;
                    price = parseFloat(salePriceText.replace(/[^0-9.]/g, ''));
                } else if (originalPriceElement) {
                    // Use original price if no sale price
                    const originalPriceText = originalPriceElement.textContent;
                    price = parseFloat(originalPriceText.replace(/[^0-9.]/g, ''));
                }
                
                totalItems += quantity;
                subtotal += price * quantity;
            });
            
            // Calculate tax and shipping
            const tax = subtotal * TAX_RATE;
            const shipping = totalItems > 0 ? SHIPPING_COST : 0;
            const total = subtotal + tax + shipping;
            
            // Update summary
            const summaryElement = document.querySelector('.sticky-cart');
            if (summaryElement) {
                // Update subtotal
                const subtotalElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:first-child span:last-child');
                const totalItemsElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:first-child span:first-child');
                
                if (subtotalElement) {
                    subtotalElement.textContent = formatPrice(subtotal);
                }
                if (totalItemsElement) {
                    totalItemsElement.textContent = `Subtotal (${totalItems} items)`;
                }
                
                // Update tax
                const taxElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:nth-child(2) span:last-child');
                if (taxElement) {
                    taxElement.textContent = formatPrice(tax);
                }
                
                // Update shipping
                const shippingElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:nth-child(3) span:last-child');
                if (shippingElement) {
                    shippingElement.textContent = shipping > 0 ? formatPrice(shipping) : 'FREE';
                    shippingElement.className = shipping > 0 ? 'text-gray-800' : 'text-green-600';
                }
                
                // Update total
                const totalElement = summaryElement.querySelector('.border-t.border-gray-200.pt-3 .flex.justify-between.text-xl.font-bold.text-gray-900 span:last-child');
                if (totalElement) {
                    totalElement.textContent = formatPrice(total);
                }
            }
        }
        
        function formatPrice(amount) {
            return amount.toFixed(3) + ' DTN';
        }
        
        // Promo Code Functions
        function applyPromoCode() {
            console.log('applyPromoCode called');
            const code = document.getElementById('promoCodeInput').value.trim();
            console.log('Promo code:', code);
            
            if (!code) {
                showNotification('Please enter a promo code', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'apply_promo_code');
            formData.append('code', code);
            
            console.log('Sending request to ajax_handler.php');
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data);
                if (data.success) {
                    showAppliedPromoCode(data.promo_code, data.discount_amount);
                    updateCartTotals();
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error applying promo code:', error);
                showNotification('Network error. Please try again.', 'error');
            });
        }
        
        function removePromoCode() {
            const formData = new FormData();
            formData.append('action', 'remove_promo_code');
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideAppliedPromoCode();
                    updateCartTotals();
                    showNotification(data.message, 'success');
                }
            })
            .catch(error => {
                console.error('Error removing promo code:', error);
                showNotification('Network error. Please try again.', 'error');
            });
        }
        
        function showAppliedPromoCode(promoCode, discountAmount) {
            document.getElementById('appliedPromoCode').classList.remove('hidden');
            document.getElementById('appliedPromoName').textContent = promoCode.name;
            document.getElementById('appliedPromoDiscount').textContent = `-${formatPrice(discountAmount)}`;
            document.getElementById('promoCodeInput').value = '';
            document.getElementById('promoCodeInput').disabled = true;
            document.getElementById('applyPromoBtn').disabled = true;
        }
        
        function hideAppliedPromoCode() {
            document.getElementById('appliedPromoCode').classList.add('hidden');
            document.getElementById('promoCodeInput').disabled = false;
            document.getElementById('applyPromoBtn').disabled = false;
        }
        
        // Update cart totals to include promo code discount
        function updateCartTotals() {
            // Recalculate totals based on remaining items
            const items = document.querySelectorAll('.cart-item-enter');
            let totalItems = 0;
            let subtotal = 0;
            
            items.forEach(item => {
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                
                // Get the correct price (sale price if available, otherwise regular price)
                let price = 0;
                const salePriceElement = item.querySelector('.text-2xl.font-bold.text-gray-900');
                const originalPriceElement = item.querySelector('.text-lg.text-gray-500.line-through');
                
                if (salePriceElement) {
                    // Use sale price if available
                    const salePriceText = salePriceElement.textContent;
                    price = parseFloat(salePriceText.replace(/[^0-9.]/g, ''));
                } else if (originalPriceElement) {
                    // Use original price if no sale price
                    const originalPriceText = originalPriceElement.textContent;
                    price = parseFloat(originalPriceText.replace(/[^0-9.]/g, ''));
                }
                
                totalItems += quantity;
                subtotal += price * quantity;
            });
            
            // Calculate tax and shipping
            const tax = subtotal * TAX_RATE;
            const shipping = totalItems > 0 ? SHIPPING_COST : 0;
            
            // Get promo code discount
            const appliedPromoCode = document.getElementById('appliedPromoCode');
            let promoDiscount = 0;
            if (!appliedPromoCode.classList.contains('hidden')) {
                const discountText = document.getElementById('appliedPromoDiscount').textContent;
                promoDiscount = parseFloat(discountText.replace(/[^0-9.]/g, ''));
            }
            
            const total = subtotal + tax + shipping - promoDiscount;
            
            // Update summary
            const summaryElement = document.querySelector('.sticky-cart');
            if (summaryElement) {
                // Update subtotal
                const subtotalElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:first-child span:last-child');
                const totalItemsElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:first-child span:first-child');
                
                if (subtotalElement) {
                    subtotalElement.textContent = formatPrice(subtotal);
                }
                if (totalItemsElement) {
                    totalItemsElement.textContent = `Subtotal (${totalItems} items)`;
                }
                
                // Update tax
                const taxElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:nth-child(2) span:last-child');
                if (taxElement) {
                    taxElement.textContent = formatPrice(tax);
                }
                
                // Update shipping
                const shippingElement = summaryElement.querySelector('.space-y-3 .flex.justify-between:nth-child(3) span:last-child');
                if (shippingElement) {
                    shippingElement.textContent = shipping > 0 ? formatPrice(shipping) : 'FREE';
                    shippingElement.className = shipping > 0 ? 'text-gray-800' : 'text-green-600';
                }
                
                // Update promo discount
                const promoDiscountRow = document.getElementById('promoDiscountRow');
                const promoDiscountAmount = document.getElementById('promoDiscountAmount');
                if (promoDiscount > 0) {
                    promoDiscountRow.classList.remove('hidden');
                    promoDiscountAmount.textContent = `-${formatPrice(promoDiscount)}`;
                } else {
                    promoDiscountRow.classList.add('hidden');
                }
                
                // Update total
                const totalElement = document.getElementById('finalTotal');
                if (totalElement) {
                    totalElement.textContent = formatPrice(total);
                }
            }
        }
        
        // Promo code apply button
        document.getElementById('applyPromoBtn').addEventListener('click', applyPromoCode);
        
        // Animate cart items
        document.querySelectorAll('.cart-item-enter').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(100%)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease-out';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, 100);
        });
    });
    </script>
</body>
</html> 