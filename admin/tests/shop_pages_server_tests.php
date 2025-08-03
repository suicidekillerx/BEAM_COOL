<?php
/**
 * Shop Pages Server-Side Test Runner
 * Tests all shop pages functionality including database, files, and server features
 */

require_once __DIR__ . '/../../config/database.php';

class ShopPagesTestRunner {
    private $testResults = [];
    private $passedTests = 0;
    private $failedTests = 0;
    private $baseUrl = 'http://localhost/BEAM_COOL/';
    
    public function runAllTests() {
        echo "🛍️ Starting Shop Pages Server-Side Tests...\n";
        echo "=============================================\n\n";
        
        $this->testFileExistence();
        $this->testDatabaseQueries();
        $this->testPageFunctionality();
        $this->testCartSystem();
        $this->testProductSystem();
        $this->testCollectionsSystem();
        $this->testSecretCollection();
        $this->testAboutPage();
        $this->testIncludes();
        $this->testAJAXHandlers();
        
        $this->displayResults();
    }
    
    private function testFileExistence() {
        echo "📁 Testing File Existence...\n";
        
        $requiredFiles = [
            'index.php' => 'Home page',
            'shop.php' => 'Shop page',
            'collections.php' => 'Collections page',
            'secret-collection.php' => 'Secret collection page',
            'about.php' => 'About page',
            'cart.php' => 'Cart page',
            'checkout.php' => 'Checkout page',
            'product-view.php' => 'Product view page',
            'view_cart.php' => 'View cart page',
            'order-confirmation.php' => 'Order confirmation page',
            'style.css' => 'Main stylesheet',
            'script.js' => 'Main JavaScript file',
            'ajax_handler.php' => 'AJAX handler',
            'add_to_cart.php' => 'Add to cart handler'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            $exists = file_exists(__DIR__ . '/../../' . $file);
            $this->addTestResult("File: {$description} ({$file})", $exists);
        }
        
        // Test includes directory
        $includesDir = __DIR__ . '/../../includes/';
        $this->addTestResult('Includes Directory Exists', is_dir($includesDir));
        
        if (is_dir($includesDir)) {
            $includeFiles = ['header.php', 'footer.php', 'functions.php'];
            foreach ($includeFiles as $file) {
                $exists = file_exists($includesDir . $file);
                $this->addTestResult("Include File: {$file}", $exists);
            }
        }
    }
    
    private function testDatabaseQueries() {
        echo "🗄️ Testing Database Queries...\n";
        
        try {
            $pdo = getDBConnection();
            
            // Test products table
            $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
            $productsTableExists = $stmt->rowCount() > 0;
            $this->addTestResult('Products Table Exists', $productsTableExists);
            
            if ($productsTableExists) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
                $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                $this->addTestResult('Products Count', $productCount > 0, "Found {$productCount} products");
                
                // Test product images
                $stmt = $pdo->query("SHOW TABLES LIKE 'product_images'");
                $imagesTableExists = $stmt->rowCount() > 0;
                $this->addTestResult('Product Images Table Exists', $imagesTableExists);
            }
            
            // Test categories table
            $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
            $categoriesTableExists = $stmt->rowCount() > 0;
            $this->addTestResult('Categories Table Exists', $categoriesTableExists);
            
            if ($categoriesTableExists) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
                $categoryCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                $this->addTestResult('Categories Count', $categoryCount > 0, "Found {$categoryCount} categories");
            }
            
            // Test orders table
            $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
            $ordersTableExists = $stmt->rowCount() > 0;
            $this->addTestResult('Orders Table Exists', $ordersTableExists);
            
