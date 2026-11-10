@extends('layouts.app')
@section('title', 'Профиль')

@section('content')
<div class="eyebrow">Личный кабинет</div>

{{-- Шапка профиля --}}
<section class="profile-hero">
    <span class="avatar xl">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
    <div class="profile-who">
        <h1>{{ $user->name }}</h1>
        <div class="muted">{{ $user->email }}</div>
        <div class="profile-tags">
            <span class="chip {{ $user->is_admin ? 'chip-yellow' : 'chip-pink' }}">{{ $user->is_owner ? 'Главный администратор' : ($user->is_admin ? 'Администратор' : 'Читатель') }}</span>
            @if ($user->created_at)
                <span class="small muted">С нами с {{ $user->created_at->translatedFormat('j F Y') }}</span>
            @endif
        </div>
    </div>
    @if ($user->is_admin)
        <a class="btn btn-outline btn-sm" href="{{ route('admin.books') }}">Админ-панель</a>
    @endif
</section>

{{-- Цифры --}}
<div class="stat-tiles">
    <div><b>{{ $stats['rooms'] }}</b><span>комнат</span></div>
    <div><b>{{ $stats['books'] }}</b><span>книг на полке</span></div>
    <div><b>{{ $stats['notes'] }}</b><span>заметок</span></div>
    <div><b>{{ $stats['marks'] }}</b><span>закладок</span></div>
    <div><b>{{ $stats['friends'] }}</b><span>друзей рядом</span></div>
    <div><b>{{ $stats['finished'] }}</b><span>дочитано</span></div>
</div>

<div class="two-cols">
    <div>
        <div class="card">
            <h2>Мой прогресс по книгам</h2>
            @forelse ($pageRooms as $r)
                <a class="prog-row" href="{{ route('room', $r) }}">
                    <span class="prog-title">{{ $r->book->title }}</span>
                    <span class="muted small">{{ $r->percentOf($user->id) }}%</span>
                    <span class="bar"><i style="width:{{ $r->percentOf($user->id) }}%"></i></span>
                </a>
            @empty
                <p class="muted small" style="margin:8px 0 0">Пока нет комнат. Выберите книгу в <a class="link" href="{{ route('library') }}">библиотеке</a>.</p>
            @endforelse
            @include('partials.pagination', ['page' => $page, 'pages' => $pages])
        </div>

        <div class="card">
            <div class="card-head">
                <h2>Мои заявки на книги</h2>
                @if ($requestsCount > 3)<span class="muted small">показаны 3 из {{ $requestsCount }}</span>@endif
            </div>
            @forelse ($requests as $r)
                @include('partials.request-item', ['r' => $r])
            @empty
                <p class="muted small" style="margin:8px 0 0">Не нашли нужную книгу в библиотеке? Оставьте заявку, и администратор её добавит.</p>
            @endforelse
            <div class="card-actions">
                <a class="btn btn-sm" href="{{ route('requests') }}">{{ $requestsCount ? 'Все заявки и новая' : 'Оставить заявку' }}</a>
            </div>
        </div>
    </div>

    <div>
        <form class="card stack" method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')
            <h2>Данные профиля</h2>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Имя" required>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Email" required>
            <div><button class="btn" type="submit">Сохранить</button></div>
        </form>

        <form class="card stack" method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PUT')
            <h2>Смена пароля</h2>
            <div class="pass">
                <input type="password" name="current_password" placeholder="Текущий пароль" required>
                <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
            </div>
            <div class="pass">
                <input type="password" name="password" placeholder="Новый пароль" required>
                <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
            </div>
            <div class="pass">
                <input type="password" name="password_confirmation" placeholder="Повторите новый пароль" required>
                <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
            </div>
            <div><button class="btn btn-outline" type="submit">Обновить пароль</button></div>
        </form>
    </div>
</div>
@endsection
