<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    /**
     * Routes that should be accessible without phone verification.
     */
    protected array $exceptRoutes = [
        'filament.admin.pages.profile',
        'filament.admin.auth.logout',
        'livewire.message',
        'livewire.upload-file',
        'livewire.preview-file',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip if phone is already verified
        if ($user->isPhoneVerified()) {
            return $next($request);
        }

        // Allow access to excepted routes
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && $this->shouldBypassVerification($currentRoute)) {
            return $next($request);
        }

        // Redirect unverified users to profile page
        return redirect()->route('filament.admin.pages.profile');
    }

    /**
     * Check if the route should bypass phone verification.
     */
    protected function shouldBypassVerification(string $routeName): bool
    {
        foreach ($this->exceptRoutes as $exceptRoute) {
            if ($routeName === $exceptRoute || str_starts_with($routeName, $exceptRoute)) {
                return true;
            }
        }

        return false;
    }
}
