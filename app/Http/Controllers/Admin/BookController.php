<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use App\Support\BookImport;
use App\Support\BookText;
use App\Support\ImportException;
use App\Support\Pager;
use App\Support\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookController extends Controller
{
    // Список книг в админ-панели: поиск по названию и автору, фильтр по жанру и сортировка
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $genre = (string) $request->query('genre', '');
        $sort = (string) $request->query('sort', 'title');
        if (!in_array($sort, ['title', 'author', 'new', 'size'], true)) {
            $sort = 'title';
        }

        $all = Book::withCount('chapters')->get();
        $total = $all->count();

        $books = $all
            ->filter(fn ($book) => $genre === '' || $book->genre === $genre)
            // поиск делаем в PHP: так он не зависит от регистра букв
            ->filter(fn ($book) => $q === '' || mb_stripos($book->title . ' ' . $book->author, $q) !== false);

        $books = (match ($sort) {
            'author' => $books->sortBy(fn ($b) => mb_strtolower($b->author), SORT_NATURAL),
            'new' => $books->sortByDesc('id'),
            'size' => $books->sortByDesc('chapters_count'),
            default => $books->sortBy(fn ($b) => mb_strtolower($b->title), SORT_NATURAL),
        })->values();

        $genres = $all->pluck('genre')->filter()->unique()->sort()->values();

        // Постраничный вывод
        $found = $books->count();
        [$books, $page, $pages] = Pager::take($books, 10, (int) $request->query('page', 1));

        return view('admin.books', compact('books', 'genres', 'q', 'genre', 'sort', 'total', 'found', 'page', 'pages'));
    }

    public function create()
    {
        return view('admin.book-form', ['book' => new Book()]);
    }

    // Предпросмотр: читаем файл и показываем, как он разобьётся на главы (ничего не сохраняем)
    public function preview(Request $request)
    {
        $request->validate([
            'text' => 'required|file|extensions:txt,fb2,zip|max:20480',
            'chapter_mode' => 'nullable|in:word,number',
            'chapter_marker' => 'nullable|string|max:30',
        ]);

        try {
            $result = BookImport::fromUpload($request->file('text'), $this->mode($request), $this->marker($request));
        } catch (ImportException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'meta' => $result['meta'],
            'cover' => !empty($result['cover']),
            'chapters' => array_map(function ($c) {
                $paragraphs = Chapter::splitParagraphs($c[1]);
                $count = count($paragraphs);
                return [
                    'title' => $c[0],
                    'count' => $count,
                    // начало и конец главы, чтобы админ увидел, что внутри
                    'head' => array_map(fn ($p) => mb_strimwidth($p, 0, 220, '…'), array_slice($paragraphs, 0, 3)),
                    'tail' => $count > 3 ? mb_strimwidth($paragraphs[$count - 1], 0, 220, '…') : null,
                    'suspect' => $this->looksLikeAd($c[1], $count),
                ];
            }, $result['chapters']),
        ]);
    }

    // Похоже ли на рекламу сайта-источника («Спасибо, что скачали книгу в библиотеке…»).
    // Проверяем только короткие главы: в большой главе такие слова — это уже текст книги.
    private function looksLikeAd(string $body, int $count): bool
    {
        if ($count > 15) {
            return false;
        }
        return (bool) preg_match('/скачали книгу|thelib|литрес|litres|флибуст|flibusta|либрусек|librusec|royallib|все книги автора|эта же книга в других форматах|приятного чтения/iu', $body);
    }

    // Убирает из разобранной книги главы, которые админ отметил в предпросмотре («Убрать»)
    private function applyDrop(array $result, Request $request): array
    {
        $drop = array_map('intval', (array) $request->input('drop', []));
        if (!$drop) {
            return $result;
        }

        $kept = [];
        foreach ($result['chapters'] as $i => $chapter) {
            if (!in_array($i, $drop, true)) {
                $kept[] = $chapter;
            }
        }
        if (!$kept) {
            throw ValidationException::withMessages(['text' => 'Нельзя убрать все главы: в книге должна остаться хотя бы одна.']);
        }

        $result['chapters'] = $kept;
        return $result;
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(true));
        $import = $this->importFrom($request);
        $fields = $this->fields($data, $import['meta'] ?? []);
        $fields['cover_path'] = $this->resolveCover($request, $import, null);

        DB::transaction(function () use ($fields, $import) {
            $book = Book::create($fields);
            BookText::storeChapters($book, $import['chapters']);
        });

        return redirect()->route('admin.books')->with('status', 'Книга добавлена');
    }

    public function edit(Book $book)
    {
        return view('admin.book-form', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate($this->rules(false));
        $import = $this->importFrom($request);
        $fields = $this->fields($data, $import['meta'] ?? []);
        $fields['cover_path'] = $this->resolveCover($request, $import, $book->cover_path);

        DB::transaction(function () use ($book, $fields, $import) {
            $book->update($fields);
            if ($import) {
                BookText::storeChapters($book, $import['chapters']);
            }
        });

        return redirect()->route('admin.books')->with('status', 'Книга сохранена');
    }

    public function destroy(Book $book)
    {
        $this->deleteCover($book->cover_path);
        $book->delete();
        return back()->with('status', 'Книга удалена');
    }

    // Какая обложка будет у книги:
    // 1) загруженная вручную; 2) из файла fb2 (если у книги ещё нет своей); 3) никакой, тогда сайт рисует обложку по умолчанию.
    private function resolveCover(Request $request, ?array $import, ?string $current): ?string
    {
        if ($request->boolean('remove_cover')) {
            $this->deleteCover($current);
            $current = null;
        }

        if ($request->hasFile('cover')) {
            $this->deleteCover($current);
            $file = $request->file('cover');
            $ext = in_array($file->extension(), ['jpg', 'jpeg', 'png', 'webp'], true) ? $file->extension() : 'jpg';
            $name = Str::random(24) . '.' . $ext;
            File::ensureDirectoryExists(public_path('covers'));
            $file->move(public_path('covers'), $name);
            return 'covers/' . $name;
        }

        if (!$current && $import && !empty($import['cover'])) {
            $name = Str::random(24) . '.' . $import['cover']['ext'];
            File::ensureDirectoryExists(public_path('covers'));
            file_put_contents(public_path('covers/' . $name), $import['cover']['data']);
            return 'covers/' . $name;
        }

        return $current;
    }

    private function deleteCover(?string $path): void
    {
        if ($path && str_starts_with($path, 'covers/')) {
            File::delete(public_path('covers/' . basename($path)));
        }
    }

    // Читает загруженный файл. Если файла нет (при редактировании), вернёт null
    private function importFrom(Request $request): ?array
    {
        if (!$request->hasFile('text')) {
            return null;
        }

        try {
            $result = BookImport::fromUpload($request->file('text'), $this->mode($request), $this->marker($request));
        } catch (ImportException $e) {
            throw ValidationException::withMessages(['text' => $e->getMessage()]);
        }

        if (!$result['chapters']) {
            throw ValidationException::withMessages(['text' => 'В файле не найден текст.']);
        }
        return $this->applyDrop($result, $request);
    }

    // Поля книги: то, что ввёл админ, а пустое заполняем данными из fb2
    private function fields(array $data, array $meta): array
    {
        $title = trim((string) ($data['title'] ?? '')) ?: (string) ($meta['title'] ?? '');
        $author = trim((string) ($data['author'] ?? '')) ?: (string) ($meta['author'] ?? '');

        $errors = [];
        if ($title === '') {
            $errors['title'] = 'Укажите название книги.';
        }
        if ($author === '') {
            $errors['author'] = 'Укажите автора.';
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $description = ($data['description'] ?? null) ?: ($meta['description'] ?? null);

        return [
            'title' => Text::plain($title),
            'author' => Text::plain($author),
            'genre' => ($data['genre'] ?? null) ?: ($meta['genre'] ?? null),
            'description' => $description ? Text::plain($description) : null,
        ];
    }

    private function mode(Request $request): string
    {
        return $request->input('chapter_mode') === 'number' ? 'number' : 'word';
    }

    private function marker(Request $request): string
    {
        return trim((string) $request->input('chapter_marker')) ?: 'Глава';
    }

    private function rules(bool $creating): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'chapter_mode' => 'nullable|in:word,number',
            'chapter_marker' => 'nullable|string|max:30',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'remove_cover' => 'nullable|boolean',
            'drop' => 'nullable|array',
            'drop.*' => 'integer|min:0',
            'text' => ($creating ? 'required' : 'nullable') . '|file|extensions:txt,fb2,zip|max:20480',
        ];
    }
}
