<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// Сообщение от сайта: приветствие, смена прав, объявление администратора
class SystemNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $text,
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'system',
            'title' => $this->title,
            'text' => $this->text,
            'url' => $this->url,
        ];
    }
}
