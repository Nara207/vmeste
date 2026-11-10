<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AnnouncementMail;
use Illuminate\Support\Facades\Mail;
use App\Models\RoleLog;
use App\Models\Room;
use App\Models\User;
use App\Support\Notifier;
use App\Support\Pager;
use Illuminate\Http\Request;

class ManageController extends Controller
{
    public function users(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::orderBy('id')->get()
            // поиск делаем в PHP: так он не зависит от регистра букв
            ->filter(fn ($u) => $q === '' || mb_stripos($u->name . ' ' . $u->email, $q) !== false)
            ->values();

        [$users, $page, $pages] = Pager::take($users, 10, (int) $request->query('page', 1));

        return view('admin.users', compact('users', 'q', 'page', 'pages'));
    }

    // Назначать и снимать администраторов может только главный администратор,
    // и только подтвердив своё действие паролем
    public function makeAdmin(Request $request, User $user)
    {
        abort_unless(auth()->user()->is_owner, 403);
        $request->validate(['password' => 'required|current_password']);

        $user->forceFill(['is_admin' => true])->save();
        RoleLog::record(auth()->user(), $user, 'admin_granted');
        Notifier::system($user, 'Вам выданы права администратора', 'Теперь в меню есть раздел «Админ».', '/admin/books');

        return back()->with('status', $user->name . ' теперь администратор');
    }

    public function removeAdmin(Request $request, User $user)
    {
        abort_unless(auth()->user()->is_owner, 403);
        $request->validate(['password' => 'required|current_password']);

        if ($user->is_owner) {
            return back()->withErrors(['user' => 'У главного администратора нельзя снять права']);
        }

        $user->forceFill(['is_admin' => false])->save();
        RoleLog::record(auth()->user(), $user, 'admin_revoked');
        Notifier::system($user, 'Права администратора сняты', 'Раздел «Админ» вам больше недоступен.');

        return back()->with('status', 'У пользователя ' . $user->name . ' сняты права администратора');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Нельзя удалить самого себя']);
        }
        if ($user->is_owner) {
            return back()->withErrors(['user' => 'Нельзя удалить главного администратора']);
        }
        if ($user->is_admin && !auth()->user()->is_owner) {
            return back()->withErrors(['user' => 'Удалять администраторов может только главный администратор']);
        }

        RoleLog::record(auth()->user(), $user, 'user_deleted');
        $user->delete();

        return back()->with('status', 'Пользователь удалён');
    }

    // Список комнат: поиск по книге, создателю и коду, постраничный вывод
    public function rooms(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $all = Room::with(['book', 'owner', 'users'])->latest()->get();

        $rooms = $all
            // поиск делаем в PHP: так он не зависит от регистра букв
            ->filter(fn ($r) => $q === '' || mb_stripos($r->book->title . ' ' . $r->owner->name . ' ' . $r->code, $q) !== false)
            ->values();

        $total = $all->count();
        $found = $rooms->count();
        [$rooms, $page, $pages] = Pager::take($rooms, 10, (int) $request->query('page', 1));

        return view('admin.rooms', compact('rooms', 'q', 'total', 'found', 'page', 'pages'));
    }

    public function destroyRoom(Room $room)
    {
        $room->delete();
        return back()->with('status', 'Комната удалена');
    }

    // Объявление всем пользователям (только главный администратор)
    public function announcementForm()
    {
        abort_unless(auth()->user()->is_owner, 403);
        return view('admin.announcements');
    }

    public function announce(Request $request)
    {
        abort_unless(auth()->user()->is_owner, 403);
        $data = $request->validate(['title' => 'required|string|max:100', 'message' => 'required|string|max:500']);

        $count = 0;
        User::chunkById(100, function ($users) use ($data, &$count, $request) {
            foreach ($users as $user) {
                if ($request->boolean('send_email')) {
                    Mail::to($user->email)->send(new AnnouncementMail($data['title'], $data['message']));
                }
                Notifier::system($user, $data['title'], $data['message']);
                $count++;
            }
        });

        return back()->with('status', 'Объявление отправлено: ' . $count . ' польз.');
    }

    // Журнал: последние 200 действий с правами и пользователями
    public function log(Request $request)
    {
        $entries = RoleLog::orderByDesc('id')->get();
        [$entries, $page, $pages] = Pager::take($entries, 15, (int) $request->query('page', 1));
        return view('admin.log', compact('entries', 'page', 'pages'));
    }
}
