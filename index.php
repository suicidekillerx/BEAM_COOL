 <?php
require_once 'includes/functions.php';
?>
<?php require_once 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="bg-hero h-[70vh] md:h-[80vh] flex flex-col justify-between text-center relative overflow-hidden" style="background-image: url('<?php echo getSiteSetting('hero_image', 'images/hero.webp'); ?>'); background-size: cover; background-position: center;">

        <div class="relative z-10 text-white flex-1 flex items-center justify-center">
           
        </div>
        <div class="relative z-10 text-white pb-8 md:pb-12">
            <button onclick="window.location.href='shop.php'" class="bg-transparent border-2 border-white text-white px-6 md:px-8 py-2 md:py-3 text-sm md:text-lg font-semibold hover:bg-white hover:text-black transition-all duration-300 shadow-lg">
                SHOP NOW
            </button>
        </div>
    </section>

    <!-- Category Showcase Section -->
    <section class="h-[50vh] bg-white overflow-hidden">
        <div class="category-carousel h-full">
            <?php 
            $categories = getCategories();
            foreach ($categories as $category): 
            ?>
            <a href="shop.php?category=<?php echo $category['id']; ?>" class="block relative group overflow-hidden category-slide cursor-pointer">
                <img src="<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-300">
                <div class="absolute inset-0 flex items-end p-3 md:p-4">
                    <h3 class="text-white text-sm md:text-lg lg:text-xl font-semibold drop-shadow-lg"><?php echo $category['name']; ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Category Filter/Navigation -->
    <section class="bg-white py-4 md:py-6 px-4 md:px-8 lg:px-12 border-t border-gray-200">
        <div class="flex flex-wrap justify-center sm:justify-start gap-x-3 md:gap-x-6 gap-y-2 overflow-x-auto no-scrollbar">
            <a href="shop.php" class="text-xs md:text-sm font-medium text-gray-700 hover:text-black whitespace-nowrap rounded-md p-1 md:p-2">VIEW ALL</a>
            <?php foreach ($categories as $index => $category): ?>
                <a href="shop.php?category=<?php echo $category['id']; ?>" class="text-xs md:text-sm font-medium text-gray-700 hover:text-black whitespace-nowrap rounded-md p-1 md:p-2 <?php echo $index >= 4 ? 'hidden sm:block' : ''; ?> <?php echo $index >= 6 ? 'hidden md:block' : ''; ?> <?php echo $index >= 8 ? 'hidden lg:block' : ''; ?> <?php echo $index >= 10 ? 'hidden xl:block' : ''; ?>"><?php echo $category['name']; ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Product Grid Section -->
    <section class="bg-gray-100">
        <!-- Last Drop Title -->
        <div class="bg-gray-100 py-6 px-4 md:px-8 lg:px-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center">LAST DROP</h2>
        </div>
        
        <div class="h-[50vh] overflow-hidden w-full">
            <div class="flex product-carousel transition-transform duration-700 h-full">
            <?php 
            $featuredProducts = getProducts(['featured' => true, 'limit' => 6]);
            foreach ($featuredProducts as $product): 
                $productImages = getProductImagesForSlider($product['id']);
                $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                $secondaryImage = $productImages[1]['image_path'] ?? $primaryImage;
            ?>
            <a href="product-view.php?id=<?php echo $product['id']; ?>" class="block bg-white relative group product-card product-slide cursor-pointer">
                <div class="relative h-full">
                    <?php if ($product['is_on_sale']): ?>
                    <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-1 z-10">SALE</span>
                    <?php endif; ?>
                    <button class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition-colors duration-200 z-10 wishlist-btn" data-product-id="<?php echo $product['id']; ?>" onclick="event.stopPropagation();">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>
                    <img src="<?php echo $primaryImage; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0">
                    <img src="<?php echo $secondaryImage; ?>" alt="<?php echo $product['name']; ?> Hover" class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300">
                        <div class="w-full p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <h4 class="text-sm font-bold text-white mb-1 uppercase"><?php echo $product['name']; ?></h4>
                            <div class="flex justify-between items-center mb-3">
                                <div class="flex items-center space-x-2">
                                    <span class="text-red-400 font-bold text-sm"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></span>
                                    <?php if ($product['sale_price']): ?>
                                    <span class="text-gray-300 text-sm line-through"><?php echo formatPrice($product['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex space-x-1 text-xs text-gray-300">
                                <?php 
                                $sizes = getProductSizes($product['id']);
                                foreach ($sizes as $size): 
                                    $stockClass = $size['stock_quantity'] > 0 ? '' : 'line-through';
                                ?>
                                <span class="<?php echo $stockClass; ?>"><?php echo $size['size']; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Collections Section -->
    <section class="bg-gray-50">
        <!-- Collections Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-0 max-w-none collections-grid" id="collections-container">
            <?php 
            $collections = getCollections();
            $totalCollections = count($collections);
            $maxDisplayed = 4;
            
            // If we have 4 or fewer collections, show all normally
            if ($totalCollections <= $maxDisplayed) {
            foreach ($collections as $collection): 
            ?>
            <a href="shop.php?collection=<?php echo $collection['id']; ?>" class="block relative group overflow-hidden collection-item cursor-pointer">
                <img src="<?php echo $collection['image']; ?>" alt="<?php echo $collection['name']; ?>" class="w-full h-[70vh] object-cover transform group-hover:scale-110 transition-all duration-500">
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300">
                    <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                        <h3 class="text-lg font-bold mb-1"><?php echo $collection['name']; ?></h3>
                        <p class="text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-300"><?php echo $collection['description']; ?></p>
                    </div>
                </div>
            </a>
                <?php endforeach; 
            } else {
                // Show first 4 collections initially
                for ($i = 0; $i < $maxDisplayed; $i++): 
                    $collection = $collections[$i];
                ?>
                <a href="shop.php?collection=<?php echo $collection['id']; ?>" class="block relative group overflow-hidden collection-item cursor-pointer collection-slide" data-collection-id="<?php echo $collection['id']; ?>">
                    <img src="<?php echo $collection['image']; ?>" alt="<?php echo $collection['name']; ?>" class="w-full h-[70vh] object-cover transform group-hover:scale-110 transition-all duration-500">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300">
                        <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                            <h3 class="text-lg font-bold mb-1"><?php echo $collection['name']; ?></h3>
                            <p class="text-xs opacity-0 group-hover:opacity-100 transition-opacity duration-300"><?php echo $collection['description']; ?></p>
                        </div>
                    </div>
                </a>
                <?php endfor; 
            }
            ?>
        </div>
        
        <?php if ($totalCollections > $maxDisplayed): ?>
        <!-- Hidden collections data for JavaScript -->
        <div id="collections-data" style="display: none;">
            <?php foreach ($collections as $collection): ?>
            <div class="collection-data" 
                 data-id="<?php echo $collection['id']; ?>"
                 data-name="<?php echo htmlspecialchars($collection['name']); ?>"
                 data-description="<?php echo htmlspecialchars($collection['description']); ?>"
                 data-image="<?php echo $collection['image']; ?>"
                 data-url="shop.php?collection=<?php echo $collection['id']; ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
<!-- Collections Filter/Navigation -->
<section class="bg-white py-4 md:py-6 px-4 md:px-8 lg:px-12 border-t border-gray-200">
    <div class="flex flex-wrap justify-center sm:justify-start gap-x-3 md:gap-x-6 gap-y-2 overflow-x-auto no-scrollbar">
        <a href="shop.php" class="text-xs md:text-sm font-medium text-gray-700 hover:text-black whitespace-nowrap rounded-md p-1 md:p-2">VIEW ALL</a>
        <?php 
        $collections = getCollections();
        foreach ($collections as $index => $collection): 
        ?>
            <a href="shop.php?collection=<?php echo $collection['id']; ?>" class="text-xs md:text-sm font-medium text-gray-700 hover:text-black whitespace-nowrap rounded-md p-1 md:p-2 <?php echo $index >= 4 ? 'hidden sm:block' : ''; ?> <?php echo $index >= 6 ? 'hidden md:block' : ''; ?> <?php echo $index >= 8 ? 'hidden lg:block' : ''; ?> <?php echo $index >= 10 ? 'hidden xl:block' : ''; ?>"><?php echo $collection['name']; ?></a>
        <?php endforeach; ?>
    </div>
</section>
    <!-- Best Seller Section -->
    <section class="bg-white">
        <!-- Best Seller Title -->
        <div class="bg-gray-100 py-6 px-4 md:px-8 lg:px-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center">BEST SELLER</h2>
        </div>
        <div class="h-[50vh] overflow-hidden w-full">
            <div class="flex bestseller-carousel transition-transform duration-700 h-full">
                <?php 
                $bestsellerProducts = getProducts(['bestseller' => true, 'limit' => 6]);
                foreach ($bestsellerProducts as $product): 
                    $productImages = getProductImagesForSlider($product['id']);
                    $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                    $secondaryImage = $productImages[1]['image_path'] ?? $primaryImage;
                ?>
                <a href="product-view.php?id=<?php echo $product['id']; ?>" class="block bg-white relative group product-card bestseller-slide cursor-pointer">
                    <div class="relative h-full">
                        <span class="absolute top-2 left-2 bg-green-600 text-white text-xs font-bold px-2 py-1 z-10">BEST</span>
                        <button class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition-colors duration-200 z-10 wishlist-btn" data-product-id="<?php echo $product['id']; ?>" onclick="event.stopPropagation();">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                        <img src="<?php echo $primaryImage; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0">
                        <img src="<?php echo $secondaryImage; ?>" alt="<?php echo $product['name']; ?> Hover" class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300">
                            <div class="w-full p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <h4 class="text-sm font-bold text-white mb-1 uppercase"><?php echo $product['name']; ?></h4>
                                <div class="flex justify-between items-center mb-3">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-green-400 font-bold text-sm"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></span>
                                        <?php if ($product['sale_price']): ?>
                                        <span class="text-gray-300 text-sm line-through"><?php echo formatPrice($product['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex space-x-1 text-xs text-gray-300">
                                    <?php 
                                    $sizes = getProductSizes($product['id']);
                                    foreach ($sizes as $size): 
                                        $stockClass = $size['stock_quantity'] > 0 ? '' : 'line-through';
                                    ?>
                                    <span class="<?php echo $stockClass; ?>"><?php echo $size['size']; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section class="relative bg-black text-white py-16 px-4 md:px-8 lg:px-12 overflow-hidden h-[70vh]">
        <?php 
        $videoSection = getVideoSection();
        if ($videoSection): 
        ?>
        <!-- Background Video -->
        <video class="absolute inset-0 w-full h-full object-cover opacity-50" autoplay muted loop playsinline>
            <source src="<?php echo $videoSection['video_path']; ?>" type="video/mp4">
        </video>
        
        <!-- Content Overlay -->
        <div class="relative z-10 max-w-6xl mx-auto text-center h-full flex flex-col justify-center items-center" style="
    background-color: #00000052;
">
            <!-- Slug -->
            <div class="mb-8">
                <h3 class="text-lg md:text-xl font-medium text-gray-400 tracking-wider"><?php echo $videoSection['slug_text']; ?></h3>
            </div>
            
            <!-- Join Community Button -->
            <div class="mb-8">
                <a href="<?php echo $videoSection['button_link']; ?>" class="bg-transparent border-2 border-white text-white px-8 md:px-12 py-3 md:py-4 text-lg md:text-xl font-semibold hover:bg-white hover:text-black transition-all duration-300">
                    <?php echo $videoSection['button_text']; ?>
                </a>
            </div>
            
            <!-- Description -->
            <div class="max-w-2xl mx-auto">
                <p class="text-gray-300 text-sm md:text-base leading-relaxed">
                    <?php echo $videoSection['description']; ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </section>

<?php require_once 'includes/footer.php'; ?>