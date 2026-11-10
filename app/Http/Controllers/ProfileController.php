<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use App\Models\Bookmark;
use App\Models\Note;
use App\Support\Pager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = auth()->user();
        $rooms = $user->rooms()->with([
            'book.chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'title', 'paragraph_count'),
            'users',
        ])->latest('rooms.created_at')->get();

        // Сколько разных людей читают вместе с вами
        $friends = $rooms->flatMap(fn ($r) => $r->users->pluck('id'))->unique()->reject(fn ($id) => $id == $user->id)->count();

        $stats = [
            'rooms' => $rooms->count(),
            'books' => $user->shelf()->count(),
            'notes' => Note::where('user_id', $user->id)->count(),
            'marks' => Bookmark::where('user_id', $user->id)->count(),
            'friends' => $friends,
            'finished' => $rooms->filter(fn ($r) => $r->percentOf($user->id) >= 100)->count(),
        ];
        $lastRoom = $rooms->first();

        // прогресс по книгам выводим по 5 комнат на странице
        [$pageRooms, $page, $pages] = Pager::take($rooms, 5, (int) $request->query('page', 1));

        // Заявки на книги: три последние в профиле, остальные на отдельной странице
        $requestsCount = BookRequest::where('user_id', $user->id)->count();
        $requests = BookRequest::where('user_id', $user->id)->with('book')->latest()->take(3)->get();

        return view('profile', compact('user', 'stats', 'lastRoom', 'rooms', 'pageRooms', 'page', 'pages', 'requests', 'requestsCount'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);
        $user->update($data);

        return back()->with('status', 'Данные сохранены');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);
        auth()->user()->update(['password' => $data['password']]);

        return back()->with('status', 'Пароль обновлён');
    }
}
