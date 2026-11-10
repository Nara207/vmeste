@extends('layouts.app')
@section('title', $book->exists ? 'Админ: изменить книгу' : 'Админ: новая книга')

@section('content')
<div class="eyebrow">Управление</div>
<h1 class="big">{{ $book->exists ? 'Изменить книгу' : 'Новая книга' }}</h1>
<p class="muted">Загрузите файл книги: сайт сам найдёт главы</p>
@if ($book->exists)
    <a class="back-link" href="{{ route('admin.books') }}">← К списку книг</a>
    @include('partials.book-tabs')
@endif

<div class="two-cols wide-left">
    <form class="card stack" data-book-form method="POST" enctype="multipart/form-data"
          action="{{ $book->exists ? route('admin.books.update', $book) : route('admin.books.store') }}">
        @csrf
        @if ($book->exists) @method('PUT') @endif

        <h2>Файл книги</h2>
        <label class="dashed upload">
            <span id="fname">{{ $book->exists ? 'Новый файл (необязательно): .fb2, .txt или .zip' : 'Выберите файл книги: .fb2, .txt или .zip' }}</span>
            <input type="file" id="book-file" name="text" accept=".txt,.fb2,.zip">
        </label>
        @if ($book->exists)
    <p class="hint"><strong>Внимание:</strong> новый файл заменит все главы книги, а заметки и закладки читателей к ним исчезнут. Чтобы поправить одну главу, откройте вкладку «Главы».</p>
@endif
        <p class="hint">Лучше всего подходит <strong>fb2</strong>: главы, название, автор и описание определятся сами. Также можно загрузить .txt или .zip с fb2 внутри.</p>

        {{-- Настройки только для txt: как в файле отмечены главы --}}
        <div id="txt-options">
            <label class="field" for="chapter-mode">Для txt: как в файле отмечены главы</label>
            <select id="chapter-mode" name="chapter_mode">
                <option value="word" @selected(old('chapter_mode', 'word') === 'word')>Слово в начале строки: «Глава 1», «Часть II»…</option>
                <option value="number" @selected(old('chapter_mode') === 'number')>Просто номер: 1, 2, 3 или I, II, III</option>
            </select>

            <div id="marker-wrap">
                <label class="field" for="chapter-marker">Какое слово</label>
                <input type="text" id="chapter-marker" name="chapter_marker" value="{{ old('chapter_marker', 'Глава') }}" maxlength="30">
                <p class="hint">Например: Глава, Часть, Ночь. Каждая строка файла, которая начинается с этого слова, станет заголовком новой главы.</p>
            </div>
            <p class="hint" id="number-hint" hidden>Заголовком считается строка, в которой только номер («1», «2.», «III») или номер с названием через точку («1. Тропа»). Короткие строки вроде «1984» заголовком не считаются.</p>
        </div>

        {{-- Сюда скрипт выводит результат проверки файла --}}
        <div class="preview" id="preview" hidden></div>

        @if ($book->exists)
            <p class="hint">Если загрузить новый файл, старые главы (и заметки к ним) будут заменены.</p>
        @endif

        <h2 style="margin-top:10px">Обложка</h2>
        @if ($book->exists)
            <div class="cover-edit">
                @include('partials.cover', ['book' => $book, 'class' => 'mini-cover'])
                <div>
                    <div class="small muted">{{ $book->cover_path ? 'Сейчас загружена своя обложка' : 'Сейчас обложка по умолчанию' }}</div>
                    @if ($book->cover_path)
                        <label class="switch"><input type="checkbox" name="remove_cover" value="1"> Удалить обложку</label>
                    @endif
                </div>
            </div>
        @endif
        <label class="dashed upload">
            <span id="cover-name">Загрузить обложку (необязательно): jpg, png или webp</span>
            <input type="file" name="cover" accept="image/png,image/jpeg,image/webp">
        </label>
        <p class="hint">Если обложки нет, на сайте будет красивая обложка по умолчанию. У fb2-книг обложка подставится из файла, если она там есть.</p>

        <h2 style="margin-top:10px">Данные книги</h2>
        <input type="text" name="title" value="{{ old('title', $book->title) }}" placeholder="Название (для fb2 подставится само)">
        <input type="text" name="author" value="{{ old('author', $book->author) }}" placeholder="Автор (для fb2 подставится сам)">
        <input type="text" name="genre" value="{{ old('genre', $book->genre) }}" placeholder="Жанр">
        <textarea name="description" rows="4" placeholder="Описание">{{ old('description', $book->description) }}</textarea>

        <div>
            <button class="btn" type="submit">Сохранить</button>
            <a class="btn btn-outline" href="{{ route('admin.books') }}">Отмена</a>
        </div>
    </form>

    <div class="card yellow how">
        <h2>Как подготовить файл</h2>
        <p class="small"><strong>fb2 (проще всего).</strong> Просто выберите файл. Главы берутся из разделов книги, а название, автор, жанр и описание подставятся в форму. Если книга лежит в архиве .fb2.zip, можно выбрать его.</p>
        <p class="small"><strong>txt.</strong></p>
        <ol class="steps">
            <li>Перед каждой главой поставьте отдельную строку-заголовок: со словом («Глава 1. Тропа») или просто номер (1, 2, 3).</li>
            <li>Каждый абзац пишите с новой строки. Пустые строки игнорируются.</li>
            <li>Выберите способ слева: сразу покажется, на какие главы файл разобьётся.</li>
        </ol>
        <pre class="example">Глава 1. Начало
