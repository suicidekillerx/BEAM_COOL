<?php
require_once 'includes/functions.php';

// Get filter parameters
$categoryFilter = $_GET['category'] ?? null;
$collectionFilter = $_GET['collection'] ?? null;
$searchQuery = $_GET['search'] ?? '';
$sizeFilter = $_GET['size'] ?? '';

// Build filters array
$filters = [];
if ($categoryFilter) {
    $filters['category'] = $categoryFilter;
}
if ($collectionFilter) {
    $filters['collection'] = $collectionFilter;
}
if ($searchQuery) {
    $filters['search'] = $searchQuery;
}

// Get products and categories
$products = getProducts($filters);
$categories = getCategories();
$collections = getCollections();

// Get all available sizes for filters
$allSizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'];
?>
<?php require_once 'includes/header.php'; ?>

    <!-- Main Content: Shop -->
    <main class="container mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">ALL PRODUCTS</h1>
            <p class="text-gray-600">Explore our latest drops and signature looks.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Mobile Filter Button -->
            <div class="lg:hidden mb-6">
                <button id="filter-toggle-button" class="w-full flex justify-center items-center py-4 px-6 bg-black text-white text-sm font-bold tracking-wide hover:bg-gray-900 transition-all duration-200 shadow-lg rounded-lg">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    FILTER & SORT
                    <span id="active-filter-count" class="ml-3 bg-white text-black w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">0</span>
                </button>
            </div>

            <!-- Filter Sidebar -->
            <aside id="filter-sidebar" class="lg:w-80 xl:w-96 lg:sticky lg:top-8 lg:h-fit bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- Sidebar Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-900">Filters</h2>
                        <div class="flex items-center space-x-3">
                            <button id="clear-filters-button" class="text-sm font-medium text-gray-600 hover:text-black transition-colors">
                            Clear All
                        </button>
                            <button id="close-filters-button" class="lg:hidden text-gray-400 hover:text-black">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <form id="filter-form" method="GET" action="shop.php" class="divide-y divide-gray-100">
                    <!-- Search Filter -->
                    <div class="p-6">
                        <div class="relative">
                            <input type="text" name="search" id="filter-search" placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery); ?>" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent transition-all text-sm">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Categories Filter -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Categories</h3>
                        <div class="space-y-3">
                            <?php foreach ($categories as $category): ?>
                            <label class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors cursor-pointer">
                                <div class="flex items-center">
                                    <input type="checkbox" name="category[]" class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black" data-filter="category" value="<?php echo $category['id']; ?>" <?php echo ($categoryFilter == $category['id']) ? 'checked' : ''; ?>>
                                    <span class="ml-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($category['name']); ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Size Filter -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Size</h3>
                            <div class="grid grid-cols-4 gap-2">
                            <?php foreach ($allSizes as $size): ?>
                                <label class="size-option">
                                <input type="checkbox" name="size[]" class="sr-only" data-filter="size" value="<?php echo strtolower($size); ?>" <?php echo (strpos($sizeFilter, strtolower($size)) !== false) ? 'checked' : ''; ?>>
                                <span class="size-label"><?php echo $size; ?></span>
                                </label>
                            <?php endforeach; ?>
                            </div>
                        </div>

                    <!-- Collection Filter -->
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Collections</h3>
                        <div class="space-y-3">
                            <?php foreach ($collections as $collection): ?>
                            <label class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors cursor-pointer">
                                <div class="flex items-center">
                                    <input type="checkbox" name="collection[]" class="w-4 h-4 text-black border-gray-300 rounded focus:ring-black" data-filter="collection" value="<?php echo $collection['id']; ?>" <?php echo ($collectionFilter == $collection['id']) ? 'checked' : ''; ?>>
                                    <span class="ml-3 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($collection['name']); ?></span>
                                </div>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><?php echo rand(5, 20); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                </div>

                <!-- Apply Button (Mobile) -->
                    <div class="lg:hidden p-6 bg-gray-50">
                        <button type="submit" id="apply-filters-button" class="w-full py-3 px-6 bg-black text-white font-bold rounded-lg hover:bg-gray-900 transition-colors">
                        Show Results
                    </button>
                </div>
                </form>
            </aside>

            <!-- Product Grid -->
            <div class="flex-1">
                <!-- Results Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                    <div class="text-sm text-gray-600">
                        Showing <span id="results-count" class="font-semibold"><?php echo count($products); ?></span> products
                    </div>
                    <div class="flex items-center space-x-4">
                        <select id="sort-select" class="text-sm border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent bg-white">
                            <option value="newest" selected>Newest First</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="name">Name: A to Z</option>
                        </select>
                    </div>
                            </div>

                
                <div id="product-grid" id="selected-filters"  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($products as $product): 
                        $productImages = getProductImagesForSlider($product['id']);
                        $primaryImage = $productImages[0]['image_path'] ?? 'images/placeholder.jpg';
                        $secondaryImage = $productImages[1]['image_path'] ?? $primaryImage;
                    ?>
                    <div class="bg-white relative group product-card border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300" 
                         data-category="<?php echo $product['category_id']; ?>" 
                         data-collection="<?php echo $product['collection_id']; ?>"
                         data-price="<?php echo $product['sale_price'] ?? $product['price']; ?>"
                         data-name="<?php echo strtolower($product['name']); ?>"
                         data-color="<?php echo strtolower($product['color'] ?? ''); ?>"
                         data-created-at="<?php echo $product['created_at']; ?>">
                        <a href="product-view.php?id=<?php echo $product['id']; ?>">
                            <div class="relative aspect-square">
                                <?php if ($product['is_on_sale']): ?>
                                <span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2 py-1 z-10 rounded">SALE</span>
                                <?php endif; ?>
                                <button class="absolute top-3 right-3 text-gray-400 hover:text-red-500 transition-colors duration-200 z-10 wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </button>
                                <img src="<?php echo $primaryImage; ?>" alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0">
                                <img src="<?php echo $secondaryImage; ?>" alt="<?php echo $product['name']; ?> Hover" class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                            <div class="p-4">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2 line-clamp-2"><?php echo $product['name']; ?></h4>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold text-gray-900"><?php echo formatPrice($product['sale_price'] ?? $product['price']); ?></span>
                                        <?php if ($product['sale_price']): ?>
                                        <span class="text-sm text-gray-500 line-through"><?php echo formatPrice($product['price']); ?></span>
                                        <?php endif; ?>
                    </div>
                            </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="no-results-message" class="hidden text-center py-16">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500 mb-6">Try adjusting your filters or search terms.</p>
                        <button id="clear-all-filters" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition-colors">
                            Clear All Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php require_once 'includes/footer.php'; ?>

