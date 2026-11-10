@extends('layouts.app')
@section('title', 'Восстановление пароля')

@section('content')
<form class="card form-card" method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="eyebrow">Восстановление</div>
    <h1>Забыли пароль?</h1>
    <p class="muted">Укажите email, с которым регистрировались. Мы отправим письмо со ссылкой для смены пароля.</p>

    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
    <button class="btn wide" type="submit">Отправить ссылку</button>

    <p class="small" style="margin-top:16px"><a class="link" href="{{ route('login') }}">Вспомнили пароль? Войти</a></p>
</form>
@endsection
