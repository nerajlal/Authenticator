<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging In - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 12px;
        }

        .subtitle {
            font-size: 16px;
            color: #718096;
            margin-bottom: 32px;
        }

        .btn {
            width: 100%;
            padding: 16px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 12px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .loading-dots {
            display: inline-block;
            margin-left: 8px;
        }

        .loading-dots span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #667eea;
            margin: 0 2px;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .loading-dots span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }

        .info-box {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }

        .info-box p {
            color: #2c5282;
            font-size: 14px;
            margin: 0;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Authentication Successful!</h1>
        <p class="subtitle">You've been verified with biometric authentication</p>

        <div class="info-box">
            <p><strong>Account:</strong> {{ $email }}</p>
        </div>

        <button id="loginBtn" class="btn btn-primary" onclick="submitLogin()">
            <span id="btnText">Continue to Your Account</span>
            <span id="btnLoading" class="loading-dots hidden">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

        <!-- Hidden form that will submit to Shopify -->
        <form id="shopifyLoginForm" method="post" action="{{ $shopifyLoginUrl }}" style="display: none;">
            <input type="hidden" name="form_type" value="customer_login">
            <input type="hidden" name="utf8" value="✓">
            <input type="hidden" name="customer[email]" value="{{ $email }}">
            <input type="hidden" name="customer[password]" value="" id="passwordField">
            <input type="hidden" name="return_url" value="/account">
        </form>

        <p style="margin-top: 20px; color: #718096; font-size: 14px;">
            Click the button above to complete your login to Shopify
        </p>
    </div>

    <script>
        const shopifyDomain = @json($shopifyDomain);
        const email = @json($email);

        function submitLogin() {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            // Update button state
            loginBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');

            // Submit the form
            // The browser's password manager will auto-fill the password if saved
            document.getElementById('shopifyLoginForm').submit();
        }

        // Auto-submit after 2 seconds
        setTimeout(function() {
            submitLogin();
        }, 2000);
    </script>
</body>
</html>
