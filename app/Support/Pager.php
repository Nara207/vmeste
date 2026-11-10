<?php

namespace App\Support;

use Illuminate\Support\Collection;

// постраничный вывод: берём из списка одну страницу
// кнопки «Назад» и «Вперёд» рисует шаблон partials/pagination.blade.php
class Pager
{
    // возвращает: элементы страницы, номер страницы, сколько всего страниц
    public static function take(Collection $items, int $perPage, int $page): array
    {
        $pages = max(1, (int) ceil($items->count() / $perPage));
        $page = min(max(1, $page), $pages);   // номер страницы не может быть больше последней

        return [$items->forPage($page, $perPage), $page, $pages];
    }
}
