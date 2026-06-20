<?php

namespace App\Notifications;

use App\Models\EventRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells an organizer their event request could not be accepted, with the
 * reason — delivered in-app and by email.
 */
class EventRequestRejected extends Notification
{
    use Queueable;

    public function __construct(
        private readonly EventRequest $eventRequest,
        private readonly string $reason,
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

        return (new MailMessage)
            ->subject('Update on your event request — '.$title)
            ->greeting('Hello,')
            ->line("Thank you for your interest in hosting “{$title}” at the Pyramid of Tirana.")
            ->line('Unfortunately we are unable to confirm this request at the moment.')
            ->line('Reason: '.$this->reason)
            ->line('You are very welcome to adjust the details and submit a new request — we would love to host you.')
            ->action('Plan another event', url('/planner'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->eventRequest->title ?? 'Your event';

        return [
            'type' => 'event_request_rejected',
            'title' => 'Your event request was declined',
            'message' => "“{$title}” could not be confirmed: {$this->reason}",
            'event_request_id' => $this->eventRequest->id,
            'event_request_title' => $this->eventRequest->title,
            'reason' => $this->reason,
        ];
    }
}
