<?php

namespace Mhquickdev\LicenseClient\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mhquickdev\LicenseClient\Services\LicenseVerificationService;

class LicenseCheckMiddleware
{
    protected LicenseVerificationService $verificationService;

    public function __construct(LicenseVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Handle an incoming request: Verify the signature locally.
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip check for excluded routes (e.g. license/activate, assets, login/logout)
        $excluded = config('license.excluded_routes', [
            'license/*',
            'api/v1/license/*',
        ]);

        foreach ($excluded as $pattern) {
            if ($request->is($pattern) || $request->is(trim($pattern, '/'))) {
                return $next($request);
            }
        }

        if (!$this->verificationService->verifyLocalLicense()) {
            return redirect()->route('license.activate');
        }

        return $next($request);
    }

    /**
     * Run terminable checks after response is sent to browser.
     */
    public function terminate(Request $request, $response)
    {
        // Check with the license server once every 7 days in the background
        $cacheKey = 'license_heartbeat_last_check';
        if (Cache::has($cacheKey)) {
            return;
        }

        $purchaseCode = Cache::get('license_purchase_code');
        if (!$purchaseCode) {
            return;
        }

        try {
            // Fast cURL check with 2-second timeout
            $res = Http::timeout(2)->post('https://mhquickdev.com/api/v1/license/verify', [
                'purchase_code' => $purchaseCode,
                'domain' => $request->getHost(),
            ]);

            if ($res->successful()) {
                if ($res->json('status') === 'revoked') {
                    // Delete local token if license is revoked / blacklisted
                    $this->verificationService->clearLicenseToken();
                } else {
                    // Refresh 7-day heartbeat window
                    Cache::put($cacheKey, true, now()->addDays(7));
                }
            }
        } catch (\Exception $e) {
            // Fail silently: Do not interrupt client application if central licensing server is down
        }
    }
}
