<?php

namespace App\Models;

use App\Support\Text;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'author', 'genre', 'description', 'cover_path'];

    // Название, автор и описание всегда показываем без html-тегов и html-кодов
    protected function title(): Attribute
    {
        return Attribute::get(fn ($value) => $value === null ? null : Text::plain($value));
    }

    protected function author(): Attribute
    {
        return Attribute::get(fn ($value) => $value === null ? null : Text::plain($value));
    }

    protected function description(): Attribute
    {
        return Attribute::get(fn ($value) => $value === null ? null : Text::plain($value));
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('number');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
