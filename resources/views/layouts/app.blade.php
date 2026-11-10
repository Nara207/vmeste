<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Вместе') — сервис совместного чтения</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('img/icon-256.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lora:wght@400&family=Nunito:wght@400;600;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

    {{-- запоминаем выбранную тему, светлую или тёмную --}}
    <script>
        var saved = localStorage.getItem('theme');
        if (saved) document.documentElement.dataset.theme = saved;
    </script>
</head>

<body>

    <header class="nav">
        <div class="container nav-inner">
            <a class="logo" href="{{ route('home') }}" aria-label="Вместе, на главную"><img class="logo-img logo-light" src="{{ asset('img/logo.png') }}" width="61" height="44" alt=""><img class="logo-img logo-dark" src="{{ asset('img/logo-dark.png') }}" width="61" height="44" alt=""><span class="logo-text">Вместе</span></a>

            <nav class="menu" id="menu" aria-label="Основное меню">
                <a class="{{ request()->routeIs('library', 'book') ? 'on' : '' }}"
                    href="{{ route('library') }}">Библиотека</a>
                <a class="{{ request()->routeIs('about') ? 'on' : '' }}" href="{{ route('about') }}">О проекте</a>
                @auth
                    <a class="{{ request()->routeIs('shelf') ? 'on' : '' }}" href="{{ route('shelf') }}">Моя полка</a>
                    <a class="{{ request()->routeIs('rooms', 'room', 'room.notes') ? 'on' : '' }}"
                        href="{{ route('rooms') }}">Мои комнаты</a>
                    <a class="{{ request()->routeIs('profile') ? 'on' : '' }}" href="{{ route('profile') }}">Профиль</a>
                    @if (auth()->user()->is_admin)
                        <a class="admin-link {{ request()->is('admin*') ? 'on' : '' }}"
                            href="{{ route('admin.books') }}">Админ</a>
                    @endif
                    {{-- на телефоне кнопка выхода переезжает в меню --}}
                    <form class="menu-logout" method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit">Выйти</button>
                    </form>
                @endauth
                @guest
                    <a class="menu-guest" href="{{ route('login') }}">Войти</a>
                    <a class="menu-guest" href="{{ route('register') }}">Регистрация</a>
                @endguest
            </nav>

            <div class="nav-actions">
                @auth
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    <a class="icon-btn {{ request()->routeIs('notifications') ? 'on' : '' }}"
                        href="{{ route('notifications') }}" title="Уведомления"
                        aria-label="Уведомления{{ $unread > 0 ? ': ' . $unread . ' новых' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.7 21a2 2 0 0 1-3.4 0" />
                        </svg>
                        @if ($unread > 0)<span class="badge">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
                    </a>
                @endauth

                <button class="icon-btn theme-btn" id="theme-btn" type="button" title="Сменить тему"
                    aria-label="Сменить тему">
                    <svg class="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z" />
                    </svg>
                    <svg class="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" />
                        <path
                            d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                    </svg>
                </button>

                @auth
                    <form class="logout-form" method="POST" action="{{ route('logout') }}">@csrf
                        <button class="logout-btn" type="submit" title="Выйти" aria-label="Выйти">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <path d="M16 17l5-5-5-5" />
                                <path d="M21 12H9" />
                            </svg>
                            <span>Выйти</span>
                        </button>
                    </form>
                @else
                    <a class="btn btn-outline btn-sm" href="{{ route('login') }}">Войти</a>
                    <a class="btn btn-sm" href="{{ route('register') }}">Регистрация</a>
                @endauth

                <button class="icon-btn burger" id="burger" type="button" aria-label="Меню"
                    aria-expanded="false" aria-controls="menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="container">
        {{-- сообщения об успехе и ошибках --}}
        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash err">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a class="logo" href="{{ route('home') }}"><img class="logo-img logo-light" src="{{ asset('img/logo.png') }}" width="61" height="44" alt=""><img class="logo-img logo-dark" src="{{ asset('img/logo-dark.png') }}" width="61" height="44" alt=""> Вместе</a>
                    <p class="muted small">Сервис совместного чтения: читайте одну книгу вдвоём, оставляйте закладки и
                        заметки. Спойлеры скрыты, пока друг не дойдёт до нужного места.</p>
                </div>
                <div>
                    <h3>Сервис</h3>
                    <a href="{{ route('library') }}">Библиотека</a>
                    @auth
                        <a href="{{ route('shelf') }}">Моя полка</a>
                        <a href="{{ route('rooms') }}">Мои комнаты</a>
                        <a href="{{ route('profile') }}">Профиль</a>
                    @else
                        <a href="{{ route('login') }}">Войти</a>
                        <a href="{{ route('register') }}">Регистрация</a>
                    @endauth
                </div>
            </div>
            <div class="footer-bottom">
                <span>© 2026 Вместе</span>
                <span>Дипломный проект</span>
            </div>
        </div>
    </footer>

    <script>
        // бургер-меню на телефоне
        var burger = document.getElementById('burger');
        burger.addEventListener('click', function () {
            var open = document.querySelector('.nav').classList.toggle('open');
            burger.setAttribute('aria-expanded', open);
        });

        // кнопка смены темы (значок меняется в CSS, тут только подсказка)
        var btn = document.getElementById('theme-btn');
        function setLabel() {
            var text = document.documentElement.dataset.theme === 'dark' ? 'Включить светлую тему' : 'Включить тёмную тему';
            btn.title = text;
            btn.setAttribute('aria-label', text);
        }
        btn.onclick = function () {
            var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = next;
            try {localStorage.setItem('theme', next);} catch (e) { }
            setLabel();
        };
        setLabel();

        // глазик у поля пароля: переключаем type между password и text
        document.addEventListener('click', function (e) {
            var eye = e.target.closest('.pass-eye');
            if (!eye) return;
            var box = eye.parentElement;
            var input = box.querySelector('input');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            box.classList.toggle('shown', show);
            var label = show ? 'Скрыть пароль' : 'Показать пароль';
            eye.setAttribute('aria-label', label);
            eye.title = label;
        });

        // выпадающие списки закрываем по клику в стороне и по Esc
        document.addEventListener('click', function (e) {
            document.querySelectorAll('details.dd[open]').forEach(function (d) {if (!d.contains(e.target)) d.removeAttribute('open');});
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') document.querySelectorAll('details.dd[open]').forEach(function (d) {d.removeAttribute('open');});
        });
    </script>
</body>

</html>