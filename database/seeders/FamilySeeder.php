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
        $ivan = User::where('email', 'ivan.horvat@example.com')->first();

        // Obtener personas
        $ivanPerson = Person::where('first_name', 'Ivan')->where('patronymic', 'Horvat')->first();
        $ivanFather = Person::where('first_name', 'Josip')->where('patronymic', 'Horvat')->first();
        $ivanMother = Person::where('first_name', 'Ana')->where('patronymic', 'Babic')->first();
        $ivanGrandfather = Person::where('first_name', 'Marko')->where('patronymic', 'Horvat')->first();
        $ivanWife = Person::where('first_name', 'Laura')->where('patronymic', 'Mendez')->first();
        $ivanSon = Person::where('first_name', 'Mateo')->where('patronymic', 'Horvat')->first();
        $ivanDaughter = Person::where('first_name', 'Sofia')->where('patronymic', 'Horvat')->first();
        $ivanBrother = Person::where('first_name', 'Petar')->where('patronymic', 'Horvat')->first();

        // Familia de los padres de Ivan
        $familyParents = Family::create([
            'husband_id' => $ivanFather->id,
            'wife_id' => $ivanMother->id,
            'marriage_date' => '1970-06-15',
            'marriage_place' => 'Guadalajara, Mexico',
            'status' => 'widowed',
            'created_by' => $ivan->id,
        ]);

        // Agregar hijos a la familia de los padres
        FamilyChild::create([
            'family_id' => $familyParents->id,
            'person_id' => $ivanBrother->id,
            'child_order' => 1,
            'relationship_type' => 'biological',
        ]);

        FamilyChild::create([
            'family_id' => $familyParents->id,
            'person_id' => $ivanPerson->id,
            'child_order' => 2,
            'relationship_type' => 'biological',
        ]);

        // Familia de Ivan y Laura
        $familyIvan = Family::create([
            'husband_id' => $ivanPerson->id,
            'wife_id' => $ivanWife->id,
            'marriage_date' => '2003-08-20',
            'marriage_place' => 'Guadalajara, Mexico',
            'status' => 'married',
            'created_by' => $ivan->id,
        ]);

        // Agregar hijos a la familia de Ivan
        FamilyChild::create([
            'family_id' => $familyIvan->id,
            'person_id' => $ivanSon->id,
            'child_order' => 1,
            'relationship_type' => 'biological',
        ]);

        FamilyChild::create([
            'family_id' => $familyIvan->id,
            'person_id' => $ivanDaughter->id,
            'child_order' => 2,
            'relationship_type' => 'biological',
        ]);

        // Familia de los abuelos (solo abuelo conocido, se crea la familia para mantener la relacion)
        $familyGrandparents = Family::create([
            'husband_id' => $ivanGrandfather->id,
            'wife_id' => null,
            'marriage_date' => '1940-01-01',
            'marriage_date_approx' => true,
            'marriage_place' => 'Osijek, Croacia',
            'status' => 'widowed',
            'created_by' => $ivan->id,
        ]);

        // El padre de Ivan es hijo del abuelo
        FamilyChild::create([
            'family_id' => $familyGrandparents->id,
            'person_id' => $ivanFather->id,
            'child_order' => 1,
            'relationship_type' => 'biological',
        ]);
    }
}
