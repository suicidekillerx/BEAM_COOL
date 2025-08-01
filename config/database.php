<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'beam_ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create database connection
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize database tables
function initializeDatabase() {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE " . DB_NAME);
    
    // Create tables
    $tables = [
        // Site settings table
        "CREATE TABLE IF NOT EXISTS site_settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Categories table
        "CREATE TABLE IF NOT EXISTS categories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            image VARCHAR(255),
            description TEXT,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Collections table
        "CREATE TABLE IF NOT EXISTS collections (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) UNIQUE NOT NULL,
            image VARCHAR(255),
            description TEXT,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Products table
        "CREATE TABLE IF NOT EXISTS products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT,
            short_description VARCHAR(500),
            price DECIMAL(10,2) NOT NULL,
            sale_price DECIMAL(10,2) NULL,
            cost_price DECIMAL(10,2) NOT NULL,
            color VARCHAR(50),
            category_id INT,
            collection_id INT NULL,
            is_featured BOOLEAN DEFAULT FALSE,
            is_bestseller BOOLEAN DEFAULT FALSE,
            is_on_sale BOOLEAN DEFAULT FALSE,
            is_active BOOLEAN DEFAULT TRUE,
            show_stock BOOLEAN DEFAULT TRUE,
            stock_status ENUM('in_stock', 'low_stock') DEFAULT 'in_stock',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
            FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE SET NULL
        )",
        
        // Product images table
        "CREATE TABLE IF NOT EXISTS product_images (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            is_primary BOOLEAN DEFAULT FALSE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )",
        
        // Product sizes table
        "CREATE TABLE IF NOT EXISTS product_sizes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_id INT NOT NULL,
            size VARCHAR(10) NOT NULL,
            stock_quantity INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_product_size (product_id, size)
        )",
        
        // Video section table
        "CREATE TABLE IF NOT EXISTS video_section (
            id INT PRIMARY KEY AUTO_INCREMENT,
            video_path VARCHAR(255) NOT NULL,
            slug_text VARCHAR(255),
            button_text VARCHAR(255),
            button_link VARCHAR(255),
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Footer sections table
        "CREATE TABLE IF NOT EXISTS footer_sections (
            id INT PRIMARY KEY AUTO_INCREMENT,
            section_name VARCHAR(100) NOT NULL,
            section_title VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Footer links table
        "CREATE TABLE IF NOT EXISTS footer_links (
            id INT PRIMARY KEY AUTO_INCREMENT,
            section_id INT NOT NULL,
            link_text VARCHAR(255) NOT NULL,
            link_url VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (section_id) REFERENCES footer_sections(id) ON DELETE CASCADE
        )",
        
        // Social media links table
        "CREATE TABLE IF NOT EXISTS social_media (
            id INT PRIMARY KEY AUTO_INCREMENT,
            platform VARCHAR(50) NOT NULL,
            icon_svg TEXT,
            link_url VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Cart table (session-based)
        "CREATE TABLE IF NOT EXISTS cart_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            session_id VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            size VARCHAR(10) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_cart_item (session_id, product_id, size)
        )",
        
        // Wishlist table (session-based)
        "CREATE TABLE IF NOT EXISTS wishlist_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            session_id VARCHAR(255) NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_wishlist_item (session_id, product_id)
        )",
        
        // About Us table - stores all content for the about page
        "CREATE TABLE IF NOT EXISTS aboutus (
            id INT PRIMARY KEY AUTO_INCREMENT,
            section_name VARCHAR(100) NOT NULL,
            content_key VARCHAR(100) NOT NULL,
            content_type ENUM('text', 'image', 'number', 'url', 'html') NOT NULL,
            content_value TEXT,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_section_key (section_name, content_key)
        )",
        
        // Orders table - optimized for billions of orders
        "CREATE TABLE IF NOT EXISTS orders (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            session_id VARCHAR(255) NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50) NOT NULL,
            shipping_address TEXT NOT NULL,
            shipping_city VARCHAR(100) NOT NULL,
            shipping_postal_code VARCHAR(20),
            shipping_notes TEXT,
            subtotal DECIMAL(10,3) NOT NULL,
            tax DECIMAL(10,3) NOT NULL,
            shipping_cost DECIMAL(10,3) NOT NULL,
            total DECIMAL(10,3) NOT NULL,
            payment_method ENUM('cash_on_delivery', 'credit_card', 'bank_transfer') DEFAULT 'cash_on_delivery',
            order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
            payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
            notes TEXT,
            admin_notes TEXT,
            tracking_number VARCHAR(100),
            shipped_at TIMESTAMP NULL,
            delivered_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_order_number (order_number),
            INDEX idx_customer_email (customer_email),
            INDEX idx_order_status (order_status),
            INDEX idx_payment_status (payment_status),
            INDEX idx_created_at (created_at),
            INDEX idx_customer_name (customer_name),
            INDEX idx_shipping_city (shipping_city),
            INDEX idx_total (total),
            INDEX idx_session_id (session_id)
        )",
        
        // Order items table - optimized for billions of items
        "CREATE TABLE IF NOT EXISTS order_items (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            order_id BIGINT NOT NULL,
            product_id INT,
            product_name VARCHAR(255) NOT NULL,
            product_price DECIMAL(10,3) NOT NULL,
            product_sale_price DECIMAL(10,3),
            size VARCHAR(10) NOT NULL,
            quantity INT NOT NULL,
            total_price DECIMAL(10,3) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
            INDEX idx_order_id (order_id),
            INDEX idx_product_id (product_id),
            INDEX idx_product_name (product_name),
            INDEX idx_total_price (total_price)
        )"
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    
    // Insert default data
   // insertDefaultData();
    
    // Migrate orders table to ensure all fields exist
    migrateOrdersTable();
}

