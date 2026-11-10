<?php

namespace App\Http\Controllers;

use App\Mail\RoomInvitation;
use App\Models\Chapter;
use App\Models\Room;
use App\Support\Notifier;
use App\Support\Pager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = auth()->user()->rooms()->with([
            'book.chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'title', 'paragraph_count'),
            'users',
        ])->get();
        [$rooms, $page, $pages] = Pager::take($rooms, 6, (int) $request->query('page', 1));
        return view('rooms', compact('rooms', 'page', 'pages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'chapter' => 'nullable|integer|min:1',
        ]);

        do {
            $code = strtoupper(Str::random(6));
        } while (Room::where('code', $code)->exists());

        $room = Room::create([
            'book_id' => $data['book_id'],
            'owner_id' => auth()->id(),
            'code' => $code,
        ]);
        $room->users()->attach(auth()->id());

        return redirect()->route('room', ['room' => $room->id, 'c' => $data['chapter'] ?? null])
            ->with('status', 'Комната создана. Отправьте другу код приглашения: ' . $code);
    }

    public function join(Request $request)
    {
        $code = strtoupper(trim($request->validate(['code' => 'required|string|max:8'])['code']));
        $room = Room::where('code', $code)->first();

        if (!$room) {
            return back()->withErrors(['code' => 'Комната с таким кодом не найдена']);
        }
        if (!$room->hasMember(auth()->id())) {
            if ($room->users()->count() >= 2) {
                return back()->withErrors(['code' => 'В этой комнате уже два участника']);
            }
            $room->users()->attach(auth()->id());
            Notifier::joined($room->refresh(), auth()->user());
        }
        return redirect()->route('room', $room);
    }

    // вход в комнату по ссылке из письма
    public function joinLink(string $code)
    {
        $room = Room::where('code', strtoupper($code))->first();

        if (!$room) {
            return redirect()->route('rooms')->withErrors(['code' => 'Комната с таким кодом не найдена']);
        }
        if (!$room->hasMember(auth()->id())) {
            if ($room->users()->count() >= 2) {
                return redirect()->route('rooms')->withErrors(['code' => 'В этой комнате уже два участника']);
            }
            $room->users()->attach(auth()->id());
            Notifier::joined($room->refresh(), auth()->user());
        }
        return redirect()->route('room', $room);
    }

    // отправляем другу письмо с приглашением
    public function invite(Request $request, Room $room)
    {
        abort_unless($room->hasMember(auth()->id()), 403);
        $data = $request->validate(['email' => 'required|email|max:255']);

        if ($room->users()->count() >= 2) {
            return back()->withErrors(['email' => 'В комнате уже два участника']);
        }
        if (strcasecmp($data['email'], auth()->user()->email) === 0) {
            return back()->withErrors(['email' => 'Это ваш собственный адрес. Укажите адрес друга.']);
        }

        $room->load('book');
        try {
            Mail::to($data['email'])->send(new RoomInvitation($room, auth()->user()));
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['email' => 'Не удалось отправить письмо. Проверьте настройки почты (MAIL_… в файле .env).']);
        }

        return back()->with('status', 'Приглашение отправлено на ' . $data['email']);
    }

    // страница чтения, номер главы берём из адреса (?c=2)
    public function show(Request $request, Room $room)
    {
        $uid = auth()->id();
        $room->load([
            'book.chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'title', 'paragraph_count'),
            'users',
        ]);
        abort_unless($room->users->contains('id', $uid), 403);

        $chapters = $room->book->chapters;
        $myPos = $room->progressOf($uid);

        $chapter = $chapters->firstWhere('number', (int) $request->query('c'))
            ?? $chapters->firstWhere('number', $myPos[0])
            ?? $chapters->first();

        // текст нужен только у открытой главы, у остальных берём название и число абзацев
        if ($chapter) {
            $chapter = Chapter::findOrFail($chapter->id);
        }

        $data = [
            'room' => $room,
            'chapters' => $chapters,
            'chapter' => $chapter,
            'friend' => $room->users->firstWhere('id', '!=', $uid),
            'myPos' => $myPos,
            'myPct' => $room->percentOf($uid),
            'friendPct' => 0,
            'paragraphs' => [],
            'notes' => collect(),
            'marks' => collect(),
            'myMarks' => collect(),
            'allMarks' => collect(),
        ];
        if ($data['friend']) {
            $data['friendPct'] = $room->percentOf($data['friend']->id);
        }

        if ($chapter) {
            $data['paragraphs'] = $chapter->paragraphs();

            // для каждой заметки проверяем, видна она или скрыта защитой от спойлеров
            $data['notes'] = $room->notes()->where('chapter_id', $chapter->id)->with('user')->orderBy('id')->get()
                ->each(fn ($n) => $n->setAttribute('visible', $room->visibleTo($uid, $n->user_id, $chapter->number, $n->paragraph)))
                ->groupBy('paragraph');

            $bookmarks = $room->bookmarks()->with(['user', 'chapter:id,number,title'])->get()
                ->filter(fn ($m) => $room->visibleTo($uid, $m->user_id, $m->chapter->number, $m->paragraph));

            $data['marks'] = $bookmarks->where('chapter_id', $chapter->id)->groupBy('paragraph');
            $data['myMarks'] = $bookmarks->where('chapter_id', $chapter->id)->where('user_id', $uid)->keyBy('paragraph');
            $data['allMarks'] = $bookmarks->sortBy(fn ($m) => sprintf('%05d-%05d', $m->chapter->number, $m->paragraph))->values();
        }

        return view('room', $data);
    }

    // все закладки и заметки комнаты (?u=me мои, ?u=friend друга)
    public function notes(Request $request, Room $room)
    {
        $uid = auth()->id();
        $room->load([
            'book.chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'title', 'paragraph_count'),
            'users',
        ]);
        abort_unless($room->users->contains('id', $uid), 403);

        $who = (string) $request->query('u', '');
        $keep = fn ($m) => $room->visibleTo($uid, $m->user_id, $m->chapter->number, $m->paragraph)
            && ($who !== 'me' || $m->user_id == $uid)
            && ($who !== 'friend' || $m->user_id != $uid);
        $order = fn ($m) => sprintf('%05d-%05d-%09d', $m->chapter->number, $m->paragraph, $m->id);

        $notes = $room->notes()->with(['user', 'chapter:id,number,title'])->get()->filter($keep)->sortBy($order)->values();
        $marks = $room->bookmarks()->with(['user', 'chapter:id,number,title'])->get()->filter($keep)->sortBy($order)->values();

        return view('room-notes', compact('room', 'notes', 'marks', 'who'));
    }

    // включить или выключить защиту от спойлеров, это может только создатель комнаты
    public function update(Request $request, Room $room)
    {
        abort_unless($room->owner_id === auth()->id(), 403);
        $room->update(['spoiler_protection' => $request->boolean('spoiler_protection')]);
        return back()->with('status', 'Настройка сохранена');
    }

    public function destroy(Room $room)
    {
        abort_unless($room->owner_id === auth()->id(), 403);
        $room->delete();
        return redirect()->route('rooms')->with('status', 'Комната удалена');
    }
}
