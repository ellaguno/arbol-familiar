<?php

namespace Database\Seeders;

use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class FamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $miguel = User::where('email', 'miguel.ramirez@example.com')->first();

        // Obtener personas
        $miguelPerson = Person::where('first_name', 'Miguel')->where('patronymic', 'Ramirez')->first();
        $miguelFather = Person::where('first_name', 'Roberto')->where('patronymic', 'Ramirez')->first();
        $miguelMother = Person::where('first_name', 'Rosa')->where('patronymic', 'Gutierrez')->first();
        $miguelGrandfather = Person::where('first_name', 'Ernesto')->where('patronymic', 'Ramirez')->first();
        $miguelWife = Person::where('first_name', 'Laura')->where('patronymic', 'Mendez')->first();
        $miguelSon = Person::where('first_name', 'Mateo')->where('patronymic', 'Ramirez')->first();
        $miguelDaughter = Person::where('first_name', 'Sofia')->where('patronymic', 'Ramirez')->first();
        $miguelBrother = Person::where('first_name', 'Daniel')->where('patronymic', 'Ramirez')->first();

        // Familia de los padres de Miguel
        $familyParents = Family::create([
            'husband_id' => $miguelFather->id,
            'wife_id' => $miguelMother->id,
            'marriage_date' => '1970-06-15',
            'marriage_place' => 'Guadalajara, Mexico',
            'status' => 'widowed',
            'created_by' => $miguel->id,
        ]);

        // Agregar hijos a la familia de los padres
        FamilyChild::create([
            'family_id' => $familyParents->id,
            'person_id' => $miguelBrother->id,
            'child_order' => 1,
            'relationship_type' => 'biological',
        ]);

        FamilyChild::create([
            'family_id' => $familyParents->id,
            'person_id' => $miguelPerson->id,
            'child_order' => 2,
            'relationship_type' => 'biological',
        ]);

        // Familia de Miguel y Laura
        $familyMiguel = Family::create([
            'husband_id' => $miguelPerson->id,
            'wife_id' => $miguelWife->id,
            'marriage_date' => '2003-08-20',
            'marriage_place' => 'Guadalajara, Mexico',
            'status' => 'married',
            'created_by' => $miguel->id,
        ]);

        // Agregar hijos a la familia de Miguel
        FamilyChild::create([
            'family_id' => $familyMiguel->id,
            'person_id' => $miguelSon->id,
            'child_order' => 1,
            'relationship_type' => 'biological',
        ]);

        FamilyChild::create([
            'family_id' => $familyMiguel->id,
            'person_id' => $miguelDaughter->id,
            'child_order' => 2,
            'relationship_type' => 'biological',
        ]);

        // Familia de los abuelos (solo abuelo conocido, se crea la familia para mantener la relacion)
        $familyGrandparents = Family::create([
            'husband_id' => $miguelGrandfather->id,
            'wife_id' => null,
            'marriage_date' => '1940-01-01',
            'marriage_date_approx' => true,
            'marriage_place' => 'Veracruz, Mexico',
            'status' => 'widowed',
            'created_by' => $miguel->id,
        ]);

        // El padre de Miguel es hijo del abuelo
        FamilyChild::create([
            'family_id' => $familyGrandparents->id,
            'person_id' => $miguelFather->id,
            'child_order' => 1,
            'relationship_type' => 'biological',
        ]);
    }
}
