<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreeTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithTree(): array
    {
        $user = User::factory()->create();

        // Create root person
        $root = Person::factory()->create([
            'first_name' => 'Ivan',
            'patronymic' => 'Horvat',
            'user_id' => $user->id,
            'created_by' => $user->id,
            'privacy_level' => 'community',
        ]);
        $user->update(['person_id' => $root->id]);

        // Create parents
        $father = Person::factory()->create([
            'first_name' => 'Ante',
            'patronymic' => 'Horvat',
            'gender' => 'M',
            'created_by' => $user->id,
            'privacy_level' => 'community',
        ]);

        $mother = Person::factory()->create([
            'first_name' => 'Maria',
            'patronymic' => 'Kovacic',
            'gender' => 'F',
            'created_by' => $user->id,
            'privacy_level' => 'community',
        ]);

        $parentFamily = Family::factory()->create([
            'husband_id' => $father->id,
            'wife_id' => $mother->id,
            'marriage_date' => '1975-06-15',
            'created_by' => $user->id,
        ]);
        $parentFamily->children()->attach($root->id, ['child_order' => 1]);

        // Create spouse and child
        $spouse = Person::factory()->create([
            'first_name' => 'Ana',
            'patronymic' => 'Garcia',
            'gender' => 'F',
            'created_by' => $user->id,
            'privacy_level' => 'community',
        ]);

        $ownFamily = Family::factory()->create([
            'husband_id' => $root->id,
            'wife_id' => $spouse->id,
            'marriage_date' => '2010-09-20',
            'status' => 'married',
            'created_by' => $user->id,
        ]);

        $child = Person::factory()->create([
            'first_name' => 'Petar',
            'patronymic' => 'Horvat',
            'birth_year' => 2015,
            'created_by' => $user->id,
            'privacy_level' => 'community',
        ]);
        $ownFamily->children()->attach($child->id, ['child_order' => 1]);

        return [
            'user' => $user->fresh(),
            'root' => $root,
            'father' => $father,
            'mother' => $mother,
            'spouse' => $spouse,
            'child' => $child,
            'parentFamily' => $parentFamily,
            'ownFamily' => $ownFamily,
        ];
    }

    public function test_tree_index_redirects_to_user_tree(): void
    {
        $data = $this->createUserWithTree();

        $response = $this->actingAs($data['user'])->get('/tree');
        $response->assertRedirect(route('tree.view', $data['root']->id));
    }

    public function test_tree_view_shows_person(): void
    {
        $data = $this->createUserWithTree();

        $response = $this->actingAs($data['user'])->get('/tree/view/' . $data['root']->id);
        $response->assertStatus(200);
    }

    public function test_tree_api_returns_json_data(): void
    {
        $data = $this->createUserWithTree();

        $response = $this->actingAs($data['user'])
            ->getJson('/tree/api/' . $data['root']->id . '/data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'root' => ['id', 'name', 'gender', 'hasFather', 'hasMother'],
            'ancestors',
            'descendants',
        ]);
    }

    public function test_tree_api_includes_ancestors(): void
    {
        $data = $this->createUserWithTree();

        $response = $this->actingAs($data['user'])
            ->getJson('/tree/api/' . $data['root']->id . '/data?direction=ancestors&generations=2');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue($json['root']['hasFather']);
        $this->assertTrue($json['root']['hasMother']);
        $this->assertNotEmpty($json['ancestors']);

        $ancestorNames = collect($json['ancestors'])->pluck('name')->toArray();
        $this->assertContains('Ante Horvat', $ancestorNames);
        $this->assertContains('Maria Kovacic', $ancestorNames);
    }

    public function test_tree_api_includes_descendants(): void
    {
        $data = $this->createUserWithTree();

        $response = $this->actingAs($data['user'])
            ->getJson('/tree/api/' . $data['root']->id . '/data?direction=descendants&generations=2');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertNotEmpty($json['descendants']);
        $firstFamily = $json['descendants'][0];
        $this->assertNotNull($firstFamily['spouse']);
        $this->assertNotEmpty($firstFamily['children']);
    }

    public function test_tree_fan_data(): void
    {
        $data = $this->createUserWithTree();

        $response = $this->actingAs($data['user'])
            ->getJson('/tree/api/' . $data['root']->id . '/fan?generations=3');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('children', $json);
    }

    public function test_tree_timeline(): void
    {
        $data = $this->createUserWithTree();

        // Add birth date to root for timeline
        $data['root']->update(['birth_date' => '1985-05-15']);

        $response = $this->actingAs($data['user'])
            ->getJson('/tree/api/' . $data['root']->id . '/timeline');

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertIsArray($json);
        // Should have at least birth event and marriage
        $types = collect($json)->pluck('type')->toArray();
        $this->assertContains('BIRT', $types);
        $this->assertContains('MARR', $types);
    }

    public function test_tree_respects_privacy_private(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $person = Person::factory()->create([
            'privacy_level' => 'direct_family',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($stranger)->get('/tree/view/' . $person->id);
        // Returns 403 Forbidden (may be rendered as error page or redirect depending on handler)
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_tree_allows_community_privacy(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $person = Person::factory()->create([
            'privacy_level' => 'community',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($other)->get('/tree/view/' . $person->id);
        $response->assertStatus(200);
    }

    public function test_tree_requires_authentication(): void
    {
        $response = $this->get('/tree');
        $response->assertRedirect('/login');
    }

    public function test_tree_node_has_correct_structure(): void
    {
        $data = $this->createUserWithTree();

        $response = $this->actingAs($data['user'])
            ->getJson('/tree/api/' . $data['root']->id . '/data');

        $root = $response->json('root');

        $this->assertArrayHasKey('id', $root);
        $this->assertArrayHasKey('name', $root);
        $this->assertArrayHasKey('firstName', $root);
        $this->assertArrayHasKey('lastName', $root);
        $this->assertArrayHasKey('gender', $root);
        $this->assertArrayHasKey('isLiving', $root);
        $this->assertArrayHasKey('hasEthnicHeritage', $root);
        $this->assertArrayHasKey('url', $root);
        $this->assertArrayHasKey('hasFather', $root);
        $this->assertArrayHasKey('hasMother', $root);
        $this->assertArrayHasKey('hasSpouse', $root);
        $this->assertArrayHasKey('siblingsCount', $root);
    }
}
