<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderReservationNotification extends Notification
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
        $specailWish = $this->reservation->special_wish ?? 'Нет особых пожеланий';
        return (new MailMessage)
            ->greeting('Здравствуйте,' . $this->reservation->user->name .  ' !')
            ->subject('Напоминание о бронировании столика')
            ->line('Напоминаем, что у вас запланировано бронирвоание в ресторане ' . $this->reservation->restaurant->name)
            ->line('Детали бронирования:')
            ->line('Дата и время: c' . $this->reservation->starts_at . ' по ' . $this->reservation->ends_at)
            ->line('Количество гостей: ' . $this->reservation->count_people)
            ->line('Адрес: ' . $this->reservation->restaurant->address)
            ->line('Ваше пожелание: ' . $specailWish)
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
