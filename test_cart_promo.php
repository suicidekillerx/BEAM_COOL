<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Cart Promo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold mb-4">Test Promo Code</h2>
        
        <div class="mb-4">
            <input type="text" id="promoCodeInput" placeholder="Enter promo code" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
        </div>
        
        <button id="applyPromoBtn" 
                class="w-full px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-black transition-colors">
            Apply
        </button>
        
        <div id="promoCodeMessage" class="mt-2 text-sm"></div>
        
        <div id="appliedPromoCode" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-semibold text-green-800" id="appliedPromoName"></span>
                    <span class="text-green-600 text-sm ml-2" id="appliedPromoDiscount"></span>
                </div>
                <button onclick="removePromoCode()" class="text-green-600 hover:text-green-800">
                    ✕
                </button>
            </div>
        </div>
    </div>
    
    <script>
    function applyPromoCode() {
        console.log('applyPromoCode called');
        const code = document.getElementById('promoCodeInput').value.trim();
        console.log('Promo code:', code);
        
        if (!code) {
            showMessage('Please enter a promo code', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'apply_promo_code');
        formData.append('code', code);
        
        console.log('Sending request to ajax_handler.php');
        
        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                showAppliedPromoCode(data.promo_code, data.discount_amount);
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error applying promo code:', error);
            showMessage('Network error. Please try again.', 'error');
        });
    }
    
    function removePromoCode() {
        const formData = new FormData();
        formData.append('action', 'remove_promo_code');
        
        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideAppliedPromoCode();
                showMessage(data.message, 'success');
            }
        })
        .catch(error => {
            console.error('Error removing promo code:', error);
            showMessage('Network error. Please try again.', 'error');
        });
    }
    
    function showAppliedPromoCode(promoCode, discountAmount) {
        document.getElementById('appliedPromoCode').classList.remove('hidden');
        document.getElementById('appliedPromoName').textContent = promoCode.name;
        document.getElementById('appliedPromoDiscount').textContent = `-${formatPrice(discountAmount)}`;
        document.getElementById('promoCodeInput').value = '';
        document.getElementById('promoCodeInput').disabled = true;
        document.getElementById('applyPromoBtn').disabled = true;
    }
    
    function hideAppliedPromoCode() {
        document.getElementById('appliedPromoCode').classList.add('hidden');
        document.getElementById('promoCodeInput').disabled = false;
        document.getElementById('applyPromoBtn').disabled = false;
    }
    
    function showMessage(message, type) {
        const messageDiv = document.getElementById('promoCodeMessage');
        messageDiv.textContent = message;
        messageDiv.className = `mt-2 text-sm ${type === 'success' ? 'text-green-600' : 'text-red-600'}`;
    }
    
    function formatPrice(amount) {
        return amount.toFixed(3) + ' DTN';
    }
    
    // Add event listener
    document.getElementById('applyPromoBtn').addEventListener('click', applyPromoCode);
    </script>
</body>
</html> 