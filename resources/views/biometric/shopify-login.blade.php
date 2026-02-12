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
            margin-bottom: 20px;
            text-align: left;
        }

        .info-box p {
            color: #2c5282;
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        .info-note {
            background: #fff5e6;
            border: 1px solid #ffd699;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            text-align: left;
        }

        .info-note h3 {
            color: #996600;
            font-size: 15px;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-note p {
            color: #7d6608;
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        .info-note ol {
            color: #7d6608;
            font-size: 14px;
            margin: 8px 0 0 20px;
            line-height: 1.6;
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
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .countdown {
            color: #718096;
            font-size: 14px;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h1>Biometric Verification Complete!</h1>
        <p class="subtitle">You've been successfully verified</p>

        <div class="info-box">
            <p><strong>Verified Account:</strong> {{ $email }}</p>
        </div>

        <div class="info-note">
            <h3>🔐 One More Step</h3>
            <p>You'll be redirected to Shopify. If you're not already logged in, you'll need to enter your password to complete the login.</p>
        </div>

        <a href="{{ $returnUrl }}" class="btn btn-primary" id="continueBtn">
            Continue to Shopify
        </a>

        <p class="countdown">
            Auto-redirecting in <span id="countdown">3</span> seconds...
        </p>
    </div>

    <script>
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');
        const continueBtn = document.getElementById('continueBtn');

        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = '{{ $returnUrl }}';
            }
        }, 1000);

        // Allow manual click to skip countdown
        continueBtn.addEventListener('click', (e) => {
            clearInterval(interval);
        });
    </script>
</body>
</html>
