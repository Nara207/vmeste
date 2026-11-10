{{-- Вкладки при правке книги: «Общая информация» и «Главы». Нужна переменная $book --}}
<div class="subtabs">
    <a class="subtab {{ request()->routeIs('admin.books.edit', 'admin.books.update') ? 'on' : '' }}" href="{{ route('admin.books.edit', $book) }}">Общая информация</a>
    <a class="subtab {{ request()->routeIs('admin.chapters*') ? 'on' : '' }}" href="{{ route('admin.chapters', $book) }}">Главы <span class="muted small">({{ $book->chapters()->count() }})</span></a>
</div>
