@extends('layouts.app')
@section('title', 'Закладки и заметки')

@section('content')
<p><a class="back-link" href="{{ route('room', $room) }}">← К чтению</a></p>
<div class="eyebrow">{{ $room->book->title }}</div>
<h1 class="big">Закладки и заметки</h1>

<div class="toolbar">
    <a class="chip chip-btn {{ $who == '' ? 'on' : '' }}" href="{{ route('room.notes', $room) }}">Все</a>
    <a class="chip chip-btn {{ $who == 'me' ? 'on' : '' }}" href="{{ route('room.notes', [$room, 'u' => 'me']) }}">Мои</a>
    <a class="chip chip-btn {{ $who == 'friend' ? 'on' : '' }}" href="{{ route('room.notes', [$room, 'u' => 'friend']) }}">Друга</a>
</div>

<div class="two-cols">
    <div class="card">
        <h2>Заметки</h2>
        @forelse ($notes as $n)
            <div class="note {{ $n->user_id == auth()->id() ? 'me' : 'friend' }}" style="margin-left:0">
                <b>{{ $n->user_id == auth()->id() ? 'Вы' : $n->user->name }} · глава {{ $n->chapter->number }}, абзац {{ $n->paragraph + 1 }}</b>
                <span class="note-body">{{ $n->body }}</span>
                <div class="actions"><a class="link small" href="{{ route('room', ['room' => $room->id, 'c' => $n->chapter->number]) }}#p{{ $n->paragraph }}">Перейти к месту</a></div>
            </div>
        @empty
            <p class="muted">Заметок нет</p>
        @endforelse
    </div>

    <div class="card">
        <h2>Закладки</h2>
        @forelse ($marks as $m)
            <div class="mark-row">
                <i class="sq {{ $m->user_id == auth()->id() ? 'pink' : 'yellow' }}"></i>
                <a class="link" href="{{ route('room', ['room' => $room->id, 'c' => $m->chapter->number]) }}#p{{ $m->paragraph }}">Глава {{ $m->chapter->number }}, абзац {{ $m->paragraph + 1 }}</a>
                <span class="muted small">· {{ $m->user_id == auth()->id() ? 'вы' : $m->user->name }}</span>
            </div>
        @empty
            <p class="muted">Закладок нет</p>
        @endforelse
    </div>
</div>
@endsection
