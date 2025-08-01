<?php
require_once '../includes/functions.php';

$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    echo '<p class="text-red-600">Invalid order number</p>';
    exit;
}

$pdo = getDBConnection();

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_quantity
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.order_number = ?
    GROUP BY o.id
");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

if (!$order) {
    echo '<p class="text-red-600">Order not found</p>';
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.color, pi.image_path
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id']]);
$orderItems = $stmt->fetchAll();

// Add default image if none exists
foreach ($orderItems as &$item) {
    if (!$item['image_path']) {
        $item['image_path'] = '../images/placeholder.jpg';
    } else {
        $item['image_path'] = '../' . $item['image_path'];
    }
}
?>

<div class="space-y-6">
    <!-- Order Header -->
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($order['order_number']); ?></h3>
                <p class="text-sm text-gray-600">Created: <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                <p class="text-sm text-gray-600">Last Updated: <?php echo date('F j, Y \a\t g:i A', strtotime($order['updated_at'])); ?></p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-gray-900"><?php echo formatPrice($order['total']); ?></p>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold <?php echo getStatusColor($order['order_status']); ?>">
                    <?php echo ucfirst($order['order_status']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Customer Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm font-medium text-gray-700">Name</p>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Email</p>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($order['customer_email']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Phone</p>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($order['customer_phone']); ?></p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Payment Method</p>
                <p class="text-sm text-gray-900"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
            </div>
        </div>
    </div>

    <!-- Shipping Information -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Shipping Information</h4>
        <div class="space-y-2">
            <div>
                <p class="text-sm font-medium text-gray-700">Address</p>
                <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">City</p>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($order['shipping_city']); ?></p>
                </div>
                <?php if ($order['shipping_postal_code']): ?>
                <div>
                    <p class="text-sm font-medium text-gray-700">Postal Code</p>
                    <p class="text-sm text-gray-900"><?php echo htmlspecialchars($order['shipping_postal_code']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-700">Delivery Notes</p>
                <?php if (!empty($order['shipping_notes'])): ?>
                    <p class="text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($order['shipping_notes'])); ?></p>
                <?php else: ?>
                    <p class="text-sm text-gray-500 italic">No delivery notes provided</p>
                <?php endif; ?>
            </div>
            <?php if (!empty($order['tracking_number'])): ?>
            <div>
                <p class="text-sm font-medium text-gray-700">Tracking Number</p>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($order['tracking_number']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Items -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Order Items (<?php echo $order['item_count']; ?> items)</h4>
        <div class="space-y-3">
            <?php foreach ($orderItems as $item): ?>
            <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                     class="w-16 h-16 object-cover rounded-lg">
                <div class="flex-1">
                    <h5 class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></h5>
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

    <!-- Order Summary -->
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Order Summary</h4>
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Subtotal</span>
                    <span class="text-sm font-medium text-gray-900"><?php echo formatPrice($order['subtotal']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Tax</span>
                    <span class="text-sm font-medium text-gray-900"><?php echo formatPrice($order['tax']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Shipping</span>
                    <span class="text-sm font-medium text-gray-900"><?php echo formatPrice($order['shipping_cost']); ?></span>
                </div>
                <?php if (!empty($order['promo_code'])): ?>
                <div class="border-t border-gray-200 pt-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Promo Code (<?php echo htmlspecialchars($order['promo_code']); ?>)</span>
                        <span class="text-sm font-medium text-green-600">-<?php echo formatPrice($order['discount']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="border-t border-gray-200 pt-2">
                    <div class="flex justify-between">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="font-bold text-gray-900"><?php echo formatPrice($order['total']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <?php if ($order['notes'] || $order['admin_notes']): ?>
    <div>
        <h4 class="text-lg font-semibold text-gray-900 mb-3">Notes</h4>
        <div class="space-y-3">
            <?php if ($order['notes']): ?>
            <div>
                <p class="text-sm font-medium text-gray-700">Customer Notes</p>
                <p class="text-sm text-gray-900 bg-yellow-50 p-3 rounded-lg"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($order['admin_notes'])): ?>
            <div>
                <p class="text-sm font-medium text-gray-700">Admin Notes</p>
                <p class="text-sm text-gray-900 bg-blue-50 p-3 rounded-lg"><?php echo nl2br(htmlspecialchars($order['admin_notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="flex space-x-3 pt-4 border-t border-gray-200">
        <button onclick="editOrder(<?php echo $order['id']; ?>)" 
                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-edit mr-2"></i>Edit Order
        </button>
        <button onclick="printInvoice('<?php echo $order['order_number']; ?>')" 
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
            <i class="fas fa-print mr-2"></i>Print Invoice
        </button>
        <button onclick="closeOrderModal()" 
                class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            Close
        </button>
    </div>
</div>

<?php
function getStatusColor($status) {
    $colors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'processing' => 'bg-purple-100 text-purple-800',
        'shipped' => 'bg-indigo-100 text-indigo-800',
        'delivered' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'refunded' => 'bg-gray-100 text-gray-800'
    ];
    return $colors[$status] ?? 'bg-gray-100 text-gray-800';
}
?> 