<?php
require_once 'includes/functions.php';

// Set content type to JSON for AJAX testing
if (isset($_GET['ajax_test'])) {
    header('Content-Type: application/json');
    
    $pdo = getDBConnection();
    
    // Simulate the delete request
    $action = 'delete';
    $table = 'video_section';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Check if table is allowed
    $allowedTables = ['site_settings', 'footer_sections', 'footer_links', 'social_media', 'video_section'];
    
    if (!in_array($table, $allowedTables)) {
        echo json_encode(['success' => false, 'error' => 'Invalid table']);
        exit;
    }
    
    if ($action === 'delete' && $id && $table) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                echo json_encode(['success' => true, 'rows_affected' => $stmt->rowCount()]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Delete failed']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    }
    exit;
}

// HTML test page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Delete Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Video Delete Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Video Section Delete</h2>
            
            <?php
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM video_section ORDER BY created_at DESC LIMIT 5");
            $stmt->execute();
            $videoSections = $stmt->fetchAll();
            ?>
            
            <?php if (empty($videoSections)): ?>
                <p class="text-gray-500">No video sections found to test.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Video Path</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug Text</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($videoSections as $video): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $video['id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($video['video_path']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($video['slug_text']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $video['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $video['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="testDelete(<?= $video['id'] ?>)" class="text-red-600 hover:text-red-900">Test Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Test Results</h2>
            <div id="testResults" class="space-y-2"></div>
        </div>
    </div>

    <script>
    async function testDelete(id) {
        if (!confirm('Are you sure you want to delete video section ID ' + id + '?')) {
            return;
        }
        
        const resultsDiv = document.getElementById('testResults');
        const resultElement = document.createElement('div');
        resultElement.className = 'p-3 rounded border';
        resultElement.innerHTML = `<strong>Testing delete for ID ${id}...</strong>`;
        resultsDiv.appendChild(resultElement);
        
        try {
            const response = await fetch(`?ajax_test=1&id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                resultElement.className = 'p-3 rounded border bg-green-100 text-green-800';
                resultElement.innerHTML = `<strong>✅ Success!</strong> Deleted video section ID ${id}. Rows affected: ${data.rows_affected}`;
            } else {
                resultElement.className = 'p-3 rounded border bg-red-100 text-red-800';
                resultElement.innerHTML = `<strong>❌ Error!</strong> ${data.error}`;
            }
        } catch (error) {
            resultElement.className = 'p-3 rounded border bg-red-100 text-red-800';
            resultElement.innerHTML = `<strong>❌ Network Error!</strong> ${error.message}`;
        }
        
        // Reload the page after 2 seconds to show updated data
        setTimeout(() => {
            location.reload();
        }, 2000);
    }
    </script>
</body>
</html> 