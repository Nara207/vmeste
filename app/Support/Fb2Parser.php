<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;

/**
 * Читает книгу формата fb2 (FictionBook 2).
 * Возвращает: ['meta' => [название, автор, жанр, описание], 'chapters' => [[название, текст], ...]],
 * где текст главы — абзацы, каждый с новой строки (так же, как у txt).
 */
class Fb2Parser
{
    // Коды жанров fb2 → понятные названия (проверяется начало кода)
    private const GENRES = [
        'prose_classic' => 'Классика', 'prose_rus_classic' => 'Классика', 'prose_su_classics' => 'Классика',
        'prose_history' => 'Историческая проза', 'prose' => 'Проза',
        'adv' => 'Приключения', 'sf' => 'Фантастика', 'det' => 'Детектив', 'thriller' => 'Триллер',
        'love' => 'Романтика', 'child' => 'Детская литература', 'poetry' => 'Поэзия',
        'humor' => 'Юмор', 'sci' => 'Научная литература', 'tale' => 'Сказки',
        'antique' => 'Старинная литература', 'horror' => 'Ужасы',
    ];

    public static function parse(string $xml): array
    {
        $dom = self::load($xml);
        $xp = new DOMXPath($dom);

        // Основной текст — первый <body> без имени (у <body name="notes"> лежат сноски, их пропускаем)
        $body = $xp->query("/*[local-name()='FictionBook']/*[local-name()='body'][not(@name)]")->item(0)
            ?? $xp->query("/*[local-name()='FictionBook']/*[local-name()='body']")->item(0);
        if (!$body instanceof DOMElement) {
            throw new ImportException('В файле fb2 не найден текст книги.');
        }

        $chapters = [];

        $intro = self::paragraphs($body);
        if ($intro) {
            $chapters[] = ['Начало', $intro];
        }
        foreach (self::children($body, 'section') as $section) {
            self::walk($section, $chapters);
        }

        $result = [];
        foreach ($chapters as $i => [$title, $paragraphs]) {
            $title = $title ?: 'Глава ' . ($i + 1);
            $result[] = [mb_substr($title, 0, 250), implode("\n", $paragraphs)];
        }
        if (!$result) {
            throw new ImportException('В файле fb2 нет текста.');
        }

        return ['meta' => self::meta($xp), 'chapters' => $result, 'cover' => self::cover($xp)];
    }

    private static function load(string $xml): DOMDocument
    {
        $xml = preg_replace('/^\xEF\xBB\xBF/', '', $xml);
        $flags = LIBXML_NONET | LIBXML_PARSEHUGE;
        $previous = libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $ok = $dom->loadXML($xml, $flags);

        if (!$ok) {
            // Запасной вариант: файл в Windows-1251 без правильного объявления кодировки
            $fixed = preg_replace('/^<\?xml[^>]*\?>/u', '', mb_convert_encoding($xml, 'UTF-8', 'Windows-1251'));
            $dom = new DOMDocument();
            $ok = $fixed !== null && $dom->loadXML($fixed, $flags);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$ok || !$dom->documentElement || $dom->documentElement->localName !== 'FictionBook') {
            throw new ImportException('Не удалось прочитать fb2: файл повреждён или это не fb2.');
        }
        return $dom;
    }

    // Один раздел книги: собственные абзацы — глава, вложенные разделы — следующие главы
    private static function walk(DOMElement $section, array &$chapters): void
    {
        $paragraphs = self::paragraphs($section);
        if ($paragraphs) {
            $chapters[] = [self::title($section), $paragraphs];
        }
        foreach (self::children($section, 'section') as $sub) {
            self::walk($sub, $chapters);
        }
    }

    private static function title(DOMElement $section): ?string
    {
        $parts = [];
        foreach (self::children($section, 'title') as $title) {
            foreach (self::children($title, 'p') as $p) {
                $text = self::text($p);
                if ($text !== '') {
                    $parts[] = $text;
                }
            }
        }
        $title = trim(implode(' ', $parts));
        if ($title === '') {
            return null;
        }
        // Заголовок из одной цифры или римской цифры превращаем в «Глава 5»
        if (preg_match('/^(\d{1,3}|[IVXLCDM]{1,8})\.?$/u', $title, $m)) {
            return 'Глава ' . $m[1];
        }
        return $title;
    }

