<?php
session_start();
require_once 'includes/functions.php';

echo "<h1>Footer Debug Test</h1>";

// Test getSocialMedia
echo "<h2>Testing getSocialMedia()</h2>";
$socialMedia = getSocialMedia();
echo "Social media items: " . count($socialMedia) . "<br>";
foreach ($socialMedia as $social) {
    echo "- " . $social['link_url'] . "<br>";
}

// Test getFooterData
echo "<h2>Testing getFooterData()</h2>";
$footerData = getFooterData();
echo "Footer sections: " . count($footerData) . "<br>";
foreach ($footerData as $section) {
    echo "- " . $section['section_title'] . " (" . count($section['links']) . " links)<br>";
}

// Test getSiteSetting
echo "<h2>Testing getSiteSetting()</h2>";
$brandName = getSiteSetting('brand_name', 'beamtheteam™');
echo "Brand name: " . $brandName . "<br>";

echo "<h2>Footer Test Complete</h2>";
?> 