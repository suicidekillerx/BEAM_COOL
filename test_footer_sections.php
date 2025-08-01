<?php
require_once 'includes/functions.php';

echo "<h1>Footer Sections Test</h1>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Test current footer sections
    echo "<h2>Current Footer Sections</h2>";
    $stmt = $pdo->prepare("SELECT * FROM footer_sections ORDER BY sort_order ASC");
    $stmt->execute();
    $sections = $stmt->fetchAll();
    
    if (empty($sections)) {
        echo "<p style='color: red;'>❌ No footer sections found</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Section Name</th><th>Section Title</th><th>Sort Order</th><th>Active</th></tr>";
        foreach ($sections as $section) {
            echo "<tr>";
            echo "<td>{$section['id']}</td>";
            echo "<td>{$section['section_name']}</td>";
            echo "<td>{$section['section_title']}</td>";
            echo "<td>{$section['sort_order']}</td>";
            echo "<td>" . ($section['is_active'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test getFooterData function
    echo "<h2>Testing getFooterData() Function</h2>";
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
    
    // Test updating a section
    echo "<h2>Test Section Update</h2>";
    echo "<form method='post'>";
    echo "<p>Update section ID 1 title to: <input type='text' name='new_title' value='TEST SECTION' required></p>";
    echo "<input type='submit' value='Update Section' style='background: #000; color: white; padding: 10px; border: none; cursor: pointer;'>";
    echo "</form>";
    
    if ($_POST && isset($_POST['new_title'])) {
        $newTitle = $_POST['new_title'];
        try {
            $stmt = $pdo->prepare("UPDATE footer_sections SET section_name = ?, section_title = ? WHERE id = 1");
            $result = $stmt->execute([$newTitle, $newTitle]);
            
            if ($result) {
                echo "<p style='color: green;'>✅ Section updated successfully!</p>";
                echo "<p>New title: $newTitle</p>";
                
                // Refresh the page to show updated data
                echo "<script>setTimeout(() => location.reload(), 2000);</script>";
            } else {
                echo "<p style='color: red;'>❌ Failed to update section</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error updating section: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test adding a new section
    echo "<h2>Test Adding New Section</h2>";
    echo "<form method='post'>";
    echo "<p>Add new section: <input type='text' name='add_title' value='NEW SECTION' required></p>";
    echo "<input type='submit' value='Add Section' style='background: #000; color: white; padding: 10px; border: none; cursor: pointer;'>";
    echo "</form>";
    
    if ($_POST && isset($_POST['add_title'])) {
        $addTitle = $_POST['add_title'];
        try {
            $stmt = $pdo->prepare("INSERT INTO footer_sections (section_name, section_title, sort_order, is_active) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$addTitle, $addTitle, 10, 1]);
            
            if ($result) {
                echo "<p style='color: green;'>✅ New section added successfully!</p>";
                echo "<p>New section: $addTitle</p>";
                
                // Refresh the page to show updated data
                echo "<script>setTimeout(() => location.reload(), 2000);</script>";
            } else {
                echo "<p style='color: red;'>❌ Failed to add section</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error adding section: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 