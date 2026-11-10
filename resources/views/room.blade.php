@extends('layouts.app')
@section('title', $room->book->title)

@section('content')
<p><a class="back-link" href="{{ route('book', $room->book) }}">← К оглавлению книги</a></p>

<div class="room-layout">
    <article class="paper">
        @if (!$chapter)
            <h1>{{ $room->book->title }}</h1>
            <p class="muted">В этой книге пока нет глав.</p>
        @else
            <div class="tools">
                @foreach ($chapters as $c)
                    <a class="chip chip-btn {{ $c->id == $chapter->id ? 'on' : '' }}" href="{{ route('room', ['room' => $room->id, 'c' => $c->number]) }}">{{ $c->number }}</a>
                @endforeach
            </div>

            <span class="chip chip-pink" style="margin-top:14px">{{ $chapter->title }}</span>
            <h1>{{ $room->book->title }}</h1>
            <p class="muted small">Комната на двоих · вы{{ $friend ? ' и ' . $friend->name : '' }}</p>
            <details class="help">
                <summary>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.3-1 .8-1 1.5M12 17h.01"/></svg>
                    Подсказки
                </summary>
                <div class="help-body">
                    <p class="legend small">
                        <span><i class="dot dot-friend"></i> заметка друга</span>
                        <span><i class="dot dot-mine"></i> ваша заметка</span>
                        <span><i class="dot dot-mark"></i> закладка</span>
                        <span><i class="dot dot-here"></i> где вы остановились</span>
                    </p>
                    <p class="small">Нажмите на любой абзац: можно поставить закладку или написать заметку. Прочитанное отмечается само, когда вы дочитали абзац и пошли дальше. Кнопка «Дочитал(а) до сюда» нужна, чтобы поставить отметку вручную.</p>
                </div>
            </details>

            @foreach ($paragraphs as $i => $text)
                @php
                    $isHere = $myPos[0] == $chapter->number && $myPos[1] == $i;
                    $paraNotes = $notes->get($i, collect());
                    $hiddenCount = $paraNotes->where('visible', false)->count();
                    $shownNotes = $paraNotes->where('visible', true);
                    $friendNotes = $shownNotes->where('user_id', '!=', auth()->id())->count();
                    $hasMarks = count($marks->get($i, [])) > 0;
                @endphp

                <div class="para {{ $isHere ? 'here' : '' }} {{ $friendNotes ? 'has-friend' : ($shownNotes->isNotEmpty() ? 'has-mine' : '') }} {{ $hasMarks ? 'has-mark' : '' }}" id="p{{ $i }}">
                    @foreach ($marks->get($i, []) as $m)
                        <span class="chip {{ $m->user_id == auth()->id() ? 'chip-pink-solid' : 'chip-yellow' }}">Закладка · {{ $m->user_id == auth()->id() ? 'вы' : $m->user->name }}</span>
                    @endforeach

                    <p>{{ $text }}</p>

                    <div class="tools">
                        {{-- закладка --}}
                        <form data-ajax method="POST" action="{{ route('room.bookmark', $room) }}">@csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <input type="hidden" name="paragraph" value="{{ $i }}">
                            <button class="chip chip-btn" type="submit">{{ $myMarks->has($i) ? 'Снять закладку' : 'Закладка' }}</button>
                        </form>

                        {{-- новая заметка --}}
                        <details class="note-form">
                            <summary class="chip chip-btn">Заметка</summary>
                            <form data-ajax method="POST" action="{{ route('room.notes.store', $room) }}">@csrf
                                <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                                <input type="hidden" name="paragraph" value="{{ $i }}">
                                <textarea name="body" rows="2" maxlength="1000" placeholder="Её увидят все участники комнаты" required></textarea>
                                <button class="btn btn-sm" type="submit">Добавить</button>
                                <span class="char-count">0 / 1000</span>
                            </form>
                        </details>

                        {{-- прогресс --}}
                        <form data-ajax method="POST" action="{{ route('room.progress', $room) }}">@csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <input type="hidden" name="paragraph" value="{{ $i }}">
                            <button class="chip chip-btn {{ $isHere ? 'on' : '' }}" type="submit">{{ $isHere ? 'Вы дочитали до сюда' : 'Дочитал(а) до сюда' }}</button>
                        </form>
                    </div>

                    {{-- заметки, скрытые защитой от спойлеров не показываем --}}
                    @foreach ($paraNotes->where('visible', true) as $n)
                        <div class="note {{ $n->user_id == auth()->id() ? 'me' : 'friend' }}">
                            <b>{{ $n->user_id == auth()->id() ? 'Вы' : $n->user->name }} <span class="note-time">{{ $n->created_at->format('d.m H:i') }}</span></b>
                            <span class="note-body">{{ $n->body }}</span>
                            @if ($n->user_id == auth()->id())
                                <div class="actions">
                                    <details class="note-form">
                                        <summary class="link small" style="cursor:pointer">Изменить</summary>
                                        <form data-ajax method="POST" action="{{ route('room.notes.update', [$room, $n]) }}">@csrf @method('PUT')
                                            <textarea name="body" rows="2" maxlength="1000" required>{{ $n->body }}</textarea>
                                            <button class="btn btn-sm" type="submit">Сохранить</button>
                                            <span class="char-count">{{ mb_strlen(str_replace("\r\n", "\n", $n->body)) }} / 1000</span>
                                        </form>
                                    </details>
                                    <form data-ajax method="POST" action="{{ route('room.notes.destroy', [$room, $n]) }}">@csrf @method('DELETE')
                                        <button class="link-btn danger" type="submit">Удалить</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    @if ($hiddenCount)
                        <div class="note hidden-note">Заметок скрыто: {{ $hiddenCount }}. Они откроются, когда вы дочитаете до этого места</div>
                    @endif
                </div>
            @endforeach

            @php
                $prev = $chapters->firstWhere('number', $chapter->number - 1);
                $next = $chapters->firstWhere('number', $chapter->number + 1);
            @endphp
            <div class="pager">
                <div class="left">
                    @if ($prev) <a class="btn btn-outline" href="{{ route('room', ['room' => $room->id, 'c' => $prev->number]) }}">← Предыдущая глава</a> @endif
                </div>
                <a class="btn btn-outline" href="{{ route('book', $room->book) }}">К оглавлению</a>
                <div class="right">
                    @if ($next) <a class="btn btn-outline" href="{{ route('room', ['room' => $room->id, 'c' => $next->number]) }}">Следующая глава →</a> @endif
                </div>
            </div>
        @endif
    </article>

    <aside>
        <div class="card progress-card {{ $friend ? 'has-friend' : '' }}">
            <h2>Прогресс</h2>
            <div class="who"><span class="avatar">В</span> <span>Вы — <span id="my-pct">{{ $myPct }}</span>%</span></div>
            <div class="bar"><i id="my-bar" style="width:{{ $myPct }}%"></i></div>
            @if ($friend)
                <div class="who"><span class="avatar friend">{{ mb_substr($friend->name, 0, 1) }}</span> <span>{{ $friend->name }} — {{ $friendPct }}%</span></div>
                <div class="bar yl"><i style="width:{{ $friendPct }}%"></i></div>
            @else
                <p class="muted small">Друг ещё не присоединился. Отправьте ему код приглашения.</p>
            @endif

            {{-- защита от спойлеров: переключает создатель комнаты, второй участник только видит --}}
            @if ($room->owner_id == auth()->id())
                <form method="POST" action="{{ route('room.update', $room) }}">@csrf @method('PATCH')
                    <input type="hidden" name="spoiler_protection" value="0">
                    <label class="check">
                        <input type="checkbox" name="spoiler_protection" value="1" {{ $room->spoiler_protection ? 'checked' : '' }} onchange="this.form.submit()">
                        Защита от спойлеров
                    </label>
                </form>
            @else
                <label class="check">
                    <input type="checkbox" disabled {{ $room->spoiler_protection ? 'checked' : '' }}>
                    Защита от спойлеров
                </label>
            @endif
        </div>

        <div class="card yellow">
            <div class="eyebrow yl">Код приглашения</div>
            <div class="code">{{ $room->code }}</div>
        </div>

        @if (!$friend)
            <div class="card" id="invite">
                <h2>Пригласить друга</h2>
                <form method="POST" action="{{ route('room.invite', $room) }}">
                    @csrf
                    <input type="email" name="email" placeholder="Email друга" required>
                    <button class="btn btn-sm" type="submit">Отправить приглашение</button>
                </form>
                <p class="hint" style="margin:12px 0 0">Другу придёт письмо со ссылкой: после входа он сразу попадёт в эту комнату.</p>
                <button class="btn btn-outline btn-sm" type="button" data-share="{{ route('join.link', $room->code) }}" style="margin-top:12px">Поделиться ссылкой</button>
            </div>
        @endif

        <div class="card">
            <h2>Закладки</h2>
            @forelse ($allMarks as $m)
                <div class="mark-row">
                    <i class="sq {{ $m->user_id == auth()->id() ? 'pink' : 'yellow' }}"></i>
                    <a class="link" href="{{ route('room', ['room' => $room->id, 'c' => $m->chapter->number]) }}#p{{ $m->paragraph }}">Глава {{ $m->chapter->number }}, абзац {{ $m->paragraph + 1 }}</a>
                    <span class="muted small">· {{ $m->user_id == auth()->id() ? 'вы' : $m->user->name }}</span>
                </div>
            @empty
                <p class="muted small">Пока нет закладок</p>
            @endforelse
            <p style="margin:14px 0 0"><a class="link small" href="{{ route('room.notes', $room) }}">Все закладки и заметки →</a></p>
        </div>

        @if ($room->owner_id == auth()->id())
            <form method="POST" action="{{ route('room.destroy', $room) }}" onsubmit="return confirm('Удалить комнату вместе со всеми заметками?')">
                @csrf @method('DELETE')
                <button class="btn-danger" type="submit">Удалить комнату</button>
            </form>
        @endif
    </aside>
