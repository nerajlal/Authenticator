import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import BiometricLoginButton from './components/BiometricLoginButton';
import BiometricEnrollmentPopup from './components/BiometricEnrollmentPopup';
import { detectBiometricCapability } from './utils/biometric';
import './index.css';

/**
 * Main Biometric App Component
 * 
 * Handles initialization and rendering of biometric components:
 * - Injects biometric login button into existing login forms
 * - Shows enrollment popup after successful login/registration
 */
const BiometricApp = () => {
    const [showEnrollment, setShowEnrollment] = useState(false);
    const [biometricType, setBiometricType] = useState('biometric');
    const [loginFormFound, setLoginFormFound] = useState(false);

    useEffect(() => {
        initializeBiometric();
    }, []);

    const initializeBiometric = async () => {
        // Check biometric capability
        const capability = await detectBiometricCapability();
        setBiometricType(capability.type);

        if (!capability.supported) {
            console.log('Biometric authentication not supported on this device');
            return;
        }

        // Check if we should show enrollment popup
        const shouldShowEnrollment = sessionStorage.getItem('biometric_enrollment_pending');
        const skipped = sessionStorage.getItem('biometric_enrollment_skipped');

        if (shouldShowEnrollment === 'true' && !skipped) {
            setShowEnrollment(true);
            sessionStorage.removeItem('biometric_enrollment_pending');
        }

        // Detect login form and inject button
        detectAndInjectLoginButton();
    };

    const detectAndInjectLoginButton = () => {
        // Look for common login form patterns
        const loginForm = document.querySelector('form[action*="login"]') ||
            document.querySelector('form:has(input[type="password"])') ||
            document.querySelector('form:has(input[name*="password"])') ||
            document.querySelector('#login-form') ||
            document.querySelector('.login-form');

        if (loginForm) {
            setLoginFormFound(true);

            // Check if we already injected the button
            if (document.getElementById('biometric-login-container')) {
                return;
            }

            // Create container for biometric button
            const container = document.createElement('div');
            container.id = 'biometric-login-container';

            // Insert before the form
            loginForm.parentNode.insertBefore(container, loginForm);

            // Render biometric button into container
            const root = ReactDOM.createRoot(container);
            root.render(<BiometricLoginButton />);
        }
    };

    return (
        <>
            {showEnrollment && (
                <BiometricEnrollmentPopup
                    onClose={() => setShowEnrollment(false)}
                    biometricType={biometricType}
                />
            )}
        </>
    );
};

/**
 * Initialize biometric authentication
 * This function is exposed globally for easy initialization
 */
window.initBiometricLogin = () => {
    // Check if already initialized
    if (document.getElementById('biometric-app-root')) {
        return;
    }

    // Create root container
    const root = document.createElement('div');
    root.id = 'biometric-app-root';
    document.body.appendChild(root);

    // Render app
    const reactRoot = ReactDOM.createRoot(root);
    reactRoot.render(<BiometricApp />);
};

/**
 * Auto-initialize if DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initBiometricLogin);
} else {
    window.initBiometricLogin();
}

export default BiometricApp;
