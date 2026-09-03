<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewaySetting;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\PaymentGatewayService;

/**
 * Payment Gateway Controller
 *
 * This controller handles the management of payment gateways.
 *
 * @package App\Http\Controllers\Admin
 */
class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        $gateways = PaymentGatewaySetting::orderBy('type')->orderBy('name')->get();

        $credentialFieldMap = [
            'razorpay' => ['key', 'secret'],
            'stripe' => ['key', 'secret'],
            'paystack' => ['public_key', 'secret_key'],
            'flutterwave' => ['public_key', 'secret_key'],
            'paypal' => ['client_id', 'client_secret', 'mode'],
            'sslcommerz' => ['store_id', 'store_password', 'mode'],
            'mollie' => ['api_key'],
            'bkash'  => ['app_key', 'app_secret', 'username', 'password', 'mode'],
            'xpay'   => ['store_id', 'api_key', 'mode'],
        ];

        return view('admin.settings.gateways.index', compact('gateways', 'credentialFieldMap'));
    }

    public function update(Request $request, string $identifier): RedirectResponse
    {
        $gateway = PaymentGatewaySetting::where('identifier', $identifier)->firstOrFail();

        $validated = $request->validate([
            'is_enabled' => ['nullable', 'boolean'],
            'offline_instructions' => ['nullable', 'string'],
            'credentials' => ['nullable', 'array'],
        ]);

        $gateway->is_enabled = (bool) ($validated['is_enabled'] ?? false);

        if ($gateway->type === 'offline') {
            $gateway->offline_instructions = $validated['offline_instructions'] ?? $gateway->offline_instructions;
        } else {
            $credentials = $validated['credentials'] ?? [];
            $gateway->credentials = collect($credentials)
                ->map(fn($value) => is_string($value) ? trim($value) : $value)
                ->filter(fn($value) => $value !== null && $value !== '')
                ->toArray();
        }

        $gateway->save();

        app(PaymentGatewayService::class)->refreshCache();

        ToastMagic::success('Gateway settings updated successfully.');

        return redirect()->route('admin.settings.gateways.index');
    }
}
