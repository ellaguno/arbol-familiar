<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Ejecutar con: php artisan db:seed
     * O para refrescar todo: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PersonSeeder::class,
            FamilySeeder::class,
            SurnameVariantSeeder::class,
            MessageSeeder::class,
        ]);

        $this->command->info('Datos de prueba de Mi Familia creados exitosamente.');
        $this->command->info('');
        $this->command->info('Usuarios de prueba:');
        $this->command->info('  - admin@mi-familia.org / MiFamilia2025! (Administrador)');
        $this->command->info('  - ivan.horvat@example.com / MiFamilia2025! (Usuario con herencia)');
        $this->command->info('  - maria.garcia@example.com / MiFamilia2025! (Usuario regular)');
        $this->command->info('  - ana.kovac@example.com / MiFamilia2025! (Usuario sin verificar)');
    }
}
