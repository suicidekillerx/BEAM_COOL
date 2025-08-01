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
                if (selectedQuantityInput) {
                    selectedQuantityInput.value = currentValue - 1;
                }
            }
        });

        plusBtn.addEventListener('click', () => {
            let currentValue = parseInt(quantityInput.value, 10);
            quantityInput.value = currentValue + 1;
            if (selectedQuantityInput) {
                selectedQuantityInput.value = currentValue + 1;
            }
        });
    }
    
    // Form validation
    const cartForm = document.querySelector('form[action="add_to_cart.php"]');
    if (cartForm) {
        cartForm.addEventListener('submit', function(e) {
            if (!selectedSizeInput || !selectedSizeInput.value) {
                e.preventDefault();
              
                return false;
            }
        });
    }
    
    // Remove old AJAX event listeners
    const oldAddToCartBtn = document.querySelector('.add-to-cart-btn');
    if (oldAddToCartBtn) {
        // Remove any existing event listeners
        const newBtn = oldAddToCartBtn.cloneNode(true);
        oldAddToCartBtn.parentNode.replaceChild(newBtn, oldAddToCartBtn);
    }
}); 