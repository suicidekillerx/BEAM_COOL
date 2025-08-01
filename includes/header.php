<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

// Check maintenance mode before loading any page
checkMaintenanceMode();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getSiteSetting('brand_name', 'BeamTheTeam'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Custom scrollbar for mega menu */
        .mega-menu-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .mega-menu-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .mega-menu-scroll::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        .mega-menu-scroll::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Ensure mega menu doesn't overflow on mobile */
        @media (max-width: 1024px) {
            .mega-menu-container {
                max-height: 60vh !important;
            }
        }
        
        /* Enhanced mega menu animations */
        .mega-menu-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.98);
        }
        
        /* Hover effects for featured banner */
        .featured-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .featured-banner:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        /* Glow effect for stats */
        .stats-card {
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Enhanced hover effects */
        .mega-menu-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .mega-menu-item:hover {
            transform: translateX(4px);
        }
        
        /* Gradient text effect */
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Search modal responsive styles */
        @media (max-width: 768px) {
            #search-modal .max-w-2xl {
                margin: 1rem;
                max-width: calc(100% - 2rem);
            }
            
            #search-modal .pt-20 {
                padding-top: 2rem;
            }
        }
        
        /* Live search results styling */
        #live-search-results {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .search-result-item {
            transition: all 0.2s ease;
        }
        
        .search-result-item:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }
        
        .search-result-item:not(:last-child) {
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- Top Announcement Bar -->
    <div class="bg-black text-white text-xs py-2 px-4 overflow-hidden">
        <div class="flex animate-scroll space-x-8 whitespace-nowrap">
            <span class="flex items-center"><svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v3H8v2h1v3h2v-3h1v-2h-1V7z"></path></svg> <?php echo getSiteSetting('announcement_text_1', 'SHIPPING TO TUNISIA ON ORDERS +$150'); ?></span>
            <span class="flex items-center"><svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v3H8v2h1v3h2v-3h1v-2h-1V7z"></path></svg> <?php echo getSiteSetting('announcement_text_2', 'EASY RETURNS'); ?></span>
            <span class="flex items-center"><svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v3H8v2h1v3h2v-3h1v-2h-1V7z"></path></svg> <?php echo getSiteSetting('announcement_text_3', 'FREE SHIPPING TO TUNISIA ON ORDERS +$150'); ?></span>
            <!-- Duplicated for seamless scroll -->
            <span class="flex items-center"><svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v3H8v2h1v3h2v-3h1v-2h-1V7z"></path></svg> <?php echo getSiteSetting('announcement_text_1', 'SHIPPING TO TUNISIA ON ORDERS +$150'); ?></span>
            <span class="flex items-center"><svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v3H8v2h1v3h2v-3h1v-2h-1V7z"></path></svg> <?php echo getSiteSetting('announcement_text_2', 'EASY RETURNS'); ?></span>
            <span class="flex items-center"><svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11H9v3H8v2h1v3h2v-3h1v-2h-1V7z"></path></svg> <?php echo getSiteSetting('announcement_text_3', 'FREE SHIPPING TO TUNISIA ON ORDERS +$150'); ?></span>
        </div>
    </div>

    <!-- Header/Navigation Bar -->
    <header class="bg-white shadow-sm py-4 px-4 md:px-8 lg:px-12 sticky top-0 z-50">
        <div class="flex items-center justify-between relative">
            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-800 hover:text-black focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
            </div>
        
            <!-- Left Nav -->
            <nav class="hidden md:flex items-center space-x-2 md:space-x-4 lg:space-x-6 text-xs md:text-sm font-medium">
                <a href="index.php" class="hover:text-gray-700 rounded-md p-1 md:p-2 block">HOME</a>
                <div class="relative group">
                    <a href="shop.php" class="hover:text-gray-700 rounded-md p-1 md:p-2 block">SHOP</a>
                    
                    <!-- Mega Menu -->
                    <div class="absolute top-full left-0 w-screen bg-white shadow-2xl border-t border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 max-h-[85vh] overflow-y-auto mega-menu-container">
                        <div class="max-w-full mx-auto px-4 md:px-6 lg:px-8 py-8">
                            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                                <!-- Left Column - Categories (2 columns) -->
                                <div class="xl:col-span-2">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-xl font-bold text-black">CATEGORIES</h3>
                                        <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><?php echo count(getCategories()); ?> items</span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto mega-menu-scroll">
                                        <?php 
                                        $categories = getCategories();
                                        foreach ($categories as $category): 
                                        ?>
                                        <a href="shop.php?category=<?php echo $category['id']; ?>" class="flex items-center p-4 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 transition-all duration-300 group border border-transparent hover:border-gray-200 mega-menu-item">
                                            <div class="relative">
                                                <img src="<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" class="w-12 h-12 object-cover rounded-lg mr-4 flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all duration-300"></div>
                                            </div>
                                            <div class="flex-1">
                                                <span class="text-gray-800 group-hover:text-black transition-colors duration-200 font-semibold block"><?php echo $category['name']; ?></span>
                                                <span class="text-xs text-gray-500 group-hover:text-gray-700 transition-colors duration-200">Browse products</span>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-black transition-colors duration-200 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Middle Column - Collections -->
                                <div class="xl:col-span-1">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-xl font-bold text-black">COLLECTIONS</h3>
                                        <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><?php echo count(getCollections()); ?> items</span>
                                    </div>
                                    <div class="space-y-4 max-h-96 overflow-y-auto mega-menu-scroll">
                                        <?php 
                                        $collections = getCollections();
                                        foreach ($collections as $collection): 
                                        ?>
                                        <a href="shop.php?collection=<?php echo $collection['id']; ?>" class="block p-4 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 transition-all duration-300 group border border-transparent hover:border-gray-200 mega-menu-item">
                                            <div class="flex items-center mb-3">
                                                <div class="relative">
                                                    <img src="<?php echo $collection['image']; ?>" alt="<?php echo $collection['name']; ?>" class="w-12 h-12 object-cover rounded-lg mr-4 flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all duration-300"></div>
                                                </div>
                                                <div class="flex-1">
                                                    <span class="text-gray-800 group-hover:text-black transition-colors duration-200 font-semibold block"><?php echo $collection['name']; ?></span>
                                                </div>
                                            </div>
                                            <p class="text-xs text-gray-600 leading-relaxed"><?php echo $collection['description']; ?></p>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Right Column - Best Seller Products -->
                                <div class="xl:col-span-1">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-xl font-bold text-black">BEST SELLERS</h3>
                                        <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Top 4</span>
                                    </div>
                                    <div class="space-y-4 max-h-96 overflow-y-auto mega-menu-scroll">
                                        <?php 
                                        $bestsellerProducts = getProducts(['bestseller' => true, 'limit' => 4]);
                                        foreach ($bestsellerProducts as $product): 
                                            $productImages = getProductImagesForSlider($product['id']);
                                            $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                                        ?>
                                        <div class="group">
                                            <a href="product-view.php?id=<?php echo $product['id']; ?>" class="block p-4 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-gray-100 transition-all duration-300 border border-transparent hover:border-gray-200 mega-menu-item">
                                                <div class="flex items-center">
                                                    <div class="relative">
                                                        <img src="<?php echo $primaryImage; ?>" alt="<?php echo $product['name']; ?>" class="w-14 h-14 object-cover rounded-lg mr-4 flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 rounded-lg transition-all duration-300"></div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-semibold text-gray-800 truncate group-hover:text-black transition-colors duration-200"><?php echo $product['name']; ?></p>
                                                        <p class="text-sm font-bold text-black"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></p>
                                                        <div class="flex items-center mt-1">
                                                            <div class="flex text-yellow-400">
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                            </div>
                                                            <span class="text-xs text-gray-500 ml-1">(4.8)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                

                            </div>
                        </div>
                    </div>
                </div>
                <a href="collections.php" class="hover:text-gray-700 rounded-md p-1 md:p-2 hidden sm:block">COLLECTIONS</a>
                <?php if (getSiteSetting('secret_collection', '0') === '1'): ?>
                <a href="secret-collection.php" class="hover:text-gray-700 rounded-md p-1 md:p-2 hidden sm:block">SECRET COLLECTION</a>
                <?php endif; ?>
                <a href="about.php" class="hover:text-gray-700 rounded-md p-1 md:p-2 hidden xl:block">ABOUT US</a>
            </nav>

            <!-- Logo -->
            <div class="absolute left-1/2 transform -translate-x-1/2">
                <a href="index.php" id="brand-logo" class="text-lg md:text-xl lg:text-2xl font-bold tracking-wider text-gray-800 rounded-md p-1 md:p-2 glitch-text relative select-none flex items-center gap-2" style="display:inline-block;">
                    <span class="logo-glitch-stack">
                        <img src="<?php echo getSiteSetting('brand_logo', 'images/logo.webp'); ?>" alt="Logo 1" class="brand-logo-img w-16 h-16 md:w-20 md:h-20 mx-auto transition-transform duration-300 glitch-logo glitch-logo-1" style="vertical-align:middle; position:absolute; left:0; top:0;"/>
                        <img src="<?php echo getSiteSetting('brand_logo2', 'images/logo2.png'); ?>" alt="Logo 2" class="brand-logo-img w-16 h-16 md:w-20 md:h-20 mx-auto transition-transform duration-300 glitch-logo glitch-logo-2" style="vertical-align:middle; position:absolute; left:0; top:0;"/>
                    </span>
                </a>
            </div>
            <style>
.logo-glitch-stack {
    position: relative;
    width: 5rem;
    height: 5rem;
    display: inline-block;
}
.glitch-logo {
    position: absolute;
    left: 0; top: 0;
    width: 100%; height: 100%;
    opacity: 0;
    filter: drop-shadow(0 0 2px #0ff) drop-shadow(0 0 4px #f0f);
    animation: logo-glitch-fade 3s infinite steps(1);
}
.glitch-logo-1 {
    animation-delay: 0s;
}
.glitch-logo-2 {
    animation-delay: 1.5s;
}
@keyframes logo-glitch-fade {
    0%   { opacity: 1; filter: none; transform: none; }
    10%  { opacity: 1; filter: blur(1px) brightness(1.2) drop-shadow(0 0 6px #0ff); transform: skewX(2deg) scale(1.05); }
    12%  { opacity: 1; filter: none; transform: none; }
    48.9% { opacity: 1; }
    49%  { opacity: 0; }
    100% { opacity: 0; }
}
</style>

            <!-- Right Icons -->
            <div class="flex items-center space-x-2 md:space-x-4 lg:space-x-6">
                <!-- Wishlist button (temporarily hidden via CSS - preserved for future use) -->
                <button id="wishlist-button" class="text-gray-600 hover:text-gray-800 rounded-md p-1 md:p-2" aria-label="Wishlist">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                </button>
                <a href="view_cart.php" class="text-gray-600 hover:text-gray-800 rounded-md p-1 md:p-2 relative" aria-label="Cart">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                      <circle cx="9" cy="20" r="1.5"/>
                      <circle cx="18" cy="20" r="1.5"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 12.29a2 2 0 0 0 2 1.71h7.2a2 2 0 0 0 2-1.71L21 7H6"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center cart-count">
                        <?php 
                        $cartItems = getCartItems();
                        $totalItems = array_sum(array_column($cartItems, 'quantity'));
                        echo $totalItems;
                        ?>
                    </span>
                </a>
                <button id="search-button" class="text-gray-600 hover:text-gray-800 rounded-md p-1 md:p-2 relative group" aria-label="Search">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                    <span class="absolute -top-1 -right-1 bg-gray-200 text-gray-600 text-xs px-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200">⌘K</span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu (Off-canvas) -->
        <div id="mobile-menu" class="fixed top-0 left-0 w-full h-full bg-white z-50 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden overflow-y-auto">
            <div class="flex justify-end p-4 border-b border-gray-200">
                <button id="close-menu-button" class="text-gray-800 hover:text-black focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="px-6 py-4">
                <!-- Quick Links -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-black mb-4">QUICK LINKS</h3>
                    <ul class="space-y-3">
                        <li><a href="index.php" class="text-gray-700 hover:text-black transition-colors duration-200 block py-2">HOME</a></li>
                        <li><a href="shop.php" class="text-gray-700 hover:text-black transition-colors duration-200 block py-2">ALL PRODUCTS</a></li>
                        <li><a href="collections.php" class="text-gray-700 hover:text-black transition-colors duration-200 block py-2">COLLECTIONS</a></li>
                        <li><a href="#" class="text-gray-700 hover:text-black transition-colors duration-200 block py-2">FAQS</a></li>
                    </ul>
                </div>

                <!-- Categories Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-black mb-4">CATEGORIES</h3>
                    <div class="space-y-3">
                        <?php 
                        $categories = getCategories();
                        foreach ($categories as $category): 
                        ?>
                        <a href="shop.php?category=<?php echo $category['id']; ?>" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <img src="<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" class="w-12 h-12 object-cover rounded mr-4">
                            <div>
                                <p class="font-medium text-gray-900"><?php echo $category['name']; ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Collections Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-black mb-4">COLLECTIONS</h3>
                    <div class="space-y-3">
                        <?php 
                        $collections = getCollections();
                        foreach ($collections as $collection): 
                        ?>
                        <a href="shop.php?collection=<?php echo $collection['id']; ?>" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <img src="<?php echo $collection['image']; ?>" alt="<?php echo $collection['name']; ?>" class="w-12 h-12 object-cover rounded mr-4">
                            <div>
                                <p class="font-medium text-gray-900"><?php echo $collection['name']; ?></p>
                                <p class="text-sm text-gray-500"><?php echo $collection['description']; ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Best Sellers Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-black mb-4">BEST SELLERS</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <?php 
                        $bestsellerProducts = getProducts(['bestseller' => true, 'limit' => 4]);
                        foreach ($bestsellerProducts as $product): 
                            $productImages = getProductImagesForSlider($product['id']);
                            $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                        ?>
                        <a href="product-view.php?id=<?php echo $product['id']; ?>" class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <img src="<?php echo $primaryImage; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-32 object-cover rounded mb-2">
                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo $product['name']; ?></p>
                            <p class="text-sm text-gray-500"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Modal -->
        <div id="search-modal" class="fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 z-50 transform -translate-y-full transition-transform duration-300 ease-in-out">
            <div class="flex justify-center items-start pt-20 px-4">
                <div class="w-full max-w-2xl bg-white rounded-lg shadow-2xl">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900">Search Products</h2>
                        <button id="close-search-button" class="text-gray-400 hover:text-black focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="p-6">
                        <form id="header-search-form" action="shop.php" method="GET" class="space-y-4">
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       id="header-search-input" 
                                       placeholder="Search for products, categories, or collections..." 
                                       class="w-full pl-12 pr-4 py-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all text-lg"
                                       autocomplete="off">
                                <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            
                            <!-- Live Search Results -->
                            <div id="live-search-results" class="hidden max-h-96 overflow-y-auto border border-gray-200 rounded-lg bg-white">
                                <!-- Results will be populated here -->
                            </div>
                        </form>
                        
                        <!-- Popular Searches -->
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Popular Searches</h3>
                            <div class="flex flex-wrap gap-2">
                                <button class="quick-search-tag px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors" data-search="shirt">Shirts</button>
                                <button class="quick-search-tag px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors" data-search="pants">Pants</button>
                                <button class="quick-search-tag px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors" data-search="jacket">Jackets</button>
                                <button class="quick-search-tag px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors" data-search="dress">Dresses</button>
                                <button class="quick-search-tag px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors" data-search="sale">Sale</button>
                            </div>
                        </div>
                        
                        <!-- Recent Searches (if any) -->
                        <div id="recent-searches" class="mt-6 hidden">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Recent Searches</h3>
                            <div id="recent-searches-list" class="space-y-2">
                                <!-- Recent searches will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Header JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search modal functionality
        const searchButton = document.getElementById('search-button');
        const searchModal = document.getElementById('search-modal');
        const closeSearchButton = document.getElementById('close-search-button');
        const headerSearchInput = document.getElementById('header-search-input');
        const headerSearchForm = document.getElementById('header-search-form');
        const quickSearchTags = document.querySelectorAll('.quick-search-tag');
        const liveSearchResults = document.getElementById('live-search-results');
        let searchTimeout;
        
        // Open search modal
        if (searchButton && searchModal) {
            searchButton.addEventListener('click', function() {
                searchModal.classList.remove('-translate-y-full');
                searchModal.classList.add('translate-y-0');
                document.body.style.overflow = 'hidden';
                
                // Focus on search input
                setTimeout(() => {
                    if (headerSearchInput) {
                        headerSearchInput.focus();
                    }
                }, 300);
            });
        }
        
        // Close search modal
        function closeSearchModal() {
            if (searchModal) {
                searchModal.classList.remove('translate-y-0');
                searchModal.classList.add('-translate-y-full');
                document.body.style.overflow = '';
                
                // Clear search input and hide results
                if (headerSearchInput) {
                    headerSearchInput.value = '';
                }
                if (liveSearchResults) {
                    liveSearchResults.classList.add('hidden');
                }
            }
        }
        
        if (closeSearchButton) {
            closeSearchButton.addEventListener('click', closeSearchModal);
        }
        
        // Close modal on overlay click
        if (searchModal) {
            searchModal.addEventListener('click', function(e) {
                if (e.target === searchModal) {
                    closeSearchModal();
                }
            });
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchModal && !searchModal.classList.contains('-translate-y-full')) {
                closeSearchModal();
            }
            
            // Open search modal with Ctrl+K or Cmd+K
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (searchButton) {
                    searchButton.click();
                }
            }
        });
        
        // Live search functionality
        if (headerSearchInput) {
            headerSearchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Hide results if query is empty
                if (query === '') {
                    liveSearchResults.classList.add('hidden');
                    return;
                }
                
                // Debounce search requests
                searchTimeout = setTimeout(() => {
                    performLiveSearch(query);
                }, 300);
            });
            
            // Show results when input is focused and has content
            headerSearchInput.addEventListener('focus', function() {
                const query = this.value.trim();
                if (query !== '') {
                    performLiveSearch(query);
                }
            });
        }
        
        // Perform live search
        function performLiveSearch(query) {
            if (query.length < 2) return;
            
            const formData = new FormData();
            formData.append('action', 'live_search');
            formData.append('query', query);
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    liveSearchResults.innerHTML = data.html;
                    liveSearchResults.classList.remove('hidden');
                    
                    // Attach click handlers to search results
                    attachSearchResultHandlers();
                }
            })
            .catch(error => {
                console.error('Live search error:', error);
            });
        }
        
        // Attach click handlers to search result items
        function attachSearchResultHandlers() {
            const searchResultItems = document.querySelectorAll('.search-result-item');
            searchResultItems.forEach(item => {
                item.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    if (productId) {
                        // Close modal and navigate to product
                        closeSearchModal();
                        window.location.href = `product-view.php?id=${productId}`;
                    }
                });
            });
        }
        
        // Quick search tags
        quickSearchTags.forEach(tag => {
            tag.addEventListener('click', function() {
                const searchTerm = this.dataset.search;
                if (headerSearchInput) {
                    headerSearchInput.value = searchTerm;
                    // Trigger live search
                    performLiveSearch(searchTerm);
                }
            });
        });
        
        // Form submission
        if (headerSearchForm) {
            headerSearchForm.addEventListener('submit', function(e) {
                const searchValue = headerSearchInput.value.trim();
                if (searchValue === '') {
                    e.preventDefault();
                    return false;
                }
                
                // Save to recent searches
                saveRecentSearch(searchValue);
                
                // Hide live search results
                liveSearchResults.classList.add('hidden');
                
                // Close modal and submit form
                closeSearchModal();
            });
        }
        
        // Quick search button
        const quickSearchBtn = document.getElementById('quick-search-btn');
        if (quickSearchBtn) {
            quickSearchBtn.addEventListener('click', function() {
                const searchValue = headerSearchInput.value.trim();
                if (searchValue !== '') {
                    // Save to recent searches
                    saveRecentSearch(searchValue);
                    
                    // Redirect to shop page with search
                    window.location.href = `shop.php?search=${encodeURIComponent(searchValue)}`;
                }
            });
        }
        
        // Recent searches functionality
        function saveRecentSearch(searchTerm) {
            let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            
            // Remove if already exists
            recentSearches = recentSearches.filter(term => term !== searchTerm);
            
            // Add to beginning
            recentSearches.unshift(searchTerm);
            
            // Keep only last 5 searches
            recentSearches = recentSearches.slice(0, 5);
            
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
        }
        
        function loadRecentSearches() {
            const recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            const recentSearchesContainer = document.getElementById('recent-searches');
            const recentSearchesList = document.getElementById('recent-searches-list');
            
            if (recentSearches.length > 0 && recentSearchesContainer && recentSearchesList) {
                recentSearchesContainer.classList.remove('hidden');
                recentSearchesList.innerHTML = '';
                
                recentSearches.forEach(searchTerm => {
                    const searchItem = document.createElement('div');
                    searchItem.className = 'flex items-center justify-between p-2 hover:bg-gray-50 rounded cursor-pointer';
                    searchItem.innerHTML = `
                        <span class="text-sm text-gray-700">${searchTerm}</span>
                        <button class="text-gray-400 hover:text-gray-600" onclick="removeRecentSearch('${searchTerm}')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;
                    
                    searchItem.addEventListener('click', function() {
                        if (headerSearchInput) {
                            headerSearchInput.value = searchTerm;
                        }
                    });
                    
                    recentSearchesList.appendChild(searchItem);
                });
            }
        }
        
        // Load recent searches when modal opens
        if (searchButton) {
            searchButton.addEventListener('click', loadRecentSearches);
        }
        
        // Remove recent search function (global)
        window.removeRecentSearch = function(searchTerm) {
            let recentSearches = JSON.parse(localStorage.getItem('recentSearches') || '[]');
            recentSearches = recentSearches.filter(term => term !== searchTerm);
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches));
            loadRecentSearches();
        };
        
        // Mobile menu functionality
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const closeMenuButton = document.getElementById('close-menu-button');
        
        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.remove('-translate-x-full');
                mobileMenu.classList.add('translate-x-0');
                document.body.style.overflow = 'hidden';
            });
        }
        
        if (closeMenuButton && mobileMenu) {
            closeMenuButton.addEventListener('click', function() {
                mobileMenu.classList.remove('translate-x-0');
                mobileMenu.classList.add('-translate-x-full');
                document.body.style.overflow = '';
            });
        }
        
        // Close mobile menu on overlay click
        if (mobileMenu) {
            mobileMenu.addEventListener('click', function(e) {
                if (e.target === mobileMenu) {
                    mobileMenu.classList.remove('translate-x-0');
                    mobileMenu.classList.add('-translate-x-full');
                    document.body.style.overflow = '';
                }
            });
        }
    });
    </script> 