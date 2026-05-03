<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\TrustScoreService;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === 'completed') {
            // Loop over items and set vendor order parameters
            foreach ($order->items as $item) {
                // Determine commission (example: 5% flat for now, this can be moved to config or Setting model)
                $commissionPercentage = 5.00;
                $totalItemPrice = $item->price * $item->quantity;
                $commissionAmount = $totalItemPrice * ($commissionPercentage / 100);
                $earnings = $totalItemPrice - $commissionAmount;

                $item->update([
                    'commission_percentage' => $commissionPercentage,
                    'commission_amount' => $commissionAmount,
                    'earnings' => $earnings,
                    'payout_status' => 'pending',
                    'payout_available_at' => now()->addHours(48),
                ]);
            }

            // Trust Score: Reward vendors for successfully completed order
            try {
                $uniqueVendorIds = $order->items->pluck('store.user_id')->unique();
                $vendors = \App\Models\User::whereIn('id', $uniqueVendorIds)->get();
                $trustService = app(TrustScoreService::class);
                
                foreach ($vendors as $vendor) {
                    $trustService->updateScore($vendor, 1, 'Successfully completed order #' . $order->id);
                }
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
