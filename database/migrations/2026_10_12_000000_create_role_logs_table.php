<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Журнал: кто, когда и кому выдал или снял права. Имена хранятся текстом,
        // чтобы запись осталась понятной, даже если человека потом удалят.
        Schema::create('role_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('actor_id')->nullable();   // кто сделал (пусто, если из консоли)
            $t->string('actor_name');
            $t->unsignedBigInteger('target_id')->nullable();  // над кем
            $t->string('target_name');
            $t->string('target_email');
            $t->string('action', 30);                          // admin_granted, admin_revoked, owner_granted, user_deleted
            $t->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_logs');
    }
};
