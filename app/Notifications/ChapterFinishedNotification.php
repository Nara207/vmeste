<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// «Друг дочитал главу»
class ChapterFinishedNotification extends Notification
{
    public function __construct(
        public int $roomId,
        public string $bookTitle,
        public string $fromName,
        public int $chapterNumber,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'chapter',
            'room_id' => $this->roomId,
            'book_title' => $this->bookTitle,
            'from_name' => $this->fromName,
            'chapter' => $this->chapterNumber,
            'url' => route('room', $this->roomId, false),
        ];
    }
}
