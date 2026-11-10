<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Chapter;

/**
 * Превращает текст книги из txt-файла в главы.
 *
 * Два способа отметить главы (выбирает администратор):
 *  - word:   строка начинается с заданного слова («Глава 1. Тропа», «Часть II»);
 *  - number: строка — просто номер (1, 2, 3 или I, II, III) либо номер с названием через
 *            точку или скобку («1. Тропа»).
 *
 * Всё, что идёт после заголовка до следующего заголовка, — текст главы.
 * Если заголовков нет, вся книга становится одной главой «Текст».
 * Каждый абзац — с новой строки.
 */
class BookText
{
    public static function decode(string $raw): string
    {
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // убираем метку BOM
        $encoding = mb_detect_encoding($raw, ['UTF-8', 'Windows-1251'], true) ?: 'Windows-1251';
        return $encoding === 'UTF-8' ? $raw : mb_convert_encoding($raw, 'UTF-8', $encoding);
    }

    // Если строка — заголовок главы, вернёт название главы, иначе null
    public static function headingTitle(string $line, string $mode = 'word', string $marker = 'Глава'): ?string
    {
        $line = trim($line);
        if ($line === '') {
            return null;
        }

        if ($mode === 'number') {
            // Только номер: 12, 12., 12), IV, IV.
            if (preg_match('/^(\d{1,3}|[IVXLCDM]{1,8})\s*[.)]?$/u', $line, $m)) {
                return 'Глава ' . $m[1];
            }
            // Номер и название: «1. Тропа», «2) Стрелка вниз»
            if (preg_match('/^(\d{1,3}|[IVXLCDM]{1,8})\s*[.)]\s*[^\d\s].{0,78}$/u', $line)) {
                return $line;
            }
            return null;
        }

        $marker = trim($marker) !== '' ? trim($marker) : 'Глава';
        return preg_match('/^' . preg_quote($marker, '/') . '(\s|$)/ui', $line) ? $line : null;
    }

    // Возвращает список вида [[название, текст], [название, текст], ...]
    public static function chapters(string $text, string $marker = 'Глава', string $mode = 'word'): array
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
        $chapters = [];
        $title = 'Текст';
        $buffer = '';

        foreach (explode("\n", $text) as $line) {
            $heading = self::headingTitle($line, $mode, $marker);
            if ($heading !== null) {
                if (trim($buffer) !== '') {
                    $chapters[] = [$title, trim($buffer)];
                }
                $title = $heading;
                $buffer = '';
            } else {
                $buffer .= $line . "\n";
            }
        }
        if (trim($buffer) !== '') {
            $chapters[] = [$title, trim($buffer)];
        }
        return $chapters;
    }

    // Заменяет главы книги главами из нового txt-текста
    public static function store(Book $book, string $text, string $marker = 'Глава', string $mode = 'word'): void
    {
        self::storeChapters($book, self::chapters($text, $marker, $mode));
    }

    // Заменяет главы книги готовым списком [[название, текст], ...] (из txt или fb2)
    public static function storeChapters(Book $book, array $chapters): void
    {
        Chapter::where('book_id', $book->id)->delete();

        foreach ($chapters as $i => [$title, $body]) {
            Chapter::create([
                'book_id' => $book->id,
                'number' => $i + 1,
                'title' => $title,
                'body' => $body,
                'paragraph_count' => count(Chapter::splitParagraphs($body)),
            ]);
        }
    }
}
