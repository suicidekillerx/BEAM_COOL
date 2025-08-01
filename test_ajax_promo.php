<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AJAX Promo Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold mb-4">Test AJAX Promo Code</h2>
        
        <div class="mb-4">
            <input type="text" id="promoCodeInput" placeholder="Enter promo code" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-black">
        </div>
        
        <button id="applyPromoBtn" 
                class="w-full px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-black transition-colors">
            Apply
        </button>
        
        <div id="result" class="mt-4 p-4 bg-gray-100 rounded-lg">
            <p>Result will appear here...</p>
        </div>
    </div>
    
    <script>
    function applyPromoCode() {
        const code = document.getElementById('promoCodeInput').value.trim();
        const resultDiv = document.getElementById('result');
        
        if (!code) {
            resultDiv.innerHTML = '<p style="color: red;">Please enter a promo code</p>';
            return;
        }
        
        resultDiv.innerHTML = '<p>Loading...</p>';
        
        const formData = new FormData();
        formData.append('action', 'apply_promo_code');
        formData.append('code', code);
        
        console.log('Sending request to ajax_handler.php');
        console.log('Code:', code);
        
        fetch('ajax_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div style="color: green;">
                            <p><strong>Success!</strong></p>
                            <p>Message: ${data.message}</p>
                            <p>Promo Code: ${data.promo_code.name}</p>
                            <p>Discount: ${data.discount_amount}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div style="color: red;">
                            <p><strong>Error!</strong></p>
                            <p>Message: ${data.message}</p>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                resultDiv.innerHTML = `
                    <div style="color: red;">
                        <p><strong>Error parsing response!</strong></p>
                        <p>Response: ${text}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            resultDiv.innerHTML = `
                <div style="color: red;">
                    <p><strong>Network error!</strong></p>
                    <p>Error: ${error.message}</p>
                </div>
            `;
        });
    }
    
    document.getElementById('applyPromoBtn').addEventListener('click', applyPromoCode);
    </script>
</body>
</html> 