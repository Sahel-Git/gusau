<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderData;
    public $user;
    public $vendor;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, User $vendor, array $orderData)
    {
        $this->user = $user;
        $this->vendor = $vendor;
        $this->orderData = $orderData;
    }
}
