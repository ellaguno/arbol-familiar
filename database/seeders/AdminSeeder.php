<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder para crear el usuario administrador inicial.
 *
 * Uso en produccion:
 *   php artisan db:seed --class=AdminSeeder
 *
 * IMPORTANTE: Cambiar la contrasena inmediatamente despues del primer login.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar si ya existe un admin
        if (User::where('is_admin', true)->exists()) {
            $this->command->warn('Ya existe un usuario administrador. Saltando...');
            return;
        }

        // Crear persona del administrador
        $person = Person::create([
            'first_name' => 'Administrador',
            'patronymic' => 'Sistema',
            'gender' => 'U',
            'is_living' => true,
            'privacy_level' => 'direct_family',
            'consent_status' => 'not_required',
        ]);

        // Crear usuario administrador
        $user = User::create([
            'email' => 'admin@mi-familia.org',
            'password' => Hash::make('MiFamiliaAdmin2025!'),
            'person_id' => $person->id,
            'is_admin' => true,
            'email_verified_at' => now(),
            'first_login_completed' => true,
            'language' => 'es',
            'privacy_level' => 'direct_family',
        ]);

        // Vincular persona al usuario
        $person->update([
            'user_account_id' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('  Usuario administrador creado:');
        $this->command->info('  Email: admin@mi-familia.org');
        $this->command->info('  Password: MiFamiliaAdmin2025!');
        $this->command->info('');
        $this->command->warn('  IMPORTANTE: Cambiar la contrasena');
        $this->command->warn('  inmediatamente despues del primer login.');
        $this->command->info('===========================================');
        $this->command->info('');
    }
}
