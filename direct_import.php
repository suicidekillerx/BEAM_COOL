<?php
// Direct SQL import script
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    echo "Reading SQL file...\n";
    $sqlFile = 'beam_ecommerce (2).sql';
    
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments and clean up
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split by DELIMITER statements
    $parts = preg_split('/DELIMITER\s+(\S+)/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE);
    
    $currentDelimiter = ';';
    $statements = [];
    
    for ($i = 0; $i < count($parts); $i++) {
        if ($i % 2 == 0) {
            // This is SQL content
            $content = trim($parts[$i]);
            if (!empty($content)) {
                // Split by current delimiter
                $sqlStatements = explode($currentDelimiter, $content);
                foreach ($sqlStatements as $stmt) {
                    $stmt = trim($stmt);
                    if (!empty($stmt)) {
                        $statements[] = $stmt;
                    }
                }
            }
        } else {
            // This is a new delimiter
            $currentDelimiter = $parts[$i];
        }
    }
    
    echo "Processing " . count($statements) . " statements...\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            // Skip certain problematic statements
            if (preg_match('/^(SET|CREATE\s+(PROCEDURE|FUNCTION)|DROP\s+(PROCEDURE|FUNCTION)|DELIMITER)/i', $statement)) {
                echo "Skipping: " . substr($statement, 0, 50) . "...\n";
                continue;
            }
            
            $pdo->exec($statement);
            $successCount++;
            
        } catch (PDOException $e) {
            $errorCount++;
            echo "Error in statement: " . substr($statement, 0, 100) . "...\n";
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nImport completed!\n";
    echo "Successful statements: $successCount\n";
    echo "Failed statements: $errorCount\n";
    
} catch(PDOException $e) {
    die("Connection error: " . $e->getMessage());
}
?> 