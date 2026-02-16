<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Biometric Login - {{ config('app.name') }}</title>
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

        .icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
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

        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .message-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        .message-info {
            background: #bee3f8;
            color: #2c5282;
            border: 1px solid #90cdf4;
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

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(102, 126, 234, 0.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">👆</div>
        <h1>Biometric Login</h1>
        <p class="subtitle">Authenticate with your fingerprint or Face ID</p>

        <div id="loading" class="loading"></div>
        <div id="message" class="message message-info">
            Please authenticate with your biometric device...
        </div>

        <button id="retryBtn" class="btn btn-secondary hidden">Try Again</button>
        <button id="cancelBtn" class="btn btn-secondary">Use Email/Password Instead</button>
    </div>

    <script>
        // Configuration
        const returnUrl = @json($returnUrl);
        const apiBase = '{{ url('/api/biometric') }}';

        // Elements
        const loading = document.getElementById('loading');
        const message = document.getElementById('message');
        const retryBtn = document.getElementById('retryBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        // Auto-start authentication
        window.addEventListener('load', () => {
            setTimeout(authenticate, 500);
        });

        // Retry button
        retryBtn.addEventListener('click', authenticate);

        // Cancel button
        cancelBtn.addEventListener('click', () => {
            // Extract base URL from return URL
            const url = new URL(returnUrl);
            window.location.href = url.origin + '/account/login';
        });

        async function authenticate() {
            try {
                loading.classList.remove('hidden');
                retryBtn.classList.add('hidden');
                showMessage('Please authenticate with your biometric device...', 'info');

                // Request login options
                const optionsResponse = await fetch(apiBase + '/login-options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });

                if (!optionsResponse.ok) {
                    const error = await optionsResponse.json();
                    throw new Error(error.message || 'Failed to get login options');
                }

                const { options } = await optionsResponse.json();

                // Prepare WebAuthn options
                const publicKeyOptions = {
                    ...options,
                    challenge: Uint8Array.from(atob(options.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0)),
                    allowCredentials: options.allowCredentials.map(cred => ({
                        ...cred,
                        id: Uint8Array.from(atob(cred.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0))
                    }))
                };

                // Trigger biometric prompt
                const assertion = await navigator.credentials.get({
                    publicKey: publicKeyOptions
                });

                // Prepare assertion data
                const assertionData = {
                    id: assertion.id,
                    rawId: btoa(String.fromCharCode(...new Uint8Array(assertion.rawId))),
                    type: assertion.type,
                    response: {
                        clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(assertion.response.clientDataJSON))),
                        authenticatorData: btoa(String.fromCharCode(...new Uint8Array(assertion.response.authenticatorData))),
                        signature: btoa(String.fromCharCode(...new Uint8Array(assertion.response.signature))),
                        userHandle: assertion.response.userHandle ? btoa(String.fromCharCode(...new Uint8Array(assertion.response.userHandle))) : null
                    }
                };

                // Verify assertion
                const verifyResponse = await fetch(apiBase + '/login-verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify(assertionData)
                });

                if (!verifyResponse.ok) {
                    const errorData = await verifyResponse.json();
                    throw new Error(errorData.message || 'Authentication failed');
                }

                const result = await verifyResponse.json();

                // Success! Redirect to custom login page with pre-filled credentials
                if (result.shopify_password) {
                    showMessage('Authentication successful! Preparing login...', 'success');
                    
                    // Extract shop domain from return URL
                    const returnUrl = new URL(@json($returnUrl));
                    const shopDomain = returnUrl.hostname;
                    
                    // Redirect to custom login page with credentials
                    const loginUrl = new URL('{{ url("/shopify/login") }}');
                    loginUrl.searchParams.set('email', result.email);
                    loginUrl.searchParams.set('password', result.shopify_password);
                    loginUrl.searchParams.set('return_url', `https://${shopDomain}/account`);
                    
                    setTimeout(() => {
                        window.location.href = loginUrl.toString();
                    }, 1000);
                } else {
                    // No password stored, redirect to login page
                    showMessage('Authentication successful! Redirecting to Shopify...', 'success');
                    
                    const returnUrl = new URL(@json($returnUrl));
                    const shopDomain = returnUrl.hostname;
                    
                    setTimeout(() => {
                        window.location.href = `https://${shopDomain}/account/login`;
                    }, 1000);
                }

            } catch (error) {
                console.error('Authentication error:', error);
                loading.classList.add('hidden');
                retryBtn.classList.remove('hidden');
                showMessage('Authentication failed: ' + error.message, 'error');
            }
        }

        function showMessage(text, type) {
            message.textContent = text;
            message.className = 'message message-' + type;
        }
    </script>
</body>
</html>
