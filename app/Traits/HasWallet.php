<?php

namespace App\Traits;

use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Services\WalletService;

trait HasWallet
{
    public function creditWallet($amount, string $description, ?string $referenceId = null)
    {
        return app(WalletService::class)->credit($this, $amount, $description, $referenceId);
    }

    public function debitWallet($amount, string $description, ?string $referenceId = null)
    {
        return app(WalletService::class)->debit($this, $amount, $description, $referenceId);
    }

    public function validateWalletIntegrity(): bool
    {
        $credits = WalletTransaction::where('user_id', $this->id)->where('type', 'credit')->sum('amount');
        $debits = WalletTransaction::where('user_id', $this->id)->where('type', 'debit')->sum('amount');
        
        $calculatedBalance = round($credits - $debits, 2);
        return $calculatedBalance === round((float)$this->wallet_balance, 2);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }
}
