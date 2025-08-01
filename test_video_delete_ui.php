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
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Video Section ID:</label>
                <input type="number" id="videoId" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Enter video section ID">
            </div>
            
            <div class="space-x-4">
                <button onclick="testDelete()" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Test Delete</button>
                <button onclick="testDeleteWithSettings()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Test with Settings.php</button>
                <button onclick="testDeleteWithBackend()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Test with Backend</button>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Test Results</h2>
            <div id="testResults" class="space-y-2"></div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Debug Information</h2>
            <div id="debugInfo" class="text-sm text-gray-600"></div>
        </div>
    </div>

    <script>
    // Add result to the results div
    function addResult(message, type = 'info') {
        const resultsDiv = document.getElementById('testResults');
        const resultElement = document.createElement('div');
        resultElement.className = `p-3 rounded border ${type === 'success' ? 'bg-green-100 text-green-800' : type === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}`;
        resultElement.innerHTML = message;
        resultsDiv.appendChild(resultElement);
    }
    
    // Test delete function
    async function testDelete() {
        const id = document.getElementById('videoId').value;
        if (!id) {
            addResult('Please enter a video section ID', 'error');
            return;
        }
        
        addResult(`Testing delete for ID: ${id}...`, 'info');
        
        if (!confirm(`Are you sure you want to delete video section ID ${id}?`)) {
            addResult('Delete cancelled by user', 'info');
            return;
        }
        
        try {
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    table: 'video_section',
                    id: parseInt(id)
                })
            });
            
            const data = await response.json();
            console.log('Delete response:', data);
            
            if (data.success) {
                addResult(`✅ Success! Deleted video section ID ${id}. Rows affected: ${data.rows_affected || 'unknown'}`, 'success');
            } else {
                addResult(`❌ Error: ${data.error || 'Unknown error'}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network Error: ${error.message}`, 'error');
        }
    }
    
    // Test with settings.php (simulating the actual admin interface)
    async function testDeleteWithSettings() {
        const id = document.getElementById('videoId').value;
        if (!id) {
            addResult('Please enter a video section ID', 'error');
            return;
        }
        
        addResult(`Testing delete with settings.php for ID: ${id}...`, 'info');
        
        if (!confirm(`Are you sure you want to delete video section ID ${id}?`)) {
            addResult('Delete cancelled by user', 'info');
            return;
        }
        
        try {
            // Simulate the makeRequest function
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    table: 'video_section',
                    data: null,
                    id: parseInt(id)
                })
            });
            
            const data = await response.json();
            console.log('Settings.php response:', data);
            
            if (data.success) {
                addResult(`✅ Success! Deleted video section ID ${id}`, 'success');
            } else {
                addResult(`❌ Error: ${data.error || 'Unknown error'}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network Error: ${error.message}`, 'error');
        }
    }
    
    // Test with backend test file
    async function testDeleteWithBackend() {
        const id = document.getElementById('videoId').value;
        if (!id) {
            addResult('Please enter a video section ID', 'error');
            return;
        }
        
        addResult(`Testing delete with backend test for ID: ${id}...`, 'info');
        
        if (!confirm(`Are you sure you want to delete video section ID ${id}?`)) {
            addResult('Delete cancelled by user', 'info');
            return;
        }
        
        try {
            const response = await fetch('test_video_delete_backend.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    table: 'video_section',
                    id: parseInt(id)
                })
            });
            
            const data = await response.json();
            console.log('Backend test response:', data);
            
            if (data.success) {
                addResult(`✅ Success! Deleted video section ID ${id}. Rows affected: ${data.rows_affected}`, 'success');
            } else {
                addResult(`❌ Error: ${data.error || 'Unknown error'}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network Error: ${error.message}`, 'error');
        }
    }
    
    // Show debug information
    function showDebugInfo() {
        const debugDiv = document.getElementById('debugInfo');
        debugDiv.innerHTML = `
            <p><strong>Current URL:</strong> ${window.location.href}</p>
            <p><strong>User Agent:</strong> ${navigator.userAgent}</p>
            <p><strong>Console:</strong> Open browser console (F12) to see detailed logs</p>
            <p><strong>Network Tab:</strong> Check browser network tab to see AJAX requests</p>
        `;
    }
    
    // Initialize debug info
    showDebugInfo();
    </script>
</body>
</html> 