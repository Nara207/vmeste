@extends('layouts.app')
@section('title', 'Админ: пользователи')

@section('content')
@php $me = auth()->user(); @endphp

<div class="eyebrow">Управление</div>
<h1 class="big">Админ-панель</h1>
<p class="muted">Книги, пользователи и комнаты</p>

<div class="admin-top">@include('partials.admin-tabs')</div>

<div class="card yellow" style="margin-top:0">
    @if ($me->is_owner)
        <strong>Вы главный администратор.</strong>
        <span class="small">Вы можете назначать и снимать администраторов. Обычные администраторы управляют книгами, пользователями и комнатами, но права другим выдавать не могут.</span>
    @else
        <strong>Вы администратор.</strong>
        <span class="small">Назначать и снимать администраторов может только главный администратор.</span>
    @endif
</div>

<form class="toolbar" method="GET" action="{{ route('admin.users') }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Найти по имени или email">
    <button class="btn btn-sm" type="submit">Найти</button>
    @if ($q !== '') <a class="link small" href="{{ route('admin.users') }}">Сбросить</a> @endif
</form>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th>Регистрация</th><th>Действия</th></tr></thead>
        <tbody>
        @forelse ($users as $user)
            <tr>
                <td><strong>{{ $user->name }}</strong></td>
                <td class="muted">{{ $user->email }}</td>
                <td>
                    @if ($user->is_owner)
                        <span class="chip chip-yellow">Главный администратор</span>
                    @elseif ($user->is_admin)
                        <span class="chip chip-yellow">Администратор</span>
                    @else
                        <span class="chip chip-pink">Пользователь</span>
                    @endif
                </td>
                <td>{{ $user->created_at?->format('d.m.Y') }}</td>
                <td>
                    <div class="row-actions">
                        {{-- Права администратора выдаёт и снимает только главный администратор, с подтверждением паролем --}}
                        @if ($me->is_owner && !$user->is_owner)
                            <details class="role-form">
                                <summary class="btn btn-sm {{ $user->is_admin ? 'btn-outline' : '' }}">{{ $user->is_admin ? 'Снять права админа' : 'Сделать админом' }}</summary>
                                <form method="POST" action="{{ $user->is_admin ? route('admin.users.admin.remove', $user) : route('admin.users.admin', $user) }}">
                                    @csrf
                                    @if ($user->is_admin) @method('DELETE') @endif
                                    <div class="pass">
                                        <input type="password" name="password" placeholder="Ваш пароль для подтверждения" required>
                                        <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
                                    </div>
                                    <button class="btn btn-sm" type="submit">Подтвердить</button>
                                </form>
                            </details>
                        @endif

                        @if ($user->id !== $me->id && !$user->is_owner && (!$user->is_admin || $me->is_owner))
                            <form class="inline-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Удалить пользователя {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button class="link-btn danger" type="submit">Удалить</button>
                            </form>
                        @endif

                        @if ($user->id === $me->id)
                            <span class="muted small">это вы</span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Никого не найдено</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])
@endsection
