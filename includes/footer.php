<?php
// Prevent footer from being included multiple times
if (defined('FOOTER_INCLUDED')) {
    return;
}
define('FOOTER_INCLUDED', true);
?>
    <!-- Footer -->
    <footer class="bg-black text-white py-12 px-4 md:px-8 lg:px-12">
        <div class="max-w-7xl mx-auto">
            <!-- Main Footer Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                <!-- Left Section - Brand & Social Media -->
                <div class="space-y-4">
                    <div class="mb-4">
                        <a href="index.php" id="brand-logo" class="text-lg md:text-xl lg:text-2xl font-bold tracking-wider text-white rounded-md p-1 md:p-2 glitch-text relative select-none flex items-center gap-2" style="display:inline-block;">
                            <span class="logo-glitch-stack">
                                <?php 
                                try {
                                    $brandLogo = getSiteSetting('brand_logo', 'images/logo.webp');
                                    $brandLogo2 = getSiteSetting('brand_logo2', 'images/logo2.png');
                                } catch (Exception $e) {
                                    error_log("Logo settings error: " . $e->getMessage());
                                    $brandLogo = 'images/logo.webp';
                                    $brandLogo2 = 'images/logo2.png';
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($brandLogo); ?>" alt="Logo 1" class="brand-logo-img w-16 h-16 md:w-20 md:h-20 mx-auto transition-transform duration-300 glitch-logo glitch-logo-1" style="vertical-align:middle; position:absolute; left:0; top:0;"/>
                                <img src="<?php echo htmlspecialchars($brandLogo2); ?>" alt="Logo 2" class="brand-logo-img w-16 h-16 md:w-20 md:h-20 mx-auto transition-transform duration-300 glitch-logo glitch-logo-2" style="vertical-align:middle; position:absolute; left:0; top:0;"/>
                            </span>
                        </a>
                    </div>
                    <div class="flex space-x-4">
                        <?php 
                        // Cache social media data to prevent multiple calls
                        static $socialMedia = null;
                        if ($socialMedia === null) {
                            try {
                                $socialMedia = getSocialMedia();
                            } catch (Exception $e) {
                                // Log error and set empty array to prevent fatal error
                                error_log("Social media data error: " . $e->getMessage());
                                $socialMedia = [];
                            }
                        }
                        
                        // Only display social media links if we have data
                        if (!empty($socialMedia)):
                            foreach ($socialMedia as $social): 
                        ?>
                        <a href="<?php echo htmlspecialchars($social['link_url']); ?>" class="text-white hover:text-gray-300 transition-colors duration-200" target="_blank" rel="noopener noreferrer">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <?php echo $social['icon_svg']; ?>
                            </svg>
                        </a>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </div>
                </div>

                <?php 
                // Cache footer data to prevent multiple calls
                static $footerData = null;
                if ($footerData === null) {
                    try {
                        $footerData = getFooterData();
                    } catch (Exception $e) {
                        // Log error and set empty array to prevent fatal error
                        error_log("Footer data error: " . $e->getMessage());
                        $footerData = [];
                    }
                }
                
                // Only display footer sections if we have data
                if (!empty($footerData)):
                    foreach ($footerData as $section): 
                ?>
                <div class="space-y-4">
                    <h4 class="text-lg font-bold text-white uppercase"><?php echo htmlspecialchars($section['section_title']); ?></h4>
                    <ul class="space-y-2">
                        <?php if (!empty($section['links'])): ?>
                            <?php foreach ($section['links'] as $link): ?>
                            <li><a href="<?php echo htmlspecialchars($link['link_url']); ?>" class="text-white hover:text-gray-300 transition-colors duration-200"><?php echo htmlspecialchars($link['link_text']); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php 
                    endforeach; 
                endif; 
                ?>

                <!-- Right Section - Newsletter Subscription -->
                <div class="space-y-4">
                    <h4 class="text-lg font-bold text-white uppercase">SUBSCRIBE TO OUR NEWSLETTER</h4>
                    <div class="space-y-3">
                        <input type="email" placeholder="E-mail" class="w-full px-4 py-2 bg-transparent border border-white text-white placeholder-gray-400 focus:outline-none focus:border-gray-300 transition-colors duration-200">
                        <button class="w-full px-4 py-2 bg-white text-black font-semibold uppercase hover:bg-gray-200 transition-colors duration-200">
                            SUBSCRIBE
                        </button>
                    </div>
                </div>
            </div>

            <!-- Copyright Section -->
            <div class="border-t border-gray-800 pt-8 text-center">
                <p class="text-gray-400 text-sm">
                    2025 BeamTheTeam, Powered by <a href="https://weult.com" target="_blank" rel="noopener noreferrer" class="text-white hover:text-gray-300 transition-colors duration-200">WeULT.com</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Overlay for side panels -->
    <div id="side-panel-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden"></div>

    <!-- Wishlist Side Panel (temporarily hidden via CSS - preserved for future use) -->
    <div id="wishlist-panel" class="fixed top-0 right-0 w-full md:w-1/3 h-full bg-white shadow-lg z-50 transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 class="text-lg font-bold">Wishlist</h2>
            <button id="close-wishlist-button" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-4">
            <p>Your wishlist is empty.</p>
            <!-- Wishlist items will be dynamically added here -->
        </div>
    </div>

    <!-- Cart Side Panel -->
    <div id="cart-panel" class="fixed top-0 right-0 w-full md:w-1/3 h-full bg-white shadow-lg z-50 transform translate-x-full transition-transform duration-300 ease-in-out">
        <div class="flex justify-between items-center p-4 border-b">
            <h2 class="text-lg font-bold">Shopping Cart</h2>
            <button id="close-cart-button" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-4">
            <p>Your cart is empty.</p>
            <!-- Cart items will be dynamically added here -->
        </div>
    </div>

    <!-- Search Overlay -->
    <div id="search-overlay" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="relative w-full max-w-2xl">
            <input type="search" id="search-input" placeholder="What are you looking for?" class="w-full bg-transparent text-white text-4xl font-bold placeholder-gray-400 border-b-2 border-gray-400 focus:border-white focus:outline-none py-4 px-2">
            <button id="close-search-button" class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 hover:text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
    <script src="script.js"></script>
    
    <!-- Cart Count Update Script -->
    <script>
    function updateCartCount() {
        fetch('ajax_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_cart_items'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.items) {
                const totalItems = data.items.reduce((sum, item) => sum + item.quantity, 0);
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = totalItems;
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
    }
    
    // Update cart count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCount();
    });
    </script>

