<?php
// Safe database import script
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Reading SQL file...\n";
    $sqlFile = 'beam_ecommerce (2).sql';
    
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    echo "Processing " . count($statements) . " statements...\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            // Skip DROP statements for procedures/functions if they exist
            if (preg_match('/DROP\s+(PROCEDURE|FUNCTION)\s+IF\s+EXISTS\s+`?(\w+)`?/i', $statement, $matches)) {
                $type = strtolower($matches[1]);
                $name = $matches[2];
                
                // Check if procedure/function exists
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = ? AND routine_name = ? AND routine_type = ?");
                $checkStmt->execute([DB_NAME, $name, strtoupper($type)]);
                $exists = $checkStmt->fetchColumn() > 0;
                
                if ($exists) {
                    echo "Skipping DROP $type '$name' (already exists)\n";
                    continue;
                }
            }
            
            // Skip CREATE statements for procedures/functions if they exist
            if (preg_match('/CREATE\s+(PROCEDURE|FUNCTION)\s+`?(\w+)`?/i', $statement, $matches)) {
                $type = strtolower($matches[1]);
                $name = $matches[2];
                
                // Check if procedure/function exists
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = ? AND routine_name = ? AND routine_type = ?");
                $checkStmt->execute([DB_NAME, $name, strtoupper($type)]);
                $exists = $checkStmt->fetchColumn() > 0;
                
                if ($exists) {
                    echo "Skipping CREATE $type '$name' (already exists)\n";
                    continue;
                }
            }
            
            // Execute the statement
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