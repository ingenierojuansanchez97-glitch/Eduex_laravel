<?php

namespace Mhquickdev\LicenseClient\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Mhquickdev\LicenseClient\Services\LicenseVerificationService;

class LicenseClientController extends Controller
{
    protected LicenseVerificationService $verificationService;

    public function __construct(LicenseVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Show the activation form page.
     */
    public function showActivationForm()
    {
        $domain = request()->getHost();

        // 1. Try to auto-verify with the CRM server first if not locally activated
        if (!$this->verificationService->verifyLocalLicense()) {
            $autoVerifyResult = $this->verificationService->checkDomainActivation($domain);

            if ($autoVerifyResult['success'] && isset($autoVerifyResult['token'])) {
                // Store license token and purchase code locally
                $this->verificationService->storeLicenseToken($autoVerifyResult['token'], $autoVerifyResult['purchase_code']);
                
                // Flash success message
                session()->flash('success_message', 'License auto-verified successfully! Welcome to EduEx.');
            }
        }

        // 2. Retrieve local activation status and purchase code
        $isActivated = $this->verificationService->verifyLocalLicense();
        $purchaseCode = $this->verificationService->getStoredPurchaseCode();

        return view('license::activate', [
            'is_activated' => $isActivated,
            'purchase_code' => $purchaseCode
        ]);
    }

    /**
     * Handle the activation form submission.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'purchase_code' => ['required', 'string', 'min:10', 'max:50'],
        ]);

        $purchaseCode = $request->input('purchase_code');
        $domain = $request->getHost();

        $result = $this->verificationService->activateProduct($purchaseCode, $domain);

        if (!$result['success']) {
            return back()->withInput()->withErrors([
                'purchase_code' => $result['message'],
            ]);
        }

        return redirect('/')->with('success', 'License activated successfully!');
    }
}
