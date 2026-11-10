{{--
    Переключатель страниц для списков в админ-панели.
    Нужны: $page (текущая страница), $pages (сколько всего страниц).
    Остальные параметры адреса (поиск, жанр, порядок) сохраняются.
--}}
@if ($pages > 1)
    @php $query = request()->except('page'); @endphp
    <nav class="pages" aria-label="Страницы">
        @if ($page > 1)
            <a class="btn btn-outline btn-sm" href="{{ request()->url() . '?' . http_build_query($query + ['page' => $page - 1]) }}">← Назад</a>
        @else
            <span class="btn btn-outline btn-sm is-off" aria-disabled="true">← Назад</span>
        @endif
        <span class="muted small">Страница {{ $page }} из {{ $pages }}</span>
        @if ($page < $pages)
            <a class="btn btn-outline btn-sm" href="{{ request()->url() . '?' . http_build_query($query + ['page' => $page + 1]) }}">Вперёд →</a>
        @else
            <span class="btn btn-outline btn-sm is-off" aria-disabled="true">Вперёд →</span>
        @endif
    </nav>
@endif
