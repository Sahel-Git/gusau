<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id', 
        'category_id', 
        'type', 
        'title', 
        'slug', 
        'description', 
        'price', 
        'stock', 
        'status', 
        'images'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    
    public function vendor()
    {
        // Helpful alias to get the underlying user
        return $this->hasOneThrough(User::class, Store::class, 'id', 'id', 'store_id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isProduct(): bool
    {
        return $this->type === 'product';
    }

    public function isService(): bool
    {
        return $this->type === 'service';
    }

    public function hasStock(): bool
    {
        return $this->isProduct() && $this->stock !== null && $this->stock > 0;
    }
}
