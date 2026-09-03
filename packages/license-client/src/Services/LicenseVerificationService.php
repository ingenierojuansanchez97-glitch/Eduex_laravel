<?php

namespace Mhquickdev\LicenseClient\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LicenseVerificationService
{
    // Hardcoded official license server URL
    private const SERVER_URL = 'https://mhquickdev.com/api/v1/license';

    /**
     * Reconstruct the RSA Public Key from split, base64-encoded chunks to prevent
     * simple regex-based search-and-replace cracked patches.
     */
    private function getPublicKey(): string
    {
        $chunks = [
            'LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0=',
            'TUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUFrL2NvQmh2V2swZVQ2MGc3M0tvLw==',
            'OEREVVpTb1ozN0ZTR1h4ajJFUEJvdDJzTUM2WnZrWDdoeHJEZS9MeTFPOEphd0J3SkNPcnRJKzhWVDU3TkM1bA==',
            'MHhrdmppSXJlaHJVcVZyTlduQlg1YmlTOUt3RnFScW83N3Q4elZ6N2srTTlQY1oza2h1RjNXODFveHFoREhqTQ==',
            'RmVFZTlTVTBYZWtFRk1adTMwUWtnbzVWYVRleUVYK25CckpWaFNoOW5uZ2NZZWdLWGwvMWdUYUVFL08ra1VjYQ==',
            'OFZMZnRWTDJib0QwWGxGeXVGNWJsSmdwZUNvL3hGaDc5Nm4wRXhpWGtXU09tc2xlU0xjS1Zib2JJdmlzcnNKTA==',
            'UVU3NVF0Wkp2S2lZSkF2OGZiYUFKSUtNNGRCWElFRWV0cWpxazY4SVp0MC9OVmp2d2JYakVMTWtvdlJjVnBJcA==',
            'WFFJREFRQUI=',
            'LS0tLS1FTkQgUFVCTElDIEtFWS0tLS0t'
        ];

        return implode("\n", array_map('base64_decode', $chunks)) . "\n";
    }

    /**
     * Get the reconstructed Public Key as clean text (for debugging/testing).
     */
    public function getCleanPublicKey(): string
    {
        return $this->getPublicKey();
    }

