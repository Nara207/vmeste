@extends('layouts.app')
@section('title', 'Админ: заявки')

@section('content')
<div class="eyebrow">Управление</div>
<h1 class="big">Админ-панель</h1>
<p class="muted">Книги, пользователи и комнаты</p>

<div class="admin-top">@include('partials.admin-tabs')</div>

<div class="card yellow">
    <strong>Заявки на книги.</strong>
    <span class="small">Читатели просят добавить книги, которых нет в библиотеке. Сначала книгу нужно загрузить на вкладке «Книги»,
        затем ответить на заявку: пользователь получит уведомление со ссылкой на книгу.</span>
</div>

@php
    $statuses = ['' => 'Все', 'new' => 'Ждут ответа', 'added' => 'Добавлены', 'rejected' => 'Отклонены'];
    $statusItems = [];
    foreach ($statuses as $key => $title) {
        $statusItems[] = ['text' => $title, 'url' => route('admin.requests', array_filter(['q' => $q, 'status' => $key])), 'on' => $status === $key];
    }
@endphp
<form class="toolbar" method="GET" action="{{ route('admin.requests') }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Найти по названию, автору или имени читателя">
    @if ($status !== '') <input type="hidden" name="status" value="{{ $status }}"> @endif
    <button class="btn btn-sm" type="submit">Найти</button>
    @include('partials.dropdown', ['label' => 'Статус', 'items' => $statusItems])
    @if ($q !== '' || $status !== '') <a class="link small" href="{{ route('admin.requests') }}">Сбросить</a> @endif
</form>

<p class="muted small found">
    @if ($q !== '' || $status !== '') Найдено заявок: {{ $found }} из {{ $total }} @else Всего заявок: {{ $total }} @endif
</p>

<div class="card table-wrap">
    <table class="stack">
        <thead><tr><th>Книга</th><th>От кого</th><th>Статус</th><th>Действия</th></tr></thead>
        <tbody>
        @forelse ($requests as $r)
            <tr>
                <td data-label="Книга">
                    <strong>{{ $r->title }}</strong>
                    @if ($r->author)<div class="muted small">{{ $r->author }}</div>@endif
                    @if ($r->comment)<div class="small request-comment">{{ $r->comment }}</div>@endif
                </td>
                <td class="muted" data-label="От кого">{{ $r->user->name }}<div class="small">{{ $r->created_at?->format('d.m.Y') }}</div></td>
                <td data-label="Статус">
                    <span class="chip {{ $r->status === 'new' ? 'chip-yellow' : ($r->status === 'added' ? 'chip-pink-solid' : 'chip-pink') }}">{{ $r->statusLabel() }}</span>
                    @if ($r->admin_reply)<div class="small muted">{{ $r->admin_reply }}</div>@endif
                </td>
                <td class="actions-cell">
                    <div class="row-actions">
                        @if ($r->status === 'new')
                            {{-- если в библиотеке уже есть книга с таким названием, сразу выбираем её --}}
                            @php
                                $guess = $books->first(fn ($b) => mb_stripos($b->title, $r->title) !== false || mb_stripos($r->title, $b->title) !== false);
                            @endphp
                            <details class="reply-form">
                                <summary class="btn btn-sm">Ответить</summary>
                                <form method="POST" action="{{ route('admin.requests.update', $r) }}">
                                    @csrf @method('PUT')
                                    <select name="action">
                                        <option value="added">Книга добавлена</option>
                                        <option value="rejected">Отклонить</option>
                                    </select>
                                    <input type="search" class="book-filter" placeholder="Найти книгу по названию или автору">
                                    <select name="book_id">
                                        <option value="">Какая книга добавлена (если добавлена)</option>
                                        @foreach ($books as $b)
                                            <option value="{{ $b->id }}" {{ $guess && $guess->id === $b->id ? 'selected' : '' }}>{{ $b->title }} — {{ $b->author }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="reply" maxlength="300" placeholder="Комментарий для пользователя (необязательно)">
                                    <div class="reply-buttons">
                                        <button class="btn btn-sm" type="submit">Отправить ответ</button>
                                        <button class="btn btn-outline btn-sm" type="button" data-cancel>Отменить</button>
                                    </div>
                                </form>
                            </details>
                        @endif
                        <form class="inline-form" method="POST" action="{{ route('admin.requests.destroy', $r) }}" onsubmit="return confirm('Удалить заявку?')">
                            @csrf @method('DELETE')
                            <button class="link-btn danger" type="submit">Удалить</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">@if ($total) Ничего не найдено. Измените запрос или <a class="link" href="{{ route('admin.requests') }}">сбросьте фильтры</a>. @else Заявок пока нет @endif</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])

<script>
    // 1. поиск по списку книг в форме ответа: оставляем в списке только подходящие варианты
    document.querySelectorAll('.book-filter').forEach(function (box) {
        var list = box.parentNode.querySelector('select[name=book_id]');
        var all = Array.from(list.options);                // запоминаем все варианты один раз
        box.addEventListener('input', function () {
            var q = box.value.trim().toLowerCase();
            var chosen = list.value;
            list.replaceChildren(all[0]);                   // первая строка-подсказка остаётся всегда
            all.slice(1).forEach(function (o) {
                if (o.text.toLowerCase().indexOf(q) !== -1) list.appendChild(o);
            });
            list.value = list.querySelector('option[value="' + chosen + '"]') ? chosen : '';
        });
    });

    // 2. «Отменить» закрывает форму ответа
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-cancel]');
        if (btn) btn.closest('details').open = false;
    });
</script>
@endsection
