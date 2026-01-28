/**
 * Detect device biometric capabilities
 * @returns {Promise<{supported: boolean, type: string}>}
 */
export const detectBiometricCapability = async () => {
    try {
        // Check if WebAuthn is supported
        if (!window.PublicKeyCredential) {
            return { supported: false, type: 'none' };
        }

        // Check if platform authenticator is available
        const available = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();

        if (!available) {
            return { supported: false, type: 'none' };
        }

        // Detect device type and biometric capability
        const userAgent = navigator.userAgent.toLowerCase();

        if (/iphone|ipad|ipod/.test(userAgent)) {
            // iOS devices - Face ID or Touch ID
            return {
                supported: true,
                type: userAgent.includes('iphone x') || userAgent.includes('iphone 1') ? 'faceid' : 'touchid'
            };
        } else if (/android/.test(userAgent)) {
            // Android devices - Fingerprint
            return { supported: true, type: 'fingerprint' };
        } else if (/mac/.test(userAgent)) {
            // macOS - Touch ID
            return { supported: true, type: 'touchid' };
        } else if (/windows/.test(userAgent)) {
            // Windows - Windows Hello
            return { supported: true, type: 'windowshello' };
        }

        // Default to generic biometric
        return { supported: true, type: 'biometric' };
    } catch (error) {
        console.error('Error detecting biometric capability:', error);
        return { supported: false, type: 'none' };
    }
};

/**
 * Get user-friendly button text based on biometric type
 * @param {string} type - Biometric type
 * @param {string} action - 'login' or 'register'
 * @returns {string}
 */
export const getBiometricButtonText = (type, action = 'login') => {
    const actionText = action === 'login' ? 'Login' : 'Enable';

    switch (type) {
        case 'faceid':
            return `${actionText} with Face ID`;
        case 'touchid':
            return `${actionText} with Touch ID`;
        case 'fingerprint':
            return `${actionText} with Fingerprint`;
        case 'windowshello':
            return `${actionText} with Windows Hello`;
        default:
            return `${actionText} with Biometric`;
    }
};

/**
 * Get icon for biometric type
 * @param {string} type - Biometric type
 * @returns {string} - SVG icon
 */
export const getBiometricIcon = (type) => {
    switch (type) {
        case 'faceid':
            return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="bio-w-6 bio-h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
      </svg>`;
        case 'touchid':
        case 'fingerprint':
            return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="bio-w-6 bio-h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 01-3.6 9.75m6.633-4.596a18.666 18.666 0 01-2.485 5.33" />
      </svg>`;
        default:
            return `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="bio-w-6 bio-h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
      </svg>`;
    }
};

/**
 * Base64 URL encode
 */
export const base64UrlEncode = (buffer) => {
    const base64 = btoa(String.fromCharCode(...new Uint8Array(buffer)));
    return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
};

/**
 * Base64 URL decode
 */
export const base64UrlDecode = (str) => {
    str = str.replace(/-/g, '+').replace(/_/g, '/');
    while (str.length % 4) {
        str += '=';
    }
    const binary = atob(str);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    return bytes.buffer;
};
