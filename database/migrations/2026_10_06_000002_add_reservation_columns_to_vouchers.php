<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'reserved_for')) {
                $table->unsignedBigInteger('reserved_for')->nullable()->after('created_by')
                    ->comment('ID user yang sedang memegang reservasi voucher sementara');
            }
            if (!Schema::hasColumn('vouchers', 'reserved_until')) {
                $table->timestamp('reserved_until')->nullable()->after('reserved_for')
                    ->comment('Batas waktu reservasi sementara (5 menit setelah game selesai)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'reserved_for')) {
                $table->dropColumn('reserved_for');
            }
            if (Schema::hasColumn('vouchers', 'reserved_until')) {
                $table->dropColumn('reserved_until');
            }
        });
    }
};
