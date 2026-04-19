<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->data['title'] ?? 'Notification from Sahel DigiMart')
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line($this->data['message']);

        if (isset($this->data['action_url']) && isset($this->data['action_text'])) {
            $message->action($this->data['action_text'], $this->data['action_url']);
        }

        return $message->line('Thank you for shopping at Sahel DigiMart!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'],
            'action_url' => $this->data['action_url'] ?? null,
            'type' => $this->data['type'] ?? 'user_alert'
        ];
    }
}
