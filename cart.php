<?php
require_once 'includes/functions.php';

// Check maintenance mode before rendering the page
checkMaintenanceMode();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartItems = getCartItems();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Beam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">Your cart is empty</p>
                <a href="index.php" class="mt-4 inline-block bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="space-y-4">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">IMG</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-600">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                                    <p class="text-gray-600">Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold"><?php echo formatPrice($item['sale_price'] ?? $item['price']); ?></p>
                                <button class="text-red-500 text-sm hover:text-red-700" onclick="removeFromCart(<?php echo $item['id']; ?>)">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold">Total:</span>
                        <span class="text-lg font-semibold">
                            <?php 
                            $total = 0;
                            foreach ($cartItems as $item) {
                                $price = $item['sale_price'] ?? $item['price'];
                                $total += $price * $item['quantity'];
                            }
                            echo formatPrice($total);
                            ?>
                        </span>
                    </div>
                    <button class="w-full mt-4 bg-black text-white py-3 rounded-lg hover:bg-gray-800">Checkout</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function removeFromCart(cartItemId) {
            $.ajax({
                url: 'includes/functions.php',
                method: 'POST',
                data: {
                    action: 'update_cart',
                    cart_item_id: cartItemId,
                    quantity: 0
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                  
                }
            });
        }
    </script>
</body>
</html>     </script>
</body>
</html> 
