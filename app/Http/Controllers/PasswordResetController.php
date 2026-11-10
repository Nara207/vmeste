<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

// Восстановление пароля: письмо со ссылкой -> новый пароль.
// Токены хранит и проверяет сам Laravel (Password::broker), таблица password_reset_tokens.
class PasswordResetController extends Controller
{
    // форма «Забыли пароль?»: вводим email
    public function requestForm()
    {
        return view('password.forgot');
    }

    // отправляем письмо со ссылкой
    public function sendLink(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink($data);
        } catch (\Throwable $e) {
            report($e);   // почта не сработала: пишем в лог, пользователю ответ тот же
        }

        // ответ всегда одинаковый, чтобы по нему нельзя было узнать, зарегистрирован ли email
        return back()->with('status', 'Если такой email зарегистрирован, мы отправили на него письмо со ссылкой для смены пароля. Ссылка действует 60 минут.');
    }

    // страница из письма: вводим новый пароль
    public function resetForm(Request $request, string $token)
    {
        return view('password.reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    // сохраняем новый пароль
    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            // пароль сам превращается в хеш (cast 'hashed' в модели User);
            // новый remember_token «выкидывает» старые сессии «запомнить меня»
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            Notifier::system($user, 'Пароль изменён', 'Пароль от вашего аккаунта был изменён через восстановление. Если это были не вы, смените его в профиле.', '/profile');
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Пароль изменён. Войдите с новым паролем.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Ссылка недействительна или устарела. Запросите новую.']);
    }
}
