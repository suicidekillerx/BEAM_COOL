<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Database Cleanup Script</h1>";

try {
    $pdo = getDBConnection();
    
    // Check current data
    echo "<h2>Current Data Count:</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM social_media");
    $socialCount = $stmt->fetch()['count'];
    echo "Social Media Items: $socialCount<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM footer_sections");
    $sectionCount = $stmt->fetch()['count'];
    echo "Footer Sections: $sectionCount<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM footer_links");
    $linkCount = $stmt->fetch()['count'];
    echo "Footer Links: $linkCount<br>";
    
    // Clean up social media - keep only one of each platform
    echo "<h2>Cleaning Social Media...</h2>";
    $pdo->exec("DELETE FROM social_media WHERE id NOT IN (
        SELECT MIN(id) FROM social_media GROUP BY platform
    )");
    echo "Social media cleaned!<br>";
    
    // Clean up footer sections - keep only one of each title
    echo "<h2>Cleaning Footer Sections...</h2>";
    $pdo->exec("DELETE FROM footer_sections WHERE id NOT IN (
        SELECT MIN(id) FROM footer_sections GROUP BY section_title
    )");
    echo "Footer sections cleaned!<br>";
    
    // Clean up footer links - keep only one of each link per section
    echo "<h2>Cleaning Footer Links...</h2>";
    $pdo->exec("DELETE FROM footer_links WHERE id NOT IN (
        SELECT MIN(id) FROM footer_links GROUP BY section_id, link_text
    )");
    echo "Footer links cleaned!<br>";
    
    // Check final data
    echo "<h2>Final Data Count:</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM social_media");
    $socialCount = $stmt->fetch()['count'];
    echo "Social Media Items: $socialCount<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM footer_sections");
    $sectionCount = $stmt->fetch()['count'];
    echo "Footer Sections: $sectionCount<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM footer_links");
    $linkCount = $stmt->fetch()['count'];
    echo "Footer Links: $linkCount<br>";
    
    echo "<h2>Database cleaned successfully!</h2>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo $e->getMessage();
}
?> 