    // Абзацы, лежащие прямо в узле (заголовки и вложенные разделы не берём)
    private static function paragraphs(DOMElement $node): array
    {
        $out = [];
        foreach ($node->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }
            switch ($child->localName) {
                case 'p':
                case 'v':
                case 'subtitle':
                case 'text-author':
                    $text = self::text($child);
                    if ($text !== '') {
                        $out[] = $text;
                    }
                    break;
                case 'poem':
                case 'stanza':
                case 'epigraph':
                case 'cite':
                case 'annotation':
                    $out = array_merge($out, self::paragraphs($child));
                    break;
            }
        }
        return $out;
    }

    // Текст узла без ссылок на сноски и картинок, с одинарными пробелами
    private static function text(DOMNode $node): string
    {
        $s = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText) {
                $s .= $child->nodeValue;
            } elseif ($child instanceof DOMElement) {
                if ($child->localName === 'image' || ($child->localName === 'a' && $child->getAttribute('type') === 'note')) {
                    continue;
                }
                $s .= self::text($child);
            }
        }
        // Некоторые программы кладут в fb2 html-коды как обычный текст («&#160;»), расшифровываем их
        if (preg_match('/&#\d+;|&nbsp;/i', $s)) {
            $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $s));
    }

    // Для названия, автора и описания: убираем html-теги (<p>) и html-коды, если они попали в fb2 как текст
    private static function clean(string $s): string
    {
        $s = preg_replace('~</p>|<br\s*/?>~i', ' ', $s);
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $s));
    }

    private static function children(DOMNode $node, string $name): array
    {
        $out = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $name) {
                $out[] = $child;
            }
        }
        return $out;
    }

    // Обложка из fb2 (если есть): ['data' => байты картинки, 'ext' => jpg|png|webp] или null
    private static function cover(DOMXPath $xp): ?array
    {
        $img = $xp->query("/*[local-name()='FictionBook']/*[local-name()='description']/*[local-name()='title-info']/*[local-name()='coverpage']/*[local-name()='image']")->item(0);
        if (!$img instanceof DOMElement) {
            return null;
        }

        $href = $img->getAttributeNS('http://www.w3.org/1999/xlink', 'href')
            ?: $img->getAttribute('l:href')
            ?: $img->getAttribute('xlink:href')
            ?: $img->getAttribute('href');
        $id = ltrim($href, '#');
        if ($id === '') {
            return null;
        }

        // Картинка лежит в конце файла в <binary id="..."> в виде base64
        foreach ($xp->query("/*[local-name()='FictionBook']/*[local-name()='binary']") as $binary) {
            if ($binary->getAttribute('id') !== $id) {
                continue;
            }
            $data = base64_decode(preg_replace('/\s+/', '', $binary->textContent), true);
            if ($data === false || strlen($data) > 3 * 1024 * 1024) {
                return null;
            }
            $info = @getimagesizefromstring($data);
            $ext = match ($info['mime'] ?? '') {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => null,
            };
            return $ext ? ['data' => $data, 'ext' => $ext] : null;
        }
        return null;
    }

    // Название, автор, жанр и описание из <description>
    private static function meta(DOMXPath $xp): array
    {
        $info = $xp->query("/*[local-name()='FictionBook']/*[local-name()='description']/*[local-name()='title-info']")->item(0);
        if (!$info instanceof DOMElement) {
            return [];
        }

        $first = fn (string $name) => self::children($info, $name)[0] ?? null;

        $title = $first('book-title') ? self::text($first('book-title')) : '';

        $author = '';
        if ($a = $first('author')) {
            $names = [];
            foreach (['first-name', 'middle-name', 'last-name'] as $part) {
                $el = self::children($a, $part)[0] ?? null;
                if ($el && self::text($el) !== '') {
                    $names[] = self::text($el);
                }
            }
            $author = implode(' ', $names);
            if ($author === '' && ($nick = self::children($a, 'nickname')[0] ?? null)) {
                $author = self::text($nick);
            }
        }

        $genre = null;
        if ($g = $first('genre')) {
            $code = strtolower(trim($g->textContent));
            foreach (self::GENRES as $prefix => $name) {
                if (str_starts_with($code, $prefix)) {
                    $genre = $name;
                    break;
                }
            }
        }

        $description = '';
        if ($ann = $first('annotation')) {
            $description = self::clean(implode(' ', self::paragraphs($ann)));
            if (mb_strlen($description) > 1800) {
                $description = mb_substr($description, 0, 1800) . '…';
            }
        }

        return [
            'title' => mb_substr(self::clean($title), 0, 255),
            'author' => mb_substr(self::clean($author), 0, 255),
            'genre' => $genre,
            'description' => $description,
        ];
    }
}
