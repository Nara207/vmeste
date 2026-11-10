@extends('layouts.app')
@section('title', $book->title)

@section('content')
@php
    $readRoom = $myRooms->first();                       // самая новая комната по этой книге
@endphp

<p><a class="back-link" href="{{ route('library') }}">← Библиотека</a></p>

<section class="book-hero">
    {{-- Левая колонка: обложка и короткие факты --}}
    <div class="book-side">
        @include('partials.cover', ['book' => $book, 'class' => 'big-cover'])

        <div class="card facts">
            @if ($book->genre)<div><span>Жанр</span><b>{{ $book->genre }}</b></div>@endif
            <div><span>Глав</span><b>{{ $book->chapters->count() }}</b></div>
            <div><span>Читают</span><b>{{ $readers }}</b></div>
        </div>
    </div>

    {{-- Правая колонка: название, описание, действия --}}
    <div class="book-info">
        <div class="eyebrow">{{ $book->genre ?: 'Книга' }}</div>
        <h1 class="big">{{ $book->title }}</h1>
        <p class="author">{{ $book->author }}</p>
        <div class="rule"></div>

        @if ($book->description)
            <div class="desc" id="desc">{{ $book->description }}</div>
            <button class="link-btn more" id="desc-more" type="button" hidden>Читать полностью</button>
        @endif

        <div class="actions">
            @auth
                @if ($readRoom)
                    <a class="btn" href="{{ route('room', $readRoom) }}">Продолжить чтение</a>
                @else
                    <form method="POST" action="{{ route('rooms.store') }}">@csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <button class="btn" type="submit">Читать вместе</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('shelf.toggle', $book) }}">@csrf
                    <button class="btn btn-outline" type="submit">{{ $onShelf ? 'Убрать с полки' : 'Добавить на полку' }}</button>
                </form>
                @if ($readRoom)
                    <form method="POST" action="{{ route('rooms.store') }}">@csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <button class="link-btn more" type="submit">+ Новая комната</button>
                    </form>
                @endif
            @else
                <a class="btn" href="{{ route('login') }}">Войдите, чтобы читать вместе</a>
            @endauth
        </div>

        @if ($readRoom)
            <p class="muted small">Вы прочитали {{ $readRoom->percentOf(auth()->id()) }}% в комнате {{ $readRoom->code }}.</p>
        @endif
    </div>
</section>

{{-- Мои комнаты по этой книге --}}
@if ($myRooms->isNotEmpty())
    <h2 class="section-title">Ваши комнаты по этой книге</h2>
    <div class="rooms-grid">
        @foreach ($myRooms as $room)
            @php $partners = $room->users->where('id', '!=', auth()->id())->pluck('name')->join(', '); @endphp
            <div class="card room-mini">
                <div class="top">
                    <span class="chip chip-yellow">{{ $room->code }}</span>
                    <span class="muted small">{{ $partners ? 'с ' . $partners : 'ждёт друга' }}</span>
                </div>
                <div class="bar" style="margin:10px 0 4px"><i style="width:{{ $room->percentOf(auth()->id()) }}%"></i></div>
                <div class="muted small">Прочитано {{ $room->percentOf(auth()->id()) }}%</div>
                <div style="margin-top:12px"><a class="btn btn-sm" href="{{ route('room', $room) }}">Открыть</a></div>
            </div>
        @endforeach
    </div>
@endif

{{-- Оглавление --}}
<h2 class="section-title">Оглавление</h2>
<div class="card toc-card">
    <ol class="toc">
        @foreach ($book->chapters as $chapter)
            <li>
                @if ($readRoom)
                    {{-- есть комната по этой книге: открываем главу в ней --}}
                    <a class="toc-link" href="{{ route('room', ['room' => $readRoom->id, 'c' => $chapter->number]) }}"><span>{{ sprintf('%02d', $chapter->number) }}</span> {{ $chapter->title }}</a>
                @elseif (auth()->check())
                    {{-- комнаты нет: создаём её и сразу открываем эту главу --}}
                    <form method="POST" action="{{ route('rooms.store') }}">@csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <input type="hidden" name="chapter" value="{{ $chapter->number }}">
                        <button class="toc-link" type="submit"><span>{{ sprintf('%02d', $chapter->number) }}</span> {{ $chapter->title }}</button>
                    </form>
                @else
                    <a class="toc-link" href="{{ route('login') }}"><span>{{ sprintf('%02d', $chapter->number) }}</span> {{ $chapter->title }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</div>
@if ($readRoom)
    <p class="hint">Главы открываются в вашей последней комнате (код {{ $readRoom->code }}).</p>
@elseif (auth()->check())
    <p class="hint">Нажмите на главу, чтобы начать читать: для вас создастся комната, куда потом можно пригласить друга.</p>
@else
    <p class="hint">Войдите, чтобы читать эту книгу.</p>
@endif

{{-- Похожие книги --}}
@if ($similar->isNotEmpty())
    <h2 class="section-title">Похожие книги</h2>
    <div class="grid-books">
        @foreach ($similar as $other)
            @include('partials.book-card', ['book' => $other])
        @endforeach
    </div>
@endif

<script>
    // Длинное описание сворачиваем; кнопка «Читать полностью» его раскрывает
    var desc = document.getElementById('desc');
    var more = document.getElementById('desc-more');
    if (desc && more) {
        if (desc.scrollHeight > desc.clientHeight + 2) {
            more.hidden = false;
            more.addEventListener('click', function () {
                var open = desc.classList.toggle('open');
                more.textContent = open ? 'Свернуть' : 'Читать полностью';
            });
        }
    }
</script>
@endsection
