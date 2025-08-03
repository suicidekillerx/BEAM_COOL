<?php
require_once 'config/database.php';

echo "=== Current Promo Codes ===\n";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=beam_ecommerce", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM promo_codes ORDER BY id");
    $stmt->execute();
    $codes = $stmt->fetchAll();
    
    if (empty($codes)) {
        echo "No promo codes found.\n";
    } else {
        foreach ($codes as $code) {
            echo "ID: {$code['id']} | Code: {$code['code']} | Name: {$code['name']} | Type: {$code['type']} | Value: {$code['value']} | Active: {$code['is_active']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 