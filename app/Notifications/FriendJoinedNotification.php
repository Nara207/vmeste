<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// «Друг присоединился к комнате»
class FriendJoinedNotification extends Notification
{
    public function __construct(
        public int $roomId,
        public string $bookTitle,
        public string $fromName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'joined',
            'room_id' => $this->roomId,
            'book_title' => $this->bookTitle,
            'from_name' => $this->fromName,
            'url' => route('room', $this->roomId, false),
        ];
    }
}
