<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enable Biometric Login - {{ config('app.name') }}</title>
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

        .user-info {
            background: #f7fafc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 32px;
            text-align: left;
        }

        .user-info-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .user-info-value {
            font-size: 16px;
            color: #2d3748;
            font-weight: 500;
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

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
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

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
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
        <div class="icon">🔐</div>
        <h1>Enable Biometric Login</h1>
        <p class="subtitle">Secure your Shopify account with fingerprint or Face ID</p>

        <div class="user-info">
            <div class="user-info-label">Account</div>
            <div class="user-info-value">{{ $user->email }}</div>
        </div>

        <div id="message" class="message hidden"></div>

        <button id="enrollBtn" class="btn btn-primary">
            <span id="btnText">Enable Biometric Login</span>
            <span id="btnLoading" class="loading hidden"></span>
        </button>

        <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
    </div>

    <script>
        // Configuration
        const returnUrl = @json($returnUrl);
        const apiBase = '{{ url('/api/biometric') }}';

        // Elements
        const enrollBtn = document.getElementById('enrollBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const message = document.getElementById('message');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');

        // Cancel button
        cancelBtn.addEventListener('click', () => {
            window.location.href = returnUrl;
        });

        // Enroll button
        enrollBtn.addEventListener('click', async () => {
            try {
                enrollBtn.disabled = true;
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
                hideMessage();

                // Request registration options
                const optionsResponse = await fetch(apiBase + '/register-options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include'
                });

                if (!optionsResponse.ok) {
                    throw new Error('Failed to get registration options');
                }

                const { options } = await optionsResponse.json();

                // Prepare WebAuthn options
                const publicKeyOptions = {
                    ...options,
                    challenge: Uint8Array.from(atob(options.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0)),
                    user: {
                        ...options.user,
                        id: Uint8Array.from(atob(options.user.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0))
                    },
                    // Convert excludeCredentials IDs to ArrayBuffer
                    excludeCredentials: (options.excludeCredentials || []).map(cred => ({
                        ...cred,
                        id: Uint8Array.from(atob(cred.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0))
                    }))
                };

                // Trigger biometric prompt
                const credential = await navigator.credentials.create({
                    publicKey: publicKeyOptions
                });

                // Prepare credential data
                const credentialData = {
                    id: credential.id,
                    rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
                    type: credential.type,
                    response: {
                        clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
                        attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject)))
                    }
                };

                // Verify credential
                const verifyResponse = await fetch(apiBase + '/register-verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'include',
                    body: JSON.stringify(credentialData)
                });

                if (!verifyResponse.ok) {
                    throw new Error('Failed to verify credential');
                }

                // Success!
                showMessage('Biometric login enabled successfully! Redirecting...', 'success');
                
                setTimeout(() => {
                    window.location.href = returnUrl;
                }, 2000);

            } catch (error) {
                console.error('Enrollment error:', error);
                showMessage('Failed to enable biometric login: ' + error.message, 'error');
                enrollBtn.disabled = false;
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            }
        });

        function showMessage(text, type) {
            message.textContent = text;
            message.className = 'message message-' + type;
            message.classList.remove('hidden');
        }

        function hideMessage() {
            message.classList.add('hidden');
        }
    </script>
</body>
</html>
