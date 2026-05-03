<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'listing_id',
        'store_id',
        'quantity',
        'price',
        'payout_status',
        'commission_percentage',
        'commission_amount',
        'earnings',
        'payout_available_at',
    ];

    protected $casts = [
        'payout_available_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
