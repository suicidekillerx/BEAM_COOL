<?php
require_once 'config/database.php';

echo "=== Create New Promo Code ===\n\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=beam_ecommerce", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user input
    echo "Enter promo code (e.g., SAVE20): ";
    $handle = fopen("php://stdin", "r");
    $code = trim(fgets($handle));
    
    echo "Enter name (e.g., 20% Off): ";
    $name = trim(fgets($handle));
    
    echo "Enter type (percentage/fixed_amount): ";
    $type = trim(fgets($handle));
    
    echo "Enter value: ";
    $value = floatval(trim(fgets($handle)));
    
    echo "Enter minimum order amount (0 for no minimum): ";
    $minOrder = floatval(trim(fgets($handle)));
    
    echo "Enter max discount (0 for no limit): ";
    $maxDiscount = floatval(trim(fgets($handle)));
    
    // Create the promo code
    $stmt = $pdo->prepare("
        INSERT INTO promo_codes (
            code, name, description, type, value, min_order_amount, 
            max_discount, usage_limit, user_limit, applies_to, 
            category_ids, product_ids, excluded_categories, excluded_products,
            start_date, end_date, is_active, is_first_time_only, is_single_use
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $code,
        $name,
        '', // description
        $type,
        $value,
        $minOrder,
        $maxDiscount > 0 ? $maxDiscount : null,
        null, // usage_limit
        null, // user_limit
        'all', // applies_to
        null, // category_ids
        null, // product_ids
        null, // excluded_categories
        null, // excluded_products
        null, // start_date
        null, // end_date
        1, // is_active
        0, // is_first_time_only
        0  // is_single_use
    ]);
    
    if ($result) {
        echo "\n✓ Promo code '{$code}' created successfully!\n";
        echo "Name: {$name}\n";
        echo "Type: {$type}\n";
        echo "Value: {$value}\n";
        echo "Min Order: {$minOrder}\n";
        if ($maxDiscount > 0) {
            echo "Max Discount: {$maxDiscount}\n";
        }
    } else {
        echo "\n✗ Failed to create promo code\n";
    }
    
    fclose($handle);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 