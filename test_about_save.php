<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>About Us Save Functionality Test</h1>";

// Test 1: Check if about_content table exists
echo "<h2>Test 1: Database Structure Check</h2>";

try {
    $pdo = getDBConnection();
    
    // Check if about_content table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'about_content'");
    if ($stmt->rowCount() > 0) {
        echo "✅ about_content table exists<br>";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE about_content");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Table structure:<br>";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})<br>";
        }
    } else {
        echo "❌ about_content table does NOT exist<br>";
        echo "Creating about_content table...<br>";
        
        // Create the table if it doesn't exist
        $createTableSQL = "
        CREATE TABLE about_content (
            id INT AUTO_INCREMENT PRIMARY KEY,
            section VARCHAR(50) NOT NULL,
            content_key VARCHAR(100) NOT NULL,
            content_type ENUM('text', 'image', 'icon') DEFAULT 'text',
            content_value TEXT,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_section_key (section, content_key)
        )";
        
        $pdo->exec($createTableSQL);
        echo "✅ about_content table created successfully<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database check failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Test updateAboutContent function
echo "<h2>Test 2: Testing updateAboutContent Function</h2>";

try {
    // Test updating content
    $result = updateAboutContent('hero', 'title_line1', 'TEST ABOUT');
    if ($result) {
        echo "✅ updateAboutContent function works - updated hero.title_line1<br>";
    } else {
        echo "⚠️ updateAboutContent returned false (content might not exist yet)<br>";
    }
    
    // Test inserting new content
    $result = insertAboutContent('test', 'test_key', 'text', 'Test content value');
    if ($result) {
        echo "✅ insertAboutContent function works - inserted test.test_key<br>";
    } else {
        echo "❌ insertAboutContent failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Function test failed: " . $e->getMessage() . "<br>";
}

// Test 3: Test getAllAboutContent function
echo "<h2>Test 3: Testing getAllAboutContent Function</h2>";

try {
    $content = getAllAboutContent(true);
    if (is_array($content)) {
        echo "✅ getAllAboutContent function works<br>";
        echo "Found " . count($content) . " sections:<br>";
        foreach ($content as $section => $sectionData) {
            echo "- $section: " . count($sectionData) . " items<br>";
        }
    } else {
        echo "❌ getAllAboutContent returned non-array: " . gettype($content) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ getAllAboutContent test failed: " . $e->getMessage() . "<br>";
}

// Test 4: Test save_about.php endpoint
echo "<h2>Test 4: Testing save_about.php Endpoint</h2>";

try {
    // Simulate a POST request to save_about.php
    $testData = [
        'key' => 'hero.title_line1',
        'value' => 'TEST SAVE ENDPOINT'
    ];
    
    // Create a temporary file to simulate the request
    $tempFile = tempnam(sys_get_temp_dir(), 'test_about');
    file_put_contents($tempFile, json_encode($testData));
    
    // Simulate the request
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Capture output
    ob_start();
    
    // Include the save_about.php file
    include 'admin/save_about.php';
    
    $output = ob_get_clean();
    
    // Parse the JSON response
    $response = json_decode($output, true);
    
    if ($response && isset($response['success'])) {
        if ($response['success']) {
            echo "✅ save_about.php endpoint works - saved hero.title_line1<br>";
        } else {
            echo "❌ save_about.php returned error: " . ($response['message'] ?? 'Unknown error') . "<br>";
        }
    } else {
        echo "❌ save_about.php returned invalid JSON: $output<br>";
    }
    
    // Clean up
    unlink($tempFile);
    
} catch (Exception $e) {
    echo "❌ save_about.php test failed: " . $e->getMessage() . "<br>";
}

// Test 5: Verify content was saved
echo "<h2>Test 5: Verifying Saved Content</h2>";

try {
    $stmt = $pdo->prepare("SELECT * FROM about_content WHERE section = 'hero' AND content_key = 'title_line1'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ Content found in database:<br>";
        echo "- Section: {$result['section']}<br>";
        echo "- Key: {$result['content_key']}<br>";
        echo "- Value: {$result['content_value']}<br>";
        echo "- Type: {$result['content_type']}<br>";
    } else {
        echo "❌ Content not found in database<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Database verification failed: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests passed, the about us saving functionality should work correctly.</p>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>Edit content on the about us page</li>";
echo "<li>Save changes automatically on blur</li>";
echo "<li>Use the 'Save All Changes' button</li>";
echo "<li>See status messages for save operations</li>";
echo "</ul>";
?> 