<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_game_at')) {
                $table->date('last_game_at')->nullable()->after('email_verified_at')
                    ->comment('Tanggal terakhir user memainkan Stopwatch Challenge (1 kali per hari)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_game_at')) {
                $table->dropColumn('last_game_at');
            }
        });
    }
};
