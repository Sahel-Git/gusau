<?php

namespace App\Actions;

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class CreateOrder
{
    /**
     * Handle the order creation securely.
     */
    public function execute(User $buyer, int $listingId, int $quantity): Order
    {
        // 1. Fetch listing and lock it for update if dealing with stock
        $listing = Listing::findOrFail($listingId);

        // 2. Validate Listing Status
        if ($listing->status !== 'approved') {
            throw ValidationException::withMessages(['listing' => 'This item is not available for purchase.']);
        }

        // 3. Validate Stock (if applicable)
        if ($listing->type === 'product') {
            if ($listing->stock < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Not enough stock available.']);
            }
        }

        // 4. Validate quantity
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        // We use the exact DB price to prevent client-side manipulation
        $totalAmount = $listing->price * $quantity;

        // 5. Database Transaction for atomicity
        return DB::transaction(function () use ($buyer, $listing, $quantity, $totalAmount) {
            // Re-fetch listing with lockForUpdate to ensure concurrency safety for stock decrement
            $lockedListing = Listing::where('id', $listing->id)->lockForUpdate()->first();
            
            if ($lockedListing->type === 'product' && $lockedListing->stock < $quantity) {
                 throw new Exception('Stock ran out during checkout process.');
            }

            // Create Order
            $order = Order::create([
                'user_id' => $buyer->id,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Create Order Item
            $order->items()->create([
                'listing_id' => $lockedListing->id,
                'store_id' => $lockedListing->store_id, // Safely grabbed from the DB model
                'quantity' => $quantity,
                'price' => $lockedListing->price, // Locked in
            ]);

            // Create initial pending transaction
            $order->transaction()->create([
                'amount' => $totalAmount,
                'status' => 'pending',
                'payment_reference' => 'TXN_' . strtoupper(uniqid()), // Mock reference for now
            ]);

            // Decrement Stock
            if ($lockedListing->type === 'product') {
                $lockedListing->decrement('stock', $quantity);
            }

            return $order;
        });
    }
}
