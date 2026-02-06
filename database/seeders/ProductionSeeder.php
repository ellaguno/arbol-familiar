<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder mínimo para producción.
 * Crea solo el usuario administrador y configuraciones básicas del sitio.
 *
 * Ejecutar con: php artisan db:seed --class=ProductionSeeder
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@mi-familia.org'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('MiFamilia2025!'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuario administrador creado:');
        $this->command->info('  Email: admin@mi-familia.org');
        $this->command->info('  Password: MiFamilia2025!');
        $this->command->warn('  ¡IMPORTANTE! Cambia la contraseña después del primer inicio de sesión.');
        $this->command->info('');

        // Ejecutar SiteSettingsSeeder si existe
        if (class_exists(SiteSettingsSeeder::class)) {
            $this->call(SiteSettingsSeeder::class);
            $this->command->info('Configuraciones del sitio creadas.');
        }

        $this->command->info('');
        $this->command->info('Seeder de producción completado.');
    }
}
