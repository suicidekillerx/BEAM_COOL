<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Refresh Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">No Refresh Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Video Section Operations (No Refresh)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium mb-3">Add Video Section</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Video Path:</label>
                            <input type="text" id="testVideoPath" value="video/test_video.mp4" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Slug Text:</label>
                            <input type="text" id="testSlugText" value="Test Video Section" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Button Text:</label>
                            <input type="text" id="testButtonText" value="Watch Now" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Button Link:</label>
                            <input type="text" id="testButtonLink" value="#" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description:</label>
                            <textarea id="testDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md">This is a test video section</textarea>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" id="testIsActive" checked class="mr-2">
                                <span class="text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                        <button onclick="testAddVideoSection()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Test Add Video Section</button>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium mb-3">Test Operations</h3>
                    <div class="space-y-3">
                        <button onclick="testGetVideoSections()" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Get Current Video Sections</button>
                        <button onclick="testDeleteVideoSection()" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Test Delete (ID: 1)</button>
                        <button onclick="testToggleVideoSection()" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700">Test Toggle (ID: 1)</button>
                        <button onclick="checkPageRefresh()" class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">Check Page Refresh</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Test Results</h2>
            <div id="testResults" class="space-y-2"></div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Page Refresh Status</h2>
            <div id="refreshStatus" class="space-y-2">
                <div class="p-3 bg-blue-100 text-blue-800 rounded">
                    <strong>Page Load Time:</strong> <span id="loadTime"></span>
                </div>
                <div class="p-3 bg-green-100 text-green-800 rounded">
                    <strong>Last Operation:</strong> <span id="lastOperation">None</span>
                </div>
                <div class="p-3 bg-yellow-100 text-yellow-800 rounded">
                    <strong>Refresh Count:</strong> <span id="refreshCount">0</span>
                </div>
            </div>
        </div>
    </div>

    <script>
    let refreshCount = 0;
    let lastOperation = 'None';
    
    function addResult(message, type = 'info') {
        const resultsDiv = document.getElementById('testResults');
        const resultElement = document.createElement('div');
        resultElement.className = `p-3 rounded border ${type === 'success' ? 'bg-green-100 text-green-800' : type === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}`;
        resultElement.innerHTML = message;
        resultsDiv.appendChild(resultElement);
    }
    
    function updateRefreshStatus(operation) {
        lastOperation = operation;
        document.getElementById('lastOperation').textContent = operation;
        document.getElementById('loadTime').textContent = new Date().toLocaleTimeString();
    }
    
    async function testAddVideoSection() {
        const data = {
            video_path: document.getElementById('testVideoPath').value,
            slug_text: document.getElementById('testSlugText').value,
            button_text: document.getElementById('testButtonText').value,
            button_link: document.getElementById('testButtonLink').value,
            description: document.getElementById('testDescription').value,
            is_active: document.getElementById('testIsActive').checked ? 1 : 0
        };
        
        updateRefreshStatus('Add Video Section');
        addResult(`Testing add video section with data: ${JSON.stringify(data)}`, 'info');
        
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
            console.log('Add video section response:', result);
            
            if (result.success) {
                addResult(`✅ Video section added successfully! ID: ${result.id}`, 'success');
            } else {
                addResult(`❌ Failed to add video section: ${result.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function testGetVideoSections() {
        updateRefreshStatus('Get Video Sections');
        addResult('Getting current video sections...', 'info');
        
        try {
            const response = await fetch('admin/setting.php');
            const html = await response.text();
            
            // Check if video sections are mentioned in the page
            const hasVideoSections = html.includes('video_section') || html.includes('video-section');
            addResult(`✅ Page loaded. Video sections found: ${hasVideoSections}`, 'success');
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Error getting video sections: ${error.message}`, 'error');
        }
    }
    
    async function testDeleteVideoSection() {
        updateRefreshStatus('Delete Video Section');
        addResult('Testing delete video section ID: 1', 'info');
        
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
            console.log('Delete video section response:', result);
            
            if (result.success) {
                addResult(`✅ Video section deleted successfully!`, 'success');
            } else {
                addResult(`❌ Failed to delete video section: ${result.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function testToggleVideoSection() {
        updateRefreshStatus('Toggle Video Section');
        addResult('Testing toggle video section ID: 1', 'info');
        
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
            console.log('Toggle video section response:', result);
            
            if (result.success) {
                addResult(`✅ Video section status toggled successfully!`, 'success');
            } else {
                addResult(`❌ Failed to toggle video section: ${result.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    function checkPageRefresh() {
        refreshCount++;
        document.getElementById('refreshCount').textContent = refreshCount;
        updateRefreshStatus('Check Page Refresh');
        addResult(`✅ Page refresh check completed. Count: ${refreshCount}`, 'success');
    }
    
    // Auto-load on page load
    window.addEventListener('load', function() {
        updateRefreshStatus('Page Loaded');
        addResult('Page loaded. Ready for testing.', 'info');
        document.getElementById('loadTime').textContent = new Date().toLocaleTimeString();
    });
    </script>
</body>
</html> 