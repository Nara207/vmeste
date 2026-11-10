<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Таблица, в которой Laravel хранит уведомления (канал «database»)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');   // кому: тип + id пользователя
            $table->text('data');           // содержимое уведомления в JSON
            $table->timestamp('read_at')->nullable(); // пусто = ещё не прочитано
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
