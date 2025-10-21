<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Variant Removal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        pre { background-color: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .loading { color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Variant Removal Logic</h1>
        <p>This page tests the variant removal functionality for Template ID = 1</p>

        <div class="test-section info">
            <h3>Test Description</h3>
            <p>This test simulates form submission with 3 variants:</p>
            <ul>
                <li><strong>Red/Large</strong> - No 'removed' flag → Should be processed ✅</li>
                <li><strong>Blue/Medium</strong> - Has 'removed' = '1' → Should be skipped 🚫</li>
                <li><strong>Green/Small</strong> - Has 'removed' = '1' → Should be skipped 🚫</li>
            </ul>
            <p><strong>Expected Result:</strong> Only 1 variant processed, 2 variants skipped</p>
        </div>

        <div class="test-section">
            <h3>Run Test</h3>
            <button onclick="runTest()">🚀 Run Variant Removal Test</button>
            <div id="test-result" style="margin-top: 20px;"></div>
        </div>

        <div class="test-section">
            <h3>What This Test Does</h3>
            <ol>
                <li>Finds ProductTemplate with ID = 1</li>
                <li>Simulates form data with mixed removed/non-removed variants</li>
                <li>Applies the same logic as the real update method</li>
                <li>Counts processed vs skipped variants</li>
                <li>Reports success/failure and detailed logs</li>
            </ol>
        </div>
    </div>

    <script>
        async function runTest() {
            const resultDiv = document.getElementById('test-result');
            resultDiv.innerHTML = '<div class="loading">🔄 Running test... Please wait...</div>';

            try {
                const response = await fetch('/test-remove-variant');
                const data = await response.json();

                if (response.ok) {
                    const isSuccess = data.test_results.processed_variants === 1 && 
                                    data.test_results.skipped_variants === 2;
                    
                    resultDiv.innerHTML = `
                        <div class="test-section ${isSuccess ? 'success' : 'error'}">
                            <h3>${isSuccess ? '✅ Test PASSED!' : '❌ Test FAILED!'}</h3>
                            <p><strong>Template:</strong> ${data.template_name} (ID: ${data.template_id})</p>
                            <p><strong>Message:</strong> ${data.message}</p>
                            
                            <h4>Test Results:</h4>
                            <ul>
                                <li>Total variants in request: ${data.test_results.total_variants_in_request}</li>
                                <li>Processed variants: ${data.test_results.processed_variants} (expected: ${data.test_results.expected_processed})</li>
                                <li>Skipped variants: ${data.test_results.skipped_variants} (expected: ${data.test_results.expected_skipped})</li>
                            </ul>
                            
                            <h4>Raw Response:</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                            
                            <p><small>Check Laravel logs for detailed processing information.</small></p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="test-section error">
                            <h3>❌ Error</h3>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="test-section error">
                        <h3>❌ Network Error</h3>
                        <p>Error: ${error.message}</p>
                        <p>Make sure the Laravel server is running and accessible.</p>
                    </div>
                `;
            }
        }

        // Auto-run test on page load
        window.addEventListener('load', () => {
            console.log('Test page loaded. Click the button to run the test.');
        });
    </script>
</body>
</html>