// Migrate orders table to add missing fields
function migrateOrdersTable() {
    $pdo = getDBConnection();
    
    try {
        // Check if orders table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
        if ($stmt->rowCount() == 0) {
            return; // Table doesn't exist, will be created by initializeDatabase
        }
        
        // Add missing fields if they don't exist
        $fields = [
            'tracking_number' => 'VARCHAR(100)',
            'shipped_at' => 'TIMESTAMP NULL',
            'delivered_at' => 'TIMESTAMP NULL',
            'admin_notes' => 'TEXT',
            'payment_status' => "ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'"
        ];
        
        foreach ($fields as $fieldName => $fieldDefinition) {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM orders LIKE ?");
            $stmt->execute([$fieldName]);
            
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN $fieldName $fieldDefinition");
                echo "Added field: $fieldName\n";
            }
        }
        
        // Add indexes if they don't exist
        $indexes = [
            'idx_order_number' => 'order_number',
            'idx_customer_email' => 'customer_email',
            'idx_order_status' => 'order_status',
            'idx_payment_status' => 'payment_status',
            'idx_created_at' => 'created_at',
            'idx_customer_name' => 'customer_name',
            'idx_shipping_city' => 'shipping_city',
            'idx_total' => 'total',
            'idx_session_id' => 'session_id'
        ];
        
        foreach ($indexes as $indexName => $columnName) {
            $stmt = $pdo->prepare("SHOW INDEX FROM orders WHERE Key_name = ?");
            $stmt->execute([$indexName]);
            
            if ($stmt->rowCount() == 0) {
                $pdo->exec("CREATE INDEX $indexName ON orders($columnName)");
                echo "Added index: $indexName\n";
            }
        }
        
    } catch (Exception $e) {
        echo "Migration error: " . $e->getMessage() . "\n";
    }
}

