@extends('layouts.app')
@section('title', 'Мои комнаты')

@section('content')
<div class="eyebrow">Читаем вместе</div>
<h1 class="big">Мои комнаты</h1>
<p class="muted">Книги, которые вы читаете вместе с друзьями</p>

@forelse ($rooms as $room)
    @php
        $partners = $room->users->where('id', '!=', auth()->id())->pluck('name')->join(', ');
    @endphp
    <div class="card room-row">
        @include('partials.cover', ['book' => $room->book, 'class' => 'mini-cover'])
        <div class="grow">
            <h2>{{ $room->book->title }}</h2>
            <div class="muted small">Вы и {{ $partners ?: 'пока никого' }} · код {{ $room->code }}</div>
            <div class="bar"><i style="width:{{ $room->percentOf(auth()->id()) }}%"></i></div>
        </div>
        <a class="btn" href="{{ route('room', $room) }}">Открыть</a>
    </div>
@empty
    <div class="card"><p class="muted" style="margin:0">Пока нет ни одной комнаты. Выберите книгу в <a class="link" href="{{ route('library') }}">библиотеке</a> и нажмите «Читать вместе».</p></div>
@endforelse

@include('partials.pagination', ['page' => $page, 'pages' => $pages])

<form class="card yellow join" method="POST" action="{{ route('rooms.join') }}">
    @csrf
    <h2>Присоединиться по коду</h2>
    <div class="inline">
        <input type="text" name="code" placeholder="Код от друга" maxlength="8" required>
        <button class="btn" type="submit">Войти</button>
    </div>
</form>
@endsection
