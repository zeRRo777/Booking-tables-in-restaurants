<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeNewEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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
        $frontendUrl = config('app.frontend_url');

        $changeUrl = $frontendUrl
            . '/email-change?token='
            . $this->token;

        return (new MailMessage)
            ->greeting('Здравствуйте!')
            ->subject('Уведомление о смене электронной почты')
            ->line('Вы получили это письмо, потому что вы захотели изменить адрес электронной почты.')
            ->action('Изменить электронную почту', $changeUrl)
            ->line(__('auth.email_change_expiration', ['count' => config('auth.email_change_expiration', 60)]))
            ->line('Если вы не запрашивали смену электронной почты, никаких дальнейших действий не требуется.');
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
