<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\Chapter;
use App\Models\Note;
use App\Support\Pager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// главы книги в админ-панели: список, правка, добавление, удаление
class ChapterController extends Controller
{
    public function index(Request $request, Book $book)
    {
        $book->load(['chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'title', 'paragraph_count')]);
        [$chapters, $page, $pages] = Pager::take($book->chapters, 15, (int) $request->query('page', 1));
        return view('admin.chapters', compact('book', 'chapters', 'page', 'pages'));
    }

    public function create(Book $book)
    {
        return view('admin.chapter-form', ['book' => $book, 'chapter' => new Chapter()]);
    }

    public function store(Request $request, Book $book)
    {
        [$title, $body, $count] = $this->fields($request);

        // новая глава становится последней
        $number = (int) Chapter::where('book_id', $book->id)->max('number') + 1;
        Chapter::create([
            'book_id' => $book->id,
            'number' => $number,
            'title' => $title,
            'body' => $body,
            'paragraph_count' => $count,
        ]);

        return redirect()->route('admin.chapters', $book)->with('status', 'Глава добавлена');
    }

    public function edit(Book $book, Chapter $chapter)
    {
        return view('admin.chapter-form', compact('book', 'chapter'));
    }

    public function update(Request $request, Book $book, Chapter $chapter)
    {
        [$title, $body, $count] = $this->fields($request);
        $removed = 0;

        DB::transaction(function () use ($chapter, $title, $body, $count, &$removed) {
            $oldCount = $chapter->paragraph_count;
            $chapter->update(['title' => $title, 'body' => $body, 'paragraph_count' => $count]);

            // если глава стала короче, удаляем заметки и закладки к абзацам, которых больше нет,
            // а отметку «дочитал» переносим на последний абзац
            if ($count < $oldCount) {
                $removed += Note::where('chapter_id', $chapter->id)->where('paragraph', '>=', $count)->delete();
                $removed += Bookmark::where('chapter_id', $chapter->id)->where('paragraph', '>=', $count)->delete();
                DB::table('room_user')
                    ->where('progress_chapter_id', $chapter->id)
                    ->where('progress_paragraph', '>=', $count)
                    ->update(['progress_paragraph' => $count - 1]);
            }
        });

        $message = 'Глава сохранена';
        if ($removed > 0) {
            $message .= '. Убрано заметок и закладок, которые остались за концом текста: ' . $removed;
        }
        return redirect()->route('admin.chapters', $book)->with('status', $message);
    }

    public function destroy(Book $book, Chapter $chapter)
    {
        if (Chapter::where('book_id', $book->id)->count() <= 1) {
            throw ValidationException::withMessages(['chapter' => 'Нельзя удалить последнюю главу: в книге должна остаться хотя бы одна.']);
        }

        DB::transaction(function () use ($book, $chapter) {
            // заметки и закладки этой главы удалятся сами (внешние ключи),
            // а прогресс тех, кто в ней был, сбросится
            $chapter->delete();

            // после удаления номера глав пересчитываем, чтобы шли подряд
            Chapter::where('book_id', $book->id)->orderBy('number')->get()
                ->each(function ($c, $i) {
                    if ($c->number !== $i + 1) {
                        $c->update(['number' => $i + 1]);
                    }
                });
        });

        return redirect()->route('admin.chapters', $book)->with('status', 'Глава удалена');
    }

    // проверяем форму, текст приводим к виду «один абзац на строку»
    private function fields(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500000',
        ]);

        $paragraphs = Chapter::splitParagraphs($data['body']);
        if (!$paragraphs) {
            throw ValidationException::withMessages(['body' => 'В главе должен быть хотя бы один абзац текста.']);
        }

        return [trim($data['title']), implode("\n", $paragraphs), count($paragraphs)];
    }
}
