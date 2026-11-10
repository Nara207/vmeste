<?php

namespace App\Support;

use App\Models\Room;
use App\Models\User;
use App\Notifications\ChapterFinishedNotification;
use App\Notifications\FriendJoinedNotification;
use App\Notifications\NewBookmarkNotification;
use App\Notifications\NewNoteNotification;
use App\Notifications\SystemNotification;

// отсюда отправляются все уведомления,
// контроллеры вызывают короткие методы
class Notifier
{
    // друг оставил заметку; если непрочитанное уведомление уже есть,
    // просто увеличиваем число («3 новые заметки»)
    public static function newNote(User $recipient, Room $room, User $from): void
    {
        self::counted(NewNoteNotification::class, $recipient, $room, $from);
    }

    // друг поставил закладку (работает так же, как с заметками)
    public static function newBookmark(User $recipient, Room $room, User $from): void
    {
        self::counted(NewBookmarkNotification::class, $recipient, $room, $from);
    }

    // друг дочитал главу
    public static function chapterFinished(User $recipient, Room $room, User $from, int $chapterNumber): void
    {
        $recipient->notify(new ChapterFinishedNotification($room->id, $room->book->title, $from->name, $chapterNumber));
    }

    // общая часть для заметок и закладок: увеличить число или создать новое уведомление
    private static function counted(string $class, User $recipient, Room $room, User $from): void
    {
        $existing = $recipient->unreadNotifications()
            ->where('type', $class)
            ->get()
            ->first(fn ($n) => ($n->data['room_id'] ?? null) === $room->id && ($n->data['from_id'] ?? null) === $from->id);

        if ($existing) {
            $data = $existing->data;
            $data['count'] = ($data['count'] ?? 1) + 1;
            $existing->forceFill(['data' => $data])->save();
            return;
        }

        $recipient->notify(new $class($room->id, $room->book->title, $from->name, $from->id));
    }

    // кто-то вошёл в комнату, говорим остальным
    public static function joined(Room $room, User $joiner): void
    {
        $room->loadMissing(['book', 'users']);

        foreach ($room->users->where('id', '!=', $joiner->id) as $other) {
            $other->notify(new FriendJoinedNotification($room->id, $room->book->title, $joiner->name));
        }
    }

    // сообщение от сайта
    public static function system(User $recipient, string $title, string $text, ?string $url = null): void
    {
        $recipient->notify(new SystemNotification($title, $text, $url));
    }
}
