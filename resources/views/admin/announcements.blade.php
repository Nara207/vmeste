@extends('layouts.app')
@section('title', 'Админ: объявления')

@section('content')
    <div class="eyebrow">Управление</div>
    <h1 class="big">Админ-панель</h1>
    <p class="muted">Книги, пользователи и комнаты</p>

    <div class="admin-top">@include('partials.admin-tabs')</div>

    <div class="card yellow">
        <strong>Объявление для всех.</strong>
        <span class="small">Сообщение придёт каждому пользователю в раздел «Уведомления». Отправлять объявления может
            главный администратор.</span>
    </div>

    <form class="card stack" method="POST" action="{{ route('admin.announcements.send') }}"
        onsubmit="return confirm('Отправить объявление всем пользователям?')">
        @csrf
        <input type="text" name="title" value="{{ old('title') }}" placeholder="Заголовок" maxlength="100" required>
        <textarea name="message" rows="4" placeholder="Текст объявления" maxlength="500"
            required>{{ old('message') }}</textarea>
        <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px; cursor:pointer;">
            <input type="checkbox" name="send_email" value="1"
                style="width:auto; margin:0; padding:0; accent-color:var(--pink-text);">
            Отправить письмо на почту
        </label>
        <div><button class="btn" type="submit">Отправить всем</button></div>
    </form>
@endsection