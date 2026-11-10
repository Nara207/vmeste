@extends('layouts.app')
@section('title', $chapter->exists ? 'Админ: изменить главу' : 'Админ: новая глава')

@section('content')
<div class="eyebrow">Управление</div>
<h1 class="big">Изменить книгу</h1>
<p class="muted">«{{ $book->title }}»: {{ $chapter->exists ? 'глава ' . $chapter->number : 'новая глава (добавится в конец)' }}</p>

<a class="back-link" href="{{ route('admin.chapters', $book) }}">← К списку глав</a>
@include('partials.book-tabs')

<form class="card stack" method="POST"
      action="{{ $chapter->exists ? route('admin.chapters.update', [$book, $chapter]) : route('admin.chapters.store', $book) }}">
    @csrf
    @if ($chapter->exists) @method('PUT') @endif

    <input type="text" name="title" value="{{ old('title', $chapter->title) }}" placeholder="Название главы" maxlength="255" required>
    <textarea class="chapter-text" name="body" rows="18" placeholder="Текст главы: каждый абзац с новой строки" required>{{ old('body', $chapter->body) }}</textarea>
    <p class="hint">Каждая непустая строка станет отдельным абзацем: к абзацам читатели ставят закладки и пишут заметки.</p>
    <div class="row-actions">
        <button class="btn" type="submit">Сохранить</button>
        <a class="btn btn-outline" href="{{ route('admin.chapters', $book) }}">Отмена</a>
    </div>
</form>
@endsection
