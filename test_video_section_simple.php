<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Section Simple Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Video Section Simple Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Video Section Add</h2>
            
            <form id="testForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Video Path:</label>
                    <input type="text" id="video_path" value="video/test_video.mp4" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug Text:</label>
                    <input type="text" id="slug_text" value="Test Video Section" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Button Text:</label>
                    <input type="text" id="button_text" value="Watch Now" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Button Link:</label>
                    <input type="text" id="button_link" value="#" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description:</label>
                    <textarea id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md">This is a test video section</textarea>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="is_active" checked class="mr-2">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                </div>
                
                <div class="flex space-x-4">
                    <button type="button" onclick="testAdd()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Test Add</button>
                    <button type="button" onclick="testEdit()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Test Edit (ID: 1)</button>
                    <button type="button" onclick="testDelete()" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Test Delete (ID: 1)</button>
                    <button type="button" onclick="testToggle()" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">Test Toggle (ID: 1)</button>
                </div>
            </form>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Test Results</h2>
            <div id="results" class="space-y-2"></div>
        </div>
    </div>

    <script>
    function addResult(message, type = 'info') {
        const resultsDiv = document.getElementById('results');
        const resultElement = document.createElement('div');
        resultElement.className = `p-3 rounded border ${type === 'success' ? 'bg-green-100 text-green-800' : type === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}`;
        resultElement.innerHTML = message;
        resultsDiv.appendChild(resultElement);
    }
    
    function getFormData() {
        return {
            video_path: document.getElementById('video_path').value,
            slug_text: document.getElementById('slug_text').value,
            button_text: document.getElementById('button_text').value,
            button_link: document.getElementById('button_link').value,
            description: document.getElementById('description').value,
            is_active: document.getElementById('is_active').checked ? 1 : 0
        };
    }
    
    async function testAdd() {
        const data = getFormData();
        addResult(`Testing ADD with data: ${JSON.stringify(data)}`, 'info');
        
        try {
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    table: 'video_section',
                    data: data
                })
            });
            
            const result = await response.json();
            console.log('Add response:', result);
            
            if (result.success) {
                addResult(`✅ ADD successful! ID: ${result.id}`, 'success');
            } else {
                addResult(`❌ ADD failed: ${result.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function testEdit() {
        const data = getFormData();
        addResult(`Testing EDIT with data: ${JSON.stringify(data)}`, 'info');
        
        try {
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'edit',
                    table: 'video_section',
                    id: 1,
                    data: data
                })
            });
            
            const result = await response.json();
            console.log('Edit response:', result);
            
            if (result.success) {
                addResult(`✅ EDIT successful!`, 'success');
            } else {
                addResult(`❌ EDIT failed: ${result.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function testDelete() {
        addResult(`Testing DELETE for ID: 1`, 'info');
        
        try {
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    table: 'video_section',
                    id: 1
                })
            });
            
            const result = await response.json();
            console.log('Delete response:', result);
            
            if (result.success) {
                addResult(`✅ DELETE successful!`, 'success');
            } else {
                addResult(`❌ DELETE failed: ${result.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function testToggle() {
        addResult(`Testing TOGGLE for ID: 1`, 'info');
        
        try {
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'toggle_active',
                    table: 'video_section',
                    id: 1
                })
            });
            
            const result = await response.json();
            console.log('Toggle response:', result);
            
            if (result.success) {
                addResult(`✅ TOGGLE successful!`, 'success');
            } else {
                addResult(`❌ TOGGLE failed: ${result.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    // Auto-test on page load
    window.addEventListener('load', function() {
        addResult('Page loaded. Ready for testing.', 'info');
    });
    </script>
</body>
</html> 