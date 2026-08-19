<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin Record',
            'role' => 'super_admin',
            'email' => 'superadmin@record.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Admin Record',
            'role' => 'admin',
            'email' => 'admin@record.com',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Budi Customer',
            'role' => 'customer',
            'email' => 'customer@record.com',
            'phone' => '081234567892',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Siti Pembeli',
            'role' => 'customer',
            'email' => 'siti@record.com',
            'phone' => '081234567893',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
