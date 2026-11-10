<?php

namespace App\Http\Controllers;

use App\Support\Pager;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Страница «Уведомления»
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()->limit(100)->get();
        [$notifications, $page, $pages] = Pager::take($notifications, 15, (int) $request->query('page', 1));
        return view('notifications', compact('notifications', 'page', 'pages'));
    }

    // Открыть уведомление: помечаем прочитанным и переходим по ссылке
    public function open(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        // Переходим только внутри нашего сайта: адрес должен начинаться с одного «/»
        if (is_string($url) && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return redirect($url);
        }
        return redirect()->route('notifications');
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }

    // Удалить одно уведомление (только своё: ищем среди уведомлений текущего пользователя)
    public function destroy(string $id)
    {
        auth()->user()->notifications()->findOrFail($id)->delete();
        return back();
    }

    public function clear()
    {
        auth()->user()->notifications()->delete();
        return back()->with('status', 'Уведомления очищены');
    }
}
