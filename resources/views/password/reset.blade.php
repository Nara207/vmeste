@extends('layouts.app')
@section('title', 'Новый пароль')

@section('content')
<form class="card form-card" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ old('email', $email) }}">

    <div class="eyebrow">Восстановление</div>
    <h1>Новый пароль</h1>
    <p class="muted">Придумайте новый пароль для {{ old('email', $email) }}. Не короче 8 символов.</p>

    <div class="pass">
        <input type="password" name="password" placeholder="Новый пароль" required autofocus>
        <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
    </div>
    <div class="pass">
        <input type="password" name="password_confirmation" placeholder="Повторите пароль" required>
        <button class="pass-eye" type="button" aria-label="Показать пароль" title="Показать пароль"></button>
    </div>
    <button class="btn wide" type="submit">Сохранить пароль</button>
</form>
@endsection
