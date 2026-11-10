@extends('layouts.app')
@section('title', 'Страница не найдена')

@section('content')
    <div class="eyebrow">Ошибка 404</div>
    <h1 class="big">Такой страницы нет</h1>
    <p>Адрес набран с ошибкой.</p>
    <a class="btn" href="{{ route('home') }}">На главную</a>
@endsection