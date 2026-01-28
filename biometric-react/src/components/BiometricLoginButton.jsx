import React, { useState, useEffect } from 'react';
import { startAuthentication } from '@simplewebauthn/browser';
import api from '../utils/api';
import { detectBiometricCapability, getBiometricButtonText, getBiometricIcon } from '../utils/biometric';
import '../index.css';

/**
 * BiometricLoginButton Component
 * 
 * Renders a prominent biometric login button that triggers WebAuthn authentication.
 * This component is injected into existing login pages above the traditional form.
 */
const BiometricLoginButton = () => {
    const [biometricType, setBiometricType] = useState('biometric');
    const [supported, setSupported] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        checkBiometricSupport();
    }, []);

    const checkBiometricSupport = async () => {
        const capability = await detectBiometricCapability();
        setSupported(capability.supported);
        setBiometricType(capability.type);
    };

    const handleBiometricLogin = async () => {
        try {
            setLoading(true);
            setError('');

            // Get authentication options from server
            const optionsResponse = await api.post('/api/biometric/login-options');

            if (!optionsResponse.data.success) {
                throw new Error(optionsResponse.data.message || 'Failed to get login options');
            }

            const options = optionsResponse.data.options;

            // Convert challenge from base64
            const publicKeyOptions = {
                ...options,
                challenge: Uint8Array.from(atob(options.challenge), c => c.charCodeAt(0)),
                allowCredentials: options.allowCredentials?.map(cred => ({
                    ...cred,
                    id: cred.id, // Keep as base64 string for @simplewebauthn/browser
                })) || [],
            };

            // Start WebAuthn authentication
            const credential = await startAuthentication(publicKeyOptions);

            // Send credential to server for verification
            const verifyResponse = await api.post('/api/biometric/login-verify', credential);

            if (!verifyResponse.data.success) {
                throw new Error(verifyResponse.data.message || 'Authentication failed');
            }

            // Redirect to dashboard or intended page
            const redirectUrl = verifyResponse.data.redirect || '/dashboard';
            window.location.href = redirectUrl;

        } catch (err) {
            console.error('Biometric login error:', err);

            // User-friendly error messages
            let errorMessage = 'Biometric authentication failed. ';

            if (err.name === 'NotAllowedError') {
                errorMessage += 'You cancelled the biometric prompt.';
            } else if (err.name === 'InvalidStateError') {
                errorMessage += 'No biometric credentials found for this device.';
            } else {
                errorMessage += 'Please try again or use your password below.';
            }

            setError(errorMessage);

            // Clear error after 5 seconds
            setTimeout(() => setError(''), 5000);
        } finally {
            setLoading(false);
        }
    };

    if (!supported) {
        return null; // Don't show button if biometric not supported
    }

    return (
        <div className="biometric-container bio-mb-6">
            <button
                onClick={handleBiometricLogin}
                disabled={loading}
                className="biometric-button bio-w-full bio-flex bio-items-center bio-justify-center bio-gap-3 bio-px-6 bio-py-4 bio-bg-gradient-to-r bio-from-blue-600 bio-to-blue-700 bio-text-white bio-rounded-xl bio-font-semibold bio-text-lg bio-shadow-lg disabled:bio-opacity-50 disabled:bio-cursor-not-allowed"
            >
                {loading ? (
                    <>
                        <div className="biometric-spinner"></div>
                        <span>Authenticating...</span>
                    </>
                ) : (
                    <>
                        <span dangerouslySetInnerHTML={{ __html: getBiometricIcon(biometricType) }} />
                        <span>{getBiometricButtonText(biometricType, 'login')}</span>
                    </>
                )}
            </button>

            {error && (
                <div className="bio-mt-3 bio-p-3 bio-bg-red-50 bio-border bio-border-red-200 bio-rounded-lg bio-text-red-700 bio-text-sm">
                    {error}
                </div>
            )}

            <div className="bio-flex bio-items-center bio-gap-3 bio-my-4">
                <div className="bio-flex-1 bio-h-px bio-bg-gray-300"></div>
                <span className="bio-text-sm bio-text-gray-500 bio-font-medium">or use password</span>
                <div className="bio-flex-1 bio-h-px bio-bg-gray-300"></div>
            </div>
        </div>
    );
};

export default BiometricLoginButton;
