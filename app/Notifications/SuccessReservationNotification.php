<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Reservation $reservation;

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
            ->greeting('Здравствуйте!')
            ->subject('Уведомление об успешном бронировании столика в ресторане')
            ->line('Вы успешно забронировали столик в ресторане ' . $this->reservation->restaurant->name)
            ->line('Ваш номер столика: ' . $this->reservation->table->number)
            ->line('Столик забронирован с ' . $this->reservation->starts_at . ' по ' . $this->reservation->ends_at)
            ->line('Спасибо, что пользуетесь нашим сервисом!');
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