function insertDefaultData() {
    $pdo = getDBConnection();
    
    // Insert default site settings
    $settings = [
        ['brand_name', 'BeamTheTeam™'],
        ['brand_logo', 'images/logo.webp'],
        ['hero_image', 'images/hero.webp'],
        ['announcement_text_1', 'SHIPPING TO TUNISIA ON ORDERS +$150'],
        ['announcement_text_2', 'EASY RETURNS'],
        ['announcement_text_3', 'FREE SHIPPING TO TUNISIA ON ORDERS +$150'],
        ['what_makes_special_title', 'What Makes Beam Special?'],
        ['what_makes_special_description', 'At Beam, we believe in more than just clothing. We stand for conscious creation, timeless design, and a commitment to quality that you can feel. Every piece is thoughtfully crafted to not only elevate your style but also to endure, becoming a cherished part of your personal collection for years to come.']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    
    // Insert default categories
    $categories = [
        ['VIEW ALL', 'view-all', 'images/1.webp', 'All products', 1],
        ['TEES', 'tees', 'images/2.webp', 'T-shirts collection', 2],
        ['HOODIES', 'hoodies', 'images/3.webp', 'Hoodies collection', 3],
        ['SHORTS', 'shorts', 'images/4.webp', 'Shorts collection', 4],
        ['JACKETS', 'jackets', 'images/5.webp', 'Jackets collection', 5],
        ['NEW CATEGORY', 'new-category', 'images/7.webp', 'New category', 6],
        ['ANOTHER CATEGORY', 'another-category', 'images/8.webp', 'Another category', 7]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, slug, image, description, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    
    // Insert default collections
    $collections = [
        ['SUMMER VIBES', 'summer-vibes', 'images/collection1.webp', 'Explore our latest summer collection', 1],
        ['URBAN STREET', 'urban-street', 'images/collection2.jpg', 'Street style meets comfort', 2],
        ['WINTER ESSENTIALS', 'winter-essentials', 'images/collection3.webp', 'Stay warm and stylish', 3],
        ['LIMITED EDITION', 'limited-edition', 'images/collection4.webp', 'Exclusive designs for true fans', 4]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO collections (name, slug, image, description, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($collections as $collection) {
        $stmt->execute($collection);
    }
    
    // Insert default social media
    $socialMedia = [
        ['Instagram', '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>', 'https://instagram.com', 1],
        ['Pinterest', '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>', 'https://pinterest.com', 2],
        ['YouTube', '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>', 'https://youtube.com', 3],
        ['TikTok', '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 2.31-3.59 3.99-6.12 4.51-2.56.52-5.18-.06-7.44-1.61-2.26-1.55-3.96-3.87-4.47-6.46-.51-2.59.06-5.21 1.61-7.47 1.55-2.26 3.87-3.96 6.46-4.47 2.59-.51 5.21.06 7.47 1.61.57.39 1.1.82 1.62 1.26.01-2.92-.01-5.84.02-8.75z"/>', 'https://tiktok.com', 4],
        ['LinkedIn', '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>', 'https://linkedin.com', 5]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO social_media (platform, icon_svg, link_url, sort_order) VALUES (?, ?, ?, ?)");
    foreach ($socialMedia as $social) {
        $stmt->execute($social);
    }
    
    // Insert default footer sections
    $footerSections = [
        ['ABOUT', 'ABOUT', 1],
        ['LEGAL', 'LEGAL', 2]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO footer_sections (section_name, section_title, sort_order) VALUES (?, ?, ?)");
    foreach ($footerSections as $section) {
        $stmt->execute($section);
    }
    
    // Insert default footer links
    $footerLinks = [
        [1, 'FAQS', '#', 1],
        [1, 'CONTACT FORM', '#', 2],
        [1, 'SHIPPING POLICY', '#', 3],
        [1, 'CAREERS', '#', 4],
        [1, 'INTERNSHIPS', '#', 5],
        [2, 'RETURN & EXCHANGE POLICY', '#', 1],
        [2, 'PRIVACY POLICY', '#', 2],
        [2, 'COOKIE POLICY', '#', 3],
        [2, 'TERMS OF SERVICE', '#', 4],
        [2, 'LEGAL NOTICE', '#', 5]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO footer_links (section_id, link_text, link_url, sort_order) VALUES (?, ?, ?, ?)");
    foreach ($footerLinks as $link) {
        $stmt->execute($link);
    }
    
    // Insert default about page content
    $aboutContent = [
        // Hero Section
        ['hero', 'title_line1', 'text', 'ABOUT', 1],
        ['hero', 'title_line2', 'text', 'BEAM', 2],
        ['hero', 'subtitle', 'text', 'Crafting the future of fashion, one thread at a time', 3],
        ['hero', 'cta_text', 'text', 'Discover Our Story', 4],
        ['hero', 'background_image', 'image', 'images/hero.webp', 5],
        
        // Story Section
        ['story', 'title', 'text', 'Our Story', 1],
        ['story', 'paragraph1', 'text', 'Born from a passion for innovation and a commitment to excellence, Beam emerged as a revolutionary force in the fashion industry. We believe that clothing is more than just fabric—it\'s a statement, a lifestyle, and an expression of individuality.', 2],
        ['story', 'paragraph2', 'text', 'Founded in 2020, our journey began with a simple vision: to create clothing that transcends trends and speaks to the soul of the modern individual. Every piece we design carries the weight of our values—quality, sustainability, and timeless elegance.', 3],
        ['story', 'quote', 'text', 'Fashion is not just about looking good, it\'s about feeling powerful.', 4],
        ['story', 'image', 'image', 'images/collection1.webp', 5],
        ['story', 'year', 'number', '2020', 6],
        ['story', 'year_label', 'text', 'Founded', 7],
        
        // Mission & Vision Section
        ['mission_vision', 'title', 'text', 'Mission & Vision', 1],
        ['mission_vision', 'subtitle', 'text', 'We\'re not just creating clothes—we\'re crafting experiences that empower individuals to express their authentic selves.', 2],
        ['mission_vision', 'mission_number', 'text', '01', 3],
        ['mission_vision', 'mission_title', 'text', 'Our Mission', 4],
        ['mission_vision', 'mission_content', 'text', 'To revolutionize the fashion industry by creating sustainable, high-quality clothing that empowers individuals to express their unique identity while maintaining the highest standards of craftsmanship and ethical production.', 5],
        ['mission_vision', 'vision_number', 'text', '02', 6],
        ['mission_vision', 'vision_title', 'text', 'Our Vision', 7],
        ['mission_vision', 'vision_content', 'text', 'To become the global leader in sustainable fashion, setting new standards for quality, innovation, and social responsibility while inspiring a new generation of conscious consumers.', 8],
        
        // Stats Section
        ['stats', 'countries_number', 'number', '50', 1],
        ['stats', 'countries_label', 'text', 'Countries', 2],
        ['stats', 'products_number', 'number', '1000', 3],
        ['stats', 'products_label', 'text', 'Products', 4],
        ['stats', 'customers_number', 'number', '10000', 5],
        ['stats', 'customers_label', 'text', 'Happy Customers', 6],
        ['stats', 'years_number', 'number', '5', 7],
        ['stats', 'years_label', 'text', 'Years', 8],
        
        // Values Section
        ['values', 'title', 'text', 'Our Values', 1],
        ['values', 'subtitle', 'text', 'The principles that guide everything we do', 2],
        ['values', 'value1_title', 'text', 'Quality', 3],
        ['values', 'value1_content', 'text', 'We never compromise on quality. Every stitch, every fabric, every detail is carefully selected and crafted to perfection.', 4],
        ['values', 'value1_icon', 'html', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>', 5],
        ['values', 'value2_title', 'text', 'Sustainability', 6],
        ['values', 'value2_content', 'text', 'We\'re committed to protecting our planet. Our sustainable practices ensure a better future for generations to come.', 7],
        ['values', 'value2_icon', 'html', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>', 8],
        ['values', 'value3_title', 'text', 'Innovation', 9],
        ['values', 'value3_content', 'text', 'We constantly push boundaries, exploring new technologies and creative solutions to redefine fashion.', 10],
        ['values', 'value3_icon', 'html', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>', 11],
        
        // Timeline Section
        ['timeline', 'title', 'text', 'Our Journey', 1],
        ['timeline', 'subtitle', 'text', 'From humble beginnings to global recognition', 2],
        ['timeline', 'year1', 'text', '2020', 3],
        ['timeline', 'year1_title', 'text', 'The Beginning', 4],
        ['timeline', 'year1_content', 'text', 'Founded with a vision to revolutionize fashion through sustainable practices and innovative design.', 5],
        ['timeline', 'year2', 'text', '2021', 6],
        ['timeline', 'year2_title', 'text', 'First Collection', 7],
        ['timeline', 'year2_content', 'text', 'Launched our debut collection, receiving critical acclaim and establishing our unique aesthetic.', 8],
        ['timeline', 'year3', 'text', '2022', 9],
        ['timeline', 'year3_title', 'text', 'Global Expansion', 10],
        ['timeline', 'year3_content', 'text', 'Expanded to international markets, bringing our vision to customers worldwide.', 11],
        ['timeline', 'year4', 'text', '2023', 12],
        ['timeline', 'year4_title', 'text', 'Sustainability Milestone', 13],
        ['timeline', 'year4_content', 'text', 'Achieved 100% sustainable production and became carbon neutral.', 14],
        ['timeline', 'year5', 'text', '2024', 15],
        ['timeline', 'year5_title', 'text', 'Innovation Hub', 16],
        ['timeline', 'year5_content', 'text', 'Launched our innovation hub, pioneering new technologies in sustainable fashion.', 17],
        
        // Team Section
        ['team', 'title', 'text', 'Meet Our Team', 1],
        ['team', 'subtitle', 'text', 'The passionate individuals behind our success', 2],
        ['team', 'member1_name', 'text', 'Sarah Johnson', 3],
        ['team', 'member1_position', 'text', 'Creative Director', 4],
        ['team', 'member1_description', 'text', 'Visionary leader with 10+ years in fashion design', 5],
        ['team', 'member1_image', 'image', 'images/collection2.jpg', 6],
        ['team', 'member2_name', 'text', 'Michael Chen', 7],
        ['team', 'member2_position', 'text', 'Head of Sustainability', 8],
        ['team', 'member2_description', 'text', 'Environmental expert driving our green initiatives', 9],
        ['team', 'member2_image', 'image', 'images/collection3.webp', 10],
        ['team', 'member3_name', 'text', 'Emma Rodriguez', 11],
        ['team', 'member3_position', 'text', 'Production Manager', 12],
        ['team', 'member3_description', 'text', 'Ensuring quality and efficiency in every process', 13],
        ['team', 'member3_image', 'image', 'images/collection4.webp', 14],
        
        // CTA Section
        ['cta', 'title', 'text', 'Join Our Journey', 1],
        ['cta', 'subtitle', 'text', 'Be part of the revolution. Discover our collections and experience the future of fashion.', 2],
        ['cta', 'button1_text', 'text', 'Shop Now', 3],
        ['cta', 'button1_url', 'url', 'shop.php', 4],
        ['cta', 'button2_text', 'text', 'View Collections', 5],
        ['cta', 'button2_url', 'url', 'collections.php', 6]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO aboutus (section_name, content_key, content_type, content_value, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($aboutContent as $content) {
        $stmt->execute($content);
    }
    
    // Insert default video section
    $stmt = $pdo->prepare("INSERT IGNORE INTO video_section (video_path, slug_text, button_text, button_link, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'video/COLD CULTURE - THINKING WORLDWIDE.mp4',
        'Beam X WeULT',
        'JOIN COMMUNITY',
        '#',
        'Join our exclusive community and stay connected with the latest updates, behind-the-scenes content, and exclusive offers from Beam.'
    ]);
}

// Initialize database on first run
if (!file_exists(__DIR__ . '/database_initialized.txt')) {
    initializeDatabase();
    file_put_contents(__DIR__ . '/database_initialized.txt', 'Database initialized on ' . date('Y-m-d H:i:s'));
}

// Add new columns to existing products table if they don't exist
function migrateProductsTable() {
    $pdo = getDBConnection();
    try {
        // Add show_stock column if it doesn't exist
        $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS show_stock BOOLEAN DEFAULT TRUE");
        
        // Check if stock_status column exists and what type it is
        $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock_status'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$column) {
            // Column doesn't exist, add it as ENUM
            $pdo->exec("ALTER TABLE products ADD COLUMN stock_status ENUM('in_stock', 'low_stock') DEFAULT 'in_stock'");
        } else if ($column['Type'] !== "enum('in_stock','low_stock')") {
            // Column exists but wrong type, modify it
            $pdo->exec("ALTER TABLE products MODIFY COLUMN stock_status ENUM('in_stock', 'low_stock') DEFAULT 'in_stock'");
        }
        
        // Update existing products to have show_stock enabled by default
        $pdo->exec("UPDATE products SET show_stock = TRUE WHERE show_stock IS NULL");
        
        // Update any existing 'out_of_stock' values to 'in_stock' since we removed that option
        $pdo->exec("UPDATE products SET stock_status = 'in_stock' WHERE stock_status = 'out_of_stock' OR stock_status IS NULL");
    } catch (Exception $e) {
        // Columns might already exist, ignore error
        echo "Migration error: " . $e->getMessage() . "\n";
    }
}

// Run migration
migrateProductsTable();
?> 