Первый абзац — одной строкой.
Второй абзац — со следующей.

Глава 2. Продолжение
Третий абзац уже во второй главе.</pre>
        <p class="small help-links">
            Образцы для проверки:
            <a class="link" href="{{ asset('example-book.fb2') }}" download>fb2</a> ·
            <a class="link" href="{{ asset('example-book.txt') }}" download>txt со словом</a> ·
            <a class="link" href="{{ asset('example-book-numbers.txt') }}" download>txt с номерами</a>
        </p>
        <p class="small muted">Если в txt заголовки оформлены иначе (например, «* * *»), замените их в Блокноте на «Глава 1», «Глава 2» (Правка → Заменить).</p>
    </div>
</div>

<script>
    var previewUrl = '{{ route('admin.books.preview') }}';
    var form = document.querySelector('form[data-book-form]');
    var fileInput = document.getElementById('book-file');
    var modeSelect = document.getElementById('chapter-mode');
    var markerInput = document.getElementById('chapter-marker');
    var box = document.getElementById('preview');
    var timer = null, seq = 0;

    function esc(s) {
        return String(s).replace(/[&<>]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]; });
    }

    function fileExt() {
        var f = fileInput.files[0];
        return f ? f.name.split('.').pop().toLowerCase() : '';
    }

    // Настройки глав нужны только для txt
    function toggleOptions() {
        var ext = fileExt();
        document.getElementById('txt-options').hidden = !(ext === '' || ext === 'txt');
        document.getElementById('marker-wrap').hidden = (modeSelect.value === 'number');
        document.getElementById('number-hint').hidden = (modeSelect.value !== 'number');
    }

    // Подставляем данные из файла только в пустые поля
    function fillIfEmpty(name, value) {
        var el = form.elements[name];
        if (el && value && !el.value.trim()) { el.value = value; return true; }
        return false;
    }

    function showError(body) {
        var msg = (body && body.error) || (body && body.errors && body.errors.text && body.errors.text[0]) || (body && body.message) || 'Не удалось прочитать файл.';
        box.hidden = false;
        box.innerHTML = '<div class="warn">' + esc(msg) + '</div>';
    }

    function render(data) {
        var ch = data.chapters || [];
        var meta = data.meta || {};
        var filled = [];
        if (fillIfEmpty('title', meta.title)) filled.push('название');
        if (fillIfEmpty('author', meta.author)) filled.push('автор');
        if (fillIfEmpty('genre', meta.genre)) filled.push('жанр');
        if (fillIfEmpty('description', meta.description)) filled.push('описание');

        var html = '';
        if (filled.length) {
            html += '<div class="info">Из файла подставлены: ' + filled.join(', ') + '. Проверьте и при желании исправьте.</div>';
        }
        if (data.cover) {
            html += '<div class="info">В файле найдена обложка: она будет использована, если вы не загрузите свою.</div>';
        }
        if (!ch.length) {
            box.hidden = false;
            box.innerHTML = html + '<div class="warn">В файле не найден текст.</div>';
            return;
        }
        var suspects = ch.filter(function (c) { return c.suspect; }).length;
        html += '<strong>В файле найдено глав: ' + ch.length + '</strong>';
        html += '<p class="hint" style="margin:4px 0 8px">Посмотрите начало глав. Отметьте «Убрать» у тех, что не нужны: они не попадут на сайт.</p>';
        if (suspects) {
            html += '<div class="warn">Похоже на рекламу сайта-источника: глав ' + suspects + '. Они уже отмечены «Убрать», проверьте.</div>';
        }
        html += '<ol class="preview-list">';
        ch.forEach(function (c, i) {
            html += '<li class="preview-ch' + (c.suspect ? ' suspect' : '') + '">';
            html += '<label class="drop"><input type="checkbox" name="drop[]" value="' + i + '"' + (c.suspect ? ' checked' : '') + '> Убрать</label>';
            html += '<details><summary><span class="chapter-title">' + esc(c.title) + '</span> <span class="muted small">абзацев: ' + c.count + '</span></summary>';
            html += '<div class="chapter-peek">';
            (c.head || []).forEach(function (t) { html += '<p>' + esc(t) + '</p>'; });
            if (c.tail) html += '<p class="muted">…</p><p>' + esc(c.tail) + '</p>';
            html += '</div></details></li>';
        });
        html += '</ol>';

        if (fileExt() !== 'txt' && ch[0].title === 'Начало') {
            html += '<div class="warn">Первая глава «Начало» — это текст до первой главы книги. Часто там реклама сайта, откуда скачан файл. Загляните в неё.</div>';
        }
        if (fileExt() === 'txt') {
            if (ch.length === 1 && ch[0].title === 'Текст') {
                html += '<div class="warn">Заголовков глав не найдено, вся книга станет одной главой. Проверьте способ деления выше.</div>';
            } else if (ch[0].title === 'Текст') {
                html += '<div class="warn">Текст до первого заголовка станет отдельной главой «Текст».</div>';
            }
            if (ch.length > 60) {
                html += '<div class="warn">Глав очень много. Возможно, заголовками распознались лишние строки. Проверьте список.</div>';
            }
        }
        box.hidden = false;
        box.innerHTML = html;
    }

    // Отправляем файл на сервер только для проверки. Сохраняется книга по кнопке «Сохранить».
    function requestPreview() {
        var file = fileInput.files[0];
        if (!file) { box.hidden = true; return; }

        var fd = new FormData();
        fd.append('_token', form.querySelector('input[name=_token]').value);
        fd.append('text', file);
        fd.append('chapter_mode', modeSelect.value);
        fd.append('chapter_marker', markerInput.value.trim() || 'Глава');

        var mine = ++seq;
        box.hidden = false;
        box.innerHTML = 'Читаем файл…';

        fetch(previewUrl, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) {
            return r.json().then(function (body) { return { ok: r.ok, body: body }; });
        }).then(function (res) {
            if (mine !== seq) return;
            res.ok ? render(res.body) : showError(res.body);
        }).catch(function () {
            if (mine !== seq) return;
            box.innerHTML = '<div class="warn">Не удалось проверить файл заранее: возможно, он слишком большой для сервера. Попробуйте загрузить его в архиве .zip.</div>';
        });
    }

    function later() { clearTimeout(timer); timer = setTimeout(requestPreview, 400); }

    fileInput.addEventListener('change', function () {
        var file = fileInput.files[0];
        if (file) document.getElementById('fname').textContent = file.name;
        toggleOptions();
        requestPreview();
    });
    modeSelect.addEventListener('change', function () { toggleOptions(); if (fileInput.files[0]) later(); });
    markerInput.addEventListener('input', function () { if (fileInput.files[0]) later(); });
    document.querySelector('input[name=cover]').addEventListener('change', function () {
        if (this.files[0]) document.getElementById('cover-name').textContent = this.files[0].name;
    });
    toggleOptions();
</script>
@endsection
