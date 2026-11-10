@extends('layouts.app')
@section('title', 'Регистрация')

@section('content')
<form class="card form-card" method="POST" action="{{ route('register') }}">
    @csrf
    <div class="eyebrow">Новый читатель</div>
    <h1>Создать аккаунт</h1>
    <p class="muted">Это займёт меньше минуты</p>

    <input type="text" name="name" value="{{ old('name') }}" placeholder="Имя" required autofocus>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
    <div class="pass">
        <input type="password" name="password" placeholder="Пароль (не короче 8 символов)" required>
        <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
    </div>
    <div class="pass">
        <input type="password" name="password_confirmation" placeholder="Повторите пароль" required>
        <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
    </div>
    <button class="btn wide" type="submit">Зарегистрироваться</button>

    <p class="small" style="margin-top:16px"><a class="link" href="{{ route('login') }}">Уже есть аккаунт? Войти</a></p>
</form>
@endsection
