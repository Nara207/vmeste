<!DOCTYPE html>
<html lang="ru">
<body style="margin:0;padding:24px;background:#F6F0E4;font-family:Arial,Helvetica,sans-serif;color:#3A2F2E;">
    <div style="max-width:520px;margin:0 auto;background:#FFFDF8;border:1px solid #E6DCCB;border-radius:16px;padding:28px;">
        <h1 style="font-family:Georgia,serif;font-size:24px;margin:0 0 12px;">Приглашение читать вместе</h1>

        <p style="font-size:16px;line-height:1.6;margin:0 0 16px;">
            {{ $inviter->name }} приглашает вас в комнату чтения по книге
            <strong>«{{ $room->book->title }}»</strong> ({{ $room->book->author }}).
        </p>

        <p style="font-size:16px;line-height:1.6;margin:0 0 20px;">
            В комнате вы будете читать один и тот же текст, ставить закладки и оставлять заметки,
            которые увидит второй читатель. Заметки к ещё не прочитанным местам скрыты, чтобы не было спойлеров.
        </p>

        <p style="margin:0 0 20px;">
            <a href="{{ route('join.link', $room->code) }}"
               style="display:inline-block;background:#E3B9C0;color:#46282F;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:12px;">
                Присоединиться к комнате
            </a>
        </p>

        <p style="font-size:14px;line-height:1.6;color:#86766F;margin:0 0 6px;">
            Если у вас ещё нет аккаунта, зарегистрируйтесь: после входа вы сразу попадёте в комнату.
        </p>
        <p style="font-size:14px;line-height:1.6;color:#86766F;margin:0;">
            Код комнаты: <strong style="color:#3A2F2E;">{{ $room->code }}</strong>
            (его можно ввести вручную на странице «Мои комнаты»).
        </p>
    </div>
</body>
</html>
