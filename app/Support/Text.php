<?php

namespace App\Support;

class Text
{
    // Убирает html-теги (<p>) и html-коды (&#160;), если они попали в текст как обычные символы
    public static function plain(string $s): string
    {
        $s = preg_replace('~</p>|<br\s*/?>~i', ' ', $s);
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $s));
    }
}
