<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ошибка сервера</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Lora:wght@400&family=Nunito:wght@400;600;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
</head>
<body>
    <main class="container">
        <div class="eyebrow">Ошибка 500</div>
        <h1 class="big">Что-то пошло не так</h1>
        <p>Мы уже разбираемся. Попробуйте зайти позже.</p>
        <a class="btn" href="/">На главную</a>
    </main>
</body>
</html>