<?php
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error = 'No file uploaded or upload error';
    if (isset($_FILES['image']['error'])) {
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $error = 'File too large (exceeds PHP upload limit)';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'File too large (exceeds form limit)';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'File upload was incomplete';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error = 'Missing temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error = 'Failed to write file to disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error = 'File upload stopped by extension';
                break;
        }
    }
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

// Check if content key is provided
if (!isset($_POST['key'])) {
    echo json_encode(['success' => false, 'error' => 'Missing content key']);
    exit;
}

$file = $_FILES['image'];
$contentKey = $_POST['key'];
$fileName = $file['name'];
$fileSize = $file['size'];
$fileTmpName = $file['tmp_name'];
$fileType = $file['type'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit;
}

// Validate file size (5MB max)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($fileSize > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 5MB.']);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = '../images/about/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$uniqueName = 'about_' . time() . '_' . uniqid() . '.' . $fileExtension;
$uploadPath = $uploadDir . $uniqueName;

// Move uploaded file
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Return relative path for database storage
    $relativePath = 'images/about/' . $uniqueName;
    
    // Parse the content key to get section and key
    $keyParts = explode('.', $contentKey, 2);
    if (count($keyParts) !== 2) {
        echo json_encode(['success' => false, 'error' => 'Invalid content key format']);
        exit;
    }
    
    $section = $keyParts[0];
    $key = $keyParts[1];
    
    try {
        // Update the content in the database
        $result = updateAboutContent($section, $key, $relativePath);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'file_path' => $relativePath,
                'file_name' => $uniqueName,
                'message' => 'Image uploaded and saved successfully',
                'section' => $section,
                'key' => $key
            ]);
        } else {
            // If update fails, try to insert
            $inserted = insertAboutContent($section, $key, 'image', $relativePath);
            if ($inserted) {
                echo json_encode([
                    'success' => true,
                    'file_path' => $relativePath,
                    'file_name' => $uniqueName,
                    'message' => 'Image uploaded and saved successfully',
                    'section' => $section,
                    'key' => $key
                ]);
            } else {
                throw new Exception('Failed to save image to database');
            }
        }
    } catch (Exception $e) {
        // If database save fails, delete the uploaded file
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
}
?> 