<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@mi-familia.org')->first();
        $miguel = User::where('email', 'miguel.ramirez@example.com')->first();

        // Persona para el admin
        $adminPerson = Person::create([
            'user_id' => $admin->id,
            'first_name' => 'Carlos',
            'patronymic' => 'Rodriguez',
            'matronymic' => 'Velasco',
            'gender' => 'M',
            'birth_date' => '1980-05-15',
            'birth_place' => 'Ciudad de Mexico',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Ciudad de Mexico',
            'residence_country' => 'Mexico',
            'occupation' => 'Ingeniero',
            'email' => 'admin@mi-familia.org',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_2',
            'origin_town' => 'Oaxaca',
            'migration_decade' => '1920-1930',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'community',
            'consent_status' => 'not_required',
            'created_by' => $admin->id,
        ]);

        // Actualizar usuario con person_id
        $admin->update(['person_id' => $adminPerson->id]);

        // Persona para Miguel (usuario con herencia)
        $miguelPerson = Person::create([
            'user_id' => $miguel->id,
            'first_name' => 'Miguel',
            'patronymic' => 'Ramirez',
            'matronymic' => 'Gutierrez',
            'gender' => 'M',
            'birth_date' => '1975-08-22',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Guadalajara',
            'residence_country' => 'Mexico',
            'occupation' => 'Comerciante',
            'email' => 'miguel.ramirez@example.com',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'origin_town' => 'Veracruz',
            'migration_decade' => '1900-1910',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'extended_family',
            'consent_status' => 'not_required',
            'created_by' => $miguel->id,
        ]);

        $miguel->update(['person_id' => $miguelPerson->id]);

        // Padre de Miguel
        $miguelFather = Person::create([
            'first_name' => 'Roberto',
            'patronymic' => 'Ramirez',
            'gender' => 'M',
            'birth_date' => '1945-03-10',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'death_date' => '2020-11-05',
            'death_place' => 'Guadalajara',
            'death_country' => 'Mexico',
            'is_living' => false,
            'occupation' => 'Agricultor',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'origin_town' => 'Veracruz',
            'migration_decade' => '1900-1910',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'extended_family',
            'consent_status' => 'not_required',
            'created_by' => $miguel->id,
        ]);

        // Madre de Miguel
        $miguelMother = Person::create([
            'first_name' => 'Rosa',
            'patronymic' => 'Gutierrez',
            'matronymic' => 'Martinez',
            'gender' => 'F',
            'birth_date' => '1948-07-20',
            'birth_place' => 'Ciudad de Mexico',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Guadalajara',
            'residence_country' => 'Mexico',
            'email' => 'rosa.gutierrez@example.com',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_2',
            'origin_town' => 'Puebla',
            'migration_decade' => '1910-1920',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'extended_family',
            'consent_status' => 'pending',
            'consent_requested_at' => now(),
            'created_by' => $miguel->id,
        ]);

        // Abuelo paterno de Miguel (emigrante)
        $miguelGrandfather = Person::create([
            'first_name' => 'Ernesto',
            'patronymic' => 'Ramirez',
            'gender' => 'M',
            'birth_date' => '1905-01-15',
            'birth_date_approx' => true,
            'birth_place' => 'Veracruz',
            'birth_country' => 'Mexico',
            'death_date' => '1985-06-30',
            'death_place' => 'Guadalajara',
            'death_country' => 'Mexico',
            'is_living' => false,
            'occupation' => 'Zapatero',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'origin_town' => 'Veracruz',
            'migration_decade' => '1920-1930',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'community',
            'consent_status' => 'not_required',
            'created_by' => $miguel->id,
        ]);

        // Esposa de Miguel
        $miguelWife = Person::create([
            'first_name' => 'Laura',
            'patronymic' => 'Mendez',
            'matronymic' => 'Ruiz',
            'gender' => 'F',
            'birth_date' => '1978-11-12',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Guadalajara',
            'residence_country' => 'Mexico',
            'email' => 'laura.mendez@example.com',
            'has_ethnic_heritage' => false,
            'privacy_level' => 'extended_family',
            'consent_status' => 'approved',
            'consent_responded_at' => now()->subDays(30),
            'created_by' => $miguel->id,
        ]);

        // Hijo de Miguel
        $miguelSon = Person::create([
            'first_name' => 'Mateo',
            'patronymic' => 'Ramirez',
            'matronymic' => 'Mendez',
            'gender' => 'M',
            'birth_date' => '2005-04-08',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'is_minor' => true,
            'residence_place' => 'Guadalajara',
            'residence_country' => 'Mexico',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'privacy_level' => 'direct_family',
            'consent_status' => 'not_required',
            'created_by' => $miguel->id,
        ]);

        // Hija de Miguel
        $miguelDaughter = Person::create([
            'first_name' => 'Sofia',
            'patronymic' => 'Ramirez',
            'matronymic' => 'Mendez',
            'gender' => 'F',
            'birth_date' => '2008-09-25',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'is_minor' => true,
            'residence_place' => 'Guadalajara',
            'residence_country' => 'Mexico',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'privacy_level' => 'direct_family',
            'consent_status' => 'not_required',
            'created_by' => $miguel->id,
        ]);

        // Hermano de Miguel
        Person::create([
            'first_name' => 'Daniel',
            'patronymic' => 'Ramirez',
            'matronymic' => 'Gutierrez',
            'gender' => 'M',
            'birth_date' => '1972-02-14',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Monterrey',
            'residence_country' => 'Mexico',
            'email' => 'daniel.ramirez@example.com',
            'occupation' => 'Medico',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'privacy_level' => 'extended_family',
            'consent_status' => 'pending',
            'consent_requested_at' => now()->subDays(3),
            'created_by' => $miguel->id,
        ]);
    }
}
