<?php
require_once 'includes/functions.php';

// Get the current auto_open_cart setting
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'auto_open_cart'");
$stmt->execute();
$autoOpenCart = $stmt->fetchColumn() ?: '1'; // Default to '1' if not set
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Open Cart Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Auto Open Cart Test</h1>
        
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h2 class="font-semibold text-blue-800 mb-2">Current Setting</h2>
            <p class="text-blue-700">
                Auto Open Cart: <span class="font-bold"><?php echo $autoOpenCart === '1' ? 'ENABLED' : 'DISABLED'; ?></span>
            </p>
            <p class="text-sm text-blue-600 mt-2">
                When enabled: User will be redirected to cart page after adding items<br>
                When disabled: User stays on current page, cart count updates
            </p>
        </div>
        
        <div class="mb-6">
            <h2 class="font-semibold mb-3">Test Add to Cart</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Size:</label>
                    <select id="test-size" class="w-full p-2 border rounded">
                        <option value="S">Small</option>
                        <option value="M">Medium</option>
                        <option value="L">Large</option>
                        <option value="XL">XL</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Quantity:</label>
                    <input type="number" id="test-quantity" value="1" min="1" max="10" class="w-full p-2 border rounded">
                </div>
                
                <button id="test-add-to-cart" class="w-full bg-black text-white py-3 rounded font-semibold hover:bg-gray-800 transition-colors">
                    Add to Cart (Test)
                </button>
            </div>
        </div>
        
        <div class="mb-6">
            <h2 class="font-semibold mb-3">How to Test</h2>
            <ol class="list-decimal list-inside space-y-2 text-sm">
                <li>Go to Admin Panel → Settings → Site Settings → System Settings</li>
                <li>Toggle "Auto Open Cart" setting</li>
                <li>Come back to this page and click "Add to Cart (Test)"</li>
                <li>Observe the behavior:
                    <ul class="list-disc list-inside ml-4 mt-1">
                        <li><strong>Enabled:</strong> You'll be redirected to cart page after 1.5 seconds</li>
                        <li><strong>Disabled:</strong> You'll stay on this page, see success message</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="text-center">
            <a href="admin/setting.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded font-semibold hover:bg-blue-700 transition-colors">
                Go to Admin Settings
            </a>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#test-add-to-cart').click(function() {
            const button = $(this);
            const originalText = button.text();
            
            // Disable button and show loading
            button.prop('disabled', true);
            button.text('Adding...');
            
            // Make AJAX request
            $.ajax({
                url: 'ajax_handler.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'add_to_cart',
                    product_id: 1, // Test product ID
                    size: $('#test-size').val(),
                    quantity: parseInt($('#test-quantity').val())
                },
                success: function(response) {
                    console.log('Response:', response);
                    
                    if (response.success) {
                        // Show success message
                        button.removeClass('bg-black').addClass('bg-green-600');
                        button.text('Added Successfully!');
                        
                        // Check auto_open_cart setting
                        if (response.auto_open_cart) {
                            // Show redirect message
                            setTimeout(function() {
                                button.text('Redirecting to cart...');
                            }, 500);
                            
                            // Redirect after delay
                            setTimeout(function() {
                                window.location.href = 'view_cart.php';
                            }, 1500);
                        } else {
                            // Just show success and reset
                            setTimeout(function() {
                                button.removeClass('bg-green-600').addClass('bg-black');
                                button.text(originalText);
                                button.prop('disabled', false);
                            }, 2000);
                        }
                    } else {
                        // Show error
                        button.removeClass('bg-black').addClass('bg-red-600');
                        button.text('Error: ' + response.message);
                        
                        setTimeout(function() {
                            button.removeClass('bg-red-600').addClass('bg-black');
                            button.text(originalText);
                            button.prop('disabled', false);
                        }, 2000);
                    }
                },
                error: function() {
                    button.removeClass('bg-black').addClass('bg-red-600');
                    button.text('Network Error');
                    
                    setTimeout(function() {
                        button.removeClass('bg-red-600').addClass('bg-black');
                        button.text(originalText);
                        button.prop('disabled', false);
                    }, 2000);
                }
            });
        });
    });
    </script>
