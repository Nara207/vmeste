<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookRequest;
use App\Support\Notifier;
use App\Support\Pager;
use Illuminate\Http\Request;

// Админ-панель: заявки пользователей на книги
class BookRequestController extends Controller
{
    // Список заявок: поиск, фильтр по статусу и постраничный вывод
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        if (!in_array($status, ['new', 'added', 'rejected'], true)) {
            $status = '';
        }

        // Сначала те, что ещё ждут ответа
        $all = BookRequest::with(['user', 'book'])
            ->orderByRaw("status = 'new' desc")
            ->latest()
            ->get();

        $requests = $all
            ->filter(fn ($r) => $status === '' || $r->status === $status)
            // поиск делаем в PHP: так он не зависит от регистра букв
            ->filter(fn ($r) => $q === '' || mb_stripos($r->title . ' ' . $r->author . ' ' . $r->user->name, $q) !== false)
            ->values();

        $total = $all->count();
        $found = $requests->count();
        [$requests, $page, $pages] = Pager::take($requests, 10, (int) $request->query('page', 1));

        $books = Book::orderBy('id', 'desc')->get();

        return view('admin.requests', compact('requests', 'books', 'q', 'status', 'total', 'found', 'page', 'pages'));
    }

    // Ответить на заявку: «книга добавлена» или «отклонена»
    public function update(Request $request, BookRequest $bookRequest)
    {
        $data = $request->validate([
            'action' => 'required|in:added,rejected',
            'reply' => 'nullable|string|max:300',
            'book_id' => 'nullable|exists:books,id',
        ]);

        $added = $data['action'] === 'added';
        $bookRequest->update([
            'status' => $data['action'],
            'admin_reply' => $data['reply'] ?? null,
            'book_id' => $added ? ($data['book_id'] ?? null) : null,
        ]);

        // Сообщаем автору заявки
        $text = $added
            ? 'Книга «' . $bookRequest->title . '» добавлена в библиотеку.'
            : 'К сожалению, книгу «' . $bookRequest->title . '» добавить не получится.';
        if (!empty($data['reply'])) {
            $text .= ' ' . $data['reply'];
        }
        $url = $added && $bookRequest->book_id ? '/library/' . $bookRequest->book_id : '/requests';
        Notifier::system($bookRequest->user, 'Ответ на вашу заявку', $text, $url);

        return back()->with('status', 'Ответ отправлен пользователю');
    }

    public function destroy(BookRequest $bookRequest)
    {
        $bookRequest->delete();
        return back()->with('status', 'Заявка удалена');
    }
}
