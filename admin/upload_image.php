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

$file = $_FILES['image'];
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
$uploadDir = '../images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$uniqueName = 'logo_' . time() . '_' . uniqid() . '.' . $fileExtension;
$uploadPath = $uploadDir . $uniqueName;

// Move uploaded file
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Return relative path for database storage
    $relativePath = 'images/' . $uniqueName;
    echo json_encode([
        'success' => true,
        'file_path' => $relativePath,
        'file_name' => $uniqueName,
        'message' => 'Image uploaded successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
}
?>