            // Test collections table
            $stmt = $pdo->query("SHOW TABLES LIKE 'collections'");
            $collectionsTableExists = $stmt->rowCount() > 0;
            $this->addTestResult('Collections Table Exists', $collectionsTableExists);
            
        } catch (Exception $e) {
            $this->addTestResult('Database Connection', false, $e->getMessage());
        }
    }
    
    private function testPageFunctionality() {
        echo "🌐 Testing Page Functionality...\n";
        
        // Test if pages are accessible (basic file check)
        $pages = [
            'index.php' => 'Home Page',
            'shop.php' => 'Shop Page',
            'collections.php' => 'Collections Page',
            'secret-collection.php' => 'Secret Collection Page',
            'about.php' => 'About Page'
        ];
        
        foreach ($pages as $page => $description) {
            $filePath = __DIR__ . '/../../' . $page;
            $fileExists = file_exists($filePath);
            $this->addTestResult("Page File: {$description}", $fileExists);
            
            if ($fileExists) {
                $fileSize = filesize($filePath);
                $this->addTestResult("Page Size: {$description}", $fileSize > 0, "Size: {$fileSize} bytes");
                
                // Check if file contains PHP code
                $content = file_get_contents($filePath);
                $hasPhpCode = strpos($content, '<?php') !== false;
                $this->addTestResult("PHP Code: {$description}", $hasPhpCode);
            }
        }
    }
    
    private function testCartSystem() {
        echo "🛒 Testing Cart System...\n";
        
        // Test cart files
        $cartFiles = [
            'cart.php' => 'Cart Page',
            'view_cart.php' => 'View Cart Page',
            'add_to_cart.php' => 'Add to Cart Handler',
            'cart-simple.js' => 'Cart JavaScript',
            'fix-cart.js' => 'Cart Fix JavaScript'
        ];
        
        foreach ($cartFiles as $file => $description) {
            $filePath = __DIR__ . '/../../' . $file;
            $exists = file_exists($filePath);
            $this->addTestResult("Cart File: {$description}", $exists);
        }
        
        // Test checkout system
        $checkoutFiles = [
            'checkout.php' => 'Checkout Page',
            'order-confirmation.php' => 'Order Confirmation Page',
            'invoice.php' => 'Invoice Page'
        ];
        
        foreach ($checkoutFiles as $file => $description) {
            $filePath = __DIR__ . '/../../' . $file;
            $exists = file_exists($filePath);
            $this->addTestResult("Checkout File: {$description}", $exists);
        }
    }
    
    private function testProductSystem() {
        echo "📦 Testing Product System...\n";
        
        try {
            $pdo = getDBConnection();
            
            // Test product queries
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
            $activeProducts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $this->addTestResult('Active Products Count', $activeProducts > 0, "Found {$activeProducts} active products");
            
            // Test product with images
            $stmt = $pdo->query("
                SELECT p.id, p.name, COUNT(pi.id) as image_count 
                FROM products p 
                LEFT JOIN product_images pi ON p.id = pi.product_id 
                WHERE p.is_active = 1 
                GROUP BY p.id 
                HAVING image_count > 0 
                LIMIT 1
            ");
            $productWithImages = $stmt->fetch();
            $this->addTestResult('Products with Images', $productWithImages !== false);
            
            // Test product categories
            $stmt = $pdo->query("
                SELECT p.id, p.name, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 
                LIMIT 1
            ");
            $productWithCategory = $stmt->fetch();
            $this->addTestResult('Products with Categories', $productWithCategory !== false);
            
        } catch (Exception $e) {
            $this->addTestResult('Product System', false, $e->getMessage());
        }
    }
    
    private function testCollectionsSystem() {
        echo "📚 Testing Collections System...\n";
        
        try {
            $pdo = getDBConnection();
            
            // Test collections table
            $stmt = $pdo->query("SHOW TABLES LIKE 'collections'");
            $collectionsTableExists = $stmt->rowCount() > 0;
            $this->addTestResult('Collections Table Exists', $collectionsTableExists);
            
            if ($collectionsTableExists) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM collections WHERE is_active = 1");
                $activeCollections = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                $this->addTestResult('Active Collections Count', $activeCollections > 0, "Found {$activeCollections} active collections");
            }
            
        } catch (Exception $e) {
            $this->addTestResult('Collections System', false, $e->getMessage());
        }
    }
    
    private function testSecretCollection() {
        echo "🔒 Testing Secret Collection...\n";
        
        // Test secret collection files
        $secretFiles = [
            'secret-collection.php' => 'Secret Collection Page',
            'secret-shop.php' => 'Secret Shop Page',
            'logout-secret.php' => 'Secret Logout Page'
        ];
        
        foreach ($secretFiles as $file => $description) {
            $filePath = __DIR__ . '/../../' . $file;
            $exists = file_exists($filePath);
            $this->addTestResult("Secret File: {$description}", $exists);
        }
        
        // Test secret session documentation
        $secretDoc = __DIR__ . '/../../SECRET_SESSION_DURATION.md';
        $this->addTestResult('Secret Session Documentation', file_exists($secretDoc));
    }
    
    private function testAboutPage() {
        echo "ℹ️ Testing About Page...\n";
        
        // Test about page files
        $aboutFiles = [
            'about.php' => 'About Page',
            'aboutus.sql' => 'About Database'
        ];
        
        foreach ($aboutFiles as $file => $description) {
            $filePath = __DIR__ . '/../../' . $file;
            $exists = file_exists($filePath);
            $this->addTestResult("About File: {$description}", $exists);
        }
        
        // Test about images directory
        $aboutImagesDir = __DIR__ . '/../../images/about/';
        $this->addTestResult('About Images Directory', is_dir($aboutImagesDir));
        
        if (is_dir($aboutImagesDir)) {
            $aboutImages = glob($aboutImagesDir . '*.jpg');
            $this->addTestResult('About Images Count', count($aboutImages) > 0, "Found " . count($aboutImages) . " images");
        }
    }
    
    private function testIncludes() {
        echo "📋 Testing Includes...\n";
        
        $includesDir = __DIR__ . '/../../includes/';
        
        if (is_dir($includesDir)) {
            $includeFiles = [
                'header.php' => 'Header Include',
                'footer.php' => 'Footer Include',
                'functions.php' => 'Functions Include'
            ];
            
            foreach ($includeFiles as $file => $description) {
                $filePath = $includesDir . $file;
                $exists = file_exists($filePath);
                $this->addTestResult("Include: {$description}", $exists);
                
                if ($exists) {
                    $content = file_get_contents($filePath);
                    $hasPhpCode = strpos($content, '<?php') !== false;
                    $this->addTestResult("PHP Code: {$description}", $hasPhpCode);
                }
            }
        }
    }
    
    private function testAJAXHandlers() {
        echo "🔄 Testing AJAX Handlers...\n";
        
        // Test AJAX handler file
        $ajaxFile = __DIR__ . '/../../ajax_handler.php';
        $this->addTestResult('AJAX Handler File', file_exists($ajaxFile));
        
        if (file_exists($ajaxFile)) {
            $content = file_get_contents($ajaxFile);
            
            // Test for common AJAX actions
            $ajaxActions = [
                'add_to_cart' => 'Add to Cart Action',
                'update_cart' => 'Update Cart Action',
                'remove_from_cart' => 'Remove from Cart Action',
                'get_products' => 'Get Products Action',
                'search_products' => 'Search Products Action'
            ];
            
            foreach ($ajaxActions as $action => $description) {
                $hasAction = strpos($content, $action) !== false;
                $this->addTestResult("AJAX Action: {$description}", $hasAction);
            }
        }
    }
    
    private function addTestResult($testName, $passed, $message = '') {
        $result = [
            'name' => $testName,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->testResults[] = $result;
        
        if ($passed) {
            $this->passedTests++;
            echo "  ✅ {$testName}";
        } else {
            $this->failedTests++;
            echo "  ❌ {$testName}";
        }
        
        if ($message) {
            echo " - {$message}";
        }
        echo "\n";
    }
    
    private function displayResults() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 SHOP PAGES TEST RESULTS SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        echo "Total Tests: " . count($this->testResults) . "\n";
        echo "Passed: {$this->passedTests}\n";
        echo "Failed: {$this->failedTests}\n";
        echo "Success Rate: " . round(($this->passedTests / count($this->testResults)) * 100, 1) . "%\n";
        
        if ($this->failedTests > 0) {
            echo "\n❌ FAILED TESTS:\n";
            foreach ($this->testResults as $result) {
                if (!$result['passed']) {
                    echo "  - {$result['name']}";
                    if ($result['message']) {
                        echo " ({$result['message']})";
                    }
                    echo "\n";
                }
            }
        }
        
        echo "\n🎯 RECOMMENDATIONS:\n";
        if ($this->failedTests === 0) {
            echo "  ✅ All shop pages tests passed! Your e-commerce site is ready.\n";
        } else {
            echo "  ⚠️  Some tests failed. Please review the failed tests above.\n";
            echo "  🔧 Consider fixing missing files or database issues.\n";
        }
        
        echo "\n📋 TESTED PAGES:\n";
        echo "  - HOME (index.php)\n";
        echo "  - SHOP (shop.php)\n";
        echo "  - COLLECTIONS (collections.php)\n";
        echo "  - SECRET COLLECTION (secret-collection.php)\n";
        echo "  - ABOUT US (about.php)\n";
        echo "  - Cart & Checkout System\n";
        echo "  - Product Management\n";
        echo "  - AJAX Handlers\n";
        
        echo "\n" . str_repeat("=", 50) . "\n";
    }
}

// Run the tests
$testRunner = new ShopPagesTestRunner();
$testRunner->runAllTests();
?> 