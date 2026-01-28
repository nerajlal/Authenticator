import React, { useState } from 'react';
import { startRegistration } from '@simplewebauthn/browser';
import api from '../utils/api';
import '../index.css';

/**
 * BiometricEnrollmentPopup Component
 * 
 * Shows a friendly popup after login/registration asking users if they want
 * to enable biometric authentication for faster future logins.
 */
const BiometricEnrollmentPopup = ({ onClose, biometricType }) => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);

    const handleEnroll = async () => {
        try {
            setLoading(true);
            setError('');

            // Get registration options from server
            const optionsResponse = await api.post('/api/biometric/register-options');

            if (!optionsResponse.data.success) {
                throw new Error(optionsResponse.data.message || 'Failed to get registration options');
            }

            const options = optionsResponse.data.options;

            // Convert challenge from base64
            const publicKeyOptions = {
                ...options,
                challenge: Uint8Array.from(atob(options.challenge), c => c.charCodeAt(0)),
                user: {
                    ...options.user,
                    id: Uint8Array.from(atob(options.user.id), c => c.charCodeAt(0)),
                },
                excludeCredentials: options.excludeCredentials?.map(cred => ({
                    ...cred,
                    id: Uint8Array.from(atob(cred.id), c => c.charCodeAt(0)),
                })) || [],
            };

            // Start WebAuthn registration
            const credential = await startRegistration(publicKeyOptions);

            // Send credential to server for verification
            const verifyResponse = await api.post('/api/biometric/register-verify', credential);

            if (!verifyResponse.data.success) {
                throw new Error(verifyResponse.data.message || 'Failed to verify registration');
            }

            setSuccess(true);
            setTimeout(() => {
                onClose();
            }, 2000);

        } catch (err) {
            console.error('Biometric enrollment error:', err);
            setError(err.message || 'Failed to enable biometric authentication. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    const handleSkip = () => {
        // Set a flag to not show this again for this session
        sessionStorage.setItem('biometric_enrollment_skipped', 'true');
        onClose();
    };

    return (
        <div className="biometric-overlay" onClick={handleSkip}>
            <div className="biometric-modal" onClick={(e) => e.stopPropagation()}>
                {success ? (
                    <div className="bio-text-center">
                        <div className="bio-mb-4 bio-text-green-500">
                            <svg className="bio-w-16 bio-h-16 bio-mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 className="bio-text-2xl bio-font-bold bio-text-gray-800 bio-mb-2">
                            All Set!
                        </h2>
                        <p className="bio-text-gray-600">
                            Biometric authentication has been enabled successfully.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="bio-text-center bio-mb-6">
                            <div className="bio-mb-4 bio-text-blue-500">
                                <svg className="bio-w-16 bio-h-16 bio-mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h2 className="bio-text-2xl bio-font-bold bio-text-gray-800 bio-mb-2">
                                Enable Faster Login?
                            </h2>
                            <p className="bio-text-gray-600 bio-mb-4">
                                Would you like to use your {biometricType === 'faceid' ? 'Face ID' :
                                    biometricType === 'touchid' ? 'Touch ID' :
                                        biometricType === 'fingerprint' ? 'fingerprint' :
                                            'biometric'} for faster login next time?
                            </p>
                            <p className="bio-text-sm bio-text-gray-500">
                                You'll be able to login with just one tap, no password needed!
                            </p>
                        </div>

                        {error && (
                            <div className="bio-mb-4 bio-p-3 bio-bg-red-50 bio-border bio-border-red-200 bio-rounded-lg bio-text-red-700 bio-text-sm">
                                {error}
                            </div>
                        )}

                        <div className="bio-flex bio-gap-3">
                            <button
                                onClick={handleSkip}
                                disabled={loading}
                                className="bio-flex-1 bio-px-6 bio-py-3 bio-bg-gray-100 bio-text-gray-700 bio-rounded-lg bio-font-medium hover:bio-bg-gray-200 bio-transition-colors disabled:bio-opacity-50"
                            >
                                Skip
                            </button>
                            <button
                                onClick={handleEnroll}
                                disabled={loading}
                                className="bio-flex-1 bio-px-6 bio-py-3 bio-bg-blue-600 bio-text-white bio-rounded-lg bio-font-medium hover:bio-bg-blue-700 bio-transition-colors disabled:bio-opacity-50 bio-flex bio-items-center bio-justify-center bio-gap-2"
                            >
                                {loading ? (
                                    <>
                                        <div className="biometric-spinner"></div>
                                        <span>Enabling...</span>
                                    </>
                                ) : (
                                    'Yes, Enable'
                                )}
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default BiometricEnrollmentPopup;
