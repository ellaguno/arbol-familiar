<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        User::create([
            'email' => 'admin@mi-familia.org',
            'password' => Hash::make('MiFamilia2025!'),
            'is_admin' => true,
            'language' => 'es',
            'privacy_level' => 'community',
            'email_verified_at' => now(),
            'first_login_completed' => true,
        ]);

        // Usuario de prueba con herencia
        User::create([
            'email' => 'ivan.horvat@example.com',
            'password' => Hash::make('MiFamilia2025!'),
            'language' => 'es',
            'privacy_level' => 'extended_family',
            'email_verified_at' => now(),
            'first_login_completed' => true,
        ]);

        // Usuario de prueba regular
        User::create([
            'email' => 'maria.garcia@example.com',
            'password' => Hash::make('MiFamilia2025!'),
            'language' => 'es',
            'privacy_level' => 'direct_family',
            'email_verified_at' => now(),
            'first_login_completed' => false,
        ]);

        // Usuario de prueba (nuevo, sin verificar)
        User::create([
            'email' => 'ana.kovac@example.com',
            'password' => Hash::make('MiFamilia2025!'),
            'language' => 'en',
            'privacy_level' => 'direct_family',
            'email_verified_at' => null,
            'first_login_completed' => false,
            'confirmation_code' => '123456',
        ]);
    }
}
