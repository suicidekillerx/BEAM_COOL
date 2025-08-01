<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Upload Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Video Upload Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Video Upload</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Video File:</label>
                <input type="file" id="testVideoFile" accept="video/*" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div class="space-x-4">
                <button onclick="testVideoUpload()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Test Video Upload</button>
                <button onclick="testVideoSave()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Test Video Save to DB</button>
                <button onclick="checkVideoDatabase()" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">Check Video Database</button>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Test Results</h2>
            <div id="testResults" class="space-y-2"></div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Current Video Database</h2>
            <div id="videoDbResults" class="space-y-2"></div>
        </div>
    </div>

    <script>
    let uploadedVideoPath = '';
    
    function addResult(message, type = 'info') {
        const resultsDiv = document.getElementById('testResults');
        const resultElement = document.createElement('div');
        resultElement.className = `p-3 rounded border ${type === 'success' ? 'bg-green-100 text-green-800' : type === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}`;
        resultElement.innerHTML = message;
        resultsDiv.appendChild(resultElement);
    }
    
    async function testVideoUpload() {
        const fileInput = document.getElementById('testVideoFile');
        if (!fileInput.files[0]) {
            addResult('Please select a video file first', 'error');
            return;
        }
        
        addResult(`Testing video upload for file: ${fileInput.files[0].name}`, 'info');
        
        const formData = new FormData();
        formData.append('video', fileInput.files[0]);
        
        try {
            const response = await fetch('admin/upload_video.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('Video upload response:', data);
            
            if (data.success) {
                uploadedVideoPath = data.file_path;
                addResult(`✅ Video upload successful! File path: ${data.file_path}`, 'success');
            } else {
                addResult(`❌ Video upload failed: ${data.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function testVideoSave() {
        if (!uploadedVideoPath) {
            addResult('Please upload a video file first', 'error');
            return;
        }
        
        addResult(`Testing video save to database for path: ${uploadedVideoPath}`, 'info');
        
        const testData = {
            video_path: uploadedVideoPath,
            slug_text: 'Test Video Section',
            button_text: 'Watch Now',
            button_link: '#',
            description: 'This is a test video section',
            is_active: 1
        };
        
        try {
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    table: 'video_section',
                    data: testData
                })
            });
            
            const data = await response.json();
            console.log('Video save response:', data);
            
            if (data.success) {
                addResult(`✅ Video save successful! ID: ${data.id}`, 'success');
            } else {
                addResult(`❌ Video save failed: ${data.error || 'Unknown error'}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function checkVideoDatabase() {
        addResult('Checking video database...', 'info');
        
        try {
            const response = await fetch('admin/setting.php');
            const html = await response.text();
            
            // Extract video sections from the page
            const videoDbResultsDiv = document.getElementById('videoDbResults');
            videoDbResultsDiv.innerHTML = `
                <div class="p-3 bg-gray-100 rounded">
                    <h3 class="font-semibold mb-2">Current Video Sections:</h3>
                    <pre class="text-sm">${html.includes('video_section') ? 'video_section found in page' : 'video_section NOT found in page'}</pre>
                </div>
            `;
            
            addResult('✅ Video database check completed', 'success');
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Error checking video database: ${error.message}`, 'error');
        }
    }
    
    // Auto-check database on page load
    window.addEventListener('load', function() {
        addResult('Page loaded. Ready for video testing.', 'info');
    });
    </script>
</body>
</html> 