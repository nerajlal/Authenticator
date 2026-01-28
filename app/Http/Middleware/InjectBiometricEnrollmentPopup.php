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
            return $response;
        }

        // Check if we should show enrollment popup
        $shouldShowEnrollment = session('biometric_enrollment_pending', false);
        
        if (!$shouldShowEnrollment) {
            return $response;
        }

        // Clear the session flag
        session()->forget('biometric_enrollment_pending');

        // Inject JavaScript to trigger enrollment popup
        $content = $response->getContent();
        
        $script = <<<'HTML'
<script>
    // Set flag for biometric enrollment
    if (typeof sessionStorage !== 'undefined') {
        sessionStorage.setItem('biometric_enrollment_pending', 'true');
    }
    
    // Trigger enrollment popup if biometric app is loaded
    if (typeof window.initBiometricLogin === 'function') {
        window.initBiometricLogin();
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
