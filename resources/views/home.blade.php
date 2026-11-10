@extends('layouts.app')
@section('title', 'Главная')

@section('content')
<section class="hero">
    <div>
        <div class="eyebrow">Книжный клуб для двоих</div>
        <h1 class="big">Читайте одну книгу вместе</h1>
        <div class="rule"></div>
        <p class="lead muted">Ставьте закладки и оставляйте заметки прямо в тексте — друг увидит их в том же месте. А чтобы не было спойлеров, заметки к непрочитанным страницам скрыты.</p>
        <p>
            @auth
                <a class="btn" href="{{ route('rooms') }}">Мои комнаты</a>
            @else
                <a class="btn" href="{{ route('register') }}">Начать читать</a>
            @endauth
            <a class="btn btn-outline" href="{{ route('library') }}">Открыть библиотеку</a>
        </p>
    </div>

    <div class="hero-art">
        <div class="arch"></div>
        <div class="book">
            <div class="page"><i></i><i></i><i></i><i></i><i></i></div>
            <div class="page"><i></i><i></i><i></i><i></i><i></i></div>
        </div>
        <div class="note friend art-note-1"><b>Аружан</b>Тайник под маяком?</div>
        <div class="note me art-note-2"><b>Вы</b>Я тоже так думаю!</div>
    </div>
</section>

<section class="features">
    <div><div class="num">01</div><h2>Библиотека</h2><p class="muted">Выбирайте книгу из каталога и добавляйте на свою полку.</p></div>
    <div><div class="num">02</div><h2>Общие заметки</h2><p class="muted">Закладки и заметки привязаны к абзацу и видны обоим.</p></div>
    <div><div class="num">03</div><h2>Без спойлеров</h2><p class="muted">Чужие заметки открываются, когда вы дочитали до этого места.</p></div>
</section>

{{-- Цифры сайта --}}
<div class="stats-strip">
    <div><b>{{ $stats['books'] }}</b><span>книг в библиотеке</span></div>
    <div><b>{{ $stats['readers'] }}</b><span>читателей</span></div>
    <div><b>{{ $stats['rooms'] }}</b><span>общих комнат</span></div>
    <div><b>{{ $stats['notes'] }}</b><span>заметок оставлено</span></div>
</div>

{{-- Как это работает --}}
<section class="home-block">
    <div class="eyebrow">Как это работает</div>
    <h2 class="home-title">Четыре шага до совместного чтения</h2>
    <div class="how-steps">
        <div class="card how-step"><span class="how-num">1</span><h3>Выберите книгу</h3><p class="muted">Найдите её в библиотеке и добавьте на свою полку.</p></div>
        <div class="card how-step"><span class="how-num">2</span><h3>Создайте комнату</h3><p class="muted">Получите код приглашения или отправьте ссылку другу на почту.</p></div>
        <div class="card how-step"><span class="how-num">3</span><h3>Читайте и отмечайте</h3><p class="muted">Нажмите на абзац, чтобы оставить закладку или заметку.</p></div>
        <div class="card how-step"><span class="how-num">4</span><h3>Обсуждайте</h3><p class="muted">Друг получит уведомление и увидит ваши мысли, когда дочитает до этого места.</p></div>
    </div>
</section>

{{-- Защита от спойлеров: наглядный пример --}}
<section class="home-block spoiler-demo">
    <div>
        <div class="eyebrow yl">Главная особенность</div>
        <h2 class="home-title">Заметки, которые не портят книгу</h2>
        <p class="muted lead">Если друг ушёл вперёд, его заметки к страницам, которые вы ещё не прочитали, остаются скрытыми. Дочитали до этого места, и заметка открывается сама.</p>
        <ul class="ticks">
            <li>Защиту можно включить или выключить в настройках комнаты</li>
            <li>Скрываются и заметки, и закладки друга</li>
            <li>В уведомлении текст заметки тоже не показывается</li>
        </ul>
    </div>
    <div class="demo-page card">
        <div class="demo-para">Лео хотел спросить, кто такой «он», но девочка уже встала и толкнула дверь.
            <div class="demo-note open"><b>Анара</b> Стрелка вниз, наверное, там тайник!</div>
        </div>
        <div class="demo-para">Внутри пахло солью и старой бумагой. На столе лежала карта без подписей.
            <div class="demo-note locked"><b>Анара</b> Заметка скрыта. Дочитайте до этого места, чтобы увидеть её.</div>
        </div>
    </div>
</section>

{{-- Новинки библиотеки --}}
@if ($newBooks->isNotEmpty())
<section class="home-block">
    <div class="home-head">
        <div>
            <div class="eyebrow">Библиотека</div>
            <h2 class="home-title">Новые книги</h2>
        </div>
        <a class="btn btn-outline btn-sm" href="{{ route('library') }}">Вся библиотека</a>
    </div>
    <div class="grid-books">
        @foreach ($newBooks as $book)
            @include('partials.book-card', ['book' => $book])
        @endforeach
    </div>
</section>
@endif

{{-- Призыв к действию --}}
<section class="cta-band">
    <h2>Найдите книгу, которую захочется обсуждать</h2>
    <p>Регистрация занимает меньше минуты. Читайте вдвоём, даже если вы далеко друг от друга.</p>
    @auth
        <a class="btn" href="{{ route('library') }}">Выбрать книгу</a>
    @else
        <a class="btn" href="{{ route('register') }}">Создать аккаунт</a>
    @endauth
</section>
@endsection
