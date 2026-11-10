<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleLog extends Model
{
    const UPDATED_AT = null; // у записи журнала есть только время создания

    protected $fillable = ['actor_id', 'actor_name', 'target_id', 'target_name', 'target_email', 'action'];

    public const LABELS = [
        'admin_granted' => 'назначил(а) администратором',
        'admin_revoked' => 'снял(а) права администратора у',
        'owner_granted' => 'назначил(а) главным администратором',
        'user_deleted' => 'удалил(а) пользователя',
    ];

    // Записать действие. $actor = null означает «консоль»
    public static function record(?User $actor, User $target, string $action): void
    {
        static::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'Консоль',
            'target_id' => $target->id,
            'target_name' => $target->name,
            'target_email' => $target->email,
            'action' => $action,
        ]);
    }
}
