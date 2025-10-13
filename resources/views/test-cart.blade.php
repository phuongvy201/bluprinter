<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Cart API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test Cart API for Guest Users</h1>
    
    <div>
        <h2>Test Data:</h2>
        <p><strong>Product ID:</strong> 1</p>
        <p><strong>Quantity:</strong> 1</p>
        <p><strong>Price:</strong> $29.99</p>
        <p><strong>Session ID:</strong> <span id="session-id">{{ session()->getId() }}</span></p>
    </div>
    
    <div>
        <button onclick="testAddToCart()">Test Add to Cart</button>
        <button onclick="testGetCart()">Test Get Cart</button>
        <button onclick="testClearCart()">Test Clear Cart</button>
    </div>
    
    <div id="results"></div>
    
    <script>
        function showResult(message, isSuccess = true) {
            const resultsDiv = document.getElementById('results');
            const resultDiv = document.createElement('div');
            resultDiv.className = `result ${isSuccess ? 'success' : 'error'}`;
            resultDiv.innerHTML = `<strong>${new Date().toLocaleTimeString()}:</strong> ${message}`;
            resultsDiv.appendChild(resultDiv);
        }
        
        function testAddToCart() {
            const testData = {
                id: 1,
                quantity: 1,
                price: 29.99,
                selectedVariant: {
                    id: 1,
                    attributes: { Color: 'Black', Size: 'M' }
                },
                customizations: []
            };
            
            fetch('/api/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(testData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(`✅ Add to Cart Success: ${data.message}`, true);
                    console.log('Cart item:', data.cart_item);
                } else {
                    showResult(`❌ Add to Cart Failed: ${data.message}`, false);
                }
            })
            .catch(error => {
                showResult(`❌ Network Error: ${error.message}`, false);
                console.error('Error:', error);
            });
        }
        
        function testGetCart() {
            fetch('/api/cart/get', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(`✅ Get Cart Success: ${data.total_items} items, Total: $${data.total_price}`, true);
                    console.log('Cart items:', data.cart_items);
                } else {
                    showResult(`❌ Get Cart Failed: ${data.message}`, false);
                }
            })
            .catch(error => {
                showResult(`❌ Network Error: ${error.message}`, false);
                console.error('Error:', error);
            });
        }
        
        function testClearCart() {
            fetch('/api/cart/clear', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(`✅ Clear Cart Success: ${data.message}`, true);
                } else {
                    showResult(`❌ Clear Cart Failed: ${data.message}`, false);
                }
            })
            .catch(error => {
                showResult(`❌ Network Error: ${error.message}`, false);
                console.error('Error:', error);
            });
        }
        
        // Show current session info
        showResult(`Session ID: ${document.getElementById('session-id').textContent}`, true);
    </script>
</body>
</html>
