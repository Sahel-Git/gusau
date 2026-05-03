<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    /**
     * Mocks a transfer request to a vendor's bank account via Paystack Transfer API.
     */
    public function transfer(float $amount, string $recipientCode, string $reference)
    {
        // ⚠️ In a live environment, this would call Paystack:
        // Http::withToken(config('services.paystack.secret'))->post('https://api.paystack.co/transfer', [...])
        
        Log::info("Mocking Paystack Transfer: amount {$amount} to {$recipientCode} ref: {$reference}");
        
        // Simulating success. Return true to pass the cron job logic.
        return [
            'status' => true,
            'message' => 'Transfer queued successfully',
            'data' => [
                'reference' => $reference,
                'transfer_code' => 'TRF_' . rand(10000, 99999),
            ],
        ];
    }

    /**
     * Helper to verify Paystack Webhook signatures.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = env('PAYSTACK_SECRET_KEY', 'dummy-secret');
        return hash_equals(hash_hmac('sha512', $payload, $secret), $signature);
    }
}
