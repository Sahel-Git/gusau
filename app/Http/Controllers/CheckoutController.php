<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function pay()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty');
        }

        $total = 0;

        foreach ($cart as $store) {
            foreach ($store['items'] as $listingId => $item) {

                $listing = \App\Models\Listing::find($listingId);

                if (!$listing) {
                    return back()->with('error', 'Invalid product detected');
                }

                if ($listing->isProduct() && $listing->stock !== null && $listing->stock < $item['quantity']) {
                    return back()->with('error', 'Stock issue for '.$listing->title);
                }

                $total += $listing->price * $item['quantity'];
            }
        }

        if ($total <= 0) {
            return back()->with('error', 'Invalid cart total');
        }

        $reference = uniqid('sdm_');

        session(['payment_reference' => $reference]);

        $email = auth()->user()->email ?? null;

        if (!$email) {
            return back()->with('error', 'User email not found');
        }

        if (env('PAYMENT_MODE') === 'simulation') {
            \Illuminate\Support\Facades\Log::info('Simulation payment triggered', [
                'reference' => $reference,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('checkout.verify', [
                'reference' => $reference
            ]);
        }

        $response = \Illuminate\Support\Facades\Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $total * 100,
                'reference' => $reference,
                'callback_url' => route('checkout.verify'),
            ]);

        if (!$response->successful() || !isset($response['data']['authorization_url'])) {
            return back()->with('error', 'Payment initialization failed');
        }

        return redirect($response['data']['authorization_url']);
    }

    public function verify(Request $request)
    {
        $reference = $request->reference;

        if (!$reference) {
            return redirect()->route('cart.index')->with('error', 'No payment reference');
        }

        if (env('PAYMENT_MODE') === 'simulation') {
            $cart = session('cart', []);
            $paidAmount = 0;
            foreach ($cart as $store) {
                foreach ($store['items'] as $listingId => $item) {
                    $listing = \App\Models\Listing::find($listingId);
                    if ($listing) {
                        $paidAmount += $listing->price * $item['quantity'];
                    }
                }
            }
        } else {
            $response = \Illuminate\Support\Facades\Http::withToken(config('services.paystack.secret'))
                ->get("https://api.paystack.co/transaction/verify/$reference");

            if (!$response->successful() || $response['data']['status'] !== 'success') {
                \Illuminate\Support\Facades\Log::error('Payment verification failed', [
                    'reference' => $reference,
                    'response' => $response->json()
                ]);
                return redirect()->route('cart.index')->with('error', 'Payment failed');
            }

            // 1. VERIFY PAYMENT AMOUNT
            $paidAmount = $response['data']['amount'] / 100;
        }

        $calculatedTotal = 0;
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Cart session expired');
        }

        foreach ($cart as $store) {
            foreach ($store['items'] as $listingId => $item) {
                $listing = \App\Models\Listing::find($listingId);
                if ($listing) {
                    $calculatedTotal += $listing->price * $item['quantity'];
                }
            }
        }

        if ($paidAmount != $calculatedTotal) {
            \Illuminate\Support\Facades\Log::error('Payment amount mismatch', [
                'reference' => $reference,
                'paid_amount' => $paidAmount,
                'calculated_total' => $calculatedTotal,
            ]);
            return redirect()->route('cart.index')->with('error', 'Payment amount mismatch');
        }

        // 🔒 Prevent duplicate processing
        if (\App\Models\Order::where('payment_reference', $reference)->exists()) {
            return redirect()->route('home')->with('success', 'Order already processed');
        }

        // ANTI-RACE CONDITION
        if (session('payment_processing')) {
            return redirect()->route('cart.index')->with('error', 'Payment already in progress');
        }
        session(['payment_processing' => true]);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            foreach ($cart as $storeId => $store) {
                $storeTotal = 0;

                foreach ($store['items'] as $listingId => $item) {
                    $listing = \App\Models\Listing::find($listingId);

                    if (!$listing) {
                        throw new \Exception('Invalid product detected');
                    }

                    $storeTotal += $listing->price * $item['quantity'];
                }

                $order = \App\Models\Order::create([
                    'user_id' => auth()->id(),
                    'store_id' => $storeId,
                    'total_amount' => $storeTotal,
                    'payment_status' => 'paid',
                    'status' => 'pending',
                    'payment_reference' => $reference,
                ]);

                foreach ($store['items'] as $listingId => $item) {
                    $listing = \App\Models\Listing::find($listingId);

                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'store_id' => $storeId,
                        'listing_id' => $listingId,
                        'quantity' => $item['quantity'],
                        'price' => $listing->price,
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            session()->forget('cart');
            session()->forget('payment_processing');

            return redirect()->route('home')->with('success', 'Payment successful!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            session()->forget('payment_processing');
            \Illuminate\Support\Facades\Log::error('Order creation failed', ['error' => $e->getMessage()]);

            return redirect()->route('cart.index')->with('error', 'Order processing failed');
        }
    }
}
