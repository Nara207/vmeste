<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // не больше 5 неудачных попыток входа в минуту,
        // чтобы пароль нельзя было подобрать
        $key = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withErrors(['email' => 'Слишком много попыток входа. Повторите через ' . $seconds . ' сек.'])
                ->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            $unread = auth()->user()->unreadNotifications()->count();
            $redirect = redirect()->intended(route('rooms'));
            return $unread > 0
                ? $redirect->with('status', 'У вас новых уведомлений: ' . $unread . '. Загляните в раздел «Уведомления».')
                : $redirect;
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors(['email' => 'Неверный email или пароль'])->onlyInput('email');
    }

    public function registerForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);
        Notifier::system($user, 'Добро пожаловать в «Вместе»!', 'Выберите книгу в библиотеке, создайте комнату и пригласите друга читать вместе.', '/library');
        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Throwable $e) {
            report($e);
        }
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('rooms'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
