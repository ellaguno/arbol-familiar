<?php

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Invitation;
use App\Models\Media;
use App\Models\Person;
use App\Models\PersonEditPermission;
use App\Models\SurnameVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    private function createFamilyWithChild(): array
    {
        $user = User::factory()->create();
        $father = Person::factory()->create(['gender' => 'M', 'created_by' => $user->id]);
        $mother = Person::factory()->create(['gender' => 'F', 'created_by' => $user->id]);
        $child = Person::factory()->create(['created_by' => $user->id]);

        $family = Family::factory()->create([
            'husband_id' => $father->id,
            'wife_id' => $mother->id,
            'created_by' => $user->id,
        ]);

        $family->children()->attach($child->id, ['child_order' => 1]);

        return [$user, $father, $mother, $child, $family];
    }

    public function test_fillable_attributes(): void
    {
        $person = Person::factory()->create([
            'first_name' => 'Ivan',
            'patronymic' => 'Horvat',
            'gender' => 'M',
        ]);

        $this->assertEquals('Ivan', $person->first_name);
        $this->assertEquals('Horvat', $person->patronymic);
        $this->assertEquals('M', $person->gender);
    }

    public function test_casts(): void
    {
        $person = Person::factory()->create([
            'is_living' => 1,
            'is_minor' => 0,
            'has_ethnic_heritage' => 1,
        ]);

        $this->assertIsBool($person->is_living);
        $this->assertIsBool($person->is_minor);
        $this->assertIsBool($person->has_ethnic_heritage);
    }

    public function test_full_name_attribute(): void
    {
        $person = Person::factory()->create([
            'first_name' => 'Ivan',
            'patronymic' => 'Horvat',
            'matronymic' => 'Kovacic',
        ]);

        $this->assertEquals('Ivan Horvat Kovacic', $person->full_name);
    }

    public function test_full_name_without_matronymic(): void
    {
        $person = Person::factory()->create([
            'first_name' => 'Ivan',
            'patronymic' => 'Horvat',
            'matronymic' => null,
        ]);

        $this->assertEquals('Ivan Horvat', $person->full_name);
    }

    public function test_age_attribute_living(): void
    {
        $person = Person::factory()->create([
            'birth_year' => 1985,
            'is_living' => true,
        ]);

        $expected = now()->year - 1985;
        $this->assertEquals($expected, $person->age);
    }

    public function test_age_attribute_deceased(): void
    {
        $person = Person::factory()->create([
            'birth_year' => 1950,
            'death_year' => 2020,
            'is_living' => false,
        ]);

        $this->assertEquals(70, $person->age);
    }

    public function test_age_null_without_birth(): void
    {
        $person = Person::factory()->create(['birth_year' => null, 'birth_date' => null]);
        $this->assertNull($person->age);
    }

    public function test_birth_date_formatted_full(): void
    {
        $person = Person::factory()->create([
            'birth_year' => 1985,
            'birth_month' => 3,
            'birth_day' => 15,
        ]);

        $this->assertEquals('15 Mar 1985', $person->birth_date_formatted);
    }

    public function test_birth_date_formatted_year_month(): void
    {
        $person = Person::factory()->create([
            'birth_year' => 1985,
            'birth_month' => 3,
            'birth_day' => null,
        ]);

        $this->assertEquals('Mar 1985', $person->birth_date_formatted);
    }

    public function test_birth_date_formatted_year_only(): void
    {
        $person = Person::factory()->create([
            'birth_year' => 1985,
            'birth_month' => null,
            'birth_day' => null,
        ]);

        $this->assertEquals('1985', $person->birth_date_formatted);
    }

    public function test_families_as_husband(): void
    {
        $user = User::factory()->create();
        $husband = Person::factory()->create(['gender' => 'M', 'created_by' => $user->id]);
        $wife = Person::factory()->create(['gender' => 'F', 'created_by' => $user->id]);

        Family::factory()->create([
            'husband_id' => $husband->id,
            'wife_id' => $wife->id,
            'created_by' => $user->id,
        ]);

        $this->assertCount(1, $husband->familiesAsHusband);
    }

    public function test_families_as_child(): void
    {
        [, , , $child, $family] = $this->createFamilyWithChild();
        $this->assertCount(1, $child->familiesAsChild);
    }

    public function test_parents_attribute(): void
    {
        [, $father, $mother, $child] = $this->createFamilyWithChild();
        $parents = $child->parents;

        $this->assertEquals($father->id, $parents['father']->id);
        $this->assertEquals($mother->id, $parents['mother']->id);
    }

    public function test_father_and_mother_attributes(): void
    {
        [, $father, $mother, $child] = $this->createFamilyWithChild();

        $this->assertEquals($father->id, $child->father->id);
        $this->assertEquals($mother->id, $child->mother->id);
    }

    public function test_siblings_attribute(): void
    {
        [$user, $father, $mother, $child1, $family] = $this->createFamilyWithChild();
        $child2 = Person::factory()->create(['created_by' => $user->id]);
        $family->children()->attach($child2->id, ['child_order' => 2]);

        $this->assertCount(1, $child1->siblings);
        $this->assertEquals($child2->id, $child1->siblings->first()->id);
    }

    public function test_current_spouse_attribute(): void
    {
        $user = User::factory()->create();
        $husband = Person::factory()->create(['gender' => 'M', 'created_by' => $user->id]);
        $wife = Person::factory()->create(['gender' => 'F', 'created_by' => $user->id]);

        Family::factory()->create([
            'husband_id' => $husband->id,
            'wife_id' => $wife->id,
            'status' => 'married',
            'created_by' => $user->id,
        ]);

        $this->assertEquals($wife->id, $husband->currentSpouse->id);
        $this->assertEquals($husband->id, $wife->currentSpouse->id);
    }

    public function test_children_attribute(): void
    {
        [, $father, , $child] = $this->createFamilyWithChild();
        $this->assertCount(1, $father->children);
        $this->assertEquals($child->id, $father->children->first()->id);
    }

    public function test_surname_variants_relationship(): void
    {
        $person = Person::factory()->create();
        SurnameVariant::create([
            'person_id' => $person->id,
            'original_surname' => 'Horvat',
            'variant_1' => 'Horvath',
        ]);

        $this->assertCount(1, $person->surnameVariants);
    }

    public function test_media_morphmany(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user->id]);
        Media::create([
            'mediable_type' => Person::class,
            'mediable_id' => $person->id,
            'type' => 'image',
            'title' => 'Test photo',
            'file_path' => 'test.jpg',
            'file_name' => 'test.jpg',
            'created_by' => $user->id,
        ]);

        $this->assertCount(1, $person->media);
    }

    public function test_events_relationship(): void
    {
        $person = Person::factory()->create();
        Event::create([
            'person_id' => $person->id,
            'type' => 'BIRT',
            'date' => '1985-05-15',
        ]);

        $this->assertCount(1, $person->events);
    }

    public function test_requires_consent(): void
    {
        $person = Person::factory()->create([
            'is_living' => true,
            'is_minor' => false,
            'email' => 'test@test.com',
            'consent_status' => 'pending',
        ]);

        $this->assertTrue($person->requiresConsent());
    }

    public function test_does_not_require_consent_when_approved(): void
    {
        $person = Person::factory()->create([
            'is_living' => true,
            'is_minor' => false,
            'email' => 'test@test.com',
            'consent_status' => 'approved',
        ]);

        $this->assertFalse($person->requiresConsent());
    }

    public function test_scope_ethnic_heritage(): void
    {
        Person::factory()->create(['has_ethnic_heritage' => true]);
        Person::factory()->create(['has_ethnic_heritage' => false]);

        $this->assertCount(1, Person::ethnicHeritage()->get());
    }

    public function test_scope_living(): void
    {
        Person::factory()->create(['is_living' => true]);
        Person::factory()->create(['is_living' => false]);

        $this->assertCount(1, Person::living()->get());
    }

    public function test_scope_search_by_name_single_term(): void
    {
        Person::factory()->create(['first_name' => 'Ivan', 'patronymic' => 'Horvat']);
        Person::factory()->create(['first_name' => 'Maria', 'patronymic' => 'Lopez']);

        $results = Person::searchByName('Ivan')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Ivan', $results->first()->first_name);
    }

    public function test_scope_search_by_name_multiple_terms(): void
    {
        Person::factory()->create(['first_name' => 'Ivan', 'patronymic' => 'Horvat']);
        Person::factory()->create(['first_name' => 'Maria', 'patronymic' => 'Lopez']);

        $results = Person::searchByName('Ivan Horvat')->get();
        $this->assertCount(1, $results);
    }

    public function test_can_be_edited_by_creator(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user->id]);

        $this->assertTrue($person->canBeEditedBy($user->id));
    }

    public function test_can_be_edited_by_linked_user(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($person->canBeEditedBy($user->id));
    }

    public function test_cannot_be_edited_by_stranger(): void
    {
        $creator = User::factory()->create();
        $stranger = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $creator->id]);

        $this->assertFalse($person->canBeEditedBy($stranger->id));
    }

    public function test_can_be_viewed_by_privacy_levels(): void
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();

        $directFamily = Person::factory()->create(['privacy_level' => 'direct_family', 'created_by' => $creator->id]);
        $community = Person::factory()->create(['privacy_level' => 'community', 'created_by' => $creator->id]);

        // Creator can always see
        $this->assertTrue($directFamily->canBeViewedBy($creator));
        $this->assertTrue($community->canBeViewedBy($creator));

        // Other user: direct_family=no (not family), community=yes
        $this->assertFalse($directFamily->canBeViewedBy($other));
        $this->assertTrue($community->canBeViewedBy($other));

        // No user: nobody can see (no public level anymore)
        $this->assertFalse($directFamily->canBeViewedBy(null));
        $this->assertFalse($community->canBeViewedBy(null));
    }

    public function test_is_minor_calculated_attribute(): void
    {
        $minor = Person::factory()->create([
            'is_minor' => false,
            'birth_year' => now()->year - 10,
        ]);

        $adult = Person::factory()->create([
            'is_minor' => false,
            'birth_year' => now()->year - 30,
        ]);

        $this->assertTrue($minor->is_minor_calculated);
        $this->assertFalse($adult->is_minor_calculated);
    }

    public function test_direct_family_attribute(): void
    {
        [, $father, $mother, $child] = $this->createFamilyWithChild();

        $directFamily = $child->directFamily;

        $relationships = $directFamily->pluck('relationship')->toArray();
        $this->assertContains('father', $relationships);
        $this->assertContains('mother', $relationships);
    }
}
