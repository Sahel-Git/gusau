<?php

namespace App\Listeners;

use App\Events\VendorApproved;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendVendorApprovalNotification implements ShouldQueue
{
    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(VendorApproved $event): void
    {
        $this->notificationService->sendToVendor($event->vendor, [
            'title' => 'Account Approved!',
            'message' => 'Congratulations, your vendor account has been approved. You can now start selling.',
            'action_text' => 'Go to Dashboard',
            'action_url' => url('/vendor/dashboard'),
            'type' => 'vendor_approved'
        ]);
    }
}
