<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Image Uploader Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Advanced Image Uploader Test</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Features Demo</h2>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li><strong>Multiple Image Selection:</strong> Select multiple images without losing previously selected ones</li>
                <li><strong>Re-selection:</strong> Add more images later without overwriting existing ones</li>
                <li><strong>Primary & Secondary Ordering:</strong> First two images can be reordered via drag & drop or buttons</li>
                <li><strong>Drag & Drop:</strong> Drag primary/secondary images to swap their positions</li>
                <li><strong>Additional Images:</strong> Rest of the images are added after the first two with no special ordering</li>
                <li><strong>Visual Feedback:</strong> Clear indication of primary (yellow), secondary (blue), and additional (gray) images</li>
            </ul>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Advanced Image Uploader</h2>
            
            <!-- Include the advanced image uploader -->
            <?php include 'admin/advanced_image_uploader.php'; ?>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4">Form Submission Test</h2>
            <form method="POST" enctype="multipart/form-data" id="testForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
                    <input type="text" name="product_name" value="Test Product" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Description</label>
                    <textarea name="product_description" class="w-full px-3 py-2 border border-gray-300 rounded-lg" rows="3">Test product description</textarea>
                </div>
                
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                    Submit Form (Check Console)
                </button>
            </form>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">Instructions</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-800">1. Upload Images</h3>
                    <p class="text-sm text-gray-600">Click the upload area or drag & drop images. You can select multiple images at once or add more later.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-800">2. Reorder Primary & Secondary</h3>
                    <p class="text-sm text-gray-600">Drag the primary (yellow) or secondary (blue) images to swap their positions, or use the "Swap Primary ↔ Secondary" button.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-800">3. Manage Additional Images</h3>
                    <p class="text-sm text-gray-600">Additional images (gray) can be moved to primary or secondary positions using the action buttons that appear on hover.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-800">4. Remove Images</h3>
                    <p class="text-sm text-gray-600">Click the trash icon on any image to remove it. The system will automatically reassign primary/secondary if needed.</p>
                </div>
                
                <div>
                    <h3 class="font-medium text-gray-800">5. Form Submission</h3>
                    <p class="text-sm text-gray-600">When you submit the form, the images will be ordered: Primary → Secondary → Additional images. Check the browser console to see the ordered files.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form submission handler to demonstrate the ordered images
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const files = formData.getAll('images[]');
            
            console.log('=== Form Submission Test ===');
            console.log('Total files:', files.length);
            console.log('Files in order:');
            files.forEach((file, index) => {
                console.log(`${index + 1}. ${file.name} (${file.type})`);
            });
            
            // Show alert with file count
            alert(`Form submitted with ${files.length} images!\nCheck the browser console to see the ordered files.`);
        });
        
        // Add some test functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Advanced Image Uploader Test Page Loaded');
            console.log('Features available:');
            console.log('- Multiple image selection');
            console.log('- Drag & drop reordering for primary/secondary');
            console.log('- Manual reordering with buttons');
            console.log('- Image removal');
            console.log('- Form submission with ordered files');
        });
    </script>
</body>
</html> 