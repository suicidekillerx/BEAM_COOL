<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'beam_ecommerce';

// Path to SQL file
$sqlFile = __DIR__ . '/../../aboutus.sql';

// Check if file exists
if (!file_exists($sqlFile)) {
    die("<h2>Error: SQL file not found at $sqlFile</h2>");
}

// Read SQL file
$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die("<h2>Error: Could not read SQL file</h2>");
}

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    
    // Drop the aboutus table if it exists
    $pdo->exec("DROP TABLE IF EXISTS `aboutus`");
    
    // Execute SQL queries
    $pdo->exec($sql);
    
    // Verify import
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `aboutus`");
    $count = $stmt->fetch()['count'];
    
    echo "<h2>Successfully imported aboutus.sql</h2>";
    echo "<p>Imported $count records into the aboutus table.</p>";
    
    // Show first 5 records
    echo "<h3>First 5 Records:</h3>";
    $stmt = $pdo->query("SELECT * FROM `aboutus` LIMIT 5");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
    
    // Link to check content
    echo "<p><a href='check_about_keys.php'>Check About Page Content</a></p>";
    
} catch (PDOException $e) {
    die("<h2>Error: " . $e->getMessage() . "</h2>");
}
?>
