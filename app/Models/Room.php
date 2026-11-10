<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['book_id', 'owner_id', 'code', 'spoiler_protection'];

    protected $casts = ['spoiler_protection' => 'boolean'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('progress_chapter_id', 'progress_paragraph')
            ->withTimestamps();
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function hasMember(int $userId): bool
    {
        return $this->users()->where('users.id', $userId)->exists();
    }

    // до какого места дошёл читатель: [номер главы, номер абзаца]
    // [0, -1] значит, что он ещё ничего не отмечал
    public function progressOf(int $userId): array
    {
        $user = $this->users->firstWhere('id', $userId);
        $chapterId = $user?->pivot->progress_chapter_id;
        if (!$chapterId) {
            return [0, -1];
        }
        $number = $this->book->chapters->firstWhere('id', $chapterId)?->number ?? 0;
        return [$number, (int) $user->pivot->progress_paragraph];
    }

    public function percentOf(int $userId): int
    {
        [$number, $paragraph] = $this->progressOf($userId);
        $total = $this->book->chapters->sum('paragraph_count');
        if ($number == 0 || $total == 0) {
            return 0;
        }
        $before = $this->book->chapters->where('number', '<', $number)->sum('paragraph_count');
        return (int) min(100, round(($before + $paragraph + 1) * 100 / $total));
    }

    // защита от спойлеров: можно ли показать читателю заметку или закладку друга
    public function visibleTo(int $viewerId, int $authorId, int $chapterNumber, int $paragraph): bool
    {
        if ($authorId === $viewerId || !$this->spoiler_protection) {
            return true;
        }
        return ([$chapterNumber, $paragraph] <=> $this->progressOf($viewerId)) <= 0;
    }
}
