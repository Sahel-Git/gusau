<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vendor;

    /**
     * Create a new event instance.
     */
    public function __construct(User $vendor)
    {
        $this->vendor = $vendor;
    }
}