</div>

<script>
    function closeAllParas() {
        document.querySelectorAll('.para.open').forEach(function (x) { x.classList.remove('open'); });
    }

    // 1. по клику на абзац показываем или прячем его кнопки (закладка, заметка, «дочитал(а)»)
    //    обработчик стоит на всём документе, поэтому работает и после обновления текста
    document.addEventListener('click', function (e) {
        var p = e.target.closest('.para > p');
        if (p) {
            if (String(window.getSelection())) return;          // человек выделяет текст, не мешаем
            var para = p.parentElement;
            var wasOpen = para.classList.contains('open');
            closeAllParas();
            if (!wasOpen) para.classList.add('open');
            return;
        }
        if (!e.target.closest('.para')) closeAllParas();       // клик в стороне закрывает кнопки
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllParas();
    });

    // 2. закладки, заметки и «дочитал(а)» отправляем в фоне, без перезагрузки страницы:
    //    отправляем форму, получаем новую страницу и меняем на ней содержимое
    document.addEventListener('submit', async function (e) {
        var form = e.target;
        if (!form.matches('form[data-ajax]')) return;
        e.preventDefault();

        var para = form.closest('.para');
        var openId = para ? para.id : null;
        var scrollY = window.scrollY;
        if (para) para.classList.add('busy');
        form.querySelectorAll('button').forEach(function (b) { b.disabled = true; });

        try {
            var response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Ошибка ' + response.status);

            var doc = new DOMParser().parseFromString(await response.text(), 'text/html');
            var fresh = doc.querySelector('main.container');
            if (!fresh) throw new Error('Нет содержимого');

            document.querySelector('main.container').innerHTML = fresh.innerHTML;
            if (openId) {
                var again = document.getElementById(openId);
                if (again) again.classList.add('open');       // абзац остаётся открытым, чтобы был виден результат
            }
            window.scrollTo(0, scrollY);
        } catch (err) {
            form.submit();                                     // если что-то пошло не так, отправляем форму обычным способом
        }
    });

    // 4. кнопка «Поделиться ссылкой»: на телефоне открывает меню «Поделиться» (мессенджеры, почта),
    //    а там, где его нет, копирует ссылку
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-share]');
        if (!btn) return;
        var url = btn.dataset.share;
        if (navigator.share) {
            navigator.share({ title: 'Читаем вместе', text: 'Присоединяйся к моей комнате чтения', url: url }).catch(function () { });
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () { btn.textContent = 'Ссылка скопирована'; });
        } else {
            btn.textContent = url;
        }
    });

    // 5. счётчик символов под полем заметки: при каждом вводе пишем «сколько / лимит»
    document.addEventListener('input', function (e) {
        var field = e.target;
        if (!field.matches('textarea[maxlength]')) return;
        var counter = field.parentNode.querySelector('.char-count');
        if (!counter) return;
        counter.textContent = field.value.length + ' / ' + field.maxLength;
        counter.classList.toggle('full', field.value.length >= field.maxLength);   // на пределе подсвечиваем
    });

    // 3. при открытии комнаты прокручиваем к месту, где читатель остановился
    //    если в адресе уже есть якорь (#p5, например из уведомления), ничего не трогаем
    if (!location.hash) {
        var here = document.querySelector('.para.here');
        if (here) here.scrollIntoView({ block: 'center' });
    }
</script>
@endsection
