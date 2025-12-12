<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Invitation;
use App\Models\Media;
use App\Models\Message;
use App\Models\Person;
use App\Models\PersonEditPermission;
use App\Models\SurnameVariant;
use App\Models\TreeAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmallModelsTest extends TestCase
{
    use RefreshDatabase;

    // --- FamilyChild ---

    public function test_family_child_relationships(): void
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

        $fc = FamilyChild::create([
            'family_id' => $family->id,
            'person_id' => $child->id,
            'child_order' => 1,
            'relationship_type' => 'biological',
        ]);

        $this->assertEquals($family->id, $fc->family->id);
        $this->assertEquals($child->id, $fc->person->id);
        $this->assertEquals($child->id, $fc->child->id);
        $this->assertTrue($fc->isBiological());
        $this->assertFalse($fc->isAdopted());
    }

    // --- Media ---

    public function test_media_types_and_scopes(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user->id]);

        Media::create(['mediable_type' => Person::class, 'mediable_id' => $person->id, 'type' => 'image', 'title' => 'Photo', 'file_path' => 'a.jpg', 'file_name' => 'a.jpg', 'created_by' => $user->id]);
        Media::create(['mediable_type' => Person::class, 'mediable_id' => $person->id, 'type' => 'document', 'title' => 'Doc', 'file_path' => 'b.pdf', 'file_name' => 'b.pdf', 'created_by' => $user->id]);
        Media::create(['mediable_type' => Person::class, 'mediable_id' => $person->id, 'type' => 'link', 'title' => 'Link', 'external_url' => 'https://example.com', 'created_by' => $user->id]);

        $this->assertCount(1, Media::images()->get());
        $this->assertCount(1, Media::documents()->get());
        $this->assertCount(1, Media::links()->get());
    }

    public function test_media_is_type_checks(): void
    {
        $img = new Media(['type' => 'image']);
        $doc = new Media(['type' => 'document']);
        $link = new Media(['type' => 'link']);

        $this->assertTrue($img->isImage());
        $this->assertTrue($doc->isDocument());
        $this->assertTrue($link->isLink());
    }

    public function test_media_formatted_size(): void
    {
        $media = new Media(['file_size' => 1536]);
        $this->assertEquals('1.5 KB', $media->formatted_size);

        $empty = new Media(['file_size' => null]);
        $this->assertEquals('', $empty->formatted_size);
    }

    public function test_media_url_for_link(): void
    {
        $media = new Media(['type' => 'link', 'external_url' => 'https://example.com']);
        $this->assertEquals('https://example.com', $media->url);
    }

    // --- Message ---

    public function test_message_types(): void
    {
        $msg = new Message(['type' => 'system', 'sender_id' => null]);
        $this->assertTrue($msg->isSystemMessage());

        $inv = new Message(['type' => 'invitation']);
        $this->assertTrue($inv->isInvitation());

        $consent = new Message(['type' => 'consent_request']);
        $this->assertTrue($consent->isConsentRequest());

        $claim = new Message(['type' => 'person_claim']);
        $this->assertTrue($claim->isPersonClaim());

        $merge = new Message(['type' => 'person_merge']);
        $this->assertTrue($merge->isPersonMerge());
    }

    public function test_message_read_and_actions(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $msg = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'type' => 'invitation',
            'subject' => 'Test',
            'body' => 'Body',
            'action_required' => true,
            'action_status' => 'pending',
        ]);

        $this->assertFalse($msg->isRead());
        $msg->markAsRead();
        $this->assertTrue($msg->fresh()->isRead());

        $msg->accept();
        $this->assertEquals('accepted', $msg->fresh()->action_status);
    }

    public function test_message_deny(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $msg = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'type' => 'consent_request',
            'subject' => 'Deny test',
            'body' => 'Body',
            'action_required' => true,
            'action_status' => 'pending',
        ]);

        $msg->deny();
        $this->assertEquals('denied', $msg->fresh()->action_status);
    }

    public function test_message_scopes(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        Message::create(['sender_id' => $sender->id, 'recipient_id' => $recipient->id, 'type' => 'general', 'subject' => 'Unread', 'body' => 'b']);
        Message::create(['sender_id' => $sender->id, 'recipient_id' => $recipient->id, 'type' => 'invitation', 'subject' => 'Read', 'body' => 'b', 'read_at' => now()]);

        $this->assertCount(1, Message::unread()->get());
        $this->assertCount(2, Message::notDeleted()->get());
        $this->assertCount(1, Message::ofType('invitation')->get());
    }

    // --- Invitation ---

    public function test_invitation_auto_token_and_expiry(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user->id]);

        $invitation = Invitation::create([
            'inviter_id' => $user->id,
            'person_id' => $person->id,
            'email' => 'invited@test.com',
            'status' => 'pending',
        ]);

        $this->assertNotNull($invitation->token);
        $this->assertEquals(64, strlen($invitation->token));
        $this->assertNotNull($invitation->expires_at);
        $this->assertTrue($invitation->expires_at->isFuture());
    }

    public function test_invitation_status_checks(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user->id]);

        $invitation = Invitation::create([
            'inviter_id' => $user->id,
            'person_id' => $person->id,
            'email' => 'test@test.com',
            'status' => 'pending',
        ]);

        $this->assertTrue($invitation->isPending());
        $this->assertFalse($invitation->isExpired());

        $invitation->accept();
        $this->assertTrue($invitation->fresh()->isAccepted());
    }

    public function test_invitation_decline(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user->id]);

        $invitation = Invitation::create([
            'inviter_id' => $user->id,
            'person_id' => $person->id,
            'email' => 'test@test.com',
            'status' => 'pending',
        ]);

        $invitation->decline();
        $this->assertTrue($invitation->fresh()->isDeclined());
    }

    public function test_invitation_find_by_token(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user->id]);

        $invitation = Invitation::create([
            'inviter_id' => $user->id,
            'person_id' => $person->id,
            'email' => 'test@test.com',
            'status' => 'pending',
        ]);

        $found = Invitation::findByToken($invitation->token);
        $this->assertNotNull($found);
        $this->assertEquals($invitation->id, $found->id);

        $this->assertNull(Invitation::findByToken('nonexistent'));
    }

    // --- Event ---

    public function test_event_type_label(): void
    {
        $event = new Event(['type' => 'BIRT']);
        $this->assertEquals('Nacimiento', $event->type_label);

        $event2 = new Event(['type' => 'MARR']);
        $this->assertEquals('Matrimonio', $event2->type_label);

        $unknown = new Event(['type' => 'CUSTOM']);
        $this->assertEquals('CUSTOM', $unknown->type_label);
    }

    public function test_event_is_person_or_family(): void
    {
        $person = Person::factory()->create();
        $personEvent = Event::create([
            'person_id' => $person->id,
            'type' => 'BIRT',
        ]);

        $this->assertTrue($personEvent->isPersonEvent());
        $this->assertFalse($personEvent->isFamilyEvent());
    }

    public function test_event_scopes(): void
    {
        $person = Person::factory()->create();

        Event::create(['person_id' => $person->id, 'type' => 'BIRT']);
        Event::create(['person_id' => $person->id, 'type' => 'DEAT']);

        $this->assertCount(1, Event::ofType('BIRT')->get());
        $this->assertCount(2, Event::forPerson($person->id)->get());
    }

    // --- ActivityLog ---

    public function test_activity_log_static(): void
    {
        $user = User::factory()->create();
        $log = ActivityLog::log('login', $user);

        $this->assertEquals('login', $log->action);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('Inicio de sesion', $log->action_label);
    }

    public function test_activity_log_scopes(): void
    {
        $user = User::factory()->create();
        ActivityLog::log('login', $user);
        ActivityLog::log('logout', $user);

        $this->assertCount(2, ActivityLog::forUser($user->id)->get());
        $this->assertCount(1, ActivityLog::ofAction('login')->get());
        $this->assertCount(2, ActivityLog::recent(7)->get());
    }

    // --- SurnameVariant ---

    public function test_surname_variant_all_variants(): void
    {
        $person = Person::factory()->create();
        $sv = SurnameVariant::create([
            'person_id' => $person->id,
            'original_surname' => 'Horvat',
            'variant_1' => 'Horvath',
            'variant_2' => null,
        ]);

        $this->assertEquals(['Horvat', 'Horvath'], $sv->allVariants);
    }

    public function test_surname_variant_matches(): void
    {
        $person = Person::factory()->create();
        $sv = SurnameVariant::create([
            'person_id' => $person->id,
            'original_surname' => 'Horvat',
            'variant_1' => 'Horvath',
        ]);

        $this->assertTrue($sv->matches('horvat'));
        $this->assertTrue($sv->matches('HORVATH'));
        $this->assertFalse($sv->matches('Garcia'));
    }

    public function test_surname_variant_search_scope(): void
    {
        $person = Person::factory()->create();
        SurnameVariant::create([
            'person_id' => $person->id,
            'original_surname' => 'Horvat',
            'variant_1' => 'Horvath',
        ]);

        $this->assertCount(1, SurnameVariant::searchVariant('Horvat')->get());
        $this->assertCount(1, SurnameVariant::searchVariant('Horvath')->get());
        $this->assertCount(0, SurnameVariant::searchVariant('Garcia')->get());
    }

    // --- PersonEditPermission ---

    public function test_edit_permission_active_and_expired(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $person = Person::factory()->create(['created_by' => $user1->id]);

        $active = PersonEditPermission::create([
            'person_id' => $person->id,
            'user_id' => $user1->id,
            'granted_by' => $user1->id,
            'granted_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $expired = PersonEditPermission::create([
            'person_id' => $person->id,
            'user_id' => $user2->id,
            'granted_by' => $user1->id,
            'granted_at' => now()->subDays(60),
            'expires_at' => now()->subDays(1),
        ]);

        $this->assertTrue($active->isActive());
        $this->assertFalse($active->isExpired());
        $this->assertFalse($expired->isActive());
        $this->assertTrue($expired->isExpired());

        $this->assertCount(1, PersonEditPermission::active()->get());
    }

    // --- TreeAccess ---

    public function test_tree_access_levels(): void
    {
        $owner = User::factory()->create();
        $accessor1 = User::factory()->create();
        $accessor2 = User::factory()->create();

        $basic = TreeAccess::create([
            'owner_id' => $owner->id,
            'accessor_id' => $accessor1->id,
            'access_level' => 'view_basic',
            'include_documents' => false,
        ]);

        $this->assertTrue($basic->hasViewBasicAccess());
        $this->assertFalse($basic->hasViewFullAccess());
        $this->assertFalse($basic->hasEditAccess());
        $this->assertFalse($basic->canViewDocuments());

        $edit = TreeAccess::create([
            'owner_id' => $owner->id,
            'accessor_id' => $accessor2->id,
            'access_level' => 'edit',
            'include_documents' => true,
        ]);

        $this->assertTrue($edit->hasViewBasicAccess());
        $this->assertTrue($edit->hasViewFullAccess());
        $this->assertTrue($edit->hasEditAccess());
        $this->assertTrue($edit->canViewDocuments());
    }

    public function test_tree_access_active_scope(): void
    {
        $owner = User::factory()->create();
        $accessor1 = User::factory()->create();
        $accessor2 = User::factory()->create();

        TreeAccess::create([
            'owner_id' => $owner->id,
            'accessor_id' => $accessor1->id,
            'access_level' => 'view_basic',
            'expires_at' => now()->addDays(30),
        ]);

        TreeAccess::create([
            'owner_id' => $owner->id,
            'accessor_id' => $accessor2->id,
            'access_level' => 'view_full',
            'expires_at' => now()->subDays(1),
        ]);

        $this->assertCount(1, TreeAccess::active()->get());
    }
}
