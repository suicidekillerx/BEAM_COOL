<?php
require_once __DIR__ . '/../includes/functions.php';

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Get all about page content from database
$dbContent = getAllAboutContent();

// Function to safely output HTML
function safeEcho($str) {
    echo htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Define all expected sections and their keys from about.php
$expectedContent = [
    'hero' => [
        'title_line1',
        'title_line2',
        'subtitle',
        'cta_text',
        'background_image'
    ],
    'story' => [
        'title',
        'paragraph1',
        'paragraph2',
        'quote',
        'image',
        'year',
        'year_label'
    ],
    'mission_vision' => [
        'title',
        'subtitle',
        'mission_number',
        'mission_title',
        'mission_content',
        'vision_number',
        'vision_title',
        'vision_content'
    ],
    'values' => [
        'title',
        'subtitle',
        'value1_title',
        'value1_content',
        'value1_icon',
        'value2_title',
        'value2_content',
        'value2_icon',
        'value3_title',
        'value3_content',
        'value3_icon'
    ],
    'timeline' => [
        'title',
        'subtitle',
        'year1',
        'year1_title',
        'year1_content',
        'year2',
        'year2_title',
        'year2_content',
        'year3',
        'year3_title',
        'year3_content',
        'year4',
        'year4_title',
        'year4_content',
        'year5',
        'year5_title',
        'year5_content'
    ],
    'team' => [
        'title',
        'subtitle',
        'member1_name',
        'member1_position',
        'member1_description',
        'member1_image',
        'member2_name',
        'member2_position',
        'member2_description',
        'member2_image',
        'member3_name',
        'member3_position',
        'member3_description',
        'member3_image'
    ],
    'cta' => [
        'title',
        'subtitle',
        'button1_text',
        'button1_url',
        'button2_text',
        'button2_url'
    ]
];

// Check for missing keys
$missingKeys = [];
foreach ($expectedContent as $section => $keys) {
    foreach ($keys as $key) {
        if (!isset($dbContent[$section][$key])) {
            $missingKeys[$section][] = $key;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Page Content Check</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1 { color: #333; }
        h2 { color: #444; margin-top: 30px; }
        ul { list-style-type: none; padding-left: 0; }
        li { margin-bottom: 5px; }
        pre { 
            background: #f4f4f4; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .missing { color: #d32f2f; font-weight: bold; }
        .present { color: #388e3c; }
    </style>
</head>
<body>
    <h1>About Page Content Check</h1>
    
    <?php
    // Output results
    echo "<h2>Missing Content Keys</h2>";
    if (empty($missingKeys)) {
        echo "<p class='present'>✓ All expected content keys are present in the database.</p>";
    } else {
        echo "<ul>";
        foreach ($missingKeys as $section => $keys) {
            echo "<li><strong>{$section}</strong><ul>";
            foreach ($keys as $key) {
                echo "<li class='missing'>{$key}</li>";
            }
            echo "</ul></li>";
        }
        echo "</ul>";
        
        // Generate SQL to insert missing keys
        echo "<h2>SQL to Add Missing Keys</h2>";
        echo "<pre>";
        $sqlStatements = [];
        foreach ($missingKeys as $section => $keys) {
            foreach ($keys as $key) {
                // Determine content type based on key name
                $contentType = 'text';
                if (strpos($key, '_image') !== false) {
                    $contentType = 'image';
                } elseif (strpos($key, '_url') !== false) {
                    $contentType = 'url';
                } elseif (strpos($key, '_number') !== false) {
                    $contentType = 'number';
                } elseif (strpos($key, '_icon') !== false) {
                    $contentType = 'html';
                }
                
                // Get default value if available
                $defaultValue = "''";
                if (strpos($key, 'title') !== false) {
                    $defaultValue = "'New Title'";
                } elseif (strpos($key, 'subtitle') !== false) {
                    $defaultValue = "'New Subtitle'";
                } elseif (strpos($key, 'content') !== false || strpos($key, 'description') !== false) {
                    $defaultValue = "'Content goes here'";
                } elseif (strpos($key, 'year') === 0 && is_numeric(substr($key, 4, 1))) {
                    $defaultValue = "'202" . (substr($key, 4, 1) - 1) . "'";
                }
                
                $sql = "INSERT INTO aboutus (section_name, content_key, content_type, content_value, sort_order) ";
                $sql .= sprintf(
                    "VALUES ('%s', '%s', '%s', %s, %d);",
                    $section,
                    $key,
                    $contentType,
                    $defaultValue,
                    count($sqlStatements) + 1
                );
                
                $sqlStatements[] = $sql;
            }
        }
        
        echo htmlspecialchars(implode("\n", $sqlStatements));
        echo "</pre>";
    }
    ?>
    
    <h2>Current Database Content</h2>
    <pre><?php 
    ob_start();
    print_r($dbContent);
    echo htmlspecialchars(ob_get_clean());
    ?></pre>
</body>
</html>
?>
