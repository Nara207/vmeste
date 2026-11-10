<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Заявка пользователя на добавление книги в библиотеку
class BookRequest extends Model
{
    protected $fillable = ['user_id', 'title', 'author', 'comment', 'status', 'admin_reply', 'book_id'];

    public const STATUSES = [
        'new' => 'Ждёт рассмотрения',
        'added' => 'Книга добавлена',
        'rejected' => 'Отклонена',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
