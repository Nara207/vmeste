@extends('layouts.app')
@section('title', 'Моя полка')

@section('content')
<div class="eyebrow">Книжная полка</div>
<h1 class="big">Моя полка</h1>
<p class="muted">Книги, которые вы добавили из библиотеки</p>

<div class="grid-books">
    @foreach ($books as $book)
        <div class="card book-card">
            <a href="{{ route('book', $book) }}">
                @include('partials.cover', ['book' => $book])
            </a>
            <div class="muted small">{{ $book->author }}</div>
            @if ($book->genre)
                <span class="chip {{ $book->id % 2 ? 'chip-yellow' : 'chip-pink' }}">{{ $book->genre }}</span>
            @endif
            <div class="row-btns">
                <form class="inline-form" method="POST" action="{{ route('rooms.store') }}">@csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    <button class="btn btn-sm" type="submit">Читать вместе</button>
                </form>
                <form class="inline-form" method="POST" action="{{ route('shelf.toggle', $book) }}">@csrf
                    <button class="btn btn-sm btn-outline" type="submit">Убрать</button>
                </form>
            </div>
        </div>
    @endforeach

    <a class="dashed" href="{{ route('library') }}">
        <span class="plus">+</span>
        <strong>Добавить книгу</strong>
        <span class="muted small">из библиотеки</span>
    </a>
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])
@endsection
