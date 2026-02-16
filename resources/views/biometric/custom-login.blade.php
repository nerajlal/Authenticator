<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            font-size: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
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
            margin-top: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        .hidden {
            display: none;
        }

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .message.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✅</div>
        <h1>Biometric Verified!</h1>
        <p class="subtitle">Click below to complete your Shopify login</p>

        <div class="success-badge">
            🔐 Identity verified via biometric authentication
        </div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" value="{{ $email }}" readonly>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" value="{{ $password }}" readonly>
            </div>

            <div id="message" class="message hidden"></div>

            <button type="submit" class="btn btn-primary" id="loginBtn">
                <span id="btnText">Login to Shopify</span>
                <span id="btnLoading" class="loading hidden"></span>
            </button>
        </form>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');
        const messageEl = document.getElementById('message');

        function showMessage(text, type) {
            messageEl.textContent = text;
            messageEl.className = `message ${type}`;
            messageEl.classList.remove('hidden');
        }

        function hideMessage() {
            messageEl.classList.add('hidden');
        }

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                loginBtn.disabled = true;
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
                hideMessage();

                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;

                const response = await fetch('{{ url("/api/shopify/authenticate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Authentication failed');
                }

                // Success! Redirect to Shopify
                showMessage('Login successful! Redirecting to Shopify...', 'success');
                
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1000);

            } catch (error) {
                console.error('Login error:', error);
                showMessage(error.message, 'error');
                loginBtn.disabled = false;
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
