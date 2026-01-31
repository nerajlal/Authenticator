<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biometric Authentication</title>
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@12.0.0/build/esm/styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
            background: #f6f6f7;
        }
        .Polaris-Page {
            padding: 2rem;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #008060;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .feature-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .feature-card {
            background: white;
            border: 1px solid #e1e3e5;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .feature-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 14px;
            font-weight: 600;
            color: #202223;
        }
        .feature-card p {
            margin: 0;
            font-size: 13px;
            color: #6d7175;
            line-height: 1.5;
        }
        .api-endpoint {
            background: #f6f6f7;
            border: 1px solid #e1e3e5;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            font-family: Monaco, Consolas, monospace;
            font-size: 12px;
        }
        .method-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 8px;
        }
        .method-post { background: #e3f1df; color: #108043; }
        .method-get { background: #e0f5fa; color: #006fbb; }
        .method-delete { background: #fbeae5; color: #d72c0d; }
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="Polaris-Page">
        <!-- Header -->
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #5c6ac4 0%, #4959bd 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" fill="white" viewBox="0 0 20 20">
                        <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #202223;">Biometric Authentication</h1>
                    <p style="margin: 0.25rem 0 0 0; font-size: 14px; color: #6d7175;">Secure fingerprint and Face ID login for your store</p>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div style="background: white; border: 1px solid #e1e3e5; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0; font-size: 16px; font-weight: 600; color: #202223;">System Status</h2>
                <span class="status-badge" style="background: #e3f1df; color: #108043; padding: 4px 12px; border-radius: 6px; font-size: 13px; font-weight: 500;">
                    <span class="status-dot"></span>
                    Active
                </span>
            </div>
            
            <div class="grid-3">
                <div style="text-align: center; padding: 1rem; background: #f6f6f7; border-radius: 8px;">
                    <div style="font-size: 28px; margin-bottom: 0.5rem;">✓</div>
                    <div style="font-size: 13px; font-weight: 600; color: #202223; margin-bottom: 0.25rem;">Laravel Backend</div>
                    <div style="font-size: 12px; color: #6d7175;">v{{ app()->version() }}</div>
                </div>
                
                <div style="text-align: center; padding: 1rem; background: #f6f6f7; border-radius: 8px;">
                    <div style="font-size: 28px; margin-bottom: 0.5rem;">✓</div>
                    <div style="font-size: 13px; font-weight: 600; color: #202223; margin-bottom: 0.25rem;">Database Connected</div>
                    <div style="font-size: 12px; color: #6d7175;">MySQL Ready</div>
                </div>
                
                <div style="text-align: center; padding: 1rem; background: #f6f6f7; border-radius: 8px;">
                    <div style="font-size: 28px; margin-bottom: 0.5rem;">✓</div>
                    <div style="font-size: 13px; font-weight: 600; color: #202223; margin-bottom: 0.25rem;">Shopify Embedded</div>
                    <div style="font-size: 12px; color: #6d7175;">Ready to Use</div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div style="margin-bottom: 2rem;">
            <h2 style="margin: 0 0 1rem 0; font-size: 16px; font-weight: 600; color: #202223;">Features</h2>
            
            <div class="grid-2">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #e0f5fa; color: #006fbb;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.625 2.655A9 9 0 0119 11a1 1 0 11-2 0 7 7 0 00-9.625-6.492 1 1 0 11-.75-1.853zM4.662 4.959A1 1 0 014.75 6.37 6.97 6.97 0 003 11a1 1 0 11-2 0 8.97 8.97 0 012.25-5.953 1 1 0 011.412-.088z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M5 11a5 5 0 1110 0 1 1 0 11-2 0 3 3 0 10-6 0c0 1.677-.345 3.276-.968 4.729a1 1 0 11-1.838-.789A9.964 9.964 0 005 11z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3>Fingerprint Login</h3>
                    <p>Secure authentication using device fingerprint sensors with WebAuthn technology</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: #f1e0ff; color: #6d28d9;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3>Face ID Support</h3>
                    <p>Fast facial recognition authentication on supported iOS and Android devices</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: #e3f1df; color: #108043;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3>WebAuthn Security</h3>
                    <p>Industry-standard FIDO2 authentication protocol with hardware-backed security</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: #fff4e6; color: #f59e0b;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3>Lightning Fast</h3>
                    <p>One-click login experience without typing passwords or remembering credentials</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: #fee2e2; color: #dc2626;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3>Cross-Device</h3>
                    <p>Works seamlessly on mobile phones, tablets, and desktop computers</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background: #e0e7ff; color: #4f46e5;">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3>Fallback Support</h3>
                    <p>Traditional password login always available as a backup option</p>
                </div>
            </div>
        </div>

        <!-- API Endpoints -->
        <div style="background: white; border: 1px solid #e1e3e5; border-radius: 12px; padding: 1.5rem;">
            <h2 style="margin: 0 0 1rem 0; font-size: 16px; font-weight: 600; color: #202223;">API Endpoints</h2>
            
            <div class="api-endpoint">
                <span class="method-badge method-post">POST</span>
                <code>/api/biometric/register-options</code>
                <div style="margin-top: 0.5rem; color: #6d7175; font-size: 11px;">Get registration challenge</div>
            </div>
            
            <div class="api-endpoint">
                <span class="method-badge method-post">POST</span>
                <code>/api/biometric/register-verify</code>
                <div style="margin-top: 0.5rem; color: #6d7175; font-size: 11px;">Verify and store credential</div>
            </div>
            
            <div class="api-endpoint">
                <span class="method-badge method-post">POST</span>
                <code>/api/biometric/login-options</code>
                <div style="margin-top: 0.5rem; color: #6d7175; font-size: 11px;">Get authentication challenge</div>
            </div>
            
            <div class="api-endpoint">
                <span class="method-badge method-post">POST</span>
                <code>/api/biometric/login-verify</code>
                <div style="margin-top: 0.5rem; color: #6d7175; font-size: 11px;">Verify and login user</div>
            </div>
            
            <div class="api-endpoint">
                <span class="method-badge method-get">GET</span>
                <code>/api/biometric/credentials</code>
                <div style="margin-top: 0.5rem; color: #6d7175; font-size: 11px;">List user's credentials</div>
            </div>
            
            <div class="api-endpoint" style="margin-bottom: 0;">
                <span class="method-badge method-delete">DELETE</span>
                <code>/api/biometric/credentials/{id}</code>
                <div style="margin-top: 0.5rem; color: #6d7175; font-size: 11px;">Delete credential</div>
            </div>
        </div>

        <!-- Footer -->
        <div style="margin-top: 2rem; text-align: center; color: #6d7175; font-size: 12px;">
            Powered by Laravel {{ app()->version() }} • WebAuthn/FIDO2 • Shopify Integration
        </div>
    </div>
</body>
</html>
