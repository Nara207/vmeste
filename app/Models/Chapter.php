<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = ['book_id', 'number', 'title', 'body', 'paragraph_count'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Абзац = непустая строка текста
    public static function splitParagraphs(string $body): array
    {
        $lines = array_map('trim', preg_split('/\R/u', $body));
        return array_values(array_filter($lines, fn ($line) => $line !== ''));
    }

    public function paragraphs(): array
    {
        return self::splitParagraphs($this->body);
    }
}