</body>
</html> 
require_once 'includes/functions.php';

// Get the current auto_open_cart setting
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'auto_open_cart'");
$stmt->execute();
$autoOpenCart = $stmt->fetchColumn() ?: '1'; // Default to '1' if not set
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Open Cart Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Auto Open Cart Test</h1>
        
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h2 class="font-semibold text-blue-800 mb-2">Current Setting</h2>
            <p class="text-blue-700">
                Auto Open Cart: <span class="font-bold"><?php echo $autoOpenCart === '1' ? 'ENABLED' : 'DISABLED'; ?></span>
            </p>
            <p class="text-sm text-blue-600 mt-2">
                When enabled: User will be redirected to cart page after adding items<br>
                When disabled: User stays on current page, cart count updates
            </p>
        </div>
        
        <div class="mb-6">
            <h2 class="font-semibold mb-3">Test Add to Cart</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Size:</label>
                    <select id="test-size" class="w-full p-2 border rounded">
                        <option value="S">Small</option>
                        <option value="M">Medium</option>
                        <option value="L">Large</option>
                        <option value="XL">XL</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1">Quantity:</label>
                    <input type="number" id="test-quantity" value="1" min="1" max="10" class="w-full p-2 border rounded">
                </div>
                
                <button id="test-add-to-cart" class="w-full bg-black text-white py-3 rounded font-semibold hover:bg-gray-800 transition-colors">
                    Add to Cart (Test)
                </button>
            </div>
        </div>
        
        <div class="mb-6">
            <h2 class="font-semibold mb-3">How to Test</h2>
            <ol class="list-decimal list-inside space-y-2 text-sm">
                <li>Go to Admin Panel → Settings → Site Settings → System Settings</li>
                <li>Toggle "Auto Open Cart" setting</li>
                <li>Come back to this page and click "Add to Cart (Test)"</li>
                <li>Observe the behavior:
                    <ul class="list-disc list-inside ml-4 mt-1">
                        <li><strong>Enabled:</strong> You'll be redirected to cart page after 1.5 seconds</li>
                        <li><strong>Disabled:</strong> You'll stay on this page, see success message</li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <div class="text-center">
            <a href="admin/setting.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded font-semibold hover:bg-blue-700 transition-colors">
                Go to Admin Settings
            </a>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $('#test-add-to-cart').click(function() {
            const button = $(this);
            const originalText = button.text();
            
            // Disable button and show loading
            button.prop('disabled', true);
            button.text('Adding...');
            
            // Make AJAX request
            $.ajax({
                url: 'ajax_handler.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'add_to_cart',
                    product_id: 1, // Test product ID
                    size: $('#test-size').val(),
                    quantity: parseInt($('#test-quantity').val())
                },
                success: function(response) {
                    console.log('Response:', response);
                    
                    if (response.success) {
                        // Show success message
                        button.removeClass('bg-black').addClass('bg-green-600');
                        button.text('Added Successfully!');
                        
                        // Check auto_open_cart setting
                        if (response.auto_open_cart) {
                            // Show redirect message
                            setTimeout(function() {
                                button.text('Redirecting to cart...');
                            }, 500);
                            
                            // Redirect after delay
                            setTimeout(function() {
                                window.location.href = 'view_cart.php';
                            }, 1500);
                        } else {
                            // Just show success and reset
                            setTimeout(function() {
                                button.removeClass('bg-green-600').addClass('bg-black');
                                button.text(originalText);
                                button.prop('disabled', false);
                            }, 2000);
                        }
                    } else {
                        // Show error
                        button.removeClass('bg-black').addClass('bg-red-600');
                        button.text('Error: ' + response.message);
                        
                        setTimeout(function() {
                            button.removeClass('bg-red-600').addClass('bg-black');
                            button.text(originalText);
                            button.prop('disabled', false);
                        }, 2000);
                    }
                },
                error: function() {
                    button.removeClass('bg-black').addClass('bg-red-600');
                    button.text('Network Error');
                    
                    setTimeout(function() {
                        button.removeClass('bg-red-600').addClass('bg-black');
                        button.text(originalText);
                        button.prop('disabled', false);
                    }, 2000);
                }
            });
        });
    });
    </script>
</body>
</html> 