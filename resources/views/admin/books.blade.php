@extends('layouts.app')
@section('title', 'Админ: книги')

@section('content')
<div class="eyebrow">Управление</div>
<h1 class="big">Админ-панель</h1>
<p class="muted">Книги, пользователи и комнаты</p>

<div class="admin-top">
    @include('partials.admin-tabs')
    <a class="btn" href="{{ route('admin.books.create') }}">+ Добавить книгу</a>
</div>

@php
    $sorts = ['title' => 'По названию', 'author' => 'По автору', 'new' => 'Сначала новые', 'size' => 'Сначала длинные'];
    $keep = fn ($extra) => route('admin.books', array_filter(array_merge(['q' => $q, 'genre' => $genre, 'sort' => $sort != 'title' ? $sort : null], $extra), fn ($v) => $v !== null && $v !== ''));
    $genreItems = [['text' => 'Все жанры', 'url' => $keep(['genre' => null]), 'on' => $genre == '']];
    foreach ($genres as $g) {
        $genreItems[] = ['text' => $g, 'url' => $keep(['genre' => $g]), 'on' => $genre == $g];
    }
    $sortItems = [];
    foreach ($sorts as $key => $title) {
        $sortItems[] = ['text' => $title, 'url' => $keep(['sort' => $key != 'title' ? $key : null]), 'on' => $sort == $key];
    }
@endphp
<form class="toolbar" method="GET" action="{{ route('admin.books') }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Найти книгу или автора">
    @if ($genre) <input type="hidden" name="genre" value="{{ $genre }}"> @endif
    @if ($sort != 'title') <input type="hidden" name="sort" value="{{ $sort }}"> @endif
    <button class="btn btn-sm" type="submit">Найти</button>
    @include('partials.dropdown', ['label' => 'Жанр', 'items' => $genreItems])
    @include('partials.dropdown', ['label' => 'Порядок', 'items' => $sortItems])
    @if ($q !== '' || $genre !== '') <a class="link small" href="{{ route('admin.books') }}">Сбросить</a> @endif
</form>

<p class="muted small found">
    @if ($q !== '' || $genre !== '') Найдено книг: {{ $found }} из {{ $total }} @else Всего книг: {{ $total }} @endif
</p>

<div class="card table-wrap">
    <table class="stack">
        <thead><tr><th>Название</th><th>Автор</th><th>Жанр</th><th>Глав</th><th>Действия</th></tr></thead>
        <tbody>
        @forelse ($books as $book)
            <tr>
                <td data-label="Название"><a class="cell-link" href="{{ route('book', $book) }}" title="Открыть страницу книги"><strong>{{ $book->title }}</strong></a></td>
                <td class="muted" data-label="Автор">{{ $book->author }}</td>
                <td data-label="Жанр">@if ($book->genre)<span class="chip {{ $book->id % 2 ? 'chip-yellow' : 'chip-pink' }}">{{ $book->genre }}</span>@endif</td>
                <td data-label="Глав">{{ $book->chapters_count }}</td>
                <td class="actions-cell">
                    <div class="row-actions">
                        <a class="link" href="{{ route('admin.books.edit', $book) }}">Изменить</a>
                        <form class="inline-form" method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Удалить книгу «{{ $book->title }}»?')">
                            @csrf @method('DELETE')
                            <button class="link-btn danger" type="submit">Удалить</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Ничего не найдено. Измените запрос или <a class="link" href="{{ route('admin.books') }}">сбросьте фильтры</a>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])
@endsection
