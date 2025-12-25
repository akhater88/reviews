<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GoogleToken;
use App\Models\Tenant;
use App\Services\Google\GoogleBusinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class GoogleOAuthController extends Controller
{
    public function __construct(
        protected GoogleBusinessService $googleService
    ) {}

    /**
     * Handle the OAuth callback from Google
     */
    public function callback(Request $request): RedirectResponse
    {
        // Check for errors
        if ($request->has('error')) {
            Log::error('Google OAuth error', [
                'error' => $request->get('error'),
                'description' => $request->get('error_description'),
            ]);

            return redirect()
                ->route('filament.admin.pages.google-settings')
                ->with('error', 'فشل في الربط مع Google: ' . $request->get('error_description', $request->get('error')));
        }

        // Validate state
        $state = $request->get('state');
        $savedState = Session::get('google_oauth_state');

        if (!$state || $state !== $savedState) {
            Log::warning('Google OAuth state mismatch', [
                'received' => $state,
                'expected' => $savedState,
            ]);

            return redirect()
                ->route('filament.admin.pages.google-settings')
                ->with('error', 'فشل التحقق من صحة الطلب. يرجى المحاولة مرة أخرى.');
        }

        // Clear the state
        Session::forget('google_oauth_state');

        // Get the authorization code
        $code = $request->get('code');

        if (!$code) {
            return redirect()
                ->route('filament.admin.pages.google-settings')
                ->with('error', 'لم يتم العثور على رمز التفويض');
        }

        try {
            // Exchange code for tokens
            $tokens = $this->googleService->exchangeCodeForTokens($code);

            // Get user info
            $userInfo = $this->googleService->getUserInfo($tokens['access_token']);

            // Get current tenant
            $tenantId = Session::get('tenant_id');
            $tenant = Tenant::findOrFail($tenantId);

            // Store tokens
            $this->googleService->storeTokens($tenant, $tokens, $userInfo);

            Log::info('Google OAuth successful', [
                'tenant_id' => $tenant->id,
                'google_email' => $userInfo['email'] ?? null,
            ]);

            return redirect()
                ->route('filament.admin.pages.google-settings')
                ->with('success', 'تم ربط حساب Google Business بنجاح!');

        } catch (\Exception $e) {
            Log::error('Google OAuth exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('filament.admin.pages.google-settings')
                ->with('error', 'حدث خطأ أثناء الربط: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Google account
     */
    public function disconnect(Request $request): RedirectResponse
    {
        try {
            $tenantId = Session::get('tenant_id');
            $tenant = Tenant::findOrFail($tenantId);

            $this->googleService->disconnect($tenant);

            return redirect()
                ->route('filament.admin.pages.google-settings')
                ->with('success', 'تم إلغاء ربط حساب Google Business بنجاح');

        } catch (\Exception $e) {
            return redirect()
                ->route('filament.admin.pages.google-settings')
                ->with('error', 'حدث خطأ أثناء إلغاء الربط: ' . $e->getMessage());
        }
    }
}
