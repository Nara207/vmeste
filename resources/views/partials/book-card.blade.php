{{-- Карточка книги в каталоге. Нужна переменная $book (лучше с withCount('chapters')) --}}
<a class="card book-card" href="{{ route('book', $book) }}">
    @include('partials.cover', ['book' => $book])
    <div class="book-title">{{ $book->title }}</div>
    <div class="muted small book-author">{{ $book->author }}</div>
    <div class="book-meta">
        @if ($book->genre)
            <span class="chip {{ $book->id % 2 ? 'chip-yellow' : 'chip-pink' }}">{{ $book->genre }}</span>
        @endif
        @if (isset($book->chapters_count))
            @php $n = $book->chapters_count; $w = ($n % 10 === 1 && $n % 100 !== 11) ? 'глава' : (in_array($n % 10, [2,3,4]) && !in_array($n % 100, [12,13,14]) ? 'главы' : 'глав'); @endphp
            <span class="small muted book-count">{{ $n }} {{ $w }}</span>
        @endif
    </div>
</a>
