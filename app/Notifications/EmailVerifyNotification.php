<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerifyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $code;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code)
    {
        $this->code = $code;
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

        $verifyUrl = $frontendUrl . '/verify/email?code=' . $this->code;

        return (new MailMessage)
            ->greeting('Здравстуйте!')
            ->line('Уведомления о подтверждении электронной почты')
            ->line('Вы получили данное письмо, потому что захотели подтвердить электронную почту')
            ->line('Для подтверждения электронной почты, пожалуйста, перейдите по ссылке ниже:')
            ->action('Подтвердить почту', $verifyUrl)
            ->line('Ваш код подтверждения: ' . $this->code)
            ->line(__('verify.email_verification_expiration', ['count' => config('verify.email_verify_expiration', 10)]));
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
