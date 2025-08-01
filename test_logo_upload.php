<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logo Upload Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Logo Upload Test</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Test Logo Upload</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Image File:</label>
                <input type="file" id="testFile" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            
            <div class="space-x-4">
                <button onclick="testUpload()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Test Upload</button>
                <button onclick="testSave()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Test Save to DB</button>
                <button onclick="checkDatabase()" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">Check Database</button>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Test Results</h2>
            <div id="testResults" class="space-y-2"></div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">Current Database Values</h2>
            <div id="dbResults" class="space-y-2"></div>
        </div>
    </div>

    <script>
    let uploadedFilePath = '';
    
    function addResult(message, type = 'info') {
        const resultsDiv = document.getElementById('testResults');
        const resultElement = document.createElement('div');
        resultElement.className = `p-3 rounded border ${type === 'success' ? 'bg-green-100 text-green-800' : type === 'error' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}`;
        resultElement.innerHTML = message;
        resultsDiv.appendChild(resultElement);
    }
    
    async function testUpload() {
        const fileInput = document.getElementById('testFile');
        if (!fileInput.files[0]) {
            addResult('Please select a file first', 'error');
            return;
        }
        
        addResult(`Testing upload for file: ${fileInput.files[0].name}`, 'info');
        
        const formData = new FormData();
        formData.append('image', fileInput.files[0]);
        
        try {
            const response = await fetch('admin/upload_image.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('Upload response:', data);
            
            if (data.success) {
                uploadedFilePath = data.file_path;
                addResult(`✅ Upload successful! File path: ${data.file_path}`, 'success');
            } else {
                addResult(`❌ Upload failed: ${data.error}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function testSave() {
        if (!uploadedFilePath) {
            addResult('Please upload a file first', 'error');
            return;
        }
        
        addResult(`Testing save to database for path: ${uploadedFilePath}`, 'info');
        
        const testData = {
            brand_logo: uploadedFilePath,
            brand_name: 'Test Brand',
            test_setting: 'test_value'
        };
        
        try {
            const response = await fetch('admin/setting.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'save_site_settings',
                    data: testData
                })
            });
            
            const data = await response.json();
            console.log('Save response:', data);
            
            if (data.success) {
                addResult(`✅ Save successful! Saved ${data.saved_count || 'unknown'} settings`, 'success');
            } else {
                addResult(`❌ Save failed: ${data.error || 'Unknown error'}`, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    async function checkDatabase() {
        addResult('Checking database values...', 'info');
        
        try {
            const response = await fetch('admin/setting.php');
            const html = await response.text();
            
            // Extract settings from the page
            const dbResultsDiv = document.getElementById('dbResults');
            dbResultsDiv.innerHTML = `
                <div class="p-3 bg-gray-100 rounded">
                    <h3 class="font-semibold mb-2">Current Settings:</h3>
                    <pre class="text-sm">${html.includes('brand_logo') ? 'brand_logo found in page' : 'brand_logo NOT found in page'}</pre>
                </div>
            `;
            
            addResult('✅ Database check completed', 'success');
        } catch (error) {
            console.error('Error:', error);
            addResult(`❌ Error checking database: ${error.message}`, 'error');
        }
    }
    
    // Auto-check database on page load
    window.addEventListener('load', function() {
        addResult('Page loaded. Ready for testing.', 'info');
    });
    </script>
</body>
</html> 