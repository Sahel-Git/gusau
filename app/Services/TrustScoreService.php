<?php

namespace App\Services;

use App\Models\User;

class TrustScoreService
{
    /**
     * Adjust a vendor's trust score and manage their flagged status.
     *
     * @param User $vendor
     * @param int $amount Positive or negative adjustment
     * @param string $reason The reason for this adjustment for logging
     */
    public function updateScore(User $vendor, int $amount, string $reason): void
    {
        if (!$vendor->isVendor()) {
            return;
        }

        $oldScore = $vendor->trust_score ?? 100;
        
        // Ensure bounds between 0 and 100
        $newScore = max(0, min(100, $oldScore + $amount));
        $vendor->trust_score = $newScore;

        // Evaluate Flagging threshold
        $flagStatusChanged = false;
        if ($newScore < 40 && !$vendor->is_flagged) {
            $vendor->is_flagged = true;
            $flagStatusChanged = 'flagged';
        } elseif ($newScore >= 50 && $vendor->is_flagged) {
            $vendor->is_flagged = false;
            $flagStatusChanged = 'unflagged';
        }

        $vendor->save();

        if (function_exists('activity_log')) {
            if ($amount < 0) {
                activity_log('Vendor trust score reduced', "Vendor {$vendor->name} (ID: {$vendor->id}) trust score reduced by {$amount}. New Score: {$newScore}. Reason: {$reason}");
            } elseif ($amount > 0) {
                activity_log('Vendor trust score increased', "Vendor {$vendor->name} (ID: {$vendor->id}) trust score increased by {$amount}. New Score: {$newScore}. Reason: {$reason}");
            }

            if ($flagStatusChanged === 'flagged') {
                activity_log('Vendor flagged', "Vendor {$vendor->name} (ID: {$vendor->id}) flagged due to low trust score ({$newScore}).");
            } elseif ($flagStatusChanged === 'unflagged') {
                activity_log('Vendor unflagged', "Vendor {$vendor->name} (ID: {$vendor->id}) unflagged due to improved trust score ({$newScore}).");
            }
        }
    }
}
