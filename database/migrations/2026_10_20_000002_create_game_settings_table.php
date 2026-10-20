<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('game_settings')) {
            Schema::create('game_settings', function (Blueprint $table) {
                $table->id();
                $table->string('game_code', 50)->unique();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->string('icon', 50)->default('fa-gamepad');
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable();
                $table->timestamps();
            });

            // Seed default row untuk Stopwatch Challenge
            DB::table('game_settings')->insert([
                'game_code'   => 'stopwatch',
                'name'        => 'Stopwatch Challenge (10.00 Detik)',
                'description' => 'Tantangan menekan tombol tepat pada 10.00 detik untuk mendapatkan voucher diskon belanja.',
                'icon'        => 'fa-stopwatch',
                'is_active'   => true,
                'config'      => json_encode([
                    'daily_limit' => 1,
                    'category_id' => null, // null = acak dari semua kategori / voucher yang ada
                    'tiers'       => [
                        'perfect' => [
                            'key'    => 'perfect',
                            'label'  => 'PERFECT 10.00s! 🎯',
                            'title'  => 'Luar Biasa Sempurna!',
                            'amount' => 50000,
                            'min'    => 9.98,
                            'max'    => 10.02,
                            'badge'  => 'Gold Tier',
                            'color'  => 'amber',
                        ],
                        'near' => [
                            'key'    => 'near',
                            'label'  => 'HAMPIR SEMPURNA! ⚡',
                            'title'  => 'Bagus Sekali, Nyaris Pas!',
                            'amount' => 30000,
                            'min'    => 9.85,
                            'max'    => 10.15,
                            'badge'  => 'Violet Tier',
                            'color'  => 'violet',
                        ],
                        'good' => [
                            'key'    => 'good',
                            'label'  => 'PARTISIPASI HEBAT! 👏',
                            'title'  => 'Terima Kasih Sudah Mencoba!',
                            'amount' => 10000,
                            'min'    => 0,
                            'max'    => 999.0,
                            'badge'  => 'Emerald Tier',
                            'color'  => 'emerald',
                        ],
                    ],
                ]),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_settings');
    }
};
