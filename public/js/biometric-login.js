/**
 * Biometric Authentication - Standalone JavaScript
 * Injects biometric login capabilities into existing login forms
 * Uses WebAuthn for fingerprint and Face ID authentication
 */

(function () {
    'use strict';

    // Configuration
    const CONFIG = {
        apiBase: window.location.origin + '/api/biometric',
        debug: true
    };

    // Shopify Configuration (set by theme)
    const SHOPIFY_CONFIG = {
        customerId: window.shopifyCustomerId || null,
        customerEmail: window.shopifyCustomerEmail || null,
        isShopify: window.isShopifyStore || false
    };

    // Utility: Log debug messages
    function log(...args) {
        if (CONFIG.debug) {
            console.log('[Biometric Auth]', ...args);
        }
    }

    // DEBUG: Confirm script load
    console.log('Biometric Script Loaded');
    // alert('Biometric Script Loaded'); // Uncomment if console is hard to check

    // Utility: Get CSRF token
    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.content : '';
    }

    // Utility: Make API request
    async function apiRequest(endpoint, data = {}) {
        try {
            // Add Shopify customer info if available
            if (SHOPIFY_CONFIG.customerId) {
                data.shopify_customer_id = SHOPIFY_CONFIG.customerId;
            }
            if (SHOPIFY_CONFIG.customerEmail && !data.email) {
                data.email = SHOPIFY_CONFIG.customerEmail;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            // Only add CSRF token if it exists (not needed for Shopify)
            const csrfToken = getCsrfToken();
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(CONFIG.apiBase + endpoint, {
                method: 'POST',
                headers: headers,
                credentials: 'include',
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'API request failed');
            }

            return await response.json();
        } catch (error) {
            log('API Error:', error);
            throw error;
        }
    }

    // Check if WebAuthn is supported
    function isWebAuthnSupported() {
        return window.PublicKeyCredential !== undefined &&
            navigator.credentials !== undefined;
    }

    // Detect device capability and return appropriate text
    function getDeviceCapabilityText() {
        const userAgent = navigator.userAgent.toLowerCase();

        if (/iphone|ipad|ipod/.test(userAgent)) {
            // iOS devices - could be Face ID or Touch ID
            return {
                button: 'Login with Face ID or Touch ID',
                icon: '👤',
                enrollment: 'Would you like to enable Face ID or Touch ID for faster login next time?'
            };
        } else if (/android/.test(userAgent)) {
            return {
                button: 'Login with Fingerprint',
                icon: '👆',
                enrollment: 'Would you like to enable fingerprint login for faster access next time?'
            };
        } else if (/mac/.test(userAgent)) {
            return {
                button: 'Login with Touch ID',
                icon: '👆',
                enrollment: 'Would you like to enable Touch ID for faster login next time?'
            };
        } else if (/windows/.test(userAgent)) {
            return {
                button: 'Login with Windows Hello',
                icon: '🔐',
                enrollment: 'Would you like to enable Windows Hello for faster login next time?'
            };
        }

        return {
            button: 'Login with Biometric',
            icon: '🔐',
            enrollment: 'Would you like to enable biometric authentication for faster login next time?'
        };
    }

    // Base64 URL encoding/decoding helpers
    function base64UrlEncode(buffer) {
        const base64 = btoa(String.fromCharCode(...new Uint8Array(buffer)));
        return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
    }

    function base64UrlDecode(base64url) {
        const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    }

    // Convert API response to WebAuthn format
    function preparePublicKeyOptions(options) {
        if (options.challenge) {
            options.challenge = base64UrlDecode(options.challenge);
        }
        if (options.user && options.user.id) {
            options.user.id = base64UrlDecode(options.user.id);
        }
        if (options.excludeCredentials) {
            options.excludeCredentials = options.excludeCredentials.map(cred => ({
                ...cred,
                id: base64UrlDecode(cred.id)
            }));
        }
        if (options.allowCredentials) {
            options.allowCredentials = options.allowCredentials.map(cred => ({
                ...cred,
                id: base64UrlDecode(cred.id)
            }));
        }
        return options;
    }

    // Convert WebAuthn credential to API format
    function prepareCredentialForApi(credential) {
        return {
            id: credential.id,
            rawId: base64UrlEncode(credential.rawId),
            type: credential.type,
            response: {
                clientDataJSON: base64UrlEncode(credential.response.clientDataJSON),
                attestationObject: credential.response.attestationObject ?
                    base64UrlEncode(credential.response.attestationObject) : undefined,
                authenticatorData: credential.response.authenticatorData ?
                    base64UrlEncode(credential.response.authenticatorData) : undefined,
                signature: credential.response.signature ?
                    base64UrlEncode(credential.response.signature) : undefined,
                userHandle: credential.response.userHandle ?
                    base64UrlEncode(credential.response.userHandle) : undefined
            }
        };
    }

    // Biometric Login Handler
    async function handleBiometricLogin(button) {
        const originalText = button.textContent;

        try {
            // Show loading state
            button.disabled = true;
            button.textContent = '🔄 Authenticating...';

            log('Requesting login options...');

            // Get authentication options from server
            const { options } = await apiRequest('/login-options');

            log('Login options received:', options);

            // Prepare options for WebAuthn
            const publicKeyOptions = preparePublicKeyOptions(options);

            log('Starting WebAuthn authentication...');

            // Trigger device biometric prompt
            const credential = await navigator.credentials.get({
                publicKey: publicKeyOptions
            });

            log('Credential received:', credential);

            // Prepare credential for API
            const credentialData = prepareCredentialForApi(credential);

            log('Verifying credential...');

            // Verify with server
            const result = await apiRequest('/login-verify', credentialData);

            log('Login successful!', result);

            // Show success message
            button.textContent = '✓ Success!';
            button.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';

            // Redirect to dashboard or reload
            setTimeout(() => {
                if (result.redirect) {
                    window.location.href = result.redirect;
                } else {
                    window.location.reload();
                }
            }, 500);

        } catch (error) {
            log('Login error:', error);

            // Show error message
            button.textContent = '✗ ' + (error.message || 'Authentication failed');
            button.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';

            // Reset button after 3 seconds
            setTimeout(() => {
                button.textContent = originalText;
                button.style.background = '';
                button.disabled = false;
            }, 3000);
        }
    }

    // Inject biometric login button into login form
    function injectBiometricButton() {
        if (!isWebAuthnSupported()) {
            log('WebAuthn not supported on this browser');
            return;
        }

        // Check if button already injected
        if (document.querySelector('.biometric-login-button')) {
            log('Biometric button already exists');
            return;
        }

        // Find login form or email input (Shopify uses different structure)
        const loginForm = document.querySelector('form[action*="login"]') ||
            document.querySelector('form input[type="password"]')?.closest('form') ||
            document.querySelector('form input[name="email"]')?.closest('form');

        // Shopify-specific: Look for the email input directly
        const emailInput = document.querySelector('#customer-authentication-web-email') ||
            document.querySelector('input[type="email"]') ||
            document.querySelector('input[name="customer[email]"]');

        if (!loginForm && !emailInput) {
            log('No login form or email input found');
            return;
        }

        log('Login form or email input found, injecting biometric button');

        const deviceText = getDeviceCapabilityText();

        // Create container
        const container = document.createElement('div');
        container.className = 'biometric-login-container';
        container.style.cssText = `
            margin-bottom: 1.5rem;
            text-align: center;
        `;

        // Create button
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'biometric-login-button';
        button.innerHTML = `
            <span style="font-size: 1.5rem; margin-right: 0.5rem;">${deviceText.icon}</span>
            <span>${deviceText.button}</span>
        `;
        button.style.cssText = `
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #5c6ac4 0%, #4959bd 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(92, 106, 196, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        `;

        // Hover effect
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-2px)';
            button.style.boxShadow = '0 6px 12px rgba(92, 106, 196, 0.4)';
        });
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0)';
            button.style.boxShadow = '0 4px 6px rgba(92, 106, 196, 0.3)';
        });

        // Click handler
        button.addEventListener('click', () => handleBiometricLogin(button));

        // Add divider
        const divider = document.createElement('div');
        divider.style.cssText = `
            margin: 1.5rem 0;
            text-align: center;
            position: relative;
        `;
        divider.innerHTML = `
            <span style="
                background: white;
                padding: 0 1rem;
                color: #6d7175;
                font-size: 0.875rem;
                position: relative;
                z-index: 1;
            ">or use password</span>
        `;

        const line = document.createElement('div');
        line.style.cssText = `
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e1e3e5;
        `;
        divider.insertBefore(line, divider.firstChild);

        container.appendChild(button);
        container.appendChild(divider);

        // Insert before the form
        loginForm.parentNode.insertBefore(container, loginForm);

        log('Biometric button injected successfully');
    }

    // Biometric Enrollment Handler
    async function handleBiometricEnrollment(acceptButton, skipButton, popup) {
        try {
            acceptButton.disabled = true;
            acceptButton.textContent = '🔄 Setting up...';

            log('Requesting registration options...');

            // Get registration options from server
            const { options } = await apiRequest('/register-options');

            log('Registration options received:', options);

            // Prepare options for WebAuthn
            const publicKeyOptions = preparePublicKeyOptions(options);

            log('Starting WebAuthn registration...');

            // Trigger device biometric prompt
            const credential = await navigator.credentials.create({
                publicKey: publicKeyOptions
            });

            log('Credential created:', credential);

            // Prepare credential for API
            const credentialData = prepareCredentialForApi(credential);

            log('Verifying credential...');

            // Verify and store with server
            const result = await apiRequest('/register-verify', credentialData);

            log('Enrollment successful!', result);

            // Show success message
            popup.innerHTML = `
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">✓</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #202223; font-size: 1.25rem;">Success!</h3>
                    <p style="margin: 0; color: #6d7175;">Biometric login is now enabled</p>
                </div>
            `;

            // Close popup after 2 seconds
            setTimeout(() => {
                popup.remove();
                // Clear the session flag
                apiRequest('/enrollment-complete').catch(() => { });
            }, 2000);

        } catch (error) {
            log('Enrollment error:', error);

            acceptButton.textContent = 'Try Again';
            acceptButton.disabled = false;

            alert('Failed to set up biometric login: ' + (error.message || 'Unknown error'));
        }
    }

    // Show enrollment popup
    function showEnrollmentPopup() {
        // Check if popup already exists
        if (document.querySelector('.biometric-enrollment-popup')) {
            return;
        }

        const deviceText = getDeviceCapabilityText();

        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'biometric-enrollment-popup';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        `;

        // Create popup
        const popup = document.createElement('div');
        popup.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: slideUp 0.3s ease;
        `;

        popup.innerHTML = `
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">${deviceText.icon}</div>
                <h3 style="margin: 0 0 0.5rem 0; color: #202223; font-size: 1.25rem; font-weight: 600;">Enable Quick Login?</h3>
                <p style="margin: 0; color: #6d7175; font-size: 0.875rem; line-height: 1.5;">${deviceText.enrollment}</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button class="biometric-accept" style="
                    flex: 1;
                    padding: 0.75rem 1.5rem;
                    background: linear-gradient(135deg, #5c6ac4 0%, #4959bd 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 0.875rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                ">Yes, Enable</button>
                <button class="biometric-skip" style="
                    flex: 1;
                    padding: 0.75rem 1.5rem;
                    background: white;
                    color: #202223;
                    border: 1px solid #e1e3e5;
                    border-radius: 8px;
                    font-size: 0.875rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                ">Skip</button>
            </div>
        `;

        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { transform: translateY(20px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // Get buttons
        const acceptButton = popup.querySelector('.biometric-accept');
        const skipButton = popup.querySelector('.biometric-skip');

        // Handle accept
        acceptButton.addEventListener('click', () => {
            handleBiometricEnrollment(acceptButton, skipButton, popup);
        });

        // Handle skip
        skipButton.addEventListener('click', () => {
            overlay.remove();
            // Clear the session flag
            apiRequest('/enrollment-complete').catch(() => { });
        });

        log('Enrollment popup displayed');
    }

    // Check if enrollment is pending
    function checkEnrollmentPending() {
        // This will be triggered by the middleware injection
        if (window.biometricEnrollmentPending) {
            log('Enrollment pending detected');
            if (isWebAuthnSupported()) {
                showEnrollmentPopup();
            }
            window.biometricEnrollmentPending = false;
        }
    }

    // Inject settings into Account page
    function injectAccountSettings() {
        if (!isWebAuthnSupported()) {
            log('WebAuthn not supported, skipping account settings');
            return;
        }

        // Look for account page indicators
        // Method 1: Look for standard Shopify account containers
        let accountContainer = document.querySelector('.account-details') ||
            document.querySelector('.customer__details') ||
            document.querySelector('.my-account__details') ||
            document.querySelector('[data-customer-account]') ||
            document.querySelector('.customer-account') ||
            document.querySelector('#customer-account');

        // Method 2: Look for "Account details" or "Account" heading
        if (!accountContainer) {
            const headings = document.querySelectorAll('h1, h2, h3');
            for (const heading of headings) {
                const text = heading.textContent.trim().toLowerCase();
                if (text === 'account details' || text === 'account' || text === 'my account') {
                    // Found account heading, use its parent or next sibling
                    accountContainer = heading.parentElement;
                    log('Account page detected via heading:', heading.textContent);
                    break;
                }
            }
        }

        // Method 3: Check URL - if we're on /account page, use main content area
        if (!accountContainer && window.location.pathname.includes('/account')) {
            accountContainer = document.querySelector('main') ||
                document.querySelector('.main-content') ||
                document.querySelector('#main') ||
                document.querySelector('body');
            log('Account page detected via URL, using main content area');
        }

        if (!accountContainer) {
            log('Account page not detected - no suitable container found');
            return;
        }

        if (document.querySelector('.biometric-settings-container')) return;

        log('Account page detected, creating settings section');

        const container = document.createElement('div');
        container.className = 'biometric-settings-container';
        container.style.cssText = `
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f4f6f8;
            border-radius: 8px;
            border: 1px solid #dfe3e8;
        `;

        container.innerHTML = `
            <h3 style="margin-top: 0; font-size: 1.1rem; color: #212b36;">Biometric Login</h3>
            <p style="margin-bottom: 1rem; color: #637381;">Enable fingerprint or Face ID for faster login.</p>
            <button class="biometric-enable-btn" style="
                padding: 0.75rem 1.5rem;
                background: linear-gradient(135deg, #5c6ac4 0%, #4959bd 100%);
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 600;
            ">Enable Biometric Login</button>
        `;

        const btn = container.querySelector('.biometric-enable-btn');
        btn.addEventListener('click', () => {
            // Re-use logic: show popup or trigger enrollment directly
            // We'll create a dummy popup object to reuse handleBiometricEnrollment
            const popup = document.createElement('div');
            popup.innerHTML = '<div class="msg"></div>';
            document.body.appendChild(popup); // Temp attach

            // Create mock buttons
            const acceptBtn = btn;
            const skipBtn = document.createElement('button'); // dummy

            handleBiometricEnrollment(acceptBtn, skipBtn, popup.querySelector('.msg'));

            // Cleanup after success/fail handled by existing function logic
        });

        accountContainer.appendChild(container);
    }

    // Initialize
    function init() {
        log('Initializing biometric authentication...');

        if (!isWebAuthnSupported()) {
            log('WebAuthn not supported, skipping initialization');
            return;
        }

        // Check for enrollment popup
        checkEnrollmentPending();

        const runInjections = () => {
            injectBiometricButton();
            injectAccountSettings();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runInjections);
        } else {
            runInjections();
        }

        // Also try after a short delay (for dynamic content)
        setTimeout(runInjections, 1000);

        log('Biometric authentication initialized');
    }

    // Expose global function
    window.initBiometricLogin = init;

    // Auto-initialize
    init();

})();
