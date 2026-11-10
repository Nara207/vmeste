{{-- Одна заявка пользователя: название, статус, ответ администратора. Нужен $r (BookRequest) --}}
<div class="request-item">
    <div class="request-head">
        <strong>{{ $r->title }}</strong>
        <span class="chip {{ $r->status === 'new' ? 'chip-yellow' : ($r->status === 'added' ? 'chip-pink-solid' : 'chip-pink') }}">{{ $r->statusLabel() }}</span>
    </div>
    @if ($r->author)<div class="muted small">{{ $r->author }}</div>@endif
    @if ($r->admin_reply)<div class="small">Ответ: {{ $r->admin_reply }}</div>@endif
    @if ($r->status === 'added' && $r->book)
        <a class="link small" href="{{ route('book', $r->book) }}">Открыть книгу →</a>
    @endif
    <div class="muted small">{{ $r->created_at?->format('d.m.Y') }}</div>
</div>
