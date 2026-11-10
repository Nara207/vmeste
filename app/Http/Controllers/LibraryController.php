<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Support\Pager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $genre = (string) $request->query('genre', '');
        $sort = (string) $request->query('sort', 'title');
        if (!in_array($sort, ['title', 'author', 'new', 'size'], true)) {
            $sort = 'title';
        }

        $books = Book::withCount('chapters')->get()
            ->filter(fn ($book) => $genre === '' || $book->genre === $genre)
            // ищем в PHP, чтобы не зависеть от регистра букв
            ->filter(fn ($book) => $q === '' || mb_stripos($book->title . ' ' . $book->author, $q) !== false);

        // сортировка: по названию, автору, новизне или числу глав
        $books = (match ($sort) {
            'author' => $books->sortBy(fn ($b) => mb_strtolower($b->author), SORT_NATURAL),
            'new' => $books->sortByDesc('id'),
            'size' => $books->sortByDesc('chapters_count'),
            default => $books->sortBy(fn ($b) => mb_strtolower($b->title), SORT_NATURAL),
        })->values();

        $genres = Book::whereNotNull('genre')->distinct()->orderBy('genre')->pluck('genre');

        // постраничный вывод, 12 книг на странице
        $found = $books->count();
        [$books, $page, $pages] = Pager::take($books, 12, (int) $request->query('page', 1));

        return view('library', compact('books', 'genres', 'q', 'genre', 'sort', 'found', 'page', 'pages'));
    }

    public function show(Book $book)
    {
        // грузим только названия глав и число абзацев, сам текст тут не нужен
        $book->load(['chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'title', 'paragraph_count')]);

        // сколько человек читает эту книгу
        $readers = DB::table('room_user')
            ->join('rooms', 'rooms.id', '=', 'room_user.room_id')
            ->where('rooms.book_id', $book->id)
            ->distinct()
            ->count('room_user.user_id');

        // похожие книги: тот же жанр или автор
        $similar = Book::where('id', '!=', $book->id)
            ->where(function ($q) use ($book) {
                if ($book->genre) {
                    $q->orWhere('genre', $book->genre);
                }
                $q->orWhere('author', $book->getRawOriginal('author'));
            })
            ->limit(4)
            ->get();

        $onShelf = false;
        $myRooms = collect();

        if (auth()->check()) {
            $onShelf = auth()->user()->shelf()->where('books.id', $book->id)->exists();
            $myRooms = auth()->user()->rooms()
                ->where('rooms.book_id', $book->id)
                ->with([
                    'book.chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'paragraph_count'),
                    'users',
                ])
                ->orderByDesc('rooms.created_at')
                ->get();
        }

        return view('book', compact('book', 'onShelf', 'myRooms', 'readers', 'similar'));
    }
}
