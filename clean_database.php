<?php
// Database cleaning script
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Dropping database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    
    echo "Creating fresh database...\n";
    $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    echo "Database cleaned successfully!\n";
    echo "Now you can import your SQL file without conflicts.\n";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?> 