<!-- Shop Page JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile filter toggle
    const filterToggleBtn = document.getElementById('filter-toggle-button');
    const filterSidebar = document.getElementById('filter-sidebar');
    const closeFiltersBtn = document.getElementById('close-filters-button');
    
    if (filterToggleBtn && filterSidebar) {
        filterToggleBtn.addEventListener('click', function() {
            filterSidebar.classList.toggle('show');
            document.body.style.overflow = filterSidebar.classList.contains('show') ? 'hidden' : '';
        });
    }
    
    if (closeFiltersBtn && filterSidebar) {
        closeFiltersBtn.addEventListener('click', function() {
            filterSidebar.classList.remove('show');
            document.body.style.overflow = '';
        });
    }
    
    // Close filter on overlay click
    if (filterSidebar) {
        filterSidebar.addEventListener('click', function(e) {
            if (e.target === filterSidebar) {
                filterSidebar.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Clear filters
    const clearFiltersBtn = document.getElementById('clear-filters-button');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            const form = document.getElementById('filter-form');
            if (form) {
                form.reset();
                applyFilters();
            }
        });
    }
    
    // Clear all filters button in no results
    const clearAllFiltersBtn = document.getElementById('clear-all-filters');
    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', function() {
            const form = document.getElementById('filter-form');
            if (form) {
                form.reset();
                applyFilters();
            }
        });
    }
    
    // Auto-submit on filter change (desktop)
    const filterCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (window.innerWidth >= 1024) { // Desktop only
                applyFilters();
            }
        });
    });
    
    // Search input with debounce
    const searchInput = document.getElementById('filter-search');
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (window.innerWidth >= 1024) { // Desktop only
                    applyFilters();
                }
            }, 500);
        });
    }
    
    // Sort functionality
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        // Apply default sort on page load
        applySort('newest');
        
        sortSelect.addEventListener('change', function() {
            applySort(this.value);
        });
    }
    
    // Apply sort function
    function applySort(sortValue) {
        const products = document.querySelectorAll('.product-card');
        const productArray = Array.from(products);
        
        productArray.sort((a, b) => {
            switch(sortValue) {
                case 'price-low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price-high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                case 'name':
                    return a.dataset.name.localeCompare(b.dataset.name);
                case 'newest':
                    // Sort by creation date (newest first)
                    const dateA = new Date(a.dataset.createdAt || 0);
                    const dateB = new Date(b.dataset.createdAt || 0);
                    return dateB - dateA;
                default:
                    return 0;
            }
        });
        
        const productGrid = document.getElementById('product-grid');
        productArray.forEach(product => {
            productGrid.appendChild(product);
        });
    }
    
    // Apply filters function
    function applyFilters() {
        const form = document.getElementById('filter-form');
        if (!form) return;
        
        // Show loading state
        const productGrid = document.getElementById('product-grid');
        const resultsCount = document.getElementById('results-count');
        const noResultsMessage = document.getElementById('no-results-message');
        
        productGrid.style.opacity = '0.6';
        productGrid.style.pointerEvents = 'none';
        
        // Collect form data
        const formData = new FormData(form);
        formData.append('action', 'filter_products');
        
        // Send AJAX request
        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update product grid
                productGrid.innerHTML = data.products;
                
                // Update results count
                if (resultsCount) {
                    resultsCount.textContent = data.count;
                }
                
                // Update selected filters
                const selectedFiltersContainer = document.getElementById('selected-filters');
                if (selectedFiltersContainer) {
                    selectedFiltersContainer.innerHTML = data.selectedFilters;
                }
                
                // Show/hide no results message
                if (noResultsMessage) {
                    if (data.hasResults) {
                        noResultsMessage.classList.add('hidden');
                    } else {
                        noResultsMessage.classList.remove('hidden');
                    }
                }
                
                // Update filter count
                updateFilterCount();
                
                // Re-attach wishlist event listeners
                attachWishlistListeners();
                
                // Re-attach remove filter listeners
                attachRemoveFilterListeners();
            }
        })
        .catch(error => {
            console.error('Error applying filters:', error);
        })
        .finally(() => {
            // Remove loading state
            productGrid.style.opacity = '1';
            productGrid.style.pointerEvents = 'auto';
        });
    }
    
    // Remove filter function
    function removeFilter(type, id) {
        if (type === 'search') {
            const searchInput = document.getElementById('filter-search');
            if (searchInput) {
                searchInput.value = '';
            }
        } else {
            const checkboxes = document.querySelectorAll(`input[name="${type}[]"]`);
            checkboxes.forEach(checkbox => {
                if (checkbox.value == id) {
                    checkbox.checked = false;
                }
            });
        }
        applyFilters();
    }
    
    // Attach remove filter listeners
    function attachRemoveFilterListeners() {
        const removeFilterBtns = document.querySelectorAll('.remove-filter');
        removeFilterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const id = this.dataset.id;
                removeFilter(type, id);
            });
        });
    }
    
    // Attach wishlist listeners
    function attachWishlistListeners() {
        const wishlistBtns = document.querySelectorAll('.wishlist-btn');
        wishlistBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.productId;
                
                // Toggle wishlist state
                this.classList.toggle('text-red-500');
                
                // Send AJAX request to add/remove from wishlist
                fetch('ajax_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add_to_wishlist&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Wishlist updated');
                    }
                })
                .catch(error => {
                    console.error('Error updating wishlist:', error);
                });
            });
        });
    }
    
    // Update active filter count
    function updateFilterCount() {
        const activeFilters = document.querySelectorAll('input[type="checkbox"]:checked, input[name="search"][value]');
        const countElement = document.getElementById('active-filter-count');
        if (countElement) {
            countElement.textContent = activeFilters.length;
        }
    }
    
    updateFilterCount();
    
    // Filter option styling
    const filterOptions = document.querySelectorAll('label[class*="flex items-center justify-between"]');
    filterOptions.forEach(option => {
        const checkbox = option.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                option.classList.toggle('selected', this.checked);
            });
        }
    });
    
    // Size option styling
    const sizeOptions = document.querySelectorAll('.size-option');
    sizeOptions.forEach(option => {
        const checkbox = option.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                option.classList.toggle('selected', this.checked);
            });
        }
    });
    
    // Initialize selected states
    function initializeSelectedStates() {
        // Categories and collections
        filterOptions.forEach(option => {
            const checkbox = option.querySelector('input[type="checkbox"]');
            if (checkbox && checkbox.checked) {
                option.classList.add('selected');
            }
        });
        
        // Sizes
        sizeOptions.forEach(option => {
            const checkbox = option.querySelector('input[type="checkbox"]');
            if (checkbox && checkbox.checked) {
                option.classList.add('selected');
            }
        });
    }
    
    initializeSelectedStates();
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            filterSidebar.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Initial wishlist listeners
    attachWishlistListeners();
});
</script>

