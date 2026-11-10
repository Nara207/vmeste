<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Note;
use App\Models\Room;
use App\Support\Notifier;
use Illuminate\Http\Request;

// закладки, заметки и прогресс в комнате
class ReadingController extends Controller
{
    private function member(Room $room): void
    {
        abort_unless($room->hasMember(auth()->id()), 403);
    }

    // проверяем, что глава из этой книги и такой абзац есть
    private function position(Request $request, Room $room, array $extraRules = []): array
    {
        $data = $request->validate(['chapter_id' => 'required|integer', 'paragraph' => 'required|integer|min:0'] + $extraRules);
        $chapter = $room->book->chapters()->findOrFail($data['chapter_id']);
        abort_unless($data['paragraph'] < $chapter->paragraph_count, 404);
        return [$data, $chapter];
    }

    private function backTo(Room $room, $chapter, int $paragraph)
    {
        return redirect(route('room', ['room' => $room->id, 'c' => $chapter->number]) . '#p' . $paragraph);
    }

    public function bookmark(Request $request, Room $room)
    {
        $this->member($room);
        [$data, $chapter] = $this->position($request, $room);

        $where = ['room_id' => $room->id, 'user_id' => auth()->id(), 'chapter_id' => $chapter->id, 'paragraph' => $data['paragraph']];
        $existing = Bookmark::where($where)->first();
        if ($existing) {
            $existing->delete();
        } else {
            Bookmark::create($where);
            // отправляем другу уведомление о закладке (когда её снимают, не отправляем)
            $room->loadMissing('book');
            foreach ($room->users()->where('users.id', '!=', auth()->id())->get() as $friend) {
                Notifier::newBookmark($friend, $room, auth()->user());
            }
        }

        return $this->backTo($room, $chapter, $data['paragraph']);
    }

    public function storeNote(Request $request, Room $room)
    {
        $this->member($room);
        [$data, $chapter] = $this->position($request, $room, ['body' => 'required|string|max:1000']);

        Note::create([
            'room_id' => $room->id,
            'user_id' => auth()->id(),
            'chapter_id' => $chapter->id,
            'paragraph' => $data['paragraph'],
            'body' => $data['body'],
        ]);

        // уведомляем друга о заметке, сам текст туда не попадает
        $room->loadMissing('book');
        foreach ($room->users()->where('users.id', '!=', auth()->id())->get() as $friend) {
            Notifier::newNote($friend, $room, auth()->user());
        }

        return $this->backTo($room, $chapter, $data['paragraph']);
    }

    public function updateNote(Request $request, Room $room, Note $note)
    {
        $this->member($room);
        abort_unless($note->room_id === $room->id && $note->user_id === auth()->id(), 403);

        $note->update($request->validate(['body' => 'required|string|max:1000']));

        return $this->backTo($room, $note->chapter, $note->paragraph);
    }

    public function destroyNote(Room $room, Note $note)
    {
        $this->member($room);
        abort_unless($note->room_id === $room->id && $note->user_id === auth()->id(), 403);

        $chapter = $note->chapter;
        $paragraph = $note->paragraph;
        $note->delete();

        return $this->backTo($room, $chapter, $paragraph);
    }

    // кнопка «Дочитал(а) до сюда»
    public function progress(Request $request, Room $room)
    {
        $this->member($room);
        [$data, $chapter] = $this->position($request, $room);

        $room->load([
            'book.chapters' => fn ($q) => $q->select('id', 'book_id', 'number', 'title', 'paragraph_count'),
            'users',
        ]);
        $uid = auth()->id();
        $before = $room->progressOf($uid);
        $new = [$chapter->number, $data['paragraph']];
        $forward = ($new <=> $before) > 0;

        $room->users()->updateExistingPivot($uid, [
            'progress_chapter_id' => $chapter->id,
            'progress_paragraph' => $data['paragraph'],
        ]);

        // если дочитали главу до конца в первый раз, говорим об этом другу
        if ($forward && $data['paragraph'] === $chapter->paragraph_count - 1) {
            foreach ($room->users->where('id', '!=', $uid) as $friend) {
                Notifier::chapterFinished($friend, $room, auth()->user(), $chapter->number);
            }
        }

        return $this->backTo($room, $chapter, $data['paragraph']);
    }
}
