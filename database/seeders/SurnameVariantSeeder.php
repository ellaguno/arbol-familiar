<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\SurnameVariant;
use Illuminate\Database\Seeder;

class SurnameVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Variantes para el abuelo Ramirez
        $grandfather = Person::where('first_name', 'Ernesto')->where('patronymic', 'Ramirez')->first();

        if ($grandfather) {
            SurnameVariant::create([
                'person_id' => $grandfather->id,
                'original_surname' => 'Ramirez',
                'variant_1' => 'Ramires',
                'variant_2' => 'Ramirez de Leon',
                'notes' => 'El apellido Ramirez aparece como Ramires en algunos registros civiles antiguos. Tambien se registra con el compuesto Ramirez de Leon en documentos parroquiales.',
            ]);
        }

        // Variantes para la madre Rosa Gutierrez
        $rosaGutierrez = Person::where('first_name', 'Rosa')->where('patronymic', 'Gutierrez')->first();

        if ($rosaGutierrez) {
            SurnameVariant::create([
                'person_id' => $rosaGutierrez->id,
                'original_surname' => 'Gutierrez',
                'variant_1' => 'Gutierres',
                'variant_2' => null,
                'notes' => 'El apellido Gutierrez frecuentemente aparece como Gutierres en registros anteriores a 1950.',
            ]);
        }

        // Admin con apellido Velasco
        $admin = Person::where('matronymic', 'Velasco')->first();

        if ($admin) {
            SurnameVariant::create([
                'person_id' => $admin->id,
                'original_surname' => 'Velasco',
                'variant_1' => 'Belasco',
                'variant_2' => 'Velazco',
                'notes' => 'El apellido Velasco tiene variantes como Belasco (intercambio b/v comun en documentos coloniales) y Velazco.',
            ]);
        }
    }
}
