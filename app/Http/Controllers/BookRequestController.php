<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookRequest;
use App\Models\User;
use App\Support\Notifier;
use App\Support\Pager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// заявки читателей на книги
class BookRequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = BookRequest::where('user_id', auth()->id())->with('book')->latest()->get();
        [$requests, $page, $pages] = Pager::take($requests, 5, (int) $request->query('page', 1));
        return view('requests', compact('requests', 'page', 'pages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'author' => 'nullable|string|max:150',
            'comment' => 'nullable|string|max:500',
        ]);
        $title = mb_strtolower(trim($data['title']));

        // если такая книга уже есть, заявка не нужна
        $inLibrary = Book::all()->contains(fn ($book) => mb_strtolower($book->title) === $title);
        if ($inLibrary) {
            throw ValidationException::withMessages(['title' => 'Такая книга уже есть в библиотеке. Найдите её через поиск.']);
        }

        // такую же заявку второй раз не принимаем, пока первую не рассмотрели
        $duplicate = BookRequest::where('user_id', auth()->id())->where('status', 'new')->get()
            ->contains(fn ($r) => mb_strtolower($r->title) === $title);
        if ($duplicate) {
            throw ValidationException::withMessages(['title' => 'Вы уже отправили заявку на эту книгу, она ждёт рассмотрения.']);
        }

        BookRequest::create([
            'user_id' => auth()->id(),
            'title' => trim($data['title']),
            'author' => $data['author'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);

        // уведомляем администраторов
        foreach (User::where('is_admin', true)->get() as $admin) {
            Notifier::system($admin, 'Новая заявка на книгу', auth()->user()->name . ' просит добавить «' . trim($data['title']) . '».', '/admin/requests');
        }

        return back()->with('status', 'Заявка отправлена. Ответ придёт в раздел «Уведомления».');
    }
}
