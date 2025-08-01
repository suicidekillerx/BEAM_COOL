<?php
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in (optional for testing)
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    // For testing purposes, allow uploads without session check
    // echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    // exit;
}

// Check if file was uploaded
if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    $error = 'No video file uploaded or upload error';
    if (isset($_FILES['video']['error'])) {
        switch ($_FILES['video']['error']) {
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

$file = $_FILES['video'];
$fileName = $file['name'];
$fileSize = $file['size'];
$fileTmpName = $file['tmp_name'];
$fileType = $file['type'];

// Validate file type
$allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only MP4, AVI, MOV, WMV, FLV, and WebM videos are allowed.']);
    exit;
}

// Validate file size (max 100MB)
$maxSize = 100 * 1024 * 1024; // 100MB
if ($fileSize > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 100MB.']);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = '../video/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
$uniqueFileName = 'video_' . time() . '_' . uniqid() . '.' . $fileExtension;
$uploadPath = $uploadDir . $uniqueFileName;

// Move uploaded file
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Return success with file path
    $relativePath = 'video/' . $uniqueFileName;
    error_log("Video uploaded successfully: $relativePath");
    echo json_encode([
        'success' => true,
        'file_path' => $relativePath,
        'file_name' => $fileName,
        'message' => 'Video uploaded successfully'
    ]);
} else {
    error_log("Failed to move uploaded video file from $fileTmpName to $uploadPath");
    echo json_encode(['success' => false, 'error' => 'Failed to save video file']);
}
?> 