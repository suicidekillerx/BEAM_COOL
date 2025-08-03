<?php
// Script to help find database credentials
header('Content-Type: text/html');

echo "<h1>How to Find Your Database Credentials</h1>";

echo "<h2>Step 1: Check Your Hosting Control Panel</h2>";
echo "<p>1. Log into your hosting control panel (cPanel, Plesk, etc.)</p>";
echo "<p>2. Look for these sections:</p>";
echo "<ul>";
echo "<li><strong>MySQL Databases</strong></li>";
echo "<li><strong>Database Management</strong></li>";
echo "<li><strong>phpMyAdmin</strong></li>";
echo "<li><strong>Databases</strong></li>";
echo "</ul>";

echo "<h2>Step 2: Common Locations</h2>";
echo "<p><strong>cPanel:</strong></p>";
echo "<ul>";
echo "<li>Databases → MySQL Databases</li>";
echo "<li>Databases → phpMyAdmin</li>";
echo "</ul>";

echo "<p><strong>Plesk:</strong></p>";
echo "<ul>";
echo "<li>Databases → MySQL</li>";
echo "<li>Databases → phpMyAdmin</li>";
echo "</ul>";

echo "<h2>Step 3: What to Look For</h2>";
echo "<p>You need to find:</p>";
echo "<ul>";
echo "<li><strong>Database Name:</strong> weultcom_beam (we know this)</li>";
echo "<li><strong>Database Username:</strong> Usually same as database name</li>";
echo "<li><strong>Database Password:</strong> The password you set</li>";
echo "<li><strong>Host:</strong> Usually 'localhost'</li>";
echo "</ul>";

echo "<h2>Step 4: Common Password Patterns</h2>";
echo "<p>If you don't remember your password, try these common patterns:</p>";
echo "<ul>";
echo "<li>Database name: <code>weultcom_beam</code></li>";
echo "<li>Short version: <code>beam</code></li>";
echo "<li>Common: <code>password</code></li>";
echo "<li>Common: <code>123456</code></li>";
echo "<li>Common: <code>root</code></li>";
echo "</ul>";

echo "<h2>Step 5: Test with Advanced Database Test</h2>";
echo "<p>1. Upload <code>db_test_advanced.php</code> to your server</p>";
echo "<p>2. Visit: <code>https://weult.com/beam/db_test_advanced.php</code></p>";
echo "<p>3. Look for the configuration that shows '✅ SUCCESS!'</p>";

echo "<h2>Step 6: Update Configuration</h2>";
echo "<p>Once you find the working credentials, update <code>db_config.php</code>:</p>";
echo "<pre>";
echo "return [\n";
echo "    'host' => 'localhost',\n";
echo "    'dbname' => 'weultcom_beam',\n";
echo "    'user' => 'your_username',\n";
echo "    'pass' => 'your_password',\n";
echo "    'charset' => 'utf8mb4'\n";
echo "];\n";
echo "</pre>";

echo "<h2>Step 7: Alternative - Contact Hosting Provider</h2>";
echo "<p>If you can't find the credentials:</p>";
echo "<ul>";
echo "<li>Contact your hosting provider</li>";
echo "<li>Ask for MySQL database credentials</li>";
echo "<li>Provide them with your domain name</li>";
echo "</ul>";

echo "<h2>Step 8: Test Cart Functionality</h2>";
echo "<p>Once you have the correct credentials:</p>";
echo "<ol>";
echo "<li>Update <code>db_config.php</code> with correct credentials</li>";
echo "<li>Test <code>cart_handler.php</code></li>";
echo "<li>Try the cart page functionality</li>";
echo "</ol>";
?> 