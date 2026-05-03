<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaystackService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, PaystackService $paystack)
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$signature || !$paystack->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid Paystack Webhook Signature', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if ($event['event'] === 'charge.success') {
            $reference = $event['data']['reference'];
            $amount = $event['data']['amount'] / 100; // Paystack is in kobo normally

            $order = Order::where('id', $reference)->first(); // Assumes reference is order ID or map it properly

            if ($order && $order->status === 'pending') {
                if ((float)$order->total_amount <= (float)$amount) {
                    $order->update(['status' => 'paid']);
                    // Additional logic can go here (e.g. notifications)
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
