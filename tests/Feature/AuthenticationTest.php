<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $person = Person::factory()->create([
            'user_id' => $user->id,
            'created_by' => $user->id,
            'email' => $user->email,
        ]);
        $user->update(['person_id' => $person->id]);
        return $user->fresh();
    }

    // --- Login ---

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = $this->createVerifiedUser();
        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect();
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = $this->createVerifiedUser([
            'password' => Hash::make('Password1!'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1!',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_wrong_password(): void
    {
        $user = $this->createVerifiedUser([
            'password' => Hash::make('Password1!'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'WrongPassword1!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_login_with_nonexistent_email(): void
    {
        $response = $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password1!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_locks_after_five_failed_attempts(): void
    {
        $user = $this->createVerifiedUser([
            'password' => Hash::make('Password1!'),
        ]);

        // Simulate 5 failed attempts directly on the model
        for ($i = 0; $i < 5; $i++) {
            $user->incrementLoginAttempts();
        }

        $user->refresh();
        $this->assertTrue($user->isLocked());

        // Attempt login with correct password while locked - should be rejected
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_login_redirects_unverified_to_verification(): void
    {
        $user = $this->createVerifiedUser([
            'password' => Hash::make('Password1!'),
            'email_verified_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1!',
        ]);

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_login_redirects_first_login_to_welcome(): void
    {
        $user = $this->createVerifiedUser([
            'password' => Hash::make('Password1!'),
            'first_login_completed' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1!',
        ]);

        $response->assertRedirect(route('welcome.first'));
    }

    // --- Logout ---

    public function test_logout(): void
    {
        $user = $this->createVerifiedUser();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // --- Registration ---

    public function test_register_page_is_accessible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_register_creates_user_and_person(): void
    {
        $response = $this->post('/register', [
            'email' => 'newuser@example.com',
            'email_confirmation' => 'newuser@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'first_name' => 'Ivan',
            'patronymic' => 'Horvat',
            'gender' => 'M',
            'has_ethnic_heritage' => 1,
            'ancestor_first_name' => 'Ante',
            'ancestor_patronymic' => 'Horvat',
            'heritage_region' => 'dalmatia',
            'privacy_accepted' => true,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $this->assertDatabaseHas('persons', ['first_name' => 'Ivan', 'patronymic' => 'Horvat']);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user->person_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_validates_password_complexity(): void
    {
        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'email_confirmation' => 'test@example.com',
            'password' => 'simple',
            'password_confirmation' => 'simple',
            'first_name' => 'Test',
            'patronymic' => 'User',
            'gender' => 'M',
            'has_ethnic_heritage' => 0,
            'privacy_accepted' => true,
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_register_validates_email_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'email' => 'taken@example.com',
            'email_confirmation' => 'taken@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'first_name' => 'Test',
            'patronymic' => 'User',
            'gender' => 'M',
            'has_ethnic_heritage' => 0,
            'privacy_accepted' => true,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_register_validates_email_confirmation(): void
    {
        $response = $this->post('/register', [
            'email' => 'new@example.com',
            'email_confirmation' => 'different@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'first_name' => 'Test',
            'patronymic' => 'User',
            'gender' => 'M',
            'has_ethnic_heritage' => 0,
            'privacy_accepted' => true,
        ]);

        $response->assertSessionHasErrors('email_confirmation');
    }

    public function test_register_requires_privacy_accepted(): void
    {
        $response = $this->post('/register', [
            'email' => 'new@example.com',
            'email_confirmation' => 'new@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'first_name' => 'Test',
            'patronymic' => 'User',
            'gender' => 'M',
            'has_ethnic_heritage' => 0,
        ]);

        $response->assertSessionHasErrors('privacy_accepted');
    }

    public function test_register_requires_ancestor_when_heritage(): void
    {
        $response = $this->post('/register', [
            'email' => 'new@example.com',
            'email_confirmation' => 'new@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'first_name' => 'Test',
            'patronymic' => 'User',
            'gender' => 'M',
            'has_ethnic_heritage' => 1,
            'privacy_accepted' => true,
            // Missing ancestor fields
        ]);

        $response->assertSessionHasErrors(['ancestor_first_name', 'ancestor_patronymic', 'heritage_region']);
    }

    // --- Protected routes ---

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_accessible_when_authenticated(): void
    {
        $user = $this->createVerifiedUser();
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_profile_requires_authentication(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }
}
