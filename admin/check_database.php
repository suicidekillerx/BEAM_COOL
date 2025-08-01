<?php
// Database configuration
$host = 'localhost';
$db   = 'beam_ecommerce';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $user, $pass, $options);
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$db'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        die("<h2>Database '$db' does not exist.</h2>");
    }
    
    echo "<h2>Database '$db' exists.</h2>";
    
    // Connect to the database
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if aboutus table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'aboutus'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        die("<h2>Table 'aboutus' does not exist in database '$db'.</h2>");
    }
    
    echo "<h2>Table 'aboutus' exists.</h2>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $stmt = $pdo->query("DESCRIBE aboutus");
    echo "<pre>";
    print_r($stmt->fetchAll());
    echo "</pre>";
    
    // Show first 5 records
    echo "<h3>First 5 Records:</h3>";
    $stmt = $pdo->query("SELECT * FROM aboutus LIMIT 5");
    echo "<pre>";
    print_r($stmt->fetchAll());
    echo "</pre>";
    
    // Show count of records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM aboutus");
    $count = $stmt->fetch()['count'];
    echo "<p>Total records in aboutus table: $count</p>";
    
} catch (PDOException $e) {
    die("<h2>Error: " . $e->getMessage() . "</h2>");
}
?>