<style>
    /* Footer-specific glitch animation to make logos white */
    footer .glitch-logo {
        position: absolute;
        left: 0; top: 0;
        width: 100%; height: 100%;
        opacity: 0;
        animation: cool-glitch-white 2.5s infinite linear;
    }

    footer .glitch-logo-1 {
        animation-delay: 0s;
    }
    footer .glitch-logo-2 {
        animation-delay: 1.25s;
    }

    @keyframes cool-glitch-white {
        0%, 15%, 24% {
            transform: translate(0);
            opacity: 1;
            filter: brightness(0) invert(1);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        }
        5% {
            transform: translate(-3px, 3px) skewX(8deg);
            filter: drop-shadow(0 0 4px #f0f) drop-shadow(0 0 6px #0ff) brightness(0) invert(1);
        }
        10% {
            transform: translate(3px, -3px) skewY(-8deg);
            filter: drop-shadow(0 0 4px #0ff) drop-shadow(0 0 6px #f0f) blur(1px) brightness(0) invert(1);
        }
        20% {
            transform: translate(8px, -4px) skewX(-15deg);
            clip-path: polygon(0 10%, 100% 10%, 100% 50%, 0 50%);
            opacity: 0.8;
            filter: brightness(0) invert(1);
        }
        22% {
            clip-path: polygon(0 60%, 100% 60%, 100% 90%, 0 90%);
            filter: brightness(0) invert(1);
        }
        49% {
            opacity: 1;
            transform: scale(1.05);
            filter: brightness(0) invert(1);
        }
        50% {
            opacity: 0;
            transform: translate(10px, 5px) scale(1.3);
            filter: brightness(0) invert(1);
        }
        51%, 100% {
            opacity: 0;
            filter: brightness(0) invert(1);
        }
    }
</style>
</body>
</html>