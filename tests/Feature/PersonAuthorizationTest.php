<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** El FormRequest valida los campos obligatorios. */
    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('persons.store'), ['gender' => 'M'])
            ->assertSessionHasErrors(['first_name', 'patronymic']);
    }

    /** El FormRequest deriva birth_date de sus componentes al crear. */
    public function test_store_derives_birth_date_from_components(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('persons.store'), [
            'first_name' => 'Ana',
            'patronymic' => 'Ruiz',
            'gender' => 'F',
            'privacy_level' => 'community',
            'birth_year' => 1990,
            'birth_month' => 5,
            'birth_day' => 3,
        ])->assertRedirect();

        $person = Person::where('first_name', 'Ana')->first();
        $this->assertNotNull($person);
        $this->assertEquals('1990-05-03', $person->birth_date->format('Y-m-d'));
    }

    /** La PersonPolicy niega editar a un extraño (persona privada ajena). */
    public function test_update_denied_for_stranger(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $person = Person::factory()->create([
            'created_by' => $owner->id,
            'privacy_level' => 'direct_family',
        ]);

        // El extraño no puede editar -> Gate deniega (update en la policy).
        $this->assertTrue($stranger->cannot('update', $person));
        // El creador sí puede.
        $this->assertTrue($owner->can('update', $person));
    }

    /** La PersonPolicy respeta la visibilidad (view). */
    public function test_view_policy_uses_privacy(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $community = Person::factory()->create(['created_by' => $owner->id, 'privacy_level' => 'community']);
        $private = Person::factory()->create(['created_by' => $owner->id, 'privacy_level' => 'direct_family']);

        $this->assertTrue($stranger->can('view', $community));
        $this->assertTrue($stranger->cannot('view', $private));
    }
}
