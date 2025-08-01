<?php
// Check database status
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "=== Database Status Check ===\n\n";
    
    // Check if database exists and is accessible
    echo "✓ Database connection successful\n";
    
    // List all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n=== Tables Found (" . count($tables) . ") ===\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check key tables for data
    $keyTables = ['products', 'categories', 'collections', 'site_settings'];
    
    echo "\n=== Data Counts ===\n";
    foreach ($keyTables as $table) {
        if (in_array($table, $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "- $table: $count records\n";
        } else {
            echo "- $table: NOT FOUND\n";
        }
    }
    
    // Check if procedures exist
    echo "\n=== Stored Procedures ===\n";
    $stmt = $pdo->query("SHOW PROCEDURE STATUS WHERE Db = '" . DB_NAME . "'");
    $procedures = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (empty($procedures)) {
        echo "- No stored procedures found\n";
    } else {
        foreach ($procedures as $proc) {
            echo "- $proc\n";
        }
    }
    
    // Check if functions exist
    echo "\n=== Stored Functions ===\n";
    $stmt = $pdo->query("SHOW FUNCTION STATUS WHERE Db = '" . DB_NAME . "'");
    $functions = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (empty($functions)) {
        echo "- No stored functions found\n";
    } else {
        foreach ($functions as $func) {
            echo "- $func\n";
        }
    }
    
    echo "\n=== Database Check Complete ===\n";
    echo "Your database is ready to use!\n";
    
} catch(PDOException $e) {
    die("Database check failed: " . $e->getMessage());
}
?> 