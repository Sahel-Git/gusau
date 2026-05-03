<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    /**
     * Credit the user's wallet with locked synchronization.
     * Ensure reference_id does not already exist.
     */
    public function credit(User $user, float $amount, string $description, ?string $referenceId = null)
    {
        return DB::transaction(function () use ($user, $amount, $description, $referenceId) {
            if ($referenceId && WalletTransaction::where('reference_id', $referenceId)->exists()) {
                if (function_exists('activity_log')) {
                    activity_log('Duplicate Wallet Credit Prevented', "Skipped duplicate credit for reference: {$referenceId}");
                }
                return null;
            }

            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            $lockedUser->wallet_balance += $amount;
            $lockedUser->save();

            $user->wallet_balance = $lockedUser->wallet_balance;

            return WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance' => $lockedUser->wallet_balance,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);
        });
    }

    /**
     * Debit the user's wallet with locked synchronization.
     * Prevents negative balance exception.
     */
    public function debit(User $user, float $amount, string $description, ?string $referenceId = null)
    {
        return DB::transaction(function () use ($user, $amount, $description, $referenceId) {
            if ($referenceId && WalletTransaction::where('reference_id', $referenceId)->exists()) {
                if (function_exists('activity_log')) {
                    activity_log('Duplicate Wallet Debit Prevented', "Skipped duplicate debit for reference: {$referenceId}");
                }
                return null;
            }

            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();

            if ($lockedUser->wallet_balance < $amount) {
                throw new Exception("Insufficient wallet balance for user ID: {$user->id}. Available: {$lockedUser->wallet_balance}, Attempted: {$amount}");
            }

            $lockedUser->wallet_balance -= $amount;
            $lockedUser->save();

            $user->wallet_balance = $lockedUser->wallet_balance;

            return WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance' => $lockedUser->wallet_balance,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);
        });
    }
}