<style>
/* Modern Filter Styles */
.size-option {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 8px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.size-option:hover {
    border-color: #d1d5db;
    background-color: #f9fafb;
    transform: translateY(-1px);
}

.size-option.selected {
    border-color: #000;
    background-color: #000;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.size-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

/* Filter option styling */
.filter-option {
    transition: all 0.2s ease;
}

.filter-option:hover {
    background-color: #f8fafc;
}

.filter-option.selected {
    background-color: #f1f5f9;
    border-color: #000;
}

/* Checkbox styling */
input[type="checkbox"]:checked {
    background-color: #000;
    border-color: #000;
}

/* Mobile filter sidebar */
@media (max-width: 1023px) {
    #filter-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 50;
        background: white;
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
        max-height: 100vh;
        overflow-y: auto;
    }
    
    #filter-sidebar.show {
        transform: translateX(0);
    }
}

/* Product card improvements */
.product-card {
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Line clamp for product titles */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Sticky sidebar on desktop */
@media (min-width: 1024px) {
    #filter-sidebar {
        position: sticky;
        top: 2rem;
        height: fit-content;
    }
}

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Focus states */
input:focus, select:focus, button:focus {
    outline: none;
    ring: 2px;
    ring-color: #000;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Active filter count badge */
#active-filter-count {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}

/* Responsive improvements */
@media (max-width: 640px) {
    .grid {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

@media (min-width: 641px) and (max-width: 1023px) {
    .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) and (max-width: 1279px) {
    .grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1280px) {
    .grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}

/* Selected filters styling */
#selected-filters {
    min-height: 2.5rem;
}

#selected-filters:empty {
    display: none;
}

#selected-filters span {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading animation */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #000;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Smooth transitions for product grid */
#product-grid {
    transition: opacity 0.3s ease;
}

/* Filter badge hover effects */
.remove-filter {
    transition: all 0.2s ease;
}

.remove-filter:hover {
    transform: scale(1.1);
}
</style>
