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
        // Variantes para el abuelo Horvat
        $grandfather = Person::where('first_name', 'Marko')->where('patronymic', 'Horvat')->first();

        if ($grandfather) {
            SurnameVariant::create([
                'person_id' => $grandfather->id,
                'original_surname' => 'Horvat',
                'variant_1' => 'Horvath',
                'variant_2' => 'Orvat',
                'notes' => 'El apellido Horvat se escribio como Horvath en algunos documentos mexicanos debido a la influencia hungara. Tambien aparece como Orvat en algunos registros civiles.',
            ]);
        }

        // Variantes para la madre Ana Babic
        $anaBabic = Person::where('first_name', 'Ana')->where('patronymic', 'Babic')->first();

        if ($anaBabic) {
            SurnameVariant::create([
                'person_id' => $anaBabic->id,
                'original_surname' => 'Babic',
                'variant_1' => 'Babich',
                'variant_2' => null,
                'notes' => 'El apellido Babic frecuentemente se hispanizo como Babich en Mexico.',
            ]);
        }

        // Admin con apellido Kovacevic
        $admin = Person::where('matronymic', 'Kovacevic')->first();

        if ($admin) {
            SurnameVariant::create([
                'person_id' => $admin->id,
                'original_surname' => 'Kovacevic',
                'variant_1' => 'Kovachevich',
                'variant_2' => 'Kovacevich',
                'notes' => 'El apellido Kovacevic (que significa "hijo del herrero") tiene multiples variantes debido a la transliteracion del alfabeto original.',
            ]);
        }
    }
}
