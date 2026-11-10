@extends('layouts.app')
@section('title', 'Уведомления')

@section('content')
<div class="eyebrow">Личный кабинет</div>
<h1 class="big">Уведомления</h1>
<p class="muted">Что нового у вас и ваших друзей</p>

@if ($notifications->isNotEmpty())
    <div class="notif-actions">
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
            <button class="btn btn-sm btn-outline" type="submit">Прочитать все</button>
        </form>
        <form method="POST" action="{{ route('notifications.clear') }}" onsubmit="return confirm('Удалить все уведомления?')">@csrf @method('DELETE')
            <button class="btn btn-sm btn-outline" type="submit">Очистить</button>
        </form>
    </div>
@endif

<div class="notif-list">
@forelse ($notifications as $n)
    @php
        $d = $n->data;
        $kind = $d['kind'] ?? 'system';
        if ($kind === 'note' || $kind === 'bookmark') {
            $count = $d['count'] ?? 1;
            $one = ($count % 10 === 1 && $count % 100 !== 11);
            $few = in_array($count % 10, [2, 3, 4]) && !in_array($count % 100, [12, 13, 14]);
            if ($kind === 'note') {
                $word = $one ? 'новую заметку' : ($few ? 'новые заметки' : 'новых заметок');
                $title = $d['from_name'] . ' оставил(а) ' . $count . ' ' . $word;
                $text = 'Книга «' . $d['book_title'] . '». Заметки откроются по мере вашего чтения.';
            } else {
                $word = $one ? 'новую закладку' : ($few ? 'новые закладки' : 'новых закладок');
                $title = $d['from_name'] . ' поставил(а) ' . $count . ' ' . $word;
                $text = 'Книга «' . $d['book_title'] . '». Закладки откроются по мере вашего чтения.';
            }
        } elseif ($kind === 'chapter') {
            $title = $d['from_name'] . ' дочитал(а) главу ' . ($d['chapter'] ?? '');
            $text = 'Книга «' . $d['book_title'] . '». Самое время догнать!';
        } elseif ($kind === 'joined') {
            $title = $d['from_name'] . ' присоединился(ась) к комнате';
            $text = 'Книга «' . $d['book_title'] . '». Можно читать вместе!';
        } else {
            $title = $d['title'] ?? 'Сообщение';
            $text = $d['text'] ?? '';
        }
    @endphp
    <div class="notif-row">
        <a class="notif {{ $n->read_at ? '' : 'unread' }}" href="{{ route('notifications.open', $n->id) }}">
            <span class="notif-dot"></span>
            <span class="notif-body">
                <strong>{{ $title }}</strong>
                <span class="muted small">{{ $text }}</span>
                <span class="muted small">{{ $n->created_at->format('d.m.Y H:i') }}</span>
            </span>
        </a>
        <form method="POST" action="{{ route('notifications.destroy', $n->id) }}">@csrf @method('DELETE')
            <button class="notif-del" type="submit" title="Удалить уведомление" aria-label="Удалить уведомление">&times;</button>
        </form>
    </div>
@empty
    <div class="card">Пока уведомлений нет. Когда друг оставит заметку или сайт сообщит новость, они появятся здесь.</div>
@endforelse
</div>

@include('partials.pagination', ['page' => $page, 'pages' => $pages])
@endsection
