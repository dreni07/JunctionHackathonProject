<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EventRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells an organizer their submitted event request was accepted by the
 * operations team and registered as a real event — in-app and by email.
 */
class EventRequestAccepted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly EventRequest $eventRequest,
        private readonly Event $event,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->eventRequest->title ?? 'your event';
        $price = $this->eventRequest->price_agreed;

        $mail = (new MailMessage)
            ->subject('Your event is confirmed — '.$title)
            ->greeting('Great news!')
            ->line("Your event “{$title}” has been confirmed and registered by the Pyramid of Tirana operations team.");

        if ($this->event->start_time !== null) {
            $mail->line('When: '.$this->event->start_time->toDayDateTimeString());
        }

        if ($price !== null) {
            $mail->line('Agreed price: €'.number_format((float) $price, 0));
        }

        return $mail
            ->action('View your event', url('/my-events/'.$this->event->id))
            ->line('We look forward to hosting you at the Pyramid.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->eventRequest->title ?? 'Your event';

        return [
            'type' => 'event_request_accepted',
            'title' => 'Your event was accepted',
            'message' => "“{$title}” has been confirmed and registered by the Pyramid operations team.",
            'event_request_id' => $this->eventRequest->id,
            'event_request_title' => $this->eventRequest->title,
            'event_id' => $this->event->id,
        ];
    }
}
