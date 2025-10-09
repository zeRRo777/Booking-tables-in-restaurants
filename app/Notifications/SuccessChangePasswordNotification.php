<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessChangePasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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

        $urlForResetPassword = $frontendUrl
            . '/forget-password';

        return (new MailMessage)
            ->greeting('Здравствуйте!')
            ->line('Уведомления о успешной смене пароля')
            ->line('Вы получили данное письмо, потому что сменили пароль')
            ->line('Если вы не меняли пароль, то вы можете сбросить пароль и установить новый')
            ->action('Сбросить пароль', $urlForResetPassword);
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
