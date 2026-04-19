<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderNotifications implements ShouldQueue
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
    public function handle(OrderPlaced $event): void
    {
        // Notify User
        $this->notificationService->sendToUser($event->user, [
            'title' => 'Order Confirmation - #' . ($event->orderData['order_id'] ?? 'N/A'),
            'message' => 'Your order has been placed successfully.',
            'action_text' => 'View Order',
            'action_url' => url('/orders'),
            'type' => 'order_placed'
        ]);

        // Notify Vendor
        $this->notificationService->sendToVendor($event->vendor, [
            'title' => 'New Order Alert - #' . ($event->orderData['order_id'] ?? 'N/A'),
            'message' => 'You have received a new order.',
            'action_text' => 'Manage Order',
            'action_url' => url('/vendor/orders'),
            'type' => 'new_order'
        ]);
    }
}
