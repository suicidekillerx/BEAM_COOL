<?php
require_once __DIR__ . '/../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['key']) || !isset($input['value'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Parse the content key (format: section.key)
$keyParts = explode('.', $input['key'], 2);
if (count($keyParts) !== 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid content key format']);
    exit;
}

$section = $keyParts[0];
$key = $keyParts[1];
$value = $input['value'];

try {
    // Update the content in the database
    $result = updateAboutContent($section, $key, $value);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Content updated successfully',
            'section' => $section,
            'key' => $key,
            'value' => $value
        ]);
    } else {
        // If update fails, try to insert
        $inserted = insertAboutContent($section, $key, 'text', $value);
        if ($inserted) {
            echo json_encode([
                'success' => true, 
                'message' => 'New content created successfully',
                'section' => $section,
                'key' => $key,
                'value' => $value
            ]);
        } else {
            throw new Exception('Failed to save content');
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving content: ' . $e->getMessage()
    ]);
}
