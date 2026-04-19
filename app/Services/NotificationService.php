<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminNotification;
use App\Notifications\VendorNotification;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Send notification to a specific user based on their role
     */
    public function sendToUser(User $user, array $data): void
    {
        $user->notify(new UserNotification($data));
    }

    /**
     * Send notification to a vendor
     */
    public function sendToVendor(User $vendor, array $data): void
    {
        // Add check to make sure the user is actually a vendor (or specific types)
        if ($vendor->isVendor() || $vendor->isProductSeller() || $vendor->isServiceProvider()) {
            $vendor->notify(new VendorNotification($data));
        }
    }

    /**
     * Send notification to all admins
     */
    public function sendToAdmins(array $data): void
    {
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new AdminNotification($data));
    }

    /**
     * Helper to route notification based on the user's role
     */
    public function sendTo(User $user, array $data): void
    {
        match ($user->role) {
            'admin' => $user->notify(new AdminNotification($data)),
            'vendor' => $user->notify(new VendorNotification($data)),
            default => $user->notify(new UserNotification($data)),
        };
    }
}
