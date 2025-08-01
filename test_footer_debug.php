<?php
require_once 'includes/functions.php';

// Test database connection
echo "<h1>Footer Debug Test</h1>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test footer_sections table
echo "<h2>Testing footer_sections table</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM footer_sections WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $sections = $stmt->fetchAll();
    
    echo "<p>Found " . count($sections) . " active footer sections:</p>";
    echo "<ul>";
    foreach ($sections as $section) {
        echo "<li>ID: {$section['id']}, Name: {$section['section_name']}, Title: {$section['section_title']}</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error querying footer_sections: " . $e->getMessage() . "</p>";
}

// Test footer_links table
echo "<h2>Testing footer_links table</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM footer_links WHERE is_active = 1 ORDER BY sort_order ASC");
    $stmt->execute();
    $links = $stmt->fetchAll();
    
    echo "<p>Found " . count($links) . " active footer links:</p>";
    echo "<ul>";
    foreach ($links as $link) {
        echo "<li>ID: {$link['id']}, Section ID: {$link['section_id']}, Text: {$link['link_text']}, URL: {$link['link_url']}</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error querying footer_links: " . $e->getMessage() . "</p>";
}

// Test getFooterData function
echo "<h2>Testing getFooterData() function</h2>";
try {
    $footerData = getFooterData();
    echo "<p>getFooterData() returned " . count($footerData) . " sections:</p>";
    
    foreach ($footerData as $section) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<h3>Section: {$section['section_title']}</h3>";
        echo "<p>Links (" . count($section['links']) . "):</p>";
        echo "<ul>";
        foreach ($section['links'] as $link) {
            echo "<li><a href='{$link['link_url']}'>{$link['link_text']}</a></li>";
        }
        echo "</ul>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error in getFooterData(): " . $e->getMessage() . "</p>";
}

// Test social media data
echo "<h2>Testing social media data</h2>";
try {
    $socialMedia = getSocialMedia();
    echo "<p>Found " . count($socialMedia) . " social media links:</p>";
    echo "<ul>";
    foreach ($socialMedia as $social) {
        echo "<li>Text: {$social['link_text']}, URL: {$social['link_url']}</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error in getSocialMedia(): " . $e->getMessage() . "</p>";
}

// Test site settings
echo "<h2>Testing site settings</h2>";
try {
    $brandLogo = getSiteSetting('brand_logo', 'images/logo.webp');
    $brandLogo2 = getSiteSetting('brand_logo2', 'images/logo2.png');
    
    echo "<p>Brand Logo: {$brandLogo}</p>";
    echo "<p>Brand Logo 2: {$brandLogo2}</p>";
    
    // Check if logo files exist
    if (file_exists($brandLogo)) {
        echo "<p style='color: green;'>✅ Brand logo file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Brand logo file not found: {$brandLogo}</p>";
    }
    
    if (file_exists($brandLogo2)) {
        echo "<p style='color: green;'>✅ Brand logo 2 file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Brand logo 2 file not found: {$brandLogo2}</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error in getSiteSetting(): " . $e->getMessage() . "</p>";
}

// Test table structure
echo "<h2>Testing table structure</h2>";
try {
    // Check footer_sections table structure
    $stmt = $pdo->prepare("DESCRIBE footer_sections");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<h3>footer_sections table structure:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}</li>";
    }
    echo "</ul>";
    
    // Check footer_links table structure
    $stmt = $pdo->prepare("DESCRIBE footer_links");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<h3>footer_links table structure:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']}</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking table structure: " . $e->getMessage() . "</p>";
}

echo "<h2>Footer Debug Complete</h2>";
?> 