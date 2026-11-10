<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Роли (is_admin, is_owner) намеренно НЕ входят в этот список: их нельзя выдать через форму,
    // только явным кодом (forceFill) в админке или командой user:admin.
    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',   // пароль сам превращается в хеш при сохранении
        'is_admin' => 'boolean',
        'is_owner' => 'boolean',
    ];

    // Laravel сам вызывает этот метод, когда нужно отправить письмо для сброса пароля
    public function sendPasswordResetNotification($token): void
    {
        $url = route('password.reset', ['token' => $token, 'email' => $this->email]);
        Mail::to($this->email)->send(new ResetPasswordMail($this, $url));
    }

    // Книги на «Моей полке»
    public function shelf()
    {
        return $this->belongsToMany(Book::class)->withTimestamps();
    }

    // Комнаты, в которых состоит пользователь
    public function rooms()
    {
        return $this->belongsToMany(Room::class)
            ->withPivot('progress_chapter_id', 'progress_paragraph')
            ->withTimestamps();
    }
}
