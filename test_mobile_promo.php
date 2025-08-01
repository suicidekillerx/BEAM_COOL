<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Promo Codes Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Mobile responsive test styles */
        .mobile-test {
            max-width: 375px;
            height: 667px;
            border: 2px solid #333;
            border-radius: 20px;
            overflow: hidden;
            margin: 20px auto;
            position: relative;
        }
        
        .mobile-test iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .test-info {
            text-align: center;
            margin: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-center mb-8">Mobile Promo Codes Test</h1>
        
        <div class="test-info">
            <h2 class="text-xl font-semibold mb-4">Test Instructions:</h2>
            <ol class="text-left space-y-2">
                <li>1. <strong>Sidebar Toggle:</strong> Click the hamburger menu to open/close sidebar</li>
                <li>2. <strong>Mobile Cards:</strong> On mobile, table becomes cards view</li>
                <li>3. <strong>Responsive Form:</strong> Modal form adapts to mobile screen</li>
                <li>4. <strong>Touch Interactions:</strong> All buttons should be touch-friendly</li>
                <li>5. <strong>Overlay:</strong> Click outside sidebar to close it</li>
            </ol>
        </div>
        
        <div class="mobile-test">
            <iframe src="admin/promo_codes.php" title="Mobile Promo Codes Test"></iframe>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-gray-600">Mobile view simulation (375x667px)</p>
            <p class="text-sm text-gray-500">Test the responsive design and mobile sidebar functionality</p>
        </div>
        
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-3">✅ Mobile Features Added:</h3>
                <ul class="space-y-2 text-sm">
                    <li>• Responsive sidebar with overlay</li>
                    <li>• Mobile cards view for promo codes</li>
                    <li>• Touch-friendly buttons and forms</li>
                    <li>• Responsive modal with scroll</li>
                    <li>• Mobile-optimized grid layouts</li>
                    <li>• Proper text sizing for mobile</li>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-3">📱 Mobile Breakpoints:</h3>
                <ul class="space-y-2 text-sm">
                    <li>• <strong>Mobile:</strong> &lt; 768px (cards view)</li>
                    <li>• <strong>Tablet:</strong> 768px - 1023px</li>
                    <li>• <strong>Desktop:</strong> &gt; 1024px (table view)</li>
                    <li>• <strong>Sidebar:</strong> Fixed on mobile, overlay</li>
                    <li>• <strong>Modal:</strong> Full width on mobile</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 