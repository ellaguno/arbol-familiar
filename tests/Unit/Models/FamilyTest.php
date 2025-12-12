<?php

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $h = Person::factory()->create(['gender' => 'M', 'created_by' => $user->id]);
        $w = Person::factory()->create(['gender' => 'F', 'created_by' => $user->id]);

        $family = Family::create([
            'husband_id' => $h->id,
            'wife_id' => $w->id,
            'status' => 'married',
            'created_by' => $user->id,
        ]);

        $this->assertEquals('married', $family->status);
        $this->assertEquals($h->id, $family->husband_id);
    }

    public function test_casts(): void
    {
        $user = User::factory()->create();
        $h = Person::factory()->create(['created_by' => $user->id]);
        $w = Person::factory()->create(['created_by' => $user->id]);

        $family = Family::create([
            'husband_id' => $h->id,
            'wife_id' => $w->id,
            'marriage_date' => '1980-06-15',
            'marriage_date_approx' => true,
            'status' => 'married',
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $family->marriage_date);
        $this->assertIsBool($family->marriage_date_approx);
    }

    public function test_husband_and_wife_relationships(): void
    {
        $user = User::factory()->create();
        $h = Person::factory()->create(['first_name' => 'Ivan', 'created_by' => $user->id]);
        $w = Person::factory()->create(['first_name' => 'Maria', 'created_by' => $user->id]);

        $family = Family::factory()->create([
            'husband_id' => $h->id,
            'wife_id' => $w->id,
            'created_by' => $user->id,
        ]);

        $this->assertEquals('Ivan', $family->husband->first_name);
        $this->assertEquals('Maria', $family->wife->first_name);
    }

    public function test_children_relationship(): void
    {
        $user = User::factory()->create();
        $h = Person::factory()->create(['created_by' => $user->id]);
        $w = Person::factory()->create(['created_by' => $user->id]);
        $child = Person::factory()->create(['created_by' => $user->id]);

        $family = Family::factory()->create([
            'husband_id' => $h->id,
            'wife_id' => $w->id,
            'created_by' => $user->id,
        ]);
        $family->children()->attach($child->id, ['child_order' => 1]);

        $this->assertCount(1, $family->children);
        $this->assertEquals($child->id, $family->children->first()->id);
    }

    public function test_spouses_attribute(): void
    {
        $user = User::factory()->create();
        $h = Person::factory()->create(['created_by' => $user->id]);
        $w = Person::factory()->create(['created_by' => $user->id]);

        $family = Family::factory()->create([
            'husband_id' => $h->id,
            'wife_id' => $w->id,
            'created_by' => $user->id,
        ]);

        $this->assertCount(2, $family->spouses);
    }

    public function test_has_ethnic_heritage(): void
    {
        $user = User::factory()->create();
        $h = Person::factory()->heritage()->create(['created_by' => $user->id]);
        $w = Person::factory()->create(['has_ethnic_heritage' => false, 'created_by' => $user->id]);

        $family = Family::factory()->create([
            'husband_id' => $h->id,
            'wife_id' => $w->id,
            'created_by' => $user->id,
        ]);

        $this->assertTrue($family->hasEthnicHeritage());
    }

    public function test_label_attribute(): void
    {
        $user = User::factory()->create();
        $h = Person::factory()->create(['patronymic' => 'Horvat', 'created_by' => $user->id]);
        $w = Person::factory()->create(['patronymic' => 'Kovacic', 'created_by' => $user->id]);

        $family = Family::factory()->create([
            'husband_id' => $h->id,
            'wife_id' => $w->id,
            'created_by' => $user->id,
        ]);

        $this->assertEquals('Horvat / Kovacic', $family->label);
    }

    public function test_label_without_spouses(): void
    {
        $user = User::factory()->create();
        $family = Family::factory()->create([
            'husband_id' => null,
            'wife_id' => null,
            'created_by' => $user->id,
        ]);

        $this->assertEquals('Familia sin nombre', $family->label);
    }

    public function test_events_relationship(): void
    {
        $user = User::factory()->create();
        $family = Family::factory()->create(['created_by' => $user->id]);

        Event::create([
            'family_id' => $family->id,
            'type' => 'MARR',
            'date' => '1980-06-15',
        ]);

        $this->assertCount(1, $family->events);
    }

    public function test_creator_relationship(): void
    {
        $user = User::factory()->create();
        $family = Family::factory()->create(['created_by' => $user->id]);

        $this->assertEquals($user->id, $family->creator->id);
    }
}
