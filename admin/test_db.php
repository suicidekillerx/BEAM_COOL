<?php
require_once __DIR__ . '/../includes/functions.php';

// Test database connection
try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM aboutus");
    $result = $stmt->fetch();
    echo "<p>Found " . $result['count'] . " records in aboutus table.</p>";
    
    // Show first few records
    $stmt = $pdo->query("SELECT * FROM aboutus LIMIT 5");
    echo "<h3>Sample Records:</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database connection failed: " . $e->getMessage() . "</p>";
}

// Test getAllAboutContent
if (function_exists('getAllAboutContent')) {
    echo "<h2>Testing getAllAboutContent()</h2>";
    $content = getAllAboutContent();
    echo "<pre>";
    print_r($content);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>getAllAboutContent() function not found!</p>";
}
?>
