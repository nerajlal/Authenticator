<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Successful - {{ config('app.name') }}</title>
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

        .info-note {
            background: #fef5e7;
            border: 1px solid #f9e79f;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 24px;
        }

        .info-note p {
            color: #7d6608;
            font-size: 13px;
            margin: 0;
        }

        .loading-text {
            color: #718096;
            font-size: 14px;
            margin-top: 20px;
        }

        .loading-dots {
            display: inline-block;
            margin-left: 8px;
        }

        .loading-dots span {
            display: inline-block;
            width: 6px;
            height: 6px;
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

        <div class="info-note">
            <p>💡 <strong>One more step:</strong> You'll be redirected to Shopify to complete your login. Your email will be pre-filled.</p>
        </div>

        <p class="loading-text">
            Redirecting to Shopify
            <span class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </p>
    </div>

    <script>
        // Redirect to Shopify login page after 2 seconds
        setTimeout(function() {
            window.location.href = '{{ $returnUrl }}';
        }, 2000);
    </script>
</body>
</html>
