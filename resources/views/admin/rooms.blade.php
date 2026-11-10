@extends('layouts.app')
@section('title', 'Админ: комнаты')

@section('content')
<div class="eyebrow">Управление</div>
<h1 class="big">Админ-панель</h1>
<p class="muted">Книги, пользователи и комнаты</p>

<div class="admin-top">@include('partials.admin-tabs')</div>

<form class="toolbar" method="GET" action="{{ route('admin.rooms') }}">
    <input type="search" name="q" value="{{ $q }}" placeholder="Найти по книге, создателю или коду">
    <button class="btn btn-sm" type="submit">Найти</button>
    @if ($q !== '') <a class="link small" href="{{ route('admin.rooms') }}">Сбросить</a> @endif
</form>

<p class="muted small found">
    @if ($q !== '') Найдено комнат: {{ $found }} из {{ $total }} @else Всего комнат: {{ $total }} @endif
</p>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Книга</th><th>Создатель</th><th>Участники</th><th>Код</th><th>Создана</th><th>Действия</th></tr></thead>
        <tbody>
        @forelse ($rooms as $room)
            <tr>
                <td><strong>{{ $room->book->title }}</strong></td>
                <td class="muted">{{ $room->owner->name }}</td>
                <td>{{ $room->users->count() }} из 2</td>
                <td>{{ $room->code }}</td>
                <td>{{ $room->created_at?->format('d.m.Y') }}</td>
                <td>
                    <form class="inline-form" method="POST" action="{{ route('admin.rooms.destroy', $room) }}" onsubmit="return confirm('Удалить комнату {{ $room->code }}?')">
                        @csrf @method('DELETE')
                        <button class="link-btn danger" type="submit">Удалить</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted">@if ($total) Ничего не найдено. Измените запрос или <a class="link" href="{{ route('admin.rooms') }}">сбросьте поиск</a>. @else Комнат пока нет @endif</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])
@endsection
