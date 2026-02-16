<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Login - {{ config('app.name') }}</title>
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
            padding: 40px;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }

        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        h1 {
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 12px;
        }

        .subtitle {
            color: #718096;
            font-size: 16px;
            margin-bottom: 32px;
        }

        .success-badge {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .email-display {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .email-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .email-value {
            font-size: 18px;
            color: #2d3748;
            font-weight: 600;
            word-break: break-all;
        }

        .info-note {
            background: #fff5e6;
            border: 1px solid #ffd699;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }

        .info-note p {
            color: #7d6608;
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .countdown {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✅</div>
        <h1>Identity Verified!</h1>
        <p class="subtitle">Complete your login to Shopify</p>

        <div class="success-badge">
            🔐 Biometric authentication successful
        </div>

        <div class="email-display">
            <div class="email-label">Your Email</div>
            <div class="email-value">{{ $email }}</div>
        </div>

        <div class="info-note">
            <p>
                <strong>Next Step:</strong> You'll be redirected to Shopify to complete your login. 
                Use the email shown above and enter your password.
            </p>
        </div>

        <button class="btn btn-primary" onclick="redirectToShopify()">
            Continue to Shopify Login
        </button>

        <div class="countdown" id="countdown">
            Auto-redirecting in <span id="seconds">5</span> seconds...
        </div>
    </div>

    <script>
        const shopifyUrl = @json($shopifyLoginUrl);
        let secondsLeft = 5;

        // Countdown timer
        const countdownInterval = setInterval(() => {
            secondsLeft--;
            document.getElementById('seconds').textContent = secondsLeft;
            
            if (secondsLeft <= 0) {
                clearInterval(countdownInterval);
                redirectToShopify();
            }
        }, 1000);

        function redirectToShopify() {
            clearInterval(countdownInterval);
            window.location.href = shopifyUrl;
        }
    </script>
</body>
</html>
