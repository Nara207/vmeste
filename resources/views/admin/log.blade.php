@extends('layouts.app')
@section('title', 'Админ: журнал')

@section('content')
<div class="eyebrow">Управление</div>
<h1 class="big">Админ-панель</h1>
<p class="muted">Книги, пользователи и комнаты</p>

<div class="admin-top">@include('partials.admin-tabs')</div>

<div class="card yellow" style="margin-top:0">
    <strong>Журнал прав.</strong>
    <span class="small">Здесь записано, кто, когда и кому выдал или снял права администратора и кто удалил пользователя. Записи хранятся в базе и не меняются через сайт.</span>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Когда</th><th>Кто</th><th>Действие</th><th>Над кем</th></tr></thead>
        <tbody>
        @forelse ($entries as $entry)
            <tr>
                <td class="muted">{{ $entry->created_at?->format('d.m.Y H:i') }}</td>
                <td><strong>{{ $entry->actor_name }}</strong></td>
                <td>{{ \App\Models\RoleLog::LABELS[$entry->action] ?? $entry->action }}</td>
                <td>{{ $entry->target_name }} <span class="muted small">({{ $entry->target_email }})</span></td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">Записей пока нет</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])
@endsection
