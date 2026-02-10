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
        $ivan = User::where('email', 'ivan.horvat@example.com')->first();

        // Persona para el admin
        $adminPerson = Person::create([
            'user_id' => $admin->id,
            'first_name' => 'Carlos',
            'patronymic' => 'Rodriguez',
            'matronymic' => 'Kovacevic',
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
            'origin_town' => 'Split',
            'migration_decade' => '1920-1930',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'community',
            'consent_status' => 'not_required',
            'created_by' => $admin->id,
        ]);

        // Actualizar usuario con person_id
        $admin->update(['person_id' => $adminPerson->id]);

        // Persona para Ivan (usuario con herencia)
        $ivanPerson = Person::create([
            'user_id' => $ivan->id,
            'first_name' => 'Ivan',
            'patronymic' => 'Horvat',
            'matronymic' => 'Babic',
            'gender' => 'M',
            'birth_date' => '1975-08-22',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Guadalajara',
            'residence_country' => 'Mexico',
            'occupation' => 'Comerciante',
            'email' => 'ivan.horvat@example.com',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'origin_town' => 'Osijek',
            'migration_decade' => '1900-1910',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'extended_family',
            'consent_status' => 'not_required',
            'created_by' => $ivan->id,
        ]);

        $ivan->update(['person_id' => $ivanPerson->id]);

        // Padre de Ivan
        $ivanFather = Person::create([
            'first_name' => 'Josip',
            'patronymic' => 'Horvat',
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
            'origin_town' => 'Osijek',
            'migration_decade' => '1900-1910',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'extended_family',
            'consent_status' => 'not_required',
            'created_by' => $ivan->id,
        ]);

        // Madre de Ivan
        $ivanMother = Person::create([
            'first_name' => 'Ana',
            'patronymic' => 'Babic',
            'matronymic' => 'Martinez',
            'gender' => 'F',
            'birth_date' => '1948-07-20',
            'birth_place' => 'Ciudad de Mexico',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Guadalajara',
            'residence_country' => 'Mexico',
            'email' => 'ana.babic@example.com',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_2',
            'origin_town' => 'Dubrovnik',
            'migration_decade' => '1910-1920',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'extended_family',
            'consent_status' => 'pending',
            'consent_requested_at' => now(),
            'created_by' => $ivan->id,
        ]);

        // Abuelo paterno de Ivan (emigrante)
        $ivanGrandfather = Person::create([
            'first_name' => 'Marko',
            'patronymic' => 'Horvat',
            'gender' => 'M',
            'birth_date' => '1905-01-15',
            'birth_date_approx' => true,
            'birth_place' => 'Osijek',
            'birth_country' => 'Croacia',
            'death_date' => '1985-06-30',
            'death_place' => 'Guadalajara',
            'death_country' => 'Mexico',
            'is_living' => false,
            'occupation' => 'Zapatero',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'origin_town' => 'Osijek',
            'migration_decade' => '1920-1930',
            'migration_destination' => 'Mexico',
            'privacy_level' => 'community',
            'consent_status' => 'not_required',
            'created_by' => $ivan->id,
        ]);

        // Esposa de Ivan
        $ivanWife = Person::create([
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
            'created_by' => $ivan->id,
        ]);

        // Hijo de Ivan
        $ivanSon = Person::create([
            'first_name' => 'Mateo',
            'patronymic' => 'Horvat',
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
            'created_by' => $ivan->id,
        ]);

        // Hija de Ivan
        $ivanDaughter = Person::create([
            'first_name' => 'Sofia',
            'patronymic' => 'Horvat',
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
            'created_by' => $ivan->id,
        ]);

        // Hermano de Ivan
        Person::create([
            'first_name' => 'Petar',
            'patronymic' => 'Horvat',
            'matronymic' => 'Babic',
            'gender' => 'M',
            'birth_date' => '1972-02-14',
            'birth_place' => 'Guadalajara',
            'birth_country' => 'Mexico',
            'is_living' => true,
            'residence_place' => 'Monterrey',
            'residence_country' => 'Mexico',
            'email' => 'petar.horvat@example.com',
            'occupation' => 'Medico',
            'has_ethnic_heritage' => true,
            'heritage_region' => 'region_3',
            'privacy_level' => 'extended_family',
            'consent_status' => 'pending',
            'consent_requested_at' => now()->subDays(3),
            'created_by' => $ivan->id,
        ]);
    }
}
