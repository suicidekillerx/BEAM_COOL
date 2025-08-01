<?php
require_once 'includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get the request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the request for debugging
error_log("Video delete test - Request data: " . print_r($data, true));

// Check if this is a delete request
if (isset($data['action']) && $data['action'] === 'delete' && isset($data['table']) && $data['table'] === 'video_section' && isset($data['id'])) {
    
    try {
        $pdo = getDBConnection();
        $id = (int)$data['id'];
        
        // Check if the record exists
        $stmt = $pdo->prepare("SELECT * FROM video_section WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetch();
        
        if (!$video) {
            echo json_encode([
                'success' => false,
                'error' => 'Video section not found',
                'debug' => ['id' => $id]
            ]);
            exit;
        }
        
        // Attempt to delete
        $stmt = $pdo->prepare("DELETE FROM video_section WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $rowsAffected = $stmt->rowCount();
            echo json_encode([
                'success' => true,
                'message' => 'Video section deleted successfully',
                'rows_affected' => $rowsAffected,
                'debug' => [
                    'id' => $id,
                    'video_path' => $video['video_path']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Delete operation failed',
                'debug' => ['id' => $id]
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Video delete error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'debug' => ['id' => $id ?? 'unknown']
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request parameters',
        'debug' => [
            'received_data' => $data,
            'required' => ['action' => 'delete', 'table' => 'video_section', 'id' => 'number']
        ]
    ]);
}
?> 