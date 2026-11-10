@extends('layouts.app')
@section('title', 'Страница не найдена')

@section('content')
    <div class="eyebrow">Ошибка 403</div>
    <h1 class="big">Доступ запрещен</h1>
    <p>Недостаточно прав для открытия страницы</p>
    <a class="btn" href="{{ route('home') }}">На главную</a>
@endsection