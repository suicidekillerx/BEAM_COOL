<?php
require_once 'config/database.php';

echo "=== Remove Promo Code ===\n\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=beam_ecommerce", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Show current promo codes
    echo "Current Promo Codes:\n";
    $stmt = $pdo->prepare("SELECT * FROM promo_codes ORDER BY id");
    $stmt->execute();
    $codes = $stmt->fetchAll();
    
    if (empty($codes)) {
        echo "No promo codes found.\n";
        exit;
    }
    
    foreach ($codes as $code) {
        echo "ID: {$code['id']} | Code: {$code['code']} | Name: {$code['name']} | Type: {$code['type']} | Value: {$code['value']}\n";
    }
    
    echo "\nEnter the ID of the promo code to remove: ";
    $handle = fopen("php://stdin", "r");
    $id = intval(trim(fgets($handle)));
    
    // Remove the promo code
    $stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo "\n✓ Promo code with ID {$id} removed successfully!\n";
    } else {
        echo "\n✗ Failed to remove promo code with ID {$id} (not found)\n";
    }
    
    fclose($handle);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 