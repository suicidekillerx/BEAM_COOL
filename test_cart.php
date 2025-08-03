<!DOCTYPE html>
<html>
<head>
    <title>Cart Handler Test</title>
</head>
<body>
    <h1>Cart Handler Test</h1>
    
    <button onclick="testCart()">Test Cart Handler</button>
    <div id="result"></div>
    
    <script>
    function testCart() {
        const formData = new FormData();
        formData.append('action', 'test');
        
        fetch('cart_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response:', text);
            document.getElementById('result').innerHTML = '<pre>' + text + '</pre>';
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('result').innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
        });
    }
    </script>
</body>
</html> 