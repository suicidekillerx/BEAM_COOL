<?php
// Include functions for getSiteSetting
require_once __DIR__ . '/../../includes/functions.php';

// Get current page for active menu highlighting
$currentPage = $currentPage ?? 'dashboard';
?>
<!-- Sidebar -->
<div id="sidebar" class="admin-sidebar w-64 flex-shrink-0 transition-all duration-300 ease-in-out flex flex-col h-full">
    <!-- Logo Section -->
    <div class="p-6 border-b border-gray-800 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="relative w-16 h-16">
                <a href="index.php" id="admin-brand-logo" class="text-white rounded-md p-1 relative select-none flex items-center justify-center" style="display:inline-block;">
                    <span class="logo-glitch-stack">
                        <img src="../<?php echo getSiteSetting('brand_logo', 'images/logo.webp'); ?>" alt="Logo 1" class="brand-logo-img w-16 h-16 transition-transform duration-300 glitch-logo glitch-logo-1" style="vertical-align:middle; position:absolute; left:0; top:0; filter: brightness(0) invert(1);"/>
                        <img src="../<?php echo getSiteSetting('brand_logo2', 'images/logo2.png'); ?>" alt="Logo 2" class="brand-logo-img w-16 h-16 transition-transform duration-300 glitch-logo glitch-logo-2" style="vertical-align:middle; position:absolute; left:0; top:0; filter: brightness(0) invert(1);"/>
                    </span>
                </a>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg">BEAM</h1>
                <p class="text-gray-400 text-xs">ADMIN PANEL</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="p-4 space-y-2 flex-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="index.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
            </svg>
            <span class="font-medium">Dashboard</span>
        </a>
        
        <!-- Products -->
        <a href="products.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'products' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <span class="font-medium">Products</span>
            <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full notification-badge">12</span>
        </a>
        
        <!-- Categories -->
        <a href="categories.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span class="font-medium">Categories</span>
        </a>
        
        <!-- Collections -->
        <a href="collections.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'collections' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span class="font-medium">Collections</span>
        </a>
        
        <!-- Orders -->
        <a href="orders.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium">Orders</span>
            <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full notification-badge">5</span>
        </a>
        
        <!-- Inventory -->
        <a href="inventory.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'inventory' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
            </svg>
            <span class="font-medium">Inventory</span>
            <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-1 rounded-full notification-badge">3</span>
        </a>
        
        <!-- About Us -->
        <a href="about.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'about-us' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">About Us</span>
        </a>
        
        <!-- Promo Codes -->
        <a href="promo_codes.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'promo_codes' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
            <span class="font-medium">Promo Codes</span>
        </a>
        

        
        <!-- Website Settings -->
        <a href="setting.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="font-medium">Settings</span>
        </a>
        
        <!-- Password Management -->
        <a href="passwords.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg <?php echo $currentPage === 'passwords' ? 'active' : ''; ?>">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <span class="font-medium">Passwords</span>
        </a>
    </nav>
    
    <!-- Bottom Section -->
    <div class="p-4 border-t border-gray-800 flex-shrink-0">
        <a href="../index.php" class="menu-item flex items-center space-x-3 px-4 py-3 text-white rounded-lg">
            <svg class="menu-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="font-medium">View Store</span>
        </a>
    </div>
</div>