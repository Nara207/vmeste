<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// «Друг поставил закладку». Место в книге не раскрываем: это может быть спойлер.
class NewBookmarkNotification extends Notification
{
    public function __construct(
        public int $roomId,
        public string $bookTitle,
        public string $fromName,
        public int $fromId,
        public int $count = 1,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'bookmark',
            'room_id' => $this->roomId,
            'book_title' => $this->bookTitle,
            'from_name' => $this->fromName,
            'from_id' => $this->fromId,
            'count' => $this->count,
            'url' => route('room', $this->roomId, false),
        ];
    }
}
