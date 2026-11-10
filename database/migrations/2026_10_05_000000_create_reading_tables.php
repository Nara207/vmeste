<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // администратор это просто отметка в таблице users
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_admin')->default(false);
        });

        Schema::create('books', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('author');
            $t->string('genre')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('chapters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('number');
            $t->string('title');
            $t->longText('body');                              // текст главы, каждый абзац с новой строки
            $t->unsignedInteger('paragraph_count')->default(0);
            $t->timestamps();
        });

        // «Моя полка»: какие книги добавил пользователь
        Schema::create('book_user', function (Blueprint $t) {
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->primary(['book_id', 'user_id']);
        });

        Schema::create('rooms', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->string('code', 8)->unique();
            $t->boolean('spoiler_protection')->default(true);
            $t->timestamps();
        });

        // кто в комнате и до какого места дочитал
        Schema::create('room_user', function (Blueprint $t) {
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('progress_chapter_id')->nullable()->constrained('chapters')->nullOnDelete();
            $t->integer('progress_paragraph')->nullable();
            $t->timestamps();
            $t->primary(['room_id', 'user_id']);
        });

        Schema::create('bookmarks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('paragraph');
            $t->timestamps();
            $t->unique(['room_id', 'user_id', 'chapter_id', 'paragraph']);
        });

        Schema::create('notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('room_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('paragraph');
            $t->text('body');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['notes', 'bookmarks', 'room_user', 'rooms', 'book_user', 'chapters', 'books'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('is_admin');
        });
    }
};
