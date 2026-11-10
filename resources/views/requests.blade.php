@extends('layouts.app')
@section('title', 'Заявка на книгу')

@section('content')
<div class="eyebrow">Библиотека</div>
<h1 class="big">Заявка на книгу</h1>
<p class="muted">Нет нужной книги? Напишите, какую добавить: администратор рассмотрит заявку и ответит в «Уведомлениях».</p>

<div class="two-cols">
    <form class="card stack" method="POST" action="{{ route('requests.store') }}">
        @csrf
        <h2>Новая заявка</h2>
        <input type="text" name="title" value="{{ old('title') }}" placeholder="Название книги" maxlength="150" required>
        <input type="text" name="author" value="{{ old('author') }}" placeholder="Автор (если знаете)" maxlength="150">
        <textarea name="comment" rows="4" maxlength="500" placeholder="Комментарий: издание, перевод, зачем нужна книга">{{ old('comment') }}</textarea>
        <div><button class="btn" type="submit">Отправить заявку</button></div>
    </form>

    <div class="card">
        <h2>Мои заявки</h2>
        @forelse ($requests as $r)
            @include('partials.request-item', ['r' => $r])
        @empty
            <p class="muted">Вы пока не отправляли заявок.</p>
        @endforelse

        @include('partials.pagination', ['page' => $page, 'pages' => $pages])
    </div>
</div>
@endsection
