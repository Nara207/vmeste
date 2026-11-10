<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // заявки на книги
        Schema::create('book_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('title', 150);                       // какую книгу просят добавить
            $t->string('author', 150)->nullable();
            $t->string('comment', 500)->nullable();
            $t->string('status', 20)->default('new');       // new ждёт, added добавлена, rejected отклонена
            $t->string('admin_reply', 300)->nullable();     // что ответил администратор
            $t->foreignId('book_id')->nullable()->constrained()->nullOnDelete();  // какую книгу добавили
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_requests');
    }
};
