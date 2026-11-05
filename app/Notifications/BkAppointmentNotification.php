<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BkAppointmentNotification extends Notification
{
    public function __construct(
        private readonly string $event,
        private readonly string $title,
        private readonly string $message,
        private readonly string $url,
    ) {
    }

    /**
     * Notifications are stored in the application's database for the bell menu.
     * No private student note or screening information is included in this payload.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
