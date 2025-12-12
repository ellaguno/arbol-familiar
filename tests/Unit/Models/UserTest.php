<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\Family;
use App\Models\Media;
use App\Models\Message;
use App\Models\Person;
use App\Models\PersonEditPermission;
use App\Models\TreeAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPerson(): array
    {
        $user = User::factory()->create();
        $person = Person::factory()->create([
            'created_by' => $user->id,
            'user_id' => $user->id,
        ]);
        $user->update(['person_id' => $person->id]);
        return [$user->fresh(), $person];
    }

    public function test_fillable_attributes(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'language' => 'hr',
            'privacy_level' => 'extended_family',
        ]);

        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('hr', $user->language);
        $this->assertEquals('extended_family', $user->privacy_level);
    }

    public function test_hidden_attributes(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
        $this->assertArrayNotHasKey('confirmation_code', $array);
    }

    public function test_casts(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'first_login_completed' => 1,
        ]);

        $this->assertIsBool($user->is_admin);
        $this->assertIsBool($user->first_login_completed);
    }

    public function test_person_relationship(): void
    {
        [$user, $person] = $this->createUserWithPerson();

        $this->assertInstanceOf(Person::class, $user->person);
        $this->assertEquals($person->id, $user->person->id);
    }

    public function test_created_persons_relationship(): void
    {
        $user = User::factory()->create();
        Person::factory()->count(3)->create(['created_by' => $user->id]);

        $this->assertCount(3, $user->createdPersons);
    }

    public function test_created_families_relationship(): void
    {
        $user = User::factory()->create();
        $p1 = Person::factory()->create(['created_by' => $user->id]);
        $p2 = Person::factory()->create(['created_by' => $user->id]);
        Family::factory()->create([
            'husband_id' => $p1->id,
            'wife_id' => $p2->id,
            'created_by' => $user->id,
        ]);

        $this->assertCount(1, $user->createdFamilies);
    }

    public function test_sent_and_received_messages(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'type' => 'general',
            'subject' => 'Test',
            'body' => 'Hello',
        ]);

        $this->assertCount(1, $sender->sentMessages);
        $this->assertCount(1, $recipient->receivedMessages);
    }

    public function test_unread_messages(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();

        Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $user->id,
            'type' => 'general',
            'subject' => 'Unread',
            'body' => 'Body',
        ]);
        Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $user->id,
            'type' => 'general',
            'subject' => 'Read',
            'body' => 'Body',
            'read_at' => now(),
        ]);

        $this->assertCount(1, $user->unreadMessages);
    }

    public function test_is_locked(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isLocked());

        $user->locked_until = now()->addMinutes(15);
        $user->save();
        $this->assertTrue($user->isLocked());

        $user->locked_until = now()->subMinutes(1);
        $user->save();
        $this->assertFalse($user->isLocked());
    }

    public function test_increment_login_attempts_locks_after_five(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $user->incrementLoginAttempts();
        }

        $user->refresh();
        $this->assertTrue($user->isLocked());
        $this->assertEquals(5, $user->login_attempts);
    }

    public function test_reset_login_attempts(): void
    {
        $user = User::factory()->create();
        $user->login_attempts = 3;
        $user->locked_until = now()->addMinutes(15);
        $user->save();

        $user->resetLoginAttempts();

        $this->assertEquals(0, $user->login_attempts);
        $this->assertNull($user->locked_until);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_full_name_attribute_with_person(): void
    {
        [$user, $person] = $this->createUserWithPerson();

        $this->assertEquals($person->full_name, $user->full_name);
    }

    public function test_full_name_attribute_without_person(): void
    {
        $user = User::factory()->create(['email' => 'fallback@test.com']);

        $this->assertEquals('fallback@test.com', $user->full_name);
    }

    public function test_is_admin(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->isAdmin());

        $user->setAdmin(true);
        $this->assertTrue($user->isAdmin());
    }

    public function test_set_admin_not_mass_assignable(): void
    {
        $user = User::factory()->create();
        $user->fill(['is_admin' => true]);
        $user->save();

        $user->refresh();
        $this->assertFalse($user->isAdmin());
    }

    public function test_editable_person_ids(): void
    {
        [$user, $person] = $this->createUserWithPerson();
        $extraPerson = Person::factory()->create(['created_by' => $user->id]);

        $ids = $user->editablePersonIds;

        $this->assertContains($person->id, $ids);
        $this->assertContains($extraPerson->id, $ids);
    }

    public function test_granted_and_received_access(): void
    {
        $owner = User::factory()->create();
        $accessor = User::factory()->create();

        TreeAccess::create([
            'owner_id' => $owner->id,
            'accessor_id' => $accessor->id,
            'access_level' => 'view_basic',
        ]);

        $this->assertCount(1, $owner->grantedAccess);
        $this->assertCount(1, $accessor->receivedAccess);
    }

    public function test_activity_log_relationship(): void
    {
        $user = User::factory()->create();
        ActivityLog::log('login', $user);

        $this->assertCount(1, $user->fresh()->activityLog);
    }
}
