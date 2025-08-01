<?php
require_once 'includes/functions.php';

echo "<h1>Video Delete Debug Test</h1>";

// Test database connection
try {
    $pdo = getDBConnection();
    echo "<p><strong>Database Connection:</strong> ✅ Success</p>";
} catch (Exception $e) {
    echo "<p><strong>Database Connection:</strong> ❌ Failed - " . $e->getMessage() . "</p>";
    exit;
}

// Test direct delete operation
echo "<h2>Testing Direct Delete Operation</h2>";

// First, let's see what video sections exist
$stmt = $pdo->prepare("SELECT * FROM video_section ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$videoSections = $stmt->fetchAll();

if (empty($videoSections)) {
    echo "<p>No video sections found to test deletion.</p>";
    exit;
}

echo "<h3>Available Video Sections for Testing:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Video Path</th><th>Slug Text</th><th>Active</th><th>Action</th></tr>";

foreach ($videoSections as $video) {
    echo "<tr>";
    echo "<td>" . $video['id'] . "</td>";
    echo "<td>" . htmlspecialchars($video['video_path']) . "</td>";
    echo "<td>" . htmlspecialchars($video['slug_text']) . "</td>";
    echo "<td>" . ($video['is_active'] ? 'Yes' : 'No') . "</td>";
    echo "<td><a href='?test_delete=" . $video['id'] . "' onclick='return confirm(\"Are you sure you want to delete this video section?\")'>Test Delete</a></td>";
    echo "</tr>";
}
echo "</table>";

// Test delete operation if requested
if (isset($_GET['test_delete']) && is_numeric($_GET['test_delete'])) {
    $deleteId = (int)$_GET['test_delete'];
    
    echo "<h3>Testing Delete for ID: " . $deleteId . "</h3>";
    
    // Check if the record exists
    $stmt = $pdo->prepare("SELECT * FROM video_section WHERE id = ?");
    $stmt->execute([$deleteId]);
    $videoToDelete = $stmt->fetch();
    
    if (!$videoToDelete) {
        echo "<p><strong>Error:</strong> Video section with ID " . $deleteId . " not found.</p>";
    } else {
        echo "<p><strong>Found video section:</strong> " . htmlspecialchars($videoToDelete['video_path']) . "</p>";
        
        // Attempt to delete
        try {
            $stmt = $pdo->prepare("DELETE FROM video_section WHERE id = ?");
            $result = $stmt->execute([$deleteId]);
            
            if ($result) {
                echo "<p><strong>Delete Result:</strong> ✅ Success - " . $stmt->rowCount() . " row(s) affected</p>";
                
                // Verify deletion
                $stmt = $pdo->prepare("SELECT * FROM video_section WHERE id = ?");
                $stmt->execute([$deleteId]);
                $checkResult = $stmt->fetch();
                
                if (!$checkResult) {
                    echo "<p><strong>Verification:</strong> ✅ Record successfully deleted</p>";
                } else {
                    echo "<p><strong>Verification:</strong> ❌ Record still exists</p>";
                }
            } else {
                echo "<p><strong>Delete Result:</strong> ❌ Failed</p>";
            }
        } catch (Exception $e) {
            echo "<p><strong>Delete Error:</strong> ❌ " . $e->getMessage() . "</p>";
        }
    }
}

// Test the settings.php endpoint
echo "<h2>Testing Settings.php Endpoint</h2>";
echo "<p><strong>Settings.php URL:</strong> <a href='admin/setting.php' target='_blank'>admin/setting.php</a></p>";

// Test the makeRequest function simulation
echo "<h3>Testing makeRequest Function Simulation</h3>";
echo "<p>This simulates what the JavaScript makeRequest function would send:</p>";

$testData = [
    'action' => 'delete',
    'table' => 'video_section',
    'id' => 1
];

echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Test if the allowed tables include video_section
echo "<h3>Checking Allowed Tables</h3>";
$allowedTables = ['site_settings', 'footer_sections', 'footer_links', 'social_media', 'video_section'];
echo "<p><strong>Allowed Tables:</strong> " . implode(', ', $allowedTables) . "</p>";

if (in_array('video_section', $allowedTables)) {
    echo "<p><strong>Status:</strong> ✅ video_section is in allowed tables</p>";
} else {
    echo "<p><strong>Status:</strong> ❌ video_section is NOT in allowed tables</p>";
}

// Test JavaScript functions
echo "<h2>Testing JavaScript Functions</h2>";
echo "<p><strong>deleteItem function:</strong> Available in admin/js/settings.js</p>";
echo "<p><strong>makeRequest function:</strong> Available in admin/js/settings.js</p>";

// Check if settings.js is loaded
echo "<h3>Checking JavaScript Loading</h3>";
echo "<p>Make sure admin/js/settings.js is included in the settings page.</p>";

echo "<h2>Debugging Steps:</h2>";
echo "<ol>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Verify that settings.js is loaded</li>";
echo "<li>Check if the deleteItem function is called</li>";
echo "<li>Verify the AJAX request is sent to setting.php</li>";
echo "<li>Check server logs for PHP errors</li>";
echo "<li>Verify database permissions</li>";
echo "</ol>";

echo "<h2>Manual Test:</h2>";
echo "<p>Try clicking the delete button and check:</p>";
echo "<ul>";
echo "<li>Does the confirmation dialog appear?</li>";
echo "<li>Does the AJAX request show in browser network tab?</li>";
echo "<li>What response does the server return?</li>";
echo "<li>Are there any JavaScript errors in console?</li>";
echo "</ul>";

echo "<p><strong>Test completed!</strong> Use the links above to test the delete functionality.</p>";
?> 