    /**
     * Activate the product using the purchase code.
     */
    public function activateProduct(string $purchaseCode, string $domain): array
    {
        try {
            $response = Http::timeout(10)->post(self::SERVER_URL . '/activate', [
                'purchase_code' => $purchaseCode,
                'domain' => $domain,
            ]);

            if ($response->successful()) {
                $token = $response->json('token');
                if ($this->verifyTokenSignature($token, $domain)) {
                    $this->storeLicenseToken($token, $purchaseCode);
                    return ['success' => true, 'message' => 'Product activated successfully.'];
                }
            }

            return [
                'success' => false, 
                'message' => $response->json('message') ?? 'Activation rejected by server.'
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Unable to connect to license server.'];
        }
    }

    /**
     * Locally verify that the stored signed token has a valid signature matching the domain.
     */
    public function verifyLocalLicense(): bool
    {
        $token = $this->getStoredToken();
        if (!$token) {
            return false;
        }

        return $this->verifyTokenSignature($token, request()->getHost());
    }

    /**
     * Cryptographically verify RS256 token signature using the Public Key.
     */
    public function verifyTokenSignature(string $token, string $currentDomain): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;
        
        $dataToVerify = $headerB64 . '.' . $payloadB64;
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $signatureB64));
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payloadB64)), true);

        // Normalize domains for comparison
        $cleanCurrentDomain = $this->normalizeDomain($currentDomain);
        $cleanPayloadDomain = isset($payload['domain']) ? $this->normalizeDomain($payload['domain']) : '';

        if (!$payload || $cleanPayloadDomain !== $cleanCurrentDomain) {
            return false; // Domain mismatch
        }

        // Validate item ID to strictly accept configured product ID only
        $configuredItemId = config('license.item_id', '61977497');
        if (!isset($payload['item_id']) || (string)$payload['item_id'] !== (string)$configuredItemId) {
            return false;
        }

        $pubKey = openssl_pkey_get_public($this->getPublicKey());
        if (!$pubKey) {
            return false;
        }

        $ok = openssl_verify($dataToVerify, $signature, $pubKey, OPENSSL_ALGO_SHA256);
        return $ok === 1;
    }

    public function getStoredToken(): ?string
    {
        // 1. Try Laravel Cache
        $token = Cache::get('license_activation_token');
        if ($token) {
            return $token;
        }

        // 2. Try Settings Repository / Database fallback
        try {
            if (function_exists('settings')) {
                $token = settings('license_activation_token');
                if ($token) {
                    Cache::forever('license_activation_token', $token);
                    return $token;
                }
            }

            // Fallback to direct DB query if settings helper isn't loaded
            $row = \Illuminate\Support\Facades\DB::table('platform_settings')
                ->where('key', 'license_activation_token')
                ->first();
            if ($row) {
                $payload = json_decode($row->payload, true);
                $token = $payload['data'] ?? null;
                if ($token) {
                    Cache::forever('license_activation_token', $token);
                    return $token;
                }
            }
        } catch (\Throwable $e) {
            // Ignore DB exceptions
        }

        return null;
    }

    public function storeLicenseToken(string $token, string $purchaseCode): void
    {
        // 1. Cache forever
        Cache::forever('license_activation_token', $token);
        Cache::forever('license_purchase_code', $purchaseCode);

        // 2. Persist to database
        try {
            if (function_exists('settings')) {
                settings()->set('license_activation_token', $token, 'system');
                settings()->set('license_purchase_code', $purchaseCode, 'system');
            } else {
                \Illuminate\Support\Facades\DB::table('platform_settings')->updateOrInsert(
                    ['key' => 'license_activation_token'],
                    [
                        'group' => 'system',
                        'type' => 'string',
                        'payload' => json_encode(['data' => $token]),
                        'is_encrypted' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                \Illuminate\Support\Facades\DB::table('platform_settings')->updateOrInsert(
                    ['key' => 'license_purchase_code'],
                    [
                        'group' => 'system',
                        'type' => 'string',
                        'payload' => json_encode(['data' => $purchaseCode]),
                        'is_encrypted' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Ignore DB exceptions
        }
    }

    public function clearLicenseToken(): void
    {
        // 1. Forget cache
        Cache::forget('license_activation_token');
        Cache::forget('license_purchase_code');
        Cache::forget('license_heartbeat_last_check');

        // 2. Remove from database
        try {
            \Illuminate\Support\Facades\DB::table('platform_settings')
                ->whereIn('key', ['license_activation_token', 'license_purchase_code'])
                ->delete();
        } catch (\Throwable $e) {
            // Ignore DB exceptions
        }
    }

    /**
     * Check if this domain is active on the CRM and retrieve the activation details.
     */
    public function checkDomainActivation(string $domain): array
    {
        try {
            $response = Http::timeout(10)->post(self::SERVER_URL . '/check-domain', [
                'domain' => $domain,
                'item_id' => config('license.item_id', '61977497'),
            ]);

            if ($response->successful()) {
                $token = $response->json('token');
                $purchaseCode = $response->json('purchase_code');

                if ($token && $purchaseCode && $this->verifyTokenSignature($token, $domain)) {
                    return [
                        'success' => true,
                        'token' => $token,
                        'purchase_code' => $purchaseCode,
                    ];
                }
            }

            return ['success' => false, 'message' => $response->json('message') ?? 'License not found on server.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Unable to connect to license server.'];
        }
    }

    /**
     * Retrieve the stored purchase code from cache or settings database.
     */
    public function getStoredPurchaseCode(): ?string
    {
        // 1. Try Laravel Cache
        $purchaseCode = Cache::get('license_purchase_code');
        if ($purchaseCode) {
            return $purchaseCode;
        }

        // 2. Try Settings Repository / Database fallback
        try {
            if (function_exists('settings')) {
                $purchaseCode = settings('license_purchase_code');
                if ($purchaseCode) {
                    Cache::forever('license_purchase_code', $purchaseCode);
                    return $purchaseCode;
                }
            }

            // Fallback to direct DB query
            $row = \Illuminate\Support\Facades\DB::table('platform_settings')
                ->where('key', 'license_purchase_code')
                ->first();
            if ($row) {
                $payload = json_decode($row->payload, true);
                $purchaseCode = $payload['data'] ?? null;
                if ($purchaseCode) {
                    Cache::forever('license_purchase_code', $purchaseCode);
                    return $purchaseCode;
                }
            }
        } catch (\Throwable $e) {
            // Ignore DB exceptions
        }

        return null;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/^https?:\/\//i', '', $domain);
        $domain = explode('/', $domain)[0];
        $domain = explode(':', $domain)[0];
        if (strpos($domain, 'www.') === 0) {
            $domain = substr($domain, 4);
        }
        return $domain;
    }
}
