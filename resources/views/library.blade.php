@extends('layouts.app')
@section('title', 'Библиотека')

@section('content')
<div class="eyebrow">Каталог</div>
<h1 class="big">Библиотека</h1>
<p class="muted">Выберите книгу и пригласите друга читать вместе</p>

@php
    $sorts = ['title' => 'По названию', 'author' => 'По автору', 'new' => 'Сначала новые', 'size' => 'Сначала длинные'];
    $genreItems = [['text' => 'Все жанры', 'url' => route('library', ['q' => $q ?: null, 'sort' => $sort != 'title' ? $sort : null]), 'on' => $genre == '']];
    foreach ($genres as $g) {
        $genreItems[] = ['text' => $g, 'url' => route('library', ['q' => $q ?: null, 'genre' => $g, 'sort' => $sort != 'title' ? $sort : null]), 'on' => $genre == $g];
    }
    $sortItems = [];
    foreach ($sorts as $key => $title) {
        $sortItems[] = ['text' => $title, 'url' => route('library', ['q' => $q ?: null, 'genre' => $genre ?: null, 'sort' => $key != 'title' ? $key : null]), 'on' => $sort == $key];
    }
@endphp
<form class="toolbar" method="GET" action="{{ route('library') }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Найти книгу или автора">
    @if ($genre) <input type="hidden" name="genre" value="{{ $genre }}"> @endif
    @if ($sort != 'title') <input type="hidden" name="sort" value="{{ $sort }}"> @endif
    <button class="btn btn-sm" type="submit">Найти</button>
    @include('partials.dropdown', ['label' => 'Жанр', 'items' => $genreItems])
    @include('partials.dropdown', ['label' => 'Порядок', 'items' => $sortItems])
</form>

<p class="muted small found">Найдено книг: {{ $found }}</p>
<div class="grid-books">
    @forelse ($books as $book)
        @include('partials.book-card', ['book' => $book])
    @empty
        <p class="muted">Ничего не найдено</p>
    @endforelse
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])

<div class="request-cta">
    <div>
        <strong>Не нашли нужную книгу?</strong>
        <span class="muted small">Оставьте заявку, и администратор добавит её в библиотеку.</span>
    </div>
    <a class="btn btn-outline" href="{{ route('requests') }}">Оставить заявку</a>
</div>
@endsection
