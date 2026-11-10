<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

// Проверка почты на сервере: php artisan mail:test адрес@пример.ру
// Показывает настройки и настоящую причину, если письмо не ушло
class MailTest extends Command
{
    protected $signature = 'mail:test {email : куда отправить тестовое письмо}';

    protected $description = 'Отправить тестовое письмо и показать, что не так с почтой';

    public function handle(): int
    {
        $this->line('Способ отправки (MAIL_MAILER): ' . config('mail.default'));
        $smtp = config('mail.mailers.smtp');
        $this->line('Сервер: ' . ($smtp['host'] ?? '-') . ':' . ($smtp['port'] ?? '-') . ', шифрование: ' . ($smtp['encryption'] ?? 'нет'));
        $this->line('Логин: ' . ($smtp['username'] ?? '-'));
        $this->line('От кого (MAIL_FROM_ADDRESS): ' . config('mail.from.address'));
        $this->line('Адрес сайта (APP_URL): ' . config('app.url'));

        if (config('mail.default') === 'log') {
            $this->warn('MAIL_MAILER=log: письма не отправляются, а только записываются в storage/logs/laravel.log. Нужно MAIL_MAILER=smtp.');
        }

        try {
            Mail::raw('Тестовое письмо из сервиса «Вместе». Если вы его читаете, почта настроена верно.', function ($m) {
                $m->to($this->argument('email'))->subject('Проверка почты «Вместе»');
            });
        } catch (\Throwable $e) {
            $this->error('Письмо не отправлено: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (config('mail.default') === 'log') {
            $this->warn('Письмо записано в storage/logs/laravel.log, во внешний ящик оно не уйдёт.');
            return self::SUCCESS;
        }

        $this->info('Письмо передано почтовому серверу. Проверьте ящик и папку «Спам».');
        return self::SUCCESS;
    }
}
