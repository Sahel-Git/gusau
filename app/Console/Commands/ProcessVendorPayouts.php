<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class ProcessVendorPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payouts:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Call the command.
     */
    public function handle()
    {
        $this->info('Starting vendor payout processing...');

        // Fetch eligible vendor orders
        $items = OrderItem::where('payout_status', 'pending')
            ->where('payout_available_at', '<=', now())
            ->get();

        if ($items->isEmpty()) {
            $this->info('No pending payouts eligible at this time.');
            return;
        }

        $paystack = new \App\Services\PaystackService();

        foreach ($items as $item) {
            try {
                DB::transaction(function () use ($item, $paystack) {
                    $lockedItem = OrderItem::where('id', $item->id)->lockForUpdate()->first();

                    if ($lockedItem->payout_status !== 'pending') {
                        return;
                    }

                    if ($lockedItem->order && $lockedItem->order->status === 'cancelled') {
                        return;
                    }

                    if (!$lockedItem->store || !$lockedItem->store->isActive()) {
                        $this->warn("Skipping item {$lockedItem->id} - store is not active.");
                        return;
                    }

                    $vendor = $lockedItem->store->user;
                    
                    if (!$vendor || $lockedItem->earnings <= 0) {
                        return;
                    }

                    if ($vendor->is_flagged) {
                        $lockedItem->update(['payout_status' => 'on_hold']);
                        if (function_exists('activity_log')) {
                            activity_log('Payout On Hold', "Vendor {$vendor->id} ({$vendor->name}) payout set to on_hold for item {$lockedItem->id} due to low trust score flag.");
                        }
                        $this->warn("Holding item {$lockedItem->id} - vendor {$vendor->id} is flagged.");
                        return;
                    }

                    $reference = 'PAYOUT_' . $lockedItem->id . '_' . time();

                    $response = $paystack->transfer(
                        amount: $lockedItem->earnings, 
                        recipientCode: 'RCP_xyz', 
                        reference: $reference
                    );

                    if ($response && isset($response['status']) && $response['status']) {
                        $vendor->creditWallet(
                            $lockedItem->earnings, 
                            "Order earnings (Order Item #{$lockedItem->id})", 
                            $reference
                        );

                        $vendor->debitWallet(
                            $lockedItem->earnings, 
                            "Payout to bank (Paystack transfer)", 
                            $reference
                        );

                        $lockedItem->update(['payout_status' => 'paid']);
                        $this->info("Payout complete for item {$lockedItem->id}");
                    } else {
                        $lockedItem->update(['payout_status' => 'failed']);
                        
                        $vendor->creditWallet(
                            $lockedItem->earnings,
                            "Order earnings retained due to transfer failure (Order Item #{$lockedItem->id})",
                            $reference
                        );
                        
                        $this->error("Payout transfer failed for item {$lockedItem->id}");
                    }
                });
            } catch (\Exception $e) {
                if (function_exists('activity_log')) {
                    activity_log('Payout Failed', "Critical failure processing payout for item {$item->id}: " . $e->getMessage());
                }
                $this->error("Exception for item {$item->id}: " . $e->getMessage());
                continue;
            }
        }
    }
}
