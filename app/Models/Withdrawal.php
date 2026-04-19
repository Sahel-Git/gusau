<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = ['vendor_id', 'amount', 'status'];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
