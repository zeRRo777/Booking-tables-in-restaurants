<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Reservation $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
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
        return (new MailMessage)
            ->subject('Ваше бронирование было отменено')
            ->line("Здравствуйте, {$notifiable->name}.")
            ->line("Ваше бронирование #{$this->reservation->id} в ресторане \"{$this->reservation->restaurant->name}\" на {$this->reservation->starts_at->format('d.m.Y H:i')} было отменено администратором.")
            ->line('Если вы считаете, что это ошибка, пожалуйста, свяжитесь с поддержкой.');
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
