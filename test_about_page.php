<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test About Us Functionality</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Test About Us Functionality</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Editable Content Test</h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Title:</label>
                <div 
                    data-content-key="hero.title_line1" 
                    contenteditable="true" 
                    class="border-2 border-dashed border-blue-300 p-3 rounded min-h-[40px] hover:bg-blue-50 focus:bg-white focus:border-blue-500"
                >
                    Click to edit this text
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Subtitle:</label>
                <div 
                    data-content-key="hero.subtitle" 
                    contenteditable="true" 
                    class="border-2 border-dashed border-blue-300 p-3 rounded min-h-[40px] hover:bg-blue-50 focus:bg-white focus:border-blue-500"
                >
                    Click to edit this subtitle
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Story Content:</label>
                <div 
                    data-content-key="story.paragraph1" 
                    contenteditable="true" 
                    class="border-2 border-dashed border-blue-300 p-3 rounded min-h-[80px] hover:bg-blue-50 focus:bg-white focus:border-blue-500"
                >
                    Click to edit this paragraph content. This is a longer text area for testing paragraph editing.
                </div>
            </div>
            
            <div class="flex space-x-4">
                <button onclick="saveAllChanges()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Save All Changes
                </button>
                <button onclick="testIndividualSave()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Test Individual Save
                </button>
                <button onclick="location.reload()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Reset
                </button>
            </div>
        </div>
        
        <div id="saveStatus" class="fixed bottom-4 left-4 p-4 rounded-lg shadow-lg text-white" style="display: none;"></div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Console Log</h2>
            <div id="consoleLog" class="bg-gray-900 text-green-400 p-4 rounded font-mono text-sm h-64 overflow-y-auto">
                <div>Console output will appear here...</div>
            </div>
        </div>
    </div>

    <script>
        // API endpoint for saving content
        const apiEndpoint = 'admin/save_about.php';
        
        // Function to show status messages
        function showStatus(message, bgClass) {
            const statusDiv = document.getElementById('saveStatus');
            if (!statusDiv) {
                console.error('Save status div not found');
                return;
            }
            
            statusDiv.className = `fixed bottom-4 left-4 p-4 rounded-lg shadow-lg text-white ${bgClass}`;
            statusDiv.textContent = message;
            statusDiv.style.display = 'block';
            
            // Hide after 3 seconds
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 3000);
        }
        
        // Function to log to console div
        function logToDiv(message) {
            const consoleDiv = document.getElementById('consoleLog');
            const timestamp = new Date().toLocaleTimeString();
            consoleDiv.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
        }
        
        // Override console.log to also log to div
        const originalLog = console.log;
        console.log = function(...args) {
            originalLog.apply(console, args);
            logToDiv(args.join(' '));
        };
        
        // Function to save all changes
        function saveAllChanges() {
            const editableElements = document.querySelectorAll('[contenteditable="true"]');
            let savedCount = 0;
            let errorCount = 0;
            
            console.log('Found', editableElements.length, 'editable elements');
            
            if (editableElements.length === 0) {
                showStatus('No editable content found', 'bg-yellow-500');
                return;
            }
            
            showStatus('Saving all changes...', 'bg-blue-500');
            
            const savePromises = Array.from(editableElements).map(element => {
                const contentKey = element.dataset.contentKey;
                const newContent = element.innerHTML;
                
                console.log('Processing element with key:', contentKey, 'content:', newContent.substring(0, 50) + '...');
                
                if (!contentKey) {
                    console.error('No data-content-key attribute found on element:', element);
                    errorCount++;
                    return Promise.resolve();
                }
                
                const requestData = {
                    key: contentKey,
                    value: newContent
                };
                
                console.log('Sending request to', apiEndpoint, 'with data:', requestData);
                
                return fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Save response for', contentKey, ':', data);
                    if (data.success) {
                        savedCount++;
                    } else {
                        throw new Error(data.message || 'Failed to save content');
                    }
                })
                .catch(error => {
                    console.error('Error saving content for', contentKey, ':', error);
                    errorCount++;
                });
            });
            
            Promise.all(savePromises)
                .then(() => {
                    if (errorCount === 0) {
                        showStatus(`All changes saved successfully (${savedCount} items)`, 'bg-green-500');
                    } else {
                        showStatus(`Saved ${savedCount} items, ${errorCount} errors`, 'bg-yellow-500');
                    }
                })
                .catch((error) => {
                    console.error('Error in save operation:', error);
                    showStatus('Error saving changes', 'bg-red-500');
                });
        }
        
        // Function to test individual save
        function testIndividualSave() {
            const element = document.querySelector('[data-content-key="hero.title_line1"]');
            if (element) {
                element.focus();
                element.blur();
            }
        }
        
        // Initialize event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing event listeners');
            
            // Save on blur (when user finishes editing)
            document.querySelectorAll('[contenteditable="true"]').forEach(element => {
                element.addEventListener('blur', function() {
                    const contentKey = this.dataset.contentKey;
                    const newContent = this.innerHTML;
                    
                    console.log('Blur event on element with key:', contentKey);
                    
                    if (!contentKey) {
                        console.error('No data-content-key attribute found on element:', this);
                        return;
                    }
                    
                    const requestData = {
                        key: contentKey,
                        value: newContent
                    };
                    
                    console.log('Blur - Sending request to', apiEndpoint, 'with data:', requestData);
                    showStatus('Saving...', 'bg-blue-500');
                    
                    fetch(apiEndpoint, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(requestData)
                    })
                    .then(response => {
                        console.log('Blur save response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Blur save response:', data);
                        if (data.success) {
                            showStatus('Changes saved successfully', 'bg-green-500');
                        } else {
                            throw new Error(data.message || 'Failed to save changes');
                        }
                    })
                    .catch(error => {
                        console.error('Error saving on blur:', error);
                        showStatus('Error: ' + error.message, 'bg-red-500');
                    });
                });
            });
            
            console.log('Event listeners initialized');
        });
    </script>
</body>
</html> 