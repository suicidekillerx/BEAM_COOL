<?php
// Test script for multiple password generation
require_once 'includes/functions.php';

echo "=== Multiple Password Generation Test ===\n\n";

// Test the generateUniquePassword function
echo "Testing password generation:\n";
for ($i = 1; $i <= 5; $i++) {
    $password = generateUniquePassword();
    echo "Generated password $i: $password\n";
}

echo "\n=== How the new feature works ===\n";
echo "1. Go to Admin Panel > Password Management\n";
echo "2. Click 'Add Password'\n";
echo "3. Enter a name (optional)\n";
echo "4. Set 'Max Uses' to a number (e.g., 100)\n";
echo "5. Check 'Generate Multiple Unique Passwords'\n";
echo "6. Click 'Add Password'\n";
echo "\nThis will create 100 unique passwords, each usable by 1 person.\n";
echo "Each password will be named 'Your Name #1', 'Your Name #2', etc.\n";

echo "\n=== Benefits ===\n";
echo "✅ Each person gets a unique password\n";
echo "✅ You can track who used which password\n";
echo "✅ Passwords are automatically single-use\n";
echo "✅ No password sharing between users\n";
echo "✅ Easy to manage and revoke individual passwords\n";

echo "\n=== Example Usage ===\n";
echo "If you want to give access to 100 people:\n";
echo "- Set Max Uses: 100\n";
echo "- Check 'Generate Multiple Unique Passwords'\n";
echo "- Name: 'VIP Access'\n";
echo "- Result: 100 unique passwords like 'VIP Access #1', 'VIP Access #2', etc.\n";
?> 