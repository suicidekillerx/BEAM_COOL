<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Promo Code Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">Checkout Promo Code Test</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">✅ Changes Made:</h2>
                <ul class="space-y-2 text-sm">
                    <li>• <strong>Database:</strong> Added discount, promo_code_id, promo_code fields to orders table</li>
                    <li>• <strong>Checkout Calculation:</strong> Includes promo code discount in total calculation</li>
                    <li>• <strong>Order Creation:</strong> Records promo code information with order</li>
                    <li>• <strong>Usage Tracking:</strong> Records promo code usage after order completion</li>
                    <li>• <strong>Visual Display:</strong> Shows applied promo code and discount on checkout</li>
                    <li>• <strong>Session Cleanup:</strong> Removes applied promo code after order</li>
                </ul>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4">🎯 Test Process:</h2>
                <ol class="space-y-2 text-sm">
                    <li>1. <strong>Add Items:</strong> Add products to cart</li>
                    <li>2. <strong>Apply Promo:</strong> Go to cart and apply a promo code</li>
                    <li>3. <strong>Check Cart:</strong> Verify discount shows in cart</li>
                    <li>4. <strong>Proceed to Checkout:</strong> Go to checkout page</li>
                    <li>5. <strong>Verify Discount:</strong> Check that discount appears in checkout</li>
                    <li>6. <strong>Complete Order:</strong> Place the order</li>
                    <li>7. <strong>Check Database:</strong> Verify order has promo code info</li>
                </ol>
            </div>
        </div>
        
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-blue-800">📝 Test Steps:</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold text-blue-700">Step 1: Add Items to Cart</h3>
                    <p class="text-sm text-blue-600">Visit the shop and add some products to your cart</p>
                    <a href="shop.php" class="inline-block mt-2 bg-blue-600 text-white px-4 py-2 rounded text-sm">Go to Shop</a>
                </div>
                
                <div>
                    <h3 class="font-semibold text-blue-700">Step 2: Apply Promo Code</h3>
                    <p class="text-sm text-blue-600">Go to cart and apply a promo code (e.g., TEST10)</p>
                    <a href="view_cart.php" class="inline-block mt-2 bg-blue-600 text-white px-4 py-2 rounded text-sm">Go to Cart</a>
                </div>
                
                <div>
                    <h3 class="font-semibold text-blue-700">Step 3: Test Checkout</h3>
                    <p class="text-sm text-blue-600">Proceed to checkout and verify the discount is applied</p>
                    <a href="checkout.php" class="inline-block mt-2 bg-blue-600 text-white px-4 py-2 rounded text-sm">Go to Checkout</a>
                </div>
            </div>
        </div>
        
        <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-green-800">✅ Expected Results:</h2>
            <ul class="space-y-2 text-sm text-green-700">
                <li>• <strong>Cart:</strong> Shows promo code discount in price breakdown</li>
                <li>• <strong>Checkout:</strong> Shows applied promo code with green badge</li>
                <li>• <strong>Checkout:</strong> Shows discount line item in price breakdown</li>
                <li>• <strong>Order:</strong> Total includes the discount</li>
                <li>• <strong>Database:</strong> Order record includes promo code information</li>
                <li>• <strong>Usage:</strong> Promo code usage is recorded</li>
            </ul>
        </div>
        
        <div class="mt-8 text-center">
            <a href="add_items_to_current_session.php" 
               class="inline-block bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors mr-4">
                Add Test Items
            </a>
            <a href="view_cart.php" 
               class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                Test Cart with Promo
            </a>
        </div>
    </div>
</body>
</html> 