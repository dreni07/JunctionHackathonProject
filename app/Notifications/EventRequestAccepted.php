<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EventRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Tells an organizer their submitted event request was accepted by the
 * operations team and registered as a real event.
 */
class EventRequestAccepted extends Notification
{
    use Queueable;

    public function __construct(
        private readonly EventRequest $eventRequest,
        private readonly Event $event,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
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
