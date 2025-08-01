<?php
session_start();
require_once 'includes/functions.php';

// Get order number from URL
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    header('Location: index.php');
    exit;
}

// Get order details
$order = getOrder($orderNumber);

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$orderItems = getOrderItems($order['id']);

$taxRate = (float)getSiteSetting('tax_rate', 0.19);
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['total_price'];
}
$tax = $subtotal * $taxRate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - <?php echo getSiteSetting('brand_name', 'Beam'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .success-animation {
            animation: successPulse 2s ease-in-out;
        }
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .order-number {
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        
        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-invoice, .print-invoice * {
                visibility: visible;
            }
            .print-invoice {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: white;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <?php require_once 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Success Header -->
            <div class="text-center mb-8">
                <div class="success-animation inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                    <i class="fas fa-check text-4xl text-green-600"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
                <p class="text-gray-600 text-lg">Thank you for your order. We'll start processing it right away.</p>
            </div>
            
            <!-- Order Details -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-receipt mr-3 text-gray-600"></i>
                        Order Details
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Order Information</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order Number:</span>
                                    <span class="font-mono font-bold text-gray-900 order-number"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order Date:</span>
                                    <span class="text-gray-900"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order Status:</span>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Method:</span>
                                    <span class="text-gray-900">Cash on Delivery</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-3">Customer Information</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Information -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-shipping-fast mr-3 text-gray-600"></i>
                        Shipping Information
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Address:</span>
                            <span class="text-gray-900 text-right"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">City:</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($order['shipping_city']); ?></span>
                        </div>
                        <?php if (!empty($order['shipping_postal_code'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Postal Code:</span>
                            <span class="text-gray-900"><?php echo htmlspecialchars($order['shipping_postal_code']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($order['shipping_notes'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Delivery Notes:</span>
                            <span class="text-gray-900 text-right"><?php echo nl2br(htmlspecialchars($order['shipping_notes'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-shopping-bag mr-3 text-gray-600"></i>
                        Order Items
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php foreach ($orderItems as $item): ?>
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            <div class="w-16 h-16 rounded-lg overflow-hidden">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                <p class="text-sm text-gray-600">Size: <?php echo htmlspecialchars($item['size']); ?> | Qty: <?php echo $item['quantity']; ?></p>
                                <?php if ($item['color']): ?>
                                <p class="text-sm text-gray-600">Color: <?php echo htmlspecialchars($item['color']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900"><?php echo formatPrice($item['total_price']); ?></p>
                                <?php if ($item['product_sale_price'] && $item['product_sale_price'] < $item['product_price']): ?>
                                <p class="text-sm text-gray-500 line-through"><?php echo formatPrice($item['product_price'] * $item['quantity']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden mb-8">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-calculator mr-3 text-gray-600"></i>
                        Order Summary
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Tax (<?php echo ($taxRate * 100); ?>%)</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span class="<?php echo $order['shipping_cost'] > 0 ? 'text-gray-800' : 'text-green-600'; ?>">
                                <?php echo $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'FREE'; ?>
                            </span>
                        </div>
                        <?php if (!empty($order['promo_code']) && $order['discount'] > 0): ?>
                        <div class="flex justify-between text-green-600 font-semibold">
                            <span>Promo Code (<?php echo htmlspecialchars($order['promo_code']); ?>)</span>
                            <span>-<?php echo formatPrice($order['discount']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-xl font-bold text-gray-900">
                                <span>Total</span>
                                <span><?php echo formatPrice($order['total']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8">
                <h3 class="text-lg font-bold text-blue-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    What happens next?
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-blue-900 mb-2">Order Confirmed</h4>
                        <p class="text-sm text-blue-700">We've received your order and will start processing it immediately.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shipping-fast text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-blue-900 mb-2">Processing & Shipping</h4>
                        <p class="text-sm text-blue-700">We'll prepare your items and ship them within 2-3 business days.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-blue-900 mb-2">Cash on Delivery</h4>
                        <p class="text-sm text-blue-700">Pay the delivery person when you receive your order.</p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="printInvoice()" class="bg-blue-600 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                    <i class="fas fa-print mr-2"></i>
                    Print Invoice
                </button>
                <a href="shop.php" class="bg-black text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-800 transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    Continue Shopping
                </a>
                <a href="index.php" class="bg-gray-200 text-gray-800 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-300 transition-all duration-300 transform hover:scale-105 shadow-lg text-center">
                    <i class="fas fa-home mr-2"></i>
                    Back to Home
                </a>
            </div>
            
            <!-- Contact Information -->
            <div class="mt-8 text-center">
                <p class="text-gray-600 mb-2">Have questions about your order?</p>
                <p class="text-sm text-gray-500">
                    Contact us at <a href="mailto:support@beam.com" class="text-black hover:underline">support@beam.com</a> 
                    or call us at <a href="tel:+21612345678" class="text-black hover:underline">+216 12 345 678</a>
                </p>
            </div>
        </div>
    </main>

    <?php require_once 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Print order details
        const printOrder = () => {
            window.print();
        };
        
        // Add print functionality if needed
        // document.getElementById('print-order').addEventListener('click', printOrder);
    });
    
    function printInvoice() {
        // Open invoice in new window
        const printWindow = window.open('invoice.php?order=<?php echo urlencode($order['order_number']); ?>', '_blank');
        if (printWindow) {
            printWindow.focus();
        } else {
            alert('Please allow pop-ups for this site to print the invoice.');
        }
    }
    </script>
</body>
</html> 