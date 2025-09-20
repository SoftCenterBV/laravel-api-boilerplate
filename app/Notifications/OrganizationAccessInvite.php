<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrganizationAccessInvite extends Notification
{
    use Queueable;


    /**
     * Create a new notification instance.
     */
    public function __construct(public string $token)
    {

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->line('You are invited for a company.')
            ->action('Accept', route('api.v1.access.accept', ['token' => $this->token]))
            ->line('If you don\'t know this company')
            ->action('Reject', route('api.v1.access.reject', ['token' => $this->token]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
