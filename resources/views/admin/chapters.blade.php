@extends('layouts.app')
@section('title', 'Админ: главы книги')

@section('content')
<div class="eyebrow">Управление</div>
<h1 class="big">Изменить книгу</h1>
<p class="muted">«{{ $book->title }}»</p>

<a class="back-link" href="{{ route('admin.books') }}">← К списку книг</a>
@include('partials.book-tabs')

<div class="admin-top" style="margin-top:0">
    <span class="muted">Главы книги: добавляйте, правьте и удаляйте по отдельности</span>
    <a class="btn" href="{{ route('admin.chapters.create', $book) }}">+ Добавить главу</a>
</div>

<div class="card yellow">
    <strong>Заметки и закладки привязаны к номерам абзацев.</strong>
    <span class="small">Если вы меняете текст главы (особенно вставляете или удаляете абзацы в середине), записи читателей могут сместиться.
        Если глава становится короче, записи за её концом удаляются. При удалении главы удаляются и её заметки.</span>
</div>

<div class="card table-wrap">
    <table class="stack">
        <thead><tr><th>№</th><th>Название главы</th><th>Абзацев</th><th>Действия</th></tr></thead>
        <tbody>
        @foreach ($chapters as $chapter)
            <tr>
                <td data-label="№">{{ $chapter->number }}</td>
                <td data-label="Название"><strong>{{ $chapter->title }}</strong></td>
                <td data-label="Абзацев">{{ $chapter->paragraph_count }}</td>
                <td class="actions-cell">
                    <div class="row-actions">
                        <a class="link" href="{{ route('admin.chapters.edit', [$book, $chapter]) }}">Изменить</a>
                        <form class="inline-form" method="POST" action="{{ route('admin.chapters.destroy', [$book, $chapter]) }}"
                              onsubmit="return confirm('Удалить главу «{{ $chapter->title }}» вместе с заметками и закладками к ней?')">
                            @csrf @method('DELETE')
                            <button class="link-btn danger" type="submit">Удалить</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])
@endsection
