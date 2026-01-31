<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inject Biometric Enrollment Popup Middleware
 * 
 * This middleware checks if the user just logged in or registered and doesn't have
 * biometric credentials yet. If so, it injects JavaScript to show the enrollment popup.
 */
class InjectBiometricEnrollmentPopup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only inject for authenticated users on HTML responses
        if (!Auth::check() || 
            !$this->isHtmlResponse($response) || 
            $request->ajax() || 
            $request->wantsJson()) {
            return $response;
        }

        $user = Auth::user();

        // Check if user has biometric credentials
        if ($user->biometricCredentials()->exists()) {
            \Illuminate\Support\Facades\Log::info('User already has biometric credentials, skipping enrollment', [
                'user_id' => $user->id
            ]);
            return $response;
        }

        // Check if we should show enrollment popup
        $shouldShowEnrollment = session('biometric_enrollment_pending', false);
        
        \Illuminate\Support\Facades\Log::info('Checking enrollment popup injection', [
            'user_id' => $user->id,
            'should_show' => $shouldShowEnrollment,
            'session_id' => session()->getId()
        ]);
        
        if (!$shouldShowEnrollment) {
            return $response;
        }

        // Clear the session flag
        session()->forget('biometric_enrollment_pending');

        \Illuminate\Support\Facades\Log::info('Injecting biometric enrollment popup script', [
            'user_id' => $user->id
        ]);

        // Inject JavaScript to trigger enrollment popup
        $content = $response->getContent();
        
        $script = <<<'HTML'
<script>
    // Set flag for biometric enrollment - will be picked up by biometric-login.js
    window.biometricEnrollmentPending = true;
    
    // Trigger enrollment check if biometric script is already loaded
    if (typeof window.initBiometricLogin === 'function') {
        setTimeout(function() {
            if (window.biometricEnrollmentPending) {
                window.initBiometricLogin();
            }
        }, 500);
    }
</script>
HTML;

        // Inject before closing body tag
        $content = str_replace('</body>', $script . '</body>', $content);
        
        $response->setContent($content);

        return $response;
    }

    /**
     * Check if response is HTML
     *
     * @param Response $response
     * @return bool
     */
    private function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html') || 
               (empty($contentType) && is_string($response->getContent()));
    }
}
