<!DOCTYPE html>
<html lang="ru">
<body style="margin:0;padding:24px;background:#F6F0E4;font-family:Arial,Helvetica,sans-serif;color:#3A2F2E;">
    <div style="max-width:520px;margin:0 auto;background:#FFFDF8;border:1px solid #E6DCCB;border-radius:16px;padding:28px;">
        <h1 style="font-family:Georgia,serif;font-size:24px;margin:0 0 12px;">Восстановление пароля</h1>

        <p style="font-size:16px;line-height:1.6;margin:0 0 16px;">
            {{ $user->name }}, мы получили просьбу сменить пароль от вашего аккаунта в «Вместе».
        </p>

        <p style="margin:0 0 20px;">
            <a href="{{ $url }}"
               style="display:inline-block;background:#E3B9C0;color:#46282F;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:12px;">
                Задать новый пароль
            </a>
        </p>

        <p style="font-size:14px;line-height:1.6;color:#7A6A66;margin:0;">
            Ссылка действует 60 минут. Если вы не просили менять пароль, просто проигнорируйте это письмо: пароль останется прежним.
        </p>
    </div>
</body>
</html>
