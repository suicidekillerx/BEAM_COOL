<?php
// Force no caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Load core functionality first (session and functions)
require_once 'includes/functions.php';

require_once 'includes/header.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Fetch product data
$product = getProduct($productId);
$productImages = getProductImages($productId);
$productSizes = getProductSizes($productId);

// If product not found, redirect to shop
if (!$product) {
    header('Location: shop.php');
    exit;
}

// Include header after functions are loaded

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($product['name']); ?> - <?php echo getSiteSetting('brand_name', 'BeamTheTeam'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <style>
        /* Mobile-specific styles */
        @media (max-width: 767px) {
            /* Larger tap targets */
            .size-btn {
                padding: 12px 8px;
                min-height: 60px;
            }
            
            /* Better spacing for mobile */
            .product-details-container > * {
                margin-bottom: 1.5rem;
            }
            
            /* Full-width images with consistent height */
            .gallery-image img {
                height: 300px;
                width: 100%;
                object-fit: cover;
            }
            
            /* Larger buttons for mobile */
            .add-to-cart-btn, .add-to-wishlist-btn {
                padding: 16px;
                font-size: 1rem;
            }
            
            /* More compact product info grid */
            .product-info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            /* Adjust breadcrumb to prevent overflow */
            .breadcrumb {
                white-space: nowrap;
                overflow-x: auto;
                padding-bottom: 8px;
            }
            
            /* Hide scrollbar but keep functionality */
            .breadcrumb::-webkit-scrollbar {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-white text-gray-900">

    <main class="container mx-auto mt-4 md:mt-8 px-4">
        
        <!-- Breadcrumb Navigation - Improved for mobile -->
        <nav class="mb-4 md:mb-6 text-sm text-gray-600 breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><a href="index.php" class="hover:text-black">Home</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="shop.php" class="hover:text-black">Shop</a></li>
                <?php if ($product['category_name']): ?>
                <li><span class="mx-2">/</span></li>
                <li><a href="shop.php?category=<?php echo $product['category_id']; ?>" class="hover:text-black"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li><span class="mx-2">/</span></li>
                <li class="text-black font-medium"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12">
            <!-- Left Column: Product Images - Mobile optimized -->
            <div class="space-y-4">
                <!-- Product Badges - Stacked on mobile -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <?php if ($product['is_featured']): ?>
                    <span class="bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded">FEATURED</span>
                    <?php endif; ?>
                    <?php if ($product['is_bestseller']): ?>
                    <span class="bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded">BEST SELLER</span>
                    <?php endif; ?>
                    <?php if ($product['is_on_sale']): ?>
                    <span class="bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded">SALE</span>
                    <?php endif; ?>
                                    </div>
                                    
                <!-- Image Gallery - Mobile optimized -->
                <div class="product-image-gallery">
                    <?php if (!empty($productImages)): ?>
                        <!-- Mobile Layout (Single Column) -->
                        <div class="md:hidden space-y-3">
                            <?php foreach ($productImages as $image): ?>
                            <a href="<?php echo htmlspecialchars($image['image_path']); ?>" 
                               class="gallery-image block overflow-hidden rounded-lg shadow-sm">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-full h-auto object-cover hover:scale-105 transition-transform duration-300">
                            </a>
                            <?php endforeach; ?>
                                    </div>
                                    
                                                 <!-- Desktop Layout (Artistic Grid) -->
                         <div class="hidden md:grid grid-cols-3 gap-3 h-full">
                             <?php 
                             $imageCount = count($productImages);
                             foreach ($productImages as $index => $image): 
                                 // Create artistic layout based on image count and position
                                 $imageClasses = '';
                                 if ($imageCount >= 4) {
                                     if ($index === 0) {
                                         // First image - large, spans 2 rows and 2 columns
                                         $imageClasses = 'col-span-2 row-span-2';
                                     } elseif ($index === 1) {
                                         // Second image - medium, spans 1 row and 1 column
                                         $imageClasses = 'col-span-1 row-span-1';
                                     } elseif ($index === 2) {
                                         // Third image - medium, spans 1 row and 1 column
                                         $imageClasses = 'col-span-1 row-span-1';
                                     } elseif ($index === 3) {
                                         // Fourth image - small, spans 1 row and 1 column
                                         $imageClasses = 'col-span-1 row-span-1';
                                     } else {
                                         // Additional images - small
                                         $imageClasses = 'col-span-1 row-span-1';
                                     }
                                 } elseif ($imageCount === 3) {
                                     if ($index === 0) {
                                         // First image - large, spans 2 rows and 2 columns
                                         $imageClasses = 'col-span-2 row-span-2';
                                     } else {
                                         // Other images - medium
                                         $imageClasses = 'col-span-1 row-span-1';
                                     }
                                 } elseif ($imageCount === 2) {
                                     if ($index === 0) {
                                         // First image - large, spans 2 rows and 2 columns
                                         $imageClasses = 'col-span-2 row-span-2';
                                     } else {
                                         // Second image - medium, spans 2 rows and 1 column
                                         $imageClasses = 'col-span-1 row-span-2';
                                     }
                                 } else {
                                     // Single image - full size
                                     $imageClasses = 'col-span-3 row-span-2';
                                 }
                             ?>
                             <a href="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                class="gallery-image block overflow-hidden <?php echo $imageClasses; ?>">
                                 <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                      alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                      class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                             </a>
                             <?php endforeach; ?>
                                    </div>
                    <?php else: ?>
                        <div class="bg-gray-200 flex items-center justify-center h-64 rounded-lg">
                            <p class="text-gray-500">No images available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Product Details - Mobile optimized -->
            <div class="space-y-6 product-details-container">
                <!-- Product Title -->
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <?php if ($product['short_description']): ?>
                    <p class="text-gray-600 text-base md:text-lg"><?php echo htmlspecialchars($product['short_description']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Price Section -->
                <div class="flex items-center space-x-4">
                    <span class="text-2xl md:text-3xl font-bold text-red-600">
                        <?php echo formatPrice($product['sale_price'] ?? $product['price']); ?>
                    </span>
                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                    <span class="text-lg md:text-xl text-gray-400 line-through">
                        <?php echo formatPrice($product['price']); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($product['is_on_sale']): ?>
                    <span class="bg-red-600 text-white text-xs font-semibold px-3 py-1 rounded-full">SALE</span>
                    <?php endif; ?>
                </div>

                <!-- Product Info Grid - Stacked on mobile -->
                <!-- Additional Info - Compact on mobile -->
                <div class="bg-gray-50 rounded-lg p-4 md:p-6">
                    <h4 class="font-semibold mb-2 md:mb-3 text-base md:text-lg">Product Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">SKU:</span>
                            <span class="ml-2 font-medium"><?php echo strtoupper($product['slug']); ?></span>
                        </div>
                        <?php if (!empty($product['color'])): ?>
                        <div>
                            <span class="text-gray-500">Color:</span>
                            <span class="ml-2 font-medium"><?php echo htmlspecialchars($product['color']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($product['category_name'])): ?>
                        <div>
                            <span class="text-gray-500">Category:</span>
                            <span class="ml-2 font-medium"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($product['collection_name'])): ?>
                        <div>
                            <span class="text-gray-500">Collection:</span>
                            <span class="ml-2 font-medium"><?php echo htmlspecialchars($product['collection_name']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Size Selection - Larger buttons on mobile -->
                <div>
                    <h3 class="text-lg font-semibold mb-3 md:mb-4">SELECT SIZE</h3>
                    <div class="grid grid-cols-3 md:grid-cols-4 gap-2 md:gap-3">
                        <?php if (!empty($productSizes)): ?>
                            <?php foreach ($productSizes as $size): ?>
                            <button class="size-btn px-3 md:px-4 py-3 border-2 border-gray-300 rounded-lg text-center font-medium transition-all duration-200 hover:border-black <?php echo $size['stock_quantity'] <= 0 ? 'text-gray-300 cursor-not-allowed bg-gray-100' : 'hover:bg-black hover:text-white'; ?>" 
                                    <?php echo $size['stock_quantity'] <= 0 ? 'disabled' : ''; ?> data-size="<?php echo htmlspecialchars($size['size']); ?>">
                                <div class="text-sm md:text-base"><?php echo htmlspecialchars($size['size']); ?></div>
                                <?php if ($product['show_stock']): ?>
                                <div class="text-xs mt-1 <?php echo $size['stock_quantity'] <= 0 ? 'text-red-500' : 'text-gray-500'; ?>">
                                    <?php echo $size['stock_quantity'] <= 0 ? 'Out of Stock' : $size['stock_quantity'] . ' left'; ?>
                                </div>
                                <?php endif; ?>
                            </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-gray-500 col-span-3 md:col-span-4">No sizes available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stock Status -->
                <?php 
                $totalStock = !empty($productSizes) ? array_sum(array_column($productSizes, 'stock_quantity')) : 0;

                
                if ($product['show_stock']): 
                    // Show exact stock numbers
                    if ($totalStock > 0): 
                ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 md:p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-green-800 font-medium text-sm md:text-base">In Stock - <?php echo $totalStock; ?> items available</span>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 md:p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="text-red-800 font-medium text-sm md:text-base">Out of Stock</span>
                    </div>
                </div>
                <?php 
                    endif;
                else: 
                    // Show admin-defined stock status or automatically determine based on actual stock
                    $statusClass = '';
                    $statusIcon = '';
                    $statusText = '';
                    
                    // If total stock is 0, automatically show "Out of Stock" regardless of admin setting
                    if ($totalStock <= 0) {
                        $statusClass = 'bg-red-50 border-red-200 text-red-800';
                        $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                        $statusText = 'Out of Stock';
                    } else {
                        // Use admin-defined status for non-zero stock
                        switch ($product['stock_status']) {
                            case 'in_stock':
                                $statusClass = 'bg-green-50 border-green-200 text-green-800';
                                $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                                $statusText = 'In Stock';
                                break;
                            case 'low_stock':
                                $statusClass = 'bg-yellow-50 border-yellow-200 text-yellow-800';
                                $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>';
                                $statusText = 'Low Stock';
                                break;
                            default:
                                $statusClass = 'bg-green-50 border-green-200 text-green-800';
                                $statusIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                                $statusText = 'In Stock';
                        }
                    }
                ?>
                <div class="<?php echo $statusClass; ?> border rounded-lg p-3 md:p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php echo $statusIcon; ?>
                        </svg>
                        <span class="font-medium text-sm md:text-base"><?php echo $statusText; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quantity Selector -->
                    <div class="flex items-center border-2 border-gray-300 rounded-lg w-32">
                        <button class="px-4 py-2 text-lg font-bold quantity-btn hover:bg-gray-100" data-action="decrease">-</button>
                        <div class="w-12 text-center border-l border-r font-semibold quantity-display" contenteditable="true">1</div>
                        <button class="px-4 py-2 text-lg font-bold quantity-btn hover:bg-gray-100" data-action="increase">+</button>
                </div>

                <!-- Action Buttons - Full width and larger on mobile -->
                <div class="space-y-3 md:space-y-4">
                    <button class="w-full bg-black text-white py-3 md:py-4 font-bold uppercase hover:bg-gray-800 transition-colors duration-200 add-to-cart-btn" 
                            data-product-id="<?php echo $product['id']; ?>">
                        <?php echo $totalStock <= 0 ? 'Out of Stock' : 'Add to Cart'; ?>
                    </button>
                    <button class="w-full border-2 border-gray-300 py-3 md:py-4 font-bold uppercase flex items-center justify-center space-x-2 hover:bg-gray-50 transition-colors duration-200 add-to-wishlist-btn <?php echo isInWishlist($product['id']) ? 'bg-red-50 border-red-300 text-red-600' : ''; ?>" 
                            data-product-id="<?php echo $product['id']; ?>">
                        <svg class="w-5 h-5 <?php echo isInWishlist($product['id']) ? 'fill-red-600' : ''; ?>" fill="<?php echo isInWishlist($product['id']) ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span><?php echo isInWishlist($product['id']) ? 'Remove from Wishlist' : 'Add to Wishlist'; ?></span>
                    </button>
                </div>

                <!-- Product Details - Accordion -->
                <div class="border-t pt-4 md:pt-6">
                    <div class="border-b border-gray-200">
                        <div class="flex justify-between items-center cursor-pointer accordion-header py-3">
                            <h4 class="font-semibold text-base md:text-lg">Product Details</h4>
                            <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div class="text-gray-600 pb-4 accordion-content hidden md:block">
                            <p class="leading-relaxed text-sm md:text-base"><?php echo htmlspecialchars($product['description'] ?? 'Product description not available.'); ?></p>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>

        <!-- Related Products Section - 2 columns on mobile -->
        <section class="mt-12 md:mt-16 border-t border-gray-200 pt-8 md:pt-12">
            <div class="mb-6 md:mb-8">
                <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-1 md:mb-2">Related Products</h2>
                <p class="text-gray-600 text-sm md:text-base">You might also like these products</p>
            </div>

                         <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                 <?php 
                 // Get related products from same category or collection
                 $relatedProducts = getRelatedProducts($product['id'], $product['category_id'], $product['collection_id'], 4);
                 
                 if (!empty($relatedProducts)): 
                     foreach ($relatedProducts as $relatedProduct): 
                         $relatedProductImages = getProductImagesForSlider($relatedProduct['id']);
                         $primaryImage = $relatedProductImages[0]['image_path'] ?? 'images/placeholder.jpg';
                         $secondaryImage = $relatedProductImages[1]['image_path'] ?? $primaryImage;
                 ?>
                 <div class="group cursor-pointer">
                     <a href="product-view.php?id=<?php echo $relatedProduct['id']; ?>" class="block">
                         <div class="relative overflow-hidden bg-white">
                             <?php if ($relatedProduct['is_on_sale']): ?>
                             <span class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-1 z-10">SALE</span>
                             <?php endif; ?>
                             <?php if ($relatedProduct['is_featured']): ?>
                             <span class="absolute top-2 right-2 bg-blue-600 text-white text-xs font-bold px-2 py-1 z-10">FEATURED</span>
                             <?php endif; ?>
                             <?php if ($relatedProduct['is_bestseller']): ?>
                             <span class="absolute top-2 right-2 bg-green-600 text-white text-xs font-bold px-2 py-1 z-10">BEST</span>
                             <?php endif; ?>
                             
                             <div class="relative aspect-square">
                                 <img src="<?php echo $primaryImage; ?>" 
                                      alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" 
                                      class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0">
                                 <img src="<?php echo $secondaryImage; ?>" 
                                      alt="<?php echo htmlspecialchars($relatedProduct['name']); ?> Hover" 
                                      class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>

                             <div class="p-4">
                                 <h3 class="font-semibold text-gray-900 mb-1 text-sm"><?php echo htmlspecialchars($relatedProduct['name']); ?></h3>
                                 <div class="flex items-center justify-between">
                                     <div class="flex items-center space-x-2">
                                         <span class="text-red-600 font-bold text-sm"><?php echo formatPrice($relatedProduct['sale_price'] ?? $relatedProduct['price']); ?></span>
                                         <?php if ($relatedProduct['sale_price'] && $relatedProduct['sale_price'] < $relatedProduct['price']): ?>
                                         <span class="text-gray-400 text-xs line-through"><?php echo formatPrice($relatedProduct['price']); ?></span>
                                         <?php endif; ?>
            </div>
                                     <span class="text-xs text-gray-500"><?php echo htmlspecialchars($relatedProduct['color'] ?? ''); ?></span>
                </div>
            </div>
        </div>
                     </a>
                 </div>
                 <?php 
                     endforeach; 
                 else: 
                 ?>
                 <div class="col-span-full text-center py-8">
                     <p class="text-gray-500">No related products found</p>
        </div>
                 <?php endif; ?>
    </div>
        </section>
    </main>

    <!-- Spacing before footer -->
    <div class="h-12 md:h-16"></div>

    <?php require_once 'includes/footer.php'; ?>

    <!-- Smooth Add to Cart System -->
    <script>
    $(document).ready(function() {
        // Initialize Magnific Popup for image gallery with navigation
        $('.gallery-image').magnificPopup({
            type: 'image',
            gallery: {
                enabled: true,
                navigateByImgClick: true,
                preload: [1, 2]
            },
            image: {
                titleSrc: function(item) {
                    return item.el.attr('alt');
                }
            },
            callbacks: {
                elementParse: function(item) {
                    item.src = item.el.attr('href');
                }
            }
        });
        
        // Accordion functionality - auto-close on mobile
        if (window.innerWidth < 768) {
            $('.accordion-content').hide();
        }
        
        $('.accordion-header').click(function() {
            var content = $(this).siblings('.accordion-content');
            var icon = $(this).find('svg');
            
            content.slideToggle(200);
            icon.toggleClass('rotate-180');
        });
        
        // Global variables
        let isAddingToCart = false;
        let selectedSize = null;
        let quantity = 1;
        
        // Size selection with smooth feedback
        $('.size-btn').click(function() {
            $('.size-btn').removeClass('bg-black text-white').addClass('bg-gray-100 text-gray-900');
            $(this).removeClass('bg-gray-100 text-gray-900').addClass('bg-black text-white');
            selectedSize = $(this).data('size');
            
            // Enable add to cart button
            $('.add-to-cart-btn').prop('disabled', false).removeClass('opacity-50');
            
            // Add haptic feedback
            if ('vibrate' in navigator) {
                navigator.vibrate(20);
            }
        });
        
        // Quantity controls with smooth animations
        $('.quantity-btn').click(function() {
            const action = $(this).data('action');
            const quantityDisplay = $('.quantity-display');
            let currentQty = parseInt(quantityDisplay.text());
            
            if (action === 'decrease' && currentQty > 1) {
                currentQty--;
                quantity = currentQty;
                quantityDisplay.text(currentQty);
                
                // Smooth animation
                quantityDisplay.addClass('scale-110');
                setTimeout(() => quantityDisplay.removeClass('scale-110'), 150);
            } else if (action === 'increase' && currentQty < 10) {
                currentQty++;
                quantity = currentQty;
                quantityDisplay.text(currentQty);
                
                // Smooth animation
                quantityDisplay.addClass('scale-110');
                setTimeout(() => quantityDisplay.removeClass('scale-110'), 150);
            }
            
            // Add haptic feedback
            if ('vibrate' in navigator) {
                navigator.vibrate(15);
            }
        });
        
        // Direct quantity input
        $('.quantity-display').on('input', function() {
            let value = parseInt($(this).text()) || 1;
            value = Math.max(1, Math.min(10, value)); // Limit between 1-10
            $(this).text(value);
            quantity = value;
        });
        
        // Smooth Add to Cart with real-time updates
        $('.add-to-cart-btn').click(function(e) {
            e.preventDefault();
            
            if (isAddingToCart) return; // Prevent double clicks
            
            const button = $(this);
            const productId = button.data('product-id');
            const originalText = button.text();
            
            // Validation
            if (!selectedSize) {
                showNotification('Please select a size', 'error');
                return;
            }
            
            // Start loading state
            isAddingToCart = true;
            button.prop('disabled', true);
            button.html('<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Adding...');
            
            // Add haptic feedback
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
            
            // AJAX request to ajax_handler.php
            $.ajax({
                url: 'ajax_handler.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'add_to_cart',
                    product_id: productId,
                    size: selectedSize,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        // Success animation
                        button.removeClass('bg-black').addClass('bg-green-600');
                        button.html('<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Added!');
                        
                        // Update cart count in real-time
                        updateCartCount();
                        
                        // Show success notification
                        showNotification('Item added to cart successfully!', 'success');
                        
                        // Check if auto_open_cart is enabled and redirect to cart
                        if (response.auto_open_cart) {
                            // Redirect to cart page after a short delay
                            setTimeout(function() {
                                window.location.href = 'view_cart.php';
                            }, 1500);
                        }
                        
                        // Reset button after delay
                        setTimeout(function() {
                            button.removeClass('bg-green-600').addClass('bg-black');
                            button.text(originalText);
                            button.prop('disabled', false);
                            isAddingToCart = false;
                        }, 2000);
                        
                    } else {
                        // Error handling
                        button.removeClass('bg-black').addClass('bg-red-600');
                        button.text('Error');
                        showNotification(response.message || 'Failed to add item to cart', 'error');
                        
                        setTimeout(function() {
                            button.removeClass('bg-red-600').addClass('bg-black');
                            button.text(originalText);
                            button.prop('disabled', false);
                            isAddingToCart = false;
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    // Network error handling
                    button.removeClass('bg-black').addClass('bg-red-600');
                    button.text('Error');
                    showNotification('Network error. Please try again.', 'error');
                    
                    setTimeout(function() {
                        button.removeClass('bg-red-600').addClass('bg-black');
                        button.text(originalText);
                        button.prop('disabled', false);
                        isAddingToCart = false;
                    }, 2000);
                }
            });
        });
        
        // Real-time cart count update function
        function updateCartCount() {
            $.ajax({
                url: 'ajax_handler.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_cart_items'
                },
                success: function(response) {
                    if (response.success && response.items) {
                        const totalItems = response.items.reduce((sum, item) => sum + item.quantity, 0);
                        const cartCount = $('.cart-count');
                        
                        if (cartCount.length) {
                            // Smooth count animation
                            const currentCount = parseInt(cartCount.text()) || 0;
                            if (totalItems > currentCount) {
                                // Animate the increase
                                cartCount.addClass('scale-125 bg-green-500');
                                setTimeout(() => {
                                    cartCount.removeClass('scale-125 bg-green-500');
                                }, 300);
                            }
                            cartCount.text(totalItems);
                        }
                    }
                }
            });
        }
        
        // Smooth notification system (no alerts)
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            $('.notification').remove();
            
            const bgColor = type === 'success' ? 'bg-green-500' : 
                           type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            const notification = $(`
                <div class="notification fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300">
                    <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            ${type === 'success' ? '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>' :
                              type === 'error' ? '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>' :
                              '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>'}
                        </svg>
                        <span>${message}</span>
                        <button class="notification-close ml-2 hover:opacity-75">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `);
            
            $('body').append(notification);
            
            // Slide in animation
            setTimeout(() => {
                notification.removeClass('translate-x-full');
            }, 100);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                notification.addClass('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
            
            // Manual close
            notification.find('.notification-close').click(function() {
                notification.addClass('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            });
        }
        
        // Wishlist functionality (hidden but preserved)
        $('.add-to-wishlist-btn').click(function() {
            // Wishlist functionality is hidden via CSS
            // Code preserved for future use
        });
        
        // Handle window resize for accordion
        $(window).resize(function() {
            if (window.innerWidth >= 768) {
                $('.accordion-content').show();
            } else {
                $('.accordion-content').hide();
            }
        });
    });
    </script>

</body>
</html>
            } else {
                $('.accordion-content').hide();
            }
        });
    });
    </script>

</body>
</html>