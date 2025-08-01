document.addEventListener('DOMContentLoaded', function() {
    // Size selection
    const sizeButtons = document.querySelectorAll('.size-btn:not([disabled])');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const selectedSizeInput = document.getElementById('selected-size');
    const selectedQuantityInput = document.getElementById('selected-quantity');
    
    if (sizeButtons.length > 0 && addToCartBtn) {
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove selected class from all size buttons
                sizeButtons.forEach(b => b.classList.remove('selected', 'bg-black', 'text-white'));
                // Add selected class to clicked button
                this.classList.add('selected', 'bg-black', 'text-white');
                
                // Get the size text (first line only)
                const sizeText = this.textContent.trim().split('\n')[0];
                selectedSizeInput.value = sizeText;
                
                // Enable the add to cart button
                addToCartBtn.disabled = false;
            });
        });
    }
    
    // Quantity selector
    const quantityInput = document.querySelector('.quantity-input');
    const minusBtn = document.querySelector('.quantity-minus');
    const plusBtn = document.querySelector('.quantity-plus');
    
    if (quantityInput && minusBtn && plusBtn) {
        minusBtn.addEventListener('click', () => {
            let currentValue = parseInt(quantityInput.value, 10);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
                selectedQuantityInput.value = currentValue - 1;
            }
        });

        plusBtn.addEventListener('click', () => {
            let currentValue = parseInt(quantityInput.value, 10);
            quantityInput.value = currentValue + 1;
            selectedQuantityInput.value = currentValue + 1;
        });
    }
    
    // Form validation
    const cartForm = document.querySelector('form[action="add_to_cart.php"]');
    if (cartForm) {
        cartForm.addEventListener('submit', function(e) {
            if (!selectedSizeInput.value) {
                e.preventDefault();
                
                return false;
            }
        });
    }
    
    // Update cart count
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
    updateCartCount();
}); 