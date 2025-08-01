/**
 * Main JavaScript file for Beam e-commerce website
 * 
 * NOTE: Wishlist functionality is temporarily hidden via CSS
 * All wishlist code is preserved and can be re-enabled by removing
 * the CSS rules that hide wishlist elements in style.css
 */

document.addEventListener('DOMContentLoaded', () => {
    // Mobile Menu functionality
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const closeMenuButton = document.getElementById('close-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.remove('-translate-x-full');
        });
    }

    if (closeMenuButton && mobileMenu) {
        closeMenuButton.addEventListener('click', () => {
            mobileMenu.classList.add('-translate-x-full');
        });
    }

    // Side Panel Functionality
    const wishlistButton = document.getElementById('wishlist-button');
    const cartButton = document.getElementById('cart-button');
    const closeWishlistButton = document.getElementById('close-wishlist-button');
    const closeCartButton = document.getElementById('close-cart-button');
    const wishlistPanel = document.getElementById('wishlist-panel');
    const cartPanel = document.getElementById('cart-panel');
    const overlay = document.getElementById('side-panel-overlay');
    
    // Debug panel elements
    console.log('Cart button:', cartButton);
    console.log('Cart panel:', cartPanel);
    console.log('Wishlist button:', wishlistButton);
    console.log('Wishlist panel:', wishlistPanel);
    console.log('Overlay:', overlay);

    const openPanel = (panel) => {
        console.log('Opening panel:', panel);
        if (panel) {
            panel.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            console.log('Panel opened successfully');
        } else {
            console.log('Panel not found!');
        }
    };

    const closeAllPanels = () => {
        if (wishlistPanel) wishlistPanel.classList.add('translate-x-full');
        if (cartPanel) cartPanel.classList.add('translate-x-full');
        if (overlay) overlay.classList.add('hidden');
    };

    if (wishlistButton) {
        wishlistButton.addEventListener('click', () => {
            openPanel(wishlistPanel);
            loadWishlistItems();
        });
    }

    if (cartButton) {
        console.log('Cart button found:', cartButton);
        cartButton.addEventListener('click', () => {
            console.log('Cart button clicked!');
            openPanel(cartPanel);
            loadCartItems();
        });
    } else {
        console.log('Cart button NOT found!');
    }

    if (closeWishlistButton) {
        closeWishlistButton.addEventListener('click', closeAllPanels);
    }

    if (closeCartButton) {
        closeCartButton.addEventListener('click', closeAllPanels);
    }

    if (overlay) {
        overlay.addEventListener('click', closeAllPanels);
    }

    // Search functionality is now handled in header.php
    // Removed conflicting search overlay code

    // Generic Carousel Setup Function
    const setupCarousel = (carouselSelector, slideSelector, interval, getVisibleSlidesFunc) => {
        const carousel = document.querySelector(carouselSelector);
        if (!carousel) return;

        const slides = carousel.querySelectorAll(slideSelector);
        const totalSlides = slides.length;
        let currentIndex = 0;
        let visibleSlides = getVisibleSlidesFunc();
        let autoSlideInterval;
        let resumeTimeout;

        function updateCarousel() {
            visibleSlides = getVisibleSlidesFunc();
            const maxIndex = totalSlides > visibleSlides ? totalSlides - visibleSlides : 0;
            if (currentIndex > maxIndex) {
                currentIndex = maxIndex;
            }
            const translateX = -(currentIndex * (100 / visibleSlides));
            carousel.style.transform = `translateX(${translateX}%)`;
        }

        function slideNext() {
            const maxIndex = totalSlides > visibleSlides ? totalSlides - visibleSlides : 0;
            if (totalSlides > visibleSlides) {
                currentIndex = (currentIndex + 1) % (maxIndex + 1);
            }
            updateCarousel();
        }

        function startAutoSlide() {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(slideNext, interval);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
            clearTimeout(resumeTimeout);
        }

        carousel.addEventListener('mouseenter', stopAutoSlide);
        carousel.addEventListener('mouseleave', () => {
            resumeTimeout = setTimeout(() => {
                startAutoSlide();
            }, 3000);
        });

        window.addEventListener('resize', () => {
            stopAutoSlide();
            updateCarousel();
            startAutoSlide();
        });

        updateCarousel();
        startAutoSlide();
    };

    // Configuration for Product and Bestseller Carousels
    const getStandardVisibleSlides = () => {
        if (window.innerWidth < 768) return 1;
        if (window.innerWidth < 1024) return 2;
        return 4;
    };

    // Configuration for Category Carousel
    const getCategoryVisibleSlides = () => {
        if (window.innerWidth < 768) return 2;
        if (window.innerWidth < 1024) return 3;
        return 5;
    };

    setupCarousel('.product-carousel', '.product-slide', 3000, getStandardVisibleSlides);
    setupCarousel('.bestsellers-carousel', '.bestseller-slide', 4000, getStandardVisibleSlides);
    setupCarousel('.category-carousel', '.category-slide', 2000, getCategoryVisibleSlides);

    // Cyber Glitch Brand Animation
    (function() {
        const brandLogo = document.getElementById('brand-logo');
        if (!brandLogo) return;
        const brandText = brandLogo.querySelector('.brand-text');
        const brandImg = brandLogo.querySelector('.brand-logo-img');
        let showingText = true;

        function glitchSwap() {
            brandLogo.classList.add('glitching');
            setTimeout(() => {
                brandLogo.classList.remove('glitching');
                if (showingText) {
                    brandText.classList.add('hidden');
                    brandImg.classList.remove('hidden');
                } else {
                    brandText.classList.remove('hidden');
                    brandImg.classList.add('hidden');
                }
                showingText = !showingText;
            }, 600);
        }

        function startCycle() {
            glitchSwap();
            setInterval(glitchSwap, 4000);
        }
        startCycle();
    })();

    // --- Infinite Scrolling Gallery --- //
    const galleryContainer = document.querySelector('.scrolling-gallery-container');
    let isLightboxOpen = false;

    const setupScrollingGallery = () => {
        if (!galleryContainer || window.innerWidth <= 768) return;

        const columns = galleryContainer.querySelectorAll('.gallery-column');
        const speed = 0.3; // Adjust speed: lower is slower, higher is faster.

        columns.forEach(column => {
            if (column.offsetParent === null) return; // Skip hidden columns

            const direction = column.classList.contains('column-2') ? -1 : 1; // Up or Down
            let scrollPos = 0;
            let animationFrameId;

            // Add state properties directly to the DOM element
            column.isHovering = false;
            column.isDragging = false;
            let initialTouchY = 0;
            let initialScrollPos = 0;

            if (direction === 1) {
                scrollPos = -(column.scrollHeight / 2);
            }

            // --- Event Listeners ---
            column.addEventListener('mouseenter', () => column.isHovering = true);
            column.addEventListener('mouseleave', () => column.isHovering = false);

            column.addEventListener('touchstart', (e) => {
                column.isDragging = true;
                initialTouchY = e.touches[0].clientY;
                initialScrollPos = scrollPos;
            }, { passive: true });

            column.addEventListener('touchmove', (e) => {
                if (!column.isDragging) return;
                const currentTouchY = e.touches[0].clientY;
                const deltaY = currentTouchY - initialTouchY;
                scrollPos = initialScrollPos + deltaY;
                column.style.transform = `translateY(${scrollPos}px)`;
            }, { passive: true });

            column.addEventListener('touchend', () => {
                column.isDragging = false;
                // Normalize scroll position to keep it within the loop bounds
                const halfHeight = column.scrollHeight / 2;
                scrollPos = scrollPos % halfHeight;
            });

            function animate() {
                if (!column.isHovering && !isLightboxOpen && !column.isDragging) {
                    scrollPos += speed * direction;

                    // Loop logic
                    const halfHeight = column.scrollHeight / 2;
                    if (direction === 1 && scrollPos >= 0) {
                        scrollPos = -halfHeight;
                    } else if (direction === -1 && scrollPos <= -halfHeight) {
                        scrollPos = 0;
                    }
                    column.style.transform = `translateY(${scrollPos}px)`;
                }
                animationFrameId = requestAnimationFrame(animate);
            }
            animate();
        });
    };

    // Wait for images to load to get correct heights
    window.addEventListener('load', setupScrollingGallery);

    // --- Collections Gallery Popup --- //
    // Removed Magnific Popup for collections gallery
    // if (galleryContainer) {
    //     $(galleryContainer).magnificPopup({
    //         delegate: 'a',
    //         type: 'image',
    //         gallery: {
    //             enabled: true
    //         },
    //         callbacks: {
    //             open: () => isLightboxOpen = true,
    //             close: () => isLightboxOpen = false
    //         }
    //     });
    // }

    // --- Product View Page Functionality ---
    const setupProductViewPage = () => {
        const productDetailsContainer = document.querySelector('.product-details-container'); // A container for all product details
        if (!productDetailsContainer) return;

        // 1. Quantity Selector
        const quantityInput = productDetailsContainer.querySelector('input[type="text"]');
        const minusBtn = productDetailsContainer.querySelector('.quantity-minus');
        const plusBtn = productDetailsContainer.querySelector('.quantity-plus');

        if (quantityInput && minusBtn && plusBtn) {
            minusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value, 10);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });

            plusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value, 10);
                quantityInput.value = currentValue + 1;
            });
        }

        // 2. Image Lightbox for Product Images
        const productImageGallery = document.querySelector('.product-image-gallery');
        if (productImageGallery) {
            $(productImageGallery).magnificPopup({
                delegate: 'a',
                type: 'image',
                gallery: {
                    enabled: true
                },
                mainClass: 'mfp-with-zoom',
                zoom: {
                    enabled: true,
                    duration: 300,
                    easing: 'ease-in-out'
                }
            });
        }

        // 3. Accordion for Details
        const accordionHeaders = productDetailsContainer.querySelectorAll('.accordion-header');
        accordionHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                content.classList.toggle('hidden');
                // Optional: Add an indicator icon rotation
                const icon = header.querySelector('svg');
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            });
        });

        // 4. Add to Cart Button
        const addToCartBtn = productDetailsContainer.querySelector('.add-to-cart-btn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const quantityInput = productDetailsContainer.querySelector('.quantity-input');
                const selectedSizeBtn = productDetailsContainer.querySelector('.size-btn.selected');
                
            
                
                const size = selectedSizeBtn.textContent.trim().split('\n')[0]; // Get just the size part
                const quantity = parseInt(quantityInput.value, 10);
                
                console.log('Adding to cart:', { productId, size, quantity });
                
                $.ajax({
                    url: 'ajax_handler.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'add_to_cart',
                        product_id: productId,
                        size: size,
                        quantity: quantity
                    },
                    success: function(response) {
                        console.log('Add to cart response:', response);
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                // Show success message
                                alert('Added to cart successfully!');
                                
                                // Check if auto_open_cart is enabled
                                if (result.auto_open_cart) {
                                    // Redirect to cart page after a short delay
                                    setTimeout(function() {
                                        window.location.href = 'view_cart.php';
                                    }, 1500);
                                } else {
                                    // Just update cart count without redirecting
                                    if (typeof loadCartItems === 'function') {
                                        loadCartItems();
                                    }
                                }
                            } else {
                                alert('Error: ' + result.message);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        console.error('Response:', xhr.responseText);
                        
                    }
                });
            });
        }

        // 5. Add to Wishlist Button
        const addToWishlistBtn = productDetailsContainer.querySelector('.add-to-wishlist-btn');
        if (addToWishlistBtn) {
            addToWishlistBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const isInWishlist = this.classList.contains('bg-red-50');
                
                $.ajax({
                    url: 'ajax_handler.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: isInWishlist ? 'remove_from_wishlist' : 'add_to_wishlist',
                        product_id: productId
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                // Toggle button state
                                addToWishlistBtn.classList.toggle('bg-red-50');
                                addToWishlistBtn.classList.toggle('border-red-300');
                                addToWishlistBtn.classList.toggle('text-red-600');
                                
                                const heartIcon = addToWishlistBtn.querySelector('svg');
                                const heartPath = addToWishlistBtn.querySelector('path');
                                const span = addToWishlistBtn.querySelector('span');
                                
                                if (isInWishlist) {
                                    // Remove from wishlist
                                    heartIcon.classList.remove('fill-red-600');
                                    heartPath.setAttribute('fill', 'none');
                                    span.textContent = 'Add to Wishlist';
                                } else {
                                    // Add to wishlist
                                    heartIcon.classList.add('fill-red-600');
                                    heartPath.setAttribute('fill', 'currentColor');
                                    span.textContent = 'Remove from Wishlist';
                                }
                                
                                // Update wishlist count if needed
                                if (typeof loadWishlistItems === 'function') {
                                    loadWishlistItems();
                                }
                            } else {
                                alert('Error: ' + result.message);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Error updating wishlist');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                        alert('Error updating wishlist');
                    }
                });
            });
        }

        // 6. Size Selection
        const sizeBtns = productDetailsContainer.querySelectorAll('.size-btn:not([disabled])');
        sizeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove selected class from all size buttons
                sizeBtns.forEach(b => b.classList.remove('selected', 'bg-black', 'text-white'));
                // Add selected class to clicked button
                this.classList.add('selected', 'bg-black', 'text-white');
            });
        });
    };

    setupProductViewPage();

    // --- Collections Gallery Popup (Original - for reference, now integrated) --- //
    // Removed global Magnific Popup for galleryContainer to prevent lightbox on collections gallery
    // if (galleryContainer) {
    //     $(galleryContainer).magnificPopup({
    //         delegate: 'a',
    //         type: 'image',
    //         gallery: {
    //             enabled: true,
    //             navigateByImgClick: true,
    //             preload: [0, 1]
    //         },
    //         image: {
    //             tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
    //             titleSrc: function(item) {
    //                 return item.el.attr('title') + '<small>by Beam</small>';
    //             }
    //         },
    //         mainClass: 'mfp-with-zoom',
    //         zoom: {
    //             enabled: true,
    //             duration: 300,
    //             easing: 'ease-in-out'
    //         },
    //         callbacks: {
    //             open: () => isLightboxOpen = true,
    //             close: () => isLightboxOpen = false
    //         }
    //     });
    // }

    // ===== ENHANCED PRODUCT FILTERING =====
    document.addEventListener('DOMContentLoaded', function() {
        const filterSidebar = document.getElementById('filter-sidebar');
        if (!filterSidebar) return;

        // DOM Elements
        const filterToggleButton = document.getElementById('filter-toggle-button');
        const closeFiltersButton = document.getElementById('close-filters-button');
        const clearFiltersButton = document.getElementById('clear-filters-button');
        const applyFiltersButton = document.getElementById('apply-filters-button');
        const filterSearch = document.getElementById('filter-search');
        const productGrid = document.getElementById('product-grid');
        const productCards = Array.from(productGrid.getElementsByClassName('product-card'));
        const noResultsMessage = document.getElementById('no-results-message');
        const activeFilterCount = document.getElementById('active-filter-count');
        const filterSections = document.querySelectorAll('.filter-section');
        
        // Price range elements
        const priceMinInput = document.querySelector('input[type="number"]:first-of-type');
        const priceMaxInput = document.querySelector('input[type="number"]:last-of-type');
        const priceRangeFill = document.querySelector('.relative.h-1.bg-gray-200.rounded-full > div');
        
        // State
        let activeFilters = {
            search: '',
            price: { min: 0, max: 200 },
            category: [],
            size: [],
            color: [],
            collection: []
        };

        // Initialize filter sections
        function initFilterSections() {
            filterSections.forEach(section => {
                const summary = section.querySelector('summary');
                const icon = summary.querySelector('.accordion-icon svg');
                
                summary.addEventListener('click', (e) => {
                    e.preventDefault();
                    section.toggleAttribute('open');
                    updateAccordionState(section, icon);
                });
                
                // Initialize accordion state
                updateAccordionState(section, icon);
            });
        }

        function updateAccordionState(section, icon) {
            if (section.hasAttribute('open')) {
                icon.style.transform = 'rotate(180deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }

        // Price range functionality
        function initPriceRange() {
            if (!priceMinInput || !priceMaxInput) return;

            function updatePriceRange() {
                const min = parseInt(priceMinInput.value) || 0;
                const max = parseInt(priceMaxInput.value) || 200;
                
                // Ensure min is not greater than max and vice versa
                if (min > max) {
                    priceMinInput.value = max;
                    priceMaxInput.value = min;
                }
                
                // Update active filters
                activeFilters.price = {
                    min: parseInt(priceMinInput.value) || 0,
                    max: parseInt(priceMaxInput.value) || 200
                };
                
                // Update visual fill
                updatePriceRangeFill();
                
                // Trigger filter
                debouncedFilterProducts();
            }

            function updatePriceRangeFill() {
                const min = parseInt(priceMinInput.value) || 0;
                const max = parseInt(priceMaxInput.value) || 200;
                const percentage = ((max - min) / 200) * 100;
                const position = (min / 200) * 100;
                
                if (priceRangeFill) {
                    priceRangeFill.style.width = `${percentage}%`;
                    priceRangeFill.style.left = `${position}%`;
                }
            }

            priceMinInput.addEventListener('change', updatePriceRange);
            priceMaxInput.addEventListener('change', updatePriceRange);
            
            // Initialize
            updatePriceRangeFill();
        }

        // Search functionality
        function initSearch() {
            if (!filterSearch) return;
            
            filterSearch.addEventListener('input', (e) => {
                activeFilters.search = e.target.value.toLowerCase().trim();
                debouncedFilterProducts();
            });
        }

        // Filter products based on active filters
        function filterProducts() {
            let visibleCount = 0;
            const activeFilterCounts = {
                category: 0,
                size: 0,
                color: 0,
                collection: 0
            };

            productCards.forEach(card => {
                const cardData = {
                    name: card.dataset.name ? card.dataset.name.toLowerCase() : '',
                    price: parseFloat(card.dataset.price) || 0,
                    category: card.dataset.category ? card.dataset.category.split(',').map(c => c.trim()) : [],
                    size: card.dataset.size ? card.dataset.size.split(',').map(s => s.trim().toLowerCase()) : [],
                    color: card.dataset.color ? card.dataset.color.split(',').map(c => c.trim().toLowerCase()) : [],
                    collection: card.dataset.collection ? card.dataset.collection.split(',').map(c => c.trim().toLowerCase()) : []
                };

                // Check search term
                const matchesSearch = activeFilters.search === '' || 
                                    cardData.name.includes(activeFilters.search);
                
                // Check price range
                const matchesPrice = cardData.price >= activeFilters.price.min && 
                                   cardData.price <= activeFilters.price.max;
                
                // Check other filters
                const matchesFilters = ['category', 'size', 'color', 'collection'].every(filterType => {
                    if (activeFilters[filterType].length === 0) return true;
                    return activeFilters[filterType].some(value => 
                        cardData[filterType].includes(value)
                    );
                });

                // Show/hide card based on all conditions
                const isVisible = matchesSearch && matchesPrice && matchesFilters;
                card.style.display = isVisible ? '' : 'none';
                
                if (isVisible) {
                    visibleCount++;
                    // Update filter counts for active filters
                    ['category', 'size', 'color', 'collection'].forEach(type => {
                        if (activeFilters[type].length > 0) {
                            activeFilterCounts[type] += activeFilters[type].some(v => 
                                cardData[type].includes(v)
                            ) ? 1 : 0;
                        }
                    });
                }
            });

            // Update UI based on results
            noResultsMessage.classList.toggle('hidden', visibleCount > 0);
            updateActiveFilterCount();
            updateFilterCounts(activeFilterCounts);
        }

        // Update active filter count badge
        function updateActiveFilterCount() {
            if (!activeFilterCount) return;
            
            const totalActive = Object.values(activeFilters).reduce((count, filter) => {
                if (Array.isArray(filter)) {
                    return count + filter.length;
                } else if (typeof filter === 'object' && filter !== null) {
                    return count + (filter.min > 0 || filter.max < 200 ? 1 : 0);
                } else if (filter && typeof filter === 'string') {
                    return count + (filter.trim() !== '' ? 1 : 0);
                }
                return count;
            }, 0);
            
            activeFilterCount.textContent = totalActive;
            activeFilterCount.classList.toggle('hidden', totalActive === 0);
        }

        // Update filter counts based on current results
        function updateFilterCounts(counts) {
            document.querySelectorAll('.filter-count').forEach(countEl => {
                const filterType = countEl.closest('[data-filter]')?.dataset.filter;
                const filterValue = countEl.closest('label')?.querySelector('input')?.value;
                
                if (filterType && filterValue && counts[filterType] !== undefined) {
                    // For now, we're just showing the count if available
                    // In a real app, you'd want to calculate actual counts from your product data
                    // countEl.textContent = counts[filterType];
                }
            });
        }

        // Clear all filters
        function clearAllFilters() {
            // Reset search
            if (filterSearch) filterSearch.value = '';
            
            // Reset price range
            if (priceMinInput) priceMinInput.value = '0';
            if (priceMaxInput) priceMaxInput.value = '200';
            
            // Reset checkboxes
            document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Reset active filters
            activeFilters = {
                search: '',
                price: { min: 0, max: 200 },
                category: [],
                size: [],
                color: [],
                collection: []
            };
            
            // Update UI and filter
            updatePriceRangeFill();
            filterProducts();
        }

        // Debounce function to limit how often filter updates occur
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Initialize event listeners
        function initEventListeners() {
            // Mobile menu toggle
            if (filterToggleButton) {
                filterToggleButton.addEventListener('click', () => {
                    filterSidebar.classList.toggle('is-open');
                    document.body.style.overflow = filterSidebar.classList.contains('is-open') ? 'hidden' : '';
                });
            }
            
            // Close button
            if (closeFiltersButton) {
                closeFiltersButton.addEventListener('click', () => {
                    filterSidebar.classList.remove('is-open');
                    document.body.style.overflow = '';
                });
            }
            
            // Clear filters
            if (clearFiltersButton) {
                clearFiltersButton.addEventListener('click', clearAllFilters);
            }
            
            // Apply filters (mobile)
            if (applyFiltersButton) {
                applyFiltersButton.addEventListener('click', () => {
                    filterSidebar.classList.remove('is-open');
                    document.body.style.overflow = '';
                    filterProducts();
                });
            }
            
            // Checkbox changes
            document.addEventListener('change', (e) => {
                const checkbox = e.target.closest('.filter-checkbox');
                if (!checkbox) return;
                
                const filterType = checkbox.dataset.filter;
                const value = checkbox.value;
                
                if (checkbox.checked) {
                    if (!activeFilters[filterType].includes(value)) {
                        activeFilters[filterType].push(value);
                    }
                } else {
                    activeFilters[filterType] = activeFilters[filterType].filter(v => v !== value);
                }
                
                debouncedFilterProducts();
            });
            
            // Close filter when clicking outside
            document.addEventListener('click', (e) => {
                if (!filterSidebar.contains(e.target) && 
                    !filterToggleButton.contains(e.target) && 
                    filterSidebar.classList.contains('is-open')) {
                    filterSidebar.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            });
            
            // Prevent clicks inside filter from closing it
            filterSidebar.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }

        // Initialize everything
        const debouncedFilterProducts = debounce(filterProducts, 200);
        
        initFilterSections();
        initPriceRange();
        initSearch();
        initEventListeners();
        
        // Initial filter
        filterProducts();
    });

    // Cart and Wishlist Functions - Make them global
    window.loadCartItems = function() {
        console.log('Loading cart items...');
        $.ajax({
            url: 'ajax_handler.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'get_cart_items'
            },
            success: function(response) {
                console.log('Cart AJAX success:', response);
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        console.log('Cart items loaded:', result.items);
                        displayCartItems(result.items);
                    } else {
                        console.log('Cart load failed:', result.message);
                        displayCartItems([]);
                    }
                } catch (e) {
                    console.log('Cart parse error:', e);
                    displayCartItems([]);
                }
            },
            error: function(xhr, status, error) {
                console.log('Cart AJAX error:', status, error);
                console.log('Response:', xhr.responseText);
                displayCartItems([]);
            }
        });
    };

    window.displayCartItems = function(items) {
        const cartPanel = document.getElementById('cart-panel');
        if (!cartPanel) return;

        const cartContent = cartPanel.querySelector('.p-4');
        
        if (!items || items.length === 0) {
            cartContent.innerHTML = '<p>Your cart is empty.</p>';
            return;
        }

        let total = 0;
        let html = '<div class="space-y-4">';
        
        items.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            html += `
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded">
                    <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h4 class="font-medium text-sm">${item.name}</h4>
                        <p class="text-xs text-gray-500">Size: ${item.size}</p>
                        <p class="text-xs text-gray-500">Quantity: ${item.quantity}</p>
                        <p class="font-bold text-sm">${item.price_formatted}</p>
                    </div>
                    <button class="text-red-500 hover:text-red-700" onclick="removeFromCart(${item.id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
        });
        
        html += `
            <div class="border-t pt-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="font-bold">Total:</span>
                    <span class="font-bold">${total.toFixed(3)} DTN</span>
                </div>
                <a href="cart.php" class="block w-full bg-black text-white text-center py-2 rounded hover:bg-gray-800 transition-colors">
                    View Cart
                </a>
            </div>
        </div>`;
        
        cartContent.innerHTML = html;
    };

    window.removeFromCart = function(cartItemId) {
        $.ajax({
            url: 'ajax_handler.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'remove_from_cart',
                cart_item_id: cartItemId
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        loadCartItems(); // Reload cart items
                    }
                } catch (e) {
                    console.error('Error removing item from cart');
                }
            },
            error: function() {
                console.error('Error removing item from cart');
            }
        });
    };

    window.loadWishlistItems = function() {
        $.ajax({
            url: 'ajax_handler.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'get_wishlist_items'
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        displayWishlistItems(result.items);
                    } else {
                        displayWishlistItems([]);
                    }
                } catch (e) {
                    displayWishlistItems([]);
                }
            },
            error: function() {
                displayWishlistItems([]);
            }
        });
    };

    window.displayWishlistItems = function(items) {
        const wishlistPanel = document.getElementById('wishlist-panel');
        if (!wishlistPanel) return;

        const wishlistContent = wishlistPanel.querySelector('.p-4');
        
        if (!items || items.length === 0) {
            wishlistContent.innerHTML = '<p>Your wishlist is empty.</p>';
            return;
        }

        let html = '<div class="space-y-4">';
        
        items.forEach(item => {
            html += `
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded">
                    <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h4 class="font-medium text-sm">${item.name}</h4>
                        <p class="font-bold text-sm">${item.price_formatted}</p>
                    </div>
                    <button class="text-red-500 hover:text-red-700" onclick="removeFromWishlist(${item.product_id})">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
        });
        
        html += '</div>';
        wishlistContent.innerHTML = html;
    };

    window.removeFromWishlist = function(productId) {
        $.ajax({
            url: 'ajax_handler.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'remove_from_wishlist',
                product_id: productId
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        loadWishlistItems(); // Reload wishlist items
                    }
                } catch (e) {
                    console.error('Error removing item from wishlist');
                }
            },
            error: function() {
                console.error('Error removing item from wishlist');
            }
        });
    };

    // Collections Rotation System
    const setupCollectionsRotation = () => {
        const collectionsContainer = document.getElementById('collections-container');
        const collectionsData = document.getElementById('collections-data');
        
        if (!collectionsContainer || !collectionsData) return;
        
        const collectionSlides = collectionsContainer.querySelectorAll('.collection-slide');
        const allCollectionsData = Array.from(collectionsData.querySelectorAll('.collection-data'));
        
        if (collectionSlides.length === 0 || allCollectionsData.length <= 4) return;
        
        let currentCollections = Array.from(collectionSlides).map(slide => 
            parseInt(slide.getAttribute('data-collection-id'))
        );
        
        let availableCollections = allCollectionsData.filter(data => 
            !currentCollections.includes(parseInt(data.getAttribute('data-id')))
        );
        
        let lastChangedIndex = -1; // Track the last changed position
        
        function rotateCollections() {
            if (availableCollections.length === 0) return;
            
            // Select a random collection to show
            const randomIndex = Math.floor(Math.random() * availableCollections.length);
            const newCollection = availableCollections[randomIndex];
            
            // Select a random position to replace (excluding hovered ones and last changed position)
            const nonHoveredSlides = Array.from(collectionSlides).filter((slide, index) => 
                !slide.matches(':hover') && index !== lastChangedIndex
            );
            
            if (nonHoveredSlides.length === 0) return;
            
            const randomSlideIndex = Math.floor(Math.random() * nonHoveredSlides.length);
            const slideToReplace = nonHoveredSlides[randomSlideIndex];
            
            // Find the index of the slide being replaced
            const slideIndex = Array.from(collectionSlides).indexOf(slideToReplace);
            lastChangedIndex = slideIndex; // Update last changed index
            
            const oldCollectionId = parseInt(slideToReplace.getAttribute('data-collection-id'));
            
            // Update the slide content
            slideToReplace.setAttribute('data-collection-id', newCollection.getAttribute('data-id'));
            slideToReplace.href = newCollection.getAttribute('data-url');
            
            const img = slideToReplace.querySelector('img');
            const title = slideToReplace.querySelector('h3');
            const description = slideToReplace.querySelector('p');
            
            // Simple fade transition
            slideToReplace.style.opacity = '0';
            
            setTimeout(() => {
                // Update content
                img.src = newCollection.getAttribute('data-image');
                img.alt = newCollection.getAttribute('data-name');
                title.textContent = newCollection.getAttribute('data-name');
                description.textContent = newCollection.getAttribute('data-description');
                
                // Fade back in
                slideToReplace.style.opacity = '1';
            }, 400);
            
            // Update tracking arrays
            const oldCollectionIndex = currentCollections.indexOf(oldCollectionId);
            if (oldCollectionIndex > -1) {
                currentCollections[oldCollectionIndex] = parseInt(newCollection.getAttribute('data-id'));
            }
            
            // Move collections between arrays
            availableCollections.splice(randomIndex, 1);
            const oldCollectionData = allCollectionsData.find(data => 
                parseInt(data.getAttribute('data-id')) === oldCollectionId
            );
            if (oldCollectionData) {
                availableCollections.push(oldCollectionData);
            }
        }
        
        // Start rotation every 4 seconds
        const rotationInterval = setInterval(rotateCollections, 4000);
        
        // Pause rotation when hovering over collections container
        collectionsContainer.addEventListener('mouseenter', () => {
            clearInterval(rotationInterval);
        });
        
        // Resume rotation when leaving collections container
        collectionsContainer.addEventListener('mouseleave', () => {
            clearInterval(rotationInterval);
            setInterval(rotateCollections, 4000);
        });
    };

    // Initialize collections rotation when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupCollectionsRotation);
    } else {
        setupCollectionsRotation();
    }
});