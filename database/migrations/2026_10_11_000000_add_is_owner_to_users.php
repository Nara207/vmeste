<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // is_owner: «главный администратор», единственный, кто может назначать и снимать других администраторов
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('is_owner')->default(false)->after('is_admin');
        });

        // Если демо-администратор уже есть, делаем его главным
        DB::table('users')->where('email', 'admin@example.com')->update(['is_admin' => true, 'is_owner' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('is_owner');
        });
    }
};
