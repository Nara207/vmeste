@extends('layouts.app')
@section('title', 'Вход')

@section('content')
<form class="card form-card" method="POST" action="{{ route('login') }}">
    @csrf
    <div class="eyebrow">Вход</div>
    <h1>С возвращением</h1>
    <p class="muted">Войдите, чтобы продолжить чтение</p>

    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
    <div class="pass">
        <input type="password" name="password" placeholder="Пароль" required>
        <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
    </div>
    <p class="small forgot"><a class="link" href="{{ route('password.request') }}">Забыли пароль?</a></p>
    <button class="btn wide" type="submit">Войти</button>

    <p class="small" style="margin-top:16px"><a class="link" href="{{ route('register') }}">Нет аккаунта? Зарегистрироваться</a></p>
</form>
@endsection
