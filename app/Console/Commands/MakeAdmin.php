<?php

namespace App\Console\Commands;

use App\Models\RoleLog;
use App\Models\User;
use Illuminate\Console\Command;

// Назначить или снять права администратора из консоли. Нужна, чтобы создать самого первого
// главного администратора (например, на хостинге, где нет демо-данных).
//
//   php artisan user:admin ivan@mail.ru --owner     главный администратор
//   php artisan user:admin ivan@mail.ru             обычный администратор
//   php artisan user:admin ivan@mail.ru --remove    снять все права
class MakeAdmin extends Command
{
    protected $signature = 'user:admin {email : Email пользователя} {--owner : Сделать главным администратором} {--remove : Снять права администратора}';

    protected $description = 'Назначить или снять права администратора';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('Пользователь с таким email не найден. Сначала он должен зарегистрироваться на сайте.');
            return self::FAILURE;
        }

        if ($this->option('remove')) {
            $user->forceFill(['is_admin' => false, 'is_owner' => false])->save();
            RoleLog::record(null, $user, 'admin_revoked');
            $this->info('Права администратора сняты: ' . $user->email);
            return self::SUCCESS;
        }

        $user->forceFill([
            'is_admin' => true,
            'is_owner' => $this->option('owner') ? true : $user->is_owner,
        ])->save();
        RoleLog::record(null, $user, $this->option('owner') ? 'owner_granted' : 'admin_granted');
        $this->info(($user->is_owner ? 'Главный администратор: ' : 'Администратор: ') . $user->email);
        return self::SUCCESS;
    }
}
