<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendUserRegistrationNotification implements ShouldQueue
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
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        // Notify the user
        $this->notificationService->sendToUser($user, [
            'title' => 'Welcome to Sahel DigiMart',
            'message' => 'Thank you for registering. We are excited to have you on board.',
            'action_text' => 'Visit Site',
            'action_url' => url('/'),
            'type' => 'welcome_email'
        ]);

        // Notify admins about the new user or vendor
        $adminMessage = "A new {$user->role} ({$user->name}) has registered.";
        $this->notificationService->sendToAdmins([
            'title' => 'New ' . ucfirst($user->role) . ' Registration',
            'message' => $adminMessage,
            'type' => 'new_registration'
        ]);
    }
}
