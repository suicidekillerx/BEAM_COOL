<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Image Upload</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Test Image Upload Functionality</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Background Image Test</h2>
            
            <div class="relative h-64 bg-gray-200 rounded-lg overflow-hidden mb-4">
                <div class="absolute inset-0 parallax-bg" 
                     style="background-image: url('images/hero.webp'); background-size: cover; background-position: center;">
                </div>
                <form class="absolute top-2 left-2" enctype="multipart/form-data" data-content-key="hero.background_image">
                    <input type="file" accept="image/*" name="image" style="display:none" />
                    <button type="button" class="bg-white text-black px-2 py-1 rounded shadow">Edit Background Image</button>
                </form>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Regular Image Test</h2>
            
            <div class="relative">
                <div class="w-full h-64 bg-gray-200 rounded-lg overflow-hidden">
                    <img src="images/collection1.webp" alt="Test Image" class="w-full h-full object-cover"
                         data-content-key="story.image">
                    <form class="absolute top-2 left-2" enctype="multipart/form-data" data-content-key="story.image">
                        <input type="file" accept="image/*" name="image" style="display:none" />
                        <button type="button" class="bg-white text-black px-2 py-1 rounded shadow">Edit Image</button>
                    </form>
                </div>
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
        
        function uploadImage(fileInput, contentKey, imgEl) {
            const file = fileInput.files[0];
            if (!file) return;
            
            console.log('Uploading image for content key:', contentKey);
            
            const formData = new FormData();
            formData.append('image', file);
            formData.append('key', contentKey);
            
            fetch('admin/upload_about_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Image upload response:', data);
                if (data.success) {
                    // Handle different types of image updates
                    if (contentKey === 'hero.background_image') {
                        // Update background image
                        const bgElement = document.querySelector('.parallax-bg');
                        if (bgElement) {
                            bgElement.style.backgroundImage = `url('${data.file_path}')`;
                            console.log('Updated background image to:', data.file_path);
                        }
                    } else if (imgEl) {
                        // Update regular image
                        imgEl.src = data.file_path;
                        console.log('Updated image src to:', data.file_path);
                    }
                    showStatus('Image uploaded successfully', 'bg-green-500');
                } else {
                    console.error('Image upload failed:', data.error);
                    showStatus('Image upload failed: ' + (data.error || 'Unknown error'), 'bg-red-500');
                }
            })
            .catch(error => {
                console.error('Image upload error:', error);
                showStatus('Image upload failed', 'bg-red-500');
            });
        }
        
        // Initialize event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing image upload event listeners');
            
            // Image upload
            document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
                const fileInput = form.querySelector('input[type="file"]');
                const button = form.querySelector('button[type="button"]');
                const contentKey = form.dataset.contentKey;
                
                console.log('Setting up image upload for content key:', contentKey);
                
                // Find the image element that should be updated
                let imgEl = null;
                const parentContainer = form.closest('.relative') || form.parentElement;
                if (parentContainer) {
                    imgEl = parentContainer.querySelector('img');
                    console.log('Found image element:', imgEl);
                }
                
                if (fileInput && button && contentKey) {
                    button.addEventListener('click', function() {
                        fileInput.click();
                    });
                    fileInput.addEventListener('change', function() {
                        console.log('File selected for upload:', fileInput.files[0]?.name);
                        uploadImage(fileInput, contentKey, imgEl);
                    });
                }
            });
            
            console.log('Image upload event listeners initialized');
        });
    </script>
</body>
</html> 