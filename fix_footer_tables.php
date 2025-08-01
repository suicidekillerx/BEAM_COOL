<?php
require_once 'includes/functions.php';

echo "<h1>Footer Tables Check and Fix</h1>";

try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if footer_sections table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'footer_sections'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ footer_sections table does not exist. Creating it...</p>";
        
        // Create footer_sections table
        $sql = "CREATE TABLE `footer_sections` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `section_name` varchar(100) NOT NULL,
            `section_title` varchar(255) NOT NULL,
            `sort_order` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ footer_sections table created</p>";
        
        // Insert default data
        $defaultSections = [
            ['section_name' => 'ABOUT', 'section_title' => 'ABOUT', 'sort_order' => 1],
            ['section_name' => 'LEGAL', 'section_title' => 'LEGAL', 'sort_order' => 2]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO footer_sections (section_name, section_title, sort_order) VALUES (?, ?, ?)");
        foreach ($defaultSections as $section) {
            $stmt->execute([$section['section_name'], $section['section_title'], $section['sort_order']]);
        }
        echo "<p style='color: green;'>✅ Default footer sections inserted</p>";
    } else {
        echo "<p style='color: green;'>✅ footer_sections table exists</p>";
    }
    
    // Check if footer_links table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'footer_links'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ footer_links table does not exist. Creating it...</p>";
        
        // Create footer_links table
        $sql = "CREATE TABLE `footer_links` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `section_id` int(11) NOT NULL,
            `link_text` varchar(255) NOT NULL,
            `link_url` varchar(255) NOT NULL,
            `sort_order` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `section_id` (`section_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ footer_links table created</p>";
        
        // Insert default data
        $defaultLinks = [
            ['section_id' => 1, 'link_text' => 'FAQS', 'link_url' => '#', 'sort_order' => 1],
            ['section_id' => 1, 'link_text' => 'CONTACT FORM', 'link_url' => '#', 'sort_order' => 2],
            ['section_id' => 1, 'link_text' => 'SHIPPING POLICY', 'link_url' => '#', 'sort_order' => 3],
            ['section_id' => 1, 'link_text' => 'CAREERS', 'link_url' => '#', 'sort_order' => 4],
            ['section_id' => 1, 'link_text' => 'INTERNSHIPS', 'link_url' => '#', 'sort_order' => 5],
            ['section_id' => 2, 'link_text' => 'RETURN & EXCHANGE POLICY', 'link_url' => '#', 'sort_order' => 1],
            ['section_id' => 2, 'link_text' => 'PRIVACY POLICY', 'link_url' => '#', 'sort_order' => 2],
            ['section_id' => 2, 'link_text' => 'COOKIE POLICY', 'link_url' => '#', 'sort_order' => 3],
            ['section_id' => 2, 'link_text' => 'TERMS OF SERVICE', 'link_url' => '#', 'sort_order' => 4],
            ['section_id' => 2, 'link_text' => 'LEGAL NOTICE', 'link_url' => '#', 'sort_order' => 5]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO footer_links (section_id, link_text, link_url, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($defaultLinks as $link) {
            $stmt->execute([$link['section_id'], $link['link_text'], $link['link_url'], $link['sort_order']]);
        }
        echo "<p style='color: green;'>✅ Default footer links inserted</p>";
    } else {
        echo "<p style='color: green;'>✅ footer_links table exists</p>";
    }
    
    // Check if social_media table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'social_media'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "<p style='color: red;'>❌ social_media table does not exist. Creating it...</p>";
        
        // Create social_media table
        $sql = "CREATE TABLE `social_media` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `link_text` varchar(100) NOT NULL,
            `link_url` varchar(255) NOT NULL,
            `icon_svg` text NOT NULL,
            `sort_order` int(11) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ social_media table created</p>";
        
        // Insert default social media data
        $defaultSocialMedia = [
            [
                'link_text' => 'Facebook',
                'link_url' => 'https://facebook.com',
                'icon_svg' => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
                'sort_order' => 1
            ],
            [
                'link_text' => 'Instagram',
                'link_url' => 'https://instagram.com',
                'icon_svg' => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
                'sort_order' => 2
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO social_media (link_text, link_url, icon_svg, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($defaultSocialMedia as $social) {
            $stmt->execute([$social['link_text'], $social['link_url'], $social['icon_svg'], $social['sort_order']]);
        }
        echo "<p style='color: green;'>✅ Default social media links inserted</p>";
    } else {
        echo "<p style='color: green;'>✅ social_media table exists</p>";
    }
    
    // Test the getFooterData function
    echo "<h2>Testing getFooterData() function</h2>";
    try {
        $footerData = getFooterData();
        echo "<p style='color: green;'>✅ getFooterData() returned " . count($footerData) . " sections</p>";
        
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
    
    echo "<h2>Footer Tables Check Complete</h2>";
    echo "<p style='color: green;'>✅ All footer tables are now properly set up!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 