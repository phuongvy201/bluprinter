<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LianLian Pay - Secure Payment</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .payment-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .payment-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .payment-content {
            padding: 40px;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .order-info h3 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .order-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-detail:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 18px;
            color: #667eea;
        }

        .payment-form {
            margin-bottom: 30px;
        }

        .payment-form h3 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .card-logos {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .card-logo {
            width: 40px;
            height: 25px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: 600;
        }

        .visa { background: #1a1f71; }
        .mastercard { background: #eb001b; }
        .amex { background: #006fcf; }

        #llpay-card-element {
            min-height: 280px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            position: relative;
            overflow: hidden;
        }

        #llpay-card-element iframe {
            width: 100% !important;
            height: 280px !important;
            border: none !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 1000 !important;
            background: white !important;
        }

        .loading-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 999;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .security-notice {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 30px;
        }

        .security-notice .icon {
            color: #059669;
            margin-right: 8px;
        }

        .security-notice h4 {
            color: #065f46;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .security-notice p {
            color: #047857;
            font-size: 14px;
        }

        .payment-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 15px 25px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #dc2626;
        }

        .success-message {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #16a34a;
        }

        @media (max-width: 768px) {
            .payment-container {
                margin: 10px;
            }
            
            .payment-content {
                padding: 20px;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .payment-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Header -->
        <div class="payment-header">
            <h1>💳 Secure Payment</h1>
            <p>Complete your payment with LianLian Pay</p>
                </div>
                
        <!-- Content -->
        <div class="payment-content">
            <!-- Order Information -->
            <div class="order-info">
                <h3>📋 Order Summary</h3>
                <div class="order-details">
                    <div class="order-detail">
                        <span>Order ID:</span>
                        <span id="order-id">{{ $orderId ?? 'N/A' }}</span>
                        </div>
                    <div class="order-detail">
                        <span>Amount:</span>
                        <span id="order-amount">${{ number_format($total ?? 0, 2) }}</span>
                        </div>
                    <div class="order-detail">
                        <span>Currency:</span>
                        <span>USD</span>
                        </div>
                    <div class="order-detail">
                        <span>Total:</span>
                        <span id="total-amount">${{ number_format($total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

            <!-- Payment Form -->
            <div class="payment-form">
                <h3>💳 Card Information</h3>
                
                <!-- Card Type Logos -->
                <div class="card-logos">
                    <div class="card-logo visa">VISA</div>
                    <div class="card-logo mastercard">MC</div>
                    <div class="card-logo amex">AMEX</div>
                </div>

                <!-- LianLian Pay Card Container -->
                <div id="llpay-card-element">
                    <div id="loading-placeholder" class="loading-placeholder">
                        <div class="spinner"></div>
                        <p>Loading secure payment form...</p>
                </div>
            </div>
        </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <h4>
                    <span class="icon">🔒</span>
                    Secure Payment
                </h4>
                <p>Your card information is encrypted and secure. We use 256-bit SSL encryption to protect your data.</p>
                </div>
                
            <!-- Error/Success Messages -->
            <div id="error-message" class="error-message" style="display: none;"></div>
            <div id="success-message" class="success-message" style="display: none;"></div>

            <!-- Payment Actions -->
            <div class="payment-actions">
                <button type="button" id="back-btn" class="btn btn-secondary">
                    ← Back to Checkout
                </button>
                <button type="button" id="pay-btn" class="btn btn-primary" disabled>
                    <span id="pay-btn-text">💳 Pay Now</span>
            </button>
        </div>
    </div>
</div>

<script>
        // Global variables
        let lianLianBindingCardInstance = null;
        let iframeToken = null;
        let isProcessing = false;

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Initializing LianLian Pay page...');
            
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            const orderId = urlParams.get('order_id');
            const amount = urlParams.get('amount');
            
            if (token) {
                iframeToken = token;
                initializeLianLianPay();
            } else {
                showError('Missing payment token. Please return to checkout and try again.');
            }
            
            // Set order information if provided
            if (orderId) {
                document.getElementById('order-id').textContent = orderId;
            }
            if (amount) {
                document.getElementById('order-amount').textContent = '$' + parseFloat(amount).toFixed(2);
                document.getElementById('total-amount').textContent = '$' + parseFloat(amount).toFixed(2);
            }
            
            // Event listeners
            document.getElementById('back-btn').addEventListener('click', function() {
                if (confirm('Are you sure you want to go back? Your payment information will be lost.')) {
                    window.history.back();
                }
            });
            
            document.getElementById('pay-btn').addEventListener('click', handlePayment);
        });

        // Initialize LianLian Pay
        const initializeLianLianPay = async () => {
            try {
                console.log('🔧 Initializing LianLian Pay with token:', iframeToken);
                
                // Remove existing script
                const existingScript = document.getElementById('lianlian-sdk');
                if (existingScript) {
                    document.body.removeChild(existingScript);
                }
                
                // Load SDK
                const script = document.createElement('script');
                script.id = 'lianlian-sdk';
                script.src = 'https://secure-checkout.lianlianpay.com/v2/llpay.min.js';
                script.async = true;
                
                script.onload = () => {
                    console.log('✅ SDK loaded successfully');
                    
                    if (!window.LLP) {
                        throw new Error('LianLian Pay SDK failed to load');
                    }
                    
                    // Set language
                    window.LLP.setLanguage('en-US');
                    
                    // Create card element
                    const elements = window.LLP.elements();
                    lianLianBindingCardInstance = elements.create('card', {
                        token: iframeToken,
                        style: {
                            base: {
                                backgroundColor: '#f8f8f8',
                                borderColor: '#f1f1f1',
                                color: '#bcbcbc',
                                fontWeight: '400',
                                fontFamily: 'Roboto, Open Sans, Segoe UI, sans-serif',
                                fontSize: '14px',
                                fontSmoothing: 'antialiased',
                                floatLabelSize: '12px',
                                floatLabelColor: '#333333',
                                floatLabelWeight: '100',
                            },
                        },
                        merchantUrl: window.location.origin,
                    });
                    
                    // Mount the card
                    lianLianBindingCardInstance.mount('#llpay-card-element');
                    
                    // Hide loading placeholder after mount
                    setTimeout(() => {
                        const loadingPlaceholder = document.getElementById('loading-placeholder');
                        if (loadingPlaceholder) {
                            loadingPlaceholder.style.display = 'none';
                        }
                        
                        // Enable pay button
                        document.getElementById('pay-btn').disabled = false;
                        
                        console.log('✅ LianLian Pay initialized successfully');
                    }, 2000);
                };
                
                script.onerror = (error) => {
                    console.error('❌ Failed to load SDK:', error);
                    showError('Failed to load payment system. Please refresh the page and try again.');
                };
                
                document.body.appendChild(script);
                
            } catch (error) {
                console.error('❌ LianLian Pay initialization error:', error);
                showError('Failed to initialize payment system: ' + error.message);
            }
        };

        // Handle payment
        const handlePayment = async () => {
            if (isProcessing) return;
            
            try {
                isProcessing = true;
                showLoading(true);
                
                // Validate card information
                const validateResult = await window.LLP.getValidateResult();
                if (!validateResult || !validateResult.validateResult) {
                    showError('Please fill in all card information correctly.');
                    return;
                }
                
                // Confirm payment
                const paymentResult = await window.LLP.confirmPay();
                console.log('Payment result:', paymentResult);
                
                if (paymentResult && paymentResult.data) {
                    const cardToken = paymentResult.data;
                    console.log('✅ Card token generated:', cardToken);
                    
                    // Send payment to server
                    await processPayment(cardToken);
                } else {
                    showError('Payment failed: ' + (paymentResult.message || 'Card processing failed'));
                }
                
            } catch (error) {
                console.error('Payment error:', error);
                showError('Payment failed: ' + error.message);
            } finally {
                isProcessing = false;
                showLoading(false);
            }
        };

        // Process payment with server
        const processPayment = async (cardToken) => {
            try {
                const paymentData = {
                    card_token: cardToken,
                    payment_method: 'lianlian_pay',
                    order_id: new URLSearchParams(window.location.search).get('order_id'),
                    amount: new URLSearchParams(window.location.search).get('amount'),
                };
                
                console.log('📤 Sending payment data:', paymentData);
                
                const response = await fetch('{{ route("payment.lianlian.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(paymentData)
                });
                
                const responseData = await response.json();
                console.log('📥 Payment response:', responseData);
                
                if (responseData.success) {
                    // Check if 3DS authentication is required
                    if (responseData.requires_3ds === true && responseData.redirect_url) {
                        console.log('🔐 3DS Authentication Required');
                        await handle3DSRedirect(responseData.redirect_url, responseData.transaction_id);
                        return;
                    }
                    
                    // Check if payment is completed immediately
                    if (responseData.payment_completed === true || responseData.payment_status === 'paid') {
                        console.log('✅ Payment Completed Immediately');
                        showSuccess('Payment successful! Redirecting to confirmation page...');
                        setTimeout(() => {
                            if (responseData.order_number) {
                                window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                            } else {
                                window.location.href = '{{ route("checkout.index") }}';
                            }
                        }, 2000);
                    } else {
                        // Payment pending - still redirect to success page
                        console.log('⏳ Payment Pending');
                        showSuccess('Payment is processing. Redirecting...');
                        setTimeout(() => {
                            if (responseData.order_number) {
                                window.location.href = '{{ route("checkout.success", ":order_number") }}'.replace(':order_number', responseData.order_number);
                            } else {
                                window.location.href = '{{ route("checkout.index") }}';
                            }
                        }, 2000);
                    }
                } else {
                    const errorMessage = responseData.message || 'Payment failed';
                    const errorCode = responseData.return_code || 'Unknown error';
                    showError(`Payment failed (${errorCode}): ${errorMessage}`);
                }
                
            } catch (error) {
                console.error('Server payment error:', error);
                showError('Payment processing failed: ' + error.message);
            }
        };

        // Handle 3DS redirect
        const handle3DSRedirect = async (redirectUrl, transactionId) => {
            const { value: confirmRedirect } = await Swal.fire({
                title: '3DS Authentication Required',
                html: `
                    <div style="text-align: left; margin: 20px 0;">
                        <p style="margin-bottom: 15px; color: #333;">
                            <i class="fas fa-shield-alt" style="color: #007bff; margin-right: 8px;"></i>
                            Your bank requires additional verification for this payment.
                        </p>
                        <p style="margin-bottom: 15px; color: #666;">
                            You will be redirected to your bank's secure page to complete the verification process.
                        </p>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;">
                            <p style="margin: 0; font-weight: 500; color: #495057;">
                                <i class="fas fa-info-circle" style="color: #007bff; margin-right: 5px;"></i>
                                Please complete the verification and you will be redirected back automatically.
                            </p>
                        </div>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-external-link-alt"></i> Continue to Bank',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                width: '500px'
            });
            
            if (confirmRedirect) {
                sessionStorage.setItem('pending_3ds_transaction', JSON.stringify({
                    transaction_id: transactionId,
                    timestamp: Date.now(),
                    redirect_url: redirectUrl
                }));
                
                showSuccess('Redirecting to 3DS Authentication...');
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1500);
            }
        };

        // Utility functions
        const showLoading = (loading) => {
            const payBtn = document.getElementById('pay-btn');
            const payBtnText = document.getElementById('pay-btn-text');
            
            if (loading) {
                payBtn.disabled = true;
                payBtnText.innerHTML = '<div class="spinner" style="width: 20px; height: 20px; border-width: 2px; margin-right: 8px;"></div> Processing...';
            } else {
                payBtn.disabled = false;
                payBtnText.textContent = '💳 Pay Now';
            }
        };

        const showError = (message) => {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // Hide success message if shown
            document.getElementById('success-message').style.display = 'none';
            
            // Scroll to error
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        };

        const showSuccess = (message) => {
            const successDiv = document.getElementById('success-message');
            successDiv.textContent = message;
            successDiv.style.display = 'block';
            
            // Hide error message if shown
            document.getElementById('error-message').style.display = 'none';
            
            // Scroll to success
            successDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        };

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (lianLianBindingCardInstance) {
                lianLianBindingCardInstance.unmount();
            }
        });
    </script>
</body>
</html>