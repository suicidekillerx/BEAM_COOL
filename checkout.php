<?php
session_start();
require_once 'includes/functions.php';

// Check maintenance mode before processing checkout
checkMaintenanceMode();

// Redirect if cart is empty
$cartItems = getCartItems();
if (empty($cartItems)) {
    header('Location: view_cart.php');
    exit;
}

// Get applied promo code and calculate totals
$appliedPromoCode = getAppliedPromoCode();
$totalItems = array_sum(array_column($cartItems, 'quantity'));
$subtotal = array_sum(array_column($cartItems, 'total_price'));
$shipping = $totalItems > 0 ? (float)getSiteSetting('shipping_cost', 15.000) : 0;
$taxRate = (float)getSiteSetting('tax_rate', 0.19);

// Calculate discount if promo code is applied
$discount = 0;
if ($appliedPromoCode) {
    $discount = $appliedPromoCode['discount_amount'];
}

// Calculate tax on subtotal (before discount)
$tax = $subtotal * $taxRate;

// Calculate final total with discount
$total = $subtotal + $shipping + $tax - $discount;

// Calculate savings
$originalTotal = 0;
foreach ($cartItems as $item) {
    $originalPrice = $item['price'] * $item['quantity'];
    $salePrice = ($item['sale_price'] ?? $item['price']) * $item['quantity'];
    $originalTotal += $originalPrice;
}
$savings = $originalTotal - $subtotal;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate required fields
    $requiredFields = [
        'customer_name' => 'Full Name',
        'customer_email' => 'Email Address',
        'customer_phone' => 'Phone Number',
        'shipping_address' => 'Shipping Address',
        'shipping_city' => 'City'
    ];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($_POST[$field])) {
            $errors[] = "$label is required.";
        }
    }
    
    // Validate email
    if (!empty($_POST['customer_email']) && !filter_var($_POST['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Validate phone number (Tunisia format)
    if (!empty($_POST['customer_phone'])) {
        $phone = preg_replace('/[^0-9]/', '', $_POST['customer_phone']);
        if (strlen($phone) < 8 || strlen($phone) > 10) {
            $errors[] = "Please enter a valid phone number.";
        }
    }
    
    // If no errors, create order
    if (empty($errors)) {
        try {
            // Use pre-calculated totals that include promo code discount
            
            $orderData = [
                'customer_name' => trim($_POST['customer_name']),
                'customer_email' => trim($_POST['customer_email']),
                'customer_phone' => trim($_POST['customer_phone']),
                'shipping_address' => trim($_POST['shipping_address']),
                'shipping_city' => $_POST['shipping_city'],
                'shipping_postal_code' => trim($_POST['shipping_postal_code'] ?? ''),
                'shipping_notes' => trim($_POST['shipping_notes'] ?? ''),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'promo_code_id' => $appliedPromoCode ? $appliedPromoCode['id'] : null,
                'promo_code' => $appliedPromoCode ? $appliedPromoCode['code'] : null,
                'notes' => trim($_POST['notes'] ?? '')
            ];
            
            $orderNumber = createOrder($orderData);
            
            // Redirect to order confirmation
            header("Location: order-confirmation.php?order=" . urlencode($orderNumber));
            exit;
            
        } catch (Exception $e) {
            $errors[] = "An error occurred while processing your order: " . $e->getMessage();
            // Log the error for debugging
            error_log("Checkout error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        }
    }
}

$tunisiaCities = getTunisiaCities();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo getSiteSetting('brand_name', 'Beam'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-input:focus {
            border-color: #000;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }
        .sticky-summary {
            top: 120px;
        }
        @media (max-width: 768px) {
            .sticky-summary {
                top: 100px;
            }
        }
        .step-indicator {
            background: linear-gradient(90deg, #000 0%, #000 50%, #e5e7eb 50%, #e5e7eb 100%);
        }
        .step-indicator.completed {
            background: linear-gradient(90deg, #000 0%, #000 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <?php require_once 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Checkout Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Checkout</h1>
                <p class="text-gray-600">Complete your order with cash on delivery</p>
            </div>
            
            <!-- Step Indicator -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-black text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <span class="ml-2 text-sm font-medium text-black">Cart</span>
                    </div>
                    <div class="w-16 h-1 step-indicator rounded-full"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-black text-white rounded-full flex items-center justify-center font-bold">2</div>
                        <span class="ml-2 text-sm font-medium text-black">Checkout</span>
                    </div>
                    <div class="w-16 h-1 bg-gray-200 rounded-full"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center font-bold">3</div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Confirmation</span>
                    </div>
                </div>
            </div>
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <div>
                            <h4 class="font-semibold">Please correct the following errors:</h4>
                            <ul class="mt-2 list-disc list-inside">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Checkout Form - Left Column -->
                <div class="lg:col-span-2">
                    <form method="POST" id="checkout-form" class="space-y-8">
                        
                        <!-- Customer Information -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-user mr-3 text-gray-600"></i>
                                    Customer Information
                                </h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            Full Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="customer_name" name="customer_name" 
                                               value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>"
                                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-colors"
                                               placeholder="Enter your full name" required>
                                    </div>
                                    <div>
                                        <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                            Email Address <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" id="customer_email" name="customer_email" 
                                               value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>"
                                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-colors"
                                               placeholder="Enter your email address" required>
                                    </div>
                                </div>
                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" id="customer_phone" name="customer_phone" 
                                           value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>"
                                           class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-colors"
                                           placeholder="e.g., 71 234 567 or +216 71 234 567" required>
                                    <p class="text-xs text-gray-500 mt-1">We'll use this to contact you about your order</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shipping Information -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-shipping-fast mr-3 text-gray-600"></i>
                                    Shipping Information
                                </h2>
                                <p class="text-sm text-gray-600 mt-1">We currently ship only within Tunisia</p>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-2">
                                        Shipping Address <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="shipping_address" name="shipping_address" rows="3"
                                              class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-colors"
                                              placeholder="Enter your complete shipping address" required><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-2">
                                            City/Governorate <span class="text-red-500">*</span>
                                        </label>
                                        <select id="shipping_city" name="shipping_city" 
                                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-colors" required>
                                            <option value="">Select your city</option>
                                            <?php foreach ($tunisiaCities as $city): ?>
                                                <option value="<?php echo $city; ?>" <?php echo (isset($_POST['shipping_city']) && $_POST['shipping_city'] === $city) ? 'selected' : ''; ?>>
                                                    <?php echo $city; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                            Postal Code
                                        </label>
                                        <input type="text" id="shipping_postal_code" name="shipping_postal_code" 
                                               value="<?php echo htmlspecialchars($_POST['shipping_postal_code'] ?? ''); ?>"
                                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-colors"
                                               placeholder="e.g., 1000">
                                    </div>
                                </div>
                                <!-- Delivery Notes section removed -->
                            </div>
                        </div>
                        <!-- Order Notes -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-sticky-note mr-3 text-gray-600"></i>
                                    Order Notes (Optional)
                                </h2>
                            </div>
                            <div class="p-6">
                                <textarea id="notes" name="notes" rows="3"
                                          class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none transition-colors"
                                          placeholder="Any special requests or notes for your order"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <!-- Payment Method -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-credit-card mr-3 text-gray-600"></i>
                                    Payment Method
                                </h2>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center p-4 border-2 border-black rounded-lg bg-gray-50">
                                    <div class="flex items-center">
                                        <i class="fas fa-money-bill-wave text-2xl text-green-600 mr-4"></i>
                                        <div>
                                            <h3 class="font-bold text-lg">Cash on Delivery</h3>
                                            <p class="text-gray-600">Pay when you receive your order</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                        <div class="text-sm text-blue-800">
                                            <p class="font-semibold">How it works:</p>
                                            <ul class="mt-2 list-disc list-inside space-y-1">
                                                <li>Place your order online</li>
                                                <li>We'll process and ship your items</li>
                                                <li>Pay the delivery person when you receive your order</li>
                                                <li>No upfront payment required</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        
                    </form>
                </div>
                
                <!-- Order Summary - Right Column -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden sticky-summary">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                <i class="fas fa-shopping-bag mr-3 text-gray-600"></i>
                                Order Summary
                            </h2>
                        </div>
                        <div class="p-6">
                            
                            <!-- Order Items -->
                            <div class="space-y-4 mb-6">
                                <?php foreach ($cartItems as $item): ?>
                                <div class="flex items-center space-x-3">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="w-12 h-12 object-cover rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-sm text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p class="text-xs text-gray-600">Size: <?php echo htmlspecialchars($item['size']); ?> | Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-sm text-gray-900"><?php echo htmlspecialchars($item['price_formatted']); ?></p>
                                        <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                        <p class="text-xs text-gray-500 line-through"><?php echo formatPrice($item['price']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Savings Alert -->
                            <?php if ($savings > 0): ?>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div class="flex items-center">
                                    <i class="fas fa-gift text-green-600 text-xl mr-3"></i>
                                    <div>
                                        <h4 class="font-bold text-green-800">You're Saving!</h4>
                                        <p class="text-green-700 text-sm">Total savings: <span class="font-bold"><?php echo formatPrice($savings); ?></span></p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Applied Promo Code -->
                            <?php if ($appliedPromoCode): ?>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-green-800 font-medium">Promo Code Applied</span>
                                    </div>
                                    <span class="text-green-600 font-semibold">-<?php echo formatPrice($discount); ?></span>
                                </div>
                                <div class="mt-2 text-sm text-green-700">
                                    <span class="font-mono bg-green-100 px-2 py-1 rounded"><?php echo htmlspecialchars($appliedPromoCode['code']); ?></span>
                                    <span class="ml-2"><?php echo htmlspecialchars($appliedPromoCode['name']); ?></span>
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
                                    <span>Tax (<?php echo round($taxRate * 100); ?>%)</span>
                                    <span><?php echo formatPrice($tax); ?></span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Shipping</span>
                                    <span class="<?php echo $shipping > 0 ? 'text-gray-800' : 'text-green-600'; ?>">
                                        <?php echo $shipping > 0 ? formatPrice($shipping) : 'FREE'; ?>
                                    </span>
                                </div>
                                <?php if ($discount > 0): ?>
                                <div class="flex justify-between text-green-600 font-semibold">
                                    <span>Promo Code Discount</span>
                                    <span>-<?php echo formatPrice($discount); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($savings > 0): ?>
                                <div class="flex justify-between text-gray-800 font-semibold">
                                    <span>Total Savings</span>
                                    <span>-<?php echo formatPrice($savings); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="border-t border-gray-200 pt-3">
                                    <div class="flex justify-between text-xl font-bold text-gray-900">
                                        <span>Total</span>
                                        <span><?php echo formatPrice($total); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Place Order Button -->
                            <button type="submit" form="checkout-form" 
                                    class="w-full bg-black text-white py-4 rounded-xl font-bold text-lg hover:bg-gray-800 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-lock mr-2"></i>
                                Place Order - <?php echo formatPrice($total); ?>
                            </button>
                            
                            <!-- Security Notice -->
                            <div class="mt-4 text-center">
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Your information is secure and encrypted
                                </p>
                            </div>
                            
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
        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('form');
        const submitBtn = document.querySelector('button[type="submit"]');
        
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Disable submit button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        });
        
        // Phone number formatting
        const phoneInput = document.getElementById('customer_phone');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Format Tunisia phone number
            if (value.startsWith('216')) {
                value = value.substring(3);
            }
            
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = value;
                } else if (value.length <= 5) {
                    value = value.substring(0, 2) + ' ' + value.substring(2);
                } else {
                    value = value.substring(0, 2) + ' ' + value.substring(2, 5) + ' ' + value.substring(5, 8);
                }
            }
            
            e.target.value = value;
        });
        
        // Postal code formatting
        const postalInput = document.getElementById('shipping_postal_code');
        postalInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });
    });
    </script>
</body>
</html> 