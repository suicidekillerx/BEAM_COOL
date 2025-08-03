<?php
require_once 'includes/functions.php';

echo "=== Removing Test Discount ===\n";

$pdo = getDBConnection();

// Remove the test discount (ID: 11)
$stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = 11");
if ($stmt->execute()) {
    echo "✓ Test Discount (ID: 11) removed successfully!\n";
} else {
    echo "✗ Failed to remove Test Discount\n";
}

// Also remove the zzz code (ID: 12)
$stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = 12");
if ($stmt->execute()) {
    echo "✓ zzz code (ID: 12) removed successfully!\n";
} else {
    echo "✗ Failed to remove zzz code\n";
}

echo "\n=== Remaining Promo Codes ===\n";
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

echo "\nDone! You can now use the remaining promo codes or create new ones.\n";
?> 