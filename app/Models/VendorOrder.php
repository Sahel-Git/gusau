<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'commission_percentage',
        'commission_amount',
        'earnings',
        'payout_status',
        'payout_available_at',
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
