<?php

namespace Plugin\SocialLogin\Services;

use App\Models\ActivityLog;
use App\Models\Person;
use App\Models\User;
use App\Plugins\Models\Plugin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Plugin\SocialLogin\Models\SocialAccount;

class SocialAuthService
{
    protected array $settings;

    public function __construct()
    {
        $plugin = Plugin::where('slug', 'social-login')->first();
        $this->settings = $plugin?->settings ?? [];
    }

    public function isProviderEnabled(string $provider): bool
    {
        return !empty($this->settings["{$provider}_enabled"])
            && !empty($this->settings["{$provider}_client_id"]);
    }

    public function getEnabledProviders(): array
    {
        $enabled = [];
        foreach (['google', 'microsoft', 'facebook'] as $provider) {
            if ($this->isProviderEnabled($provider)) {
                $enabled[] = $provider;
            }
        }
        return $enabled;
    }

    public function handleCallback(string $provider, SocialiteUser $socialiteUser): User
    {
        // 1. Check if social account already exists
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialiteUser->getId())
            ->first();

        if ($socialAccount) {
            $this->updateSocialAccount($socialAccount, $socialiteUser);

            ActivityLog::log('oauth_login', $socialAccount->user, null, [
                'provider' => $provider,
            ]);

            return $socialAccount->user;
        }

        // 2. Check if email matches existing user
        $email = $socialiteUser->getEmail();

        if ($email && ($this->settings['link_existing_accounts'] ?? true)) {
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                $this->linkAccountToUser($existingUser, $provider, $socialiteUser);

                ActivityLog::log('oauth_login', $existingUser, null, [
                    'provider' => $provider,
                    'linked' => true,
                ]);

                return $existingUser;
            }
        }

        // 3. Create new user if allowed
        if (!($this->settings['auto_create_users'] ?? true)) {
            throw new \Exception(__('No se permite el registro automatico con este proveedor.'));
        }

        if (!$email) {
            throw new \Exception(__('El proveedor OAuth no proporciono un email.'));
        }

        return $this->createUserFromSocial($provider, $socialiteUser);
    }

    public function linkAccountToUser(User $user, string $provider, SocialiteUser $socialiteUser): SocialAccount
    {
        return SocialAccount::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialiteUser->getId(),
            'provider_email' => $socialiteUser->getEmail(),
            'provider_name' => $socialiteUser->getName(),
            'avatar_url' => $socialiteUser->getAvatar(),
            'access_token' => $socialiteUser->token ? encrypt($socialiteUser->token) : null,
            'refresh_token' => ($socialiteUser->refreshToken ?? null) ? encrypt($socialiteUser->refreshToken) : null,
            'token_expires_at' => isset($socialiteUser->expiresIn)
                ? now()->addSeconds($socialiteUser->expiresIn)
                : null,
        ]);
    }

    protected function createUserFromSocial(string $provider, SocialiteUser $socialiteUser): User
    {
        return DB::transaction(function () use ($provider, $socialiteUser) {
            $name = $socialiteUser->getName() ?? '';
            [$firstName, $patronymic] = $this->splitName($name);

            // Create user
            $user = User::create([
                'email' => $socialiteUser->getEmail(),
                'password' => Hash::make(Str::random(32)),
                'language' => app()->getLocale(),
                'privacy_level' => 'extended_family',
                'email_verified_at' => ($this->settings['auto_verify_email'] ?? true) ? now() : null,
                'first_login_completed' => false,
            ]);

            // Create person record
            $person = Person::create([
                'first_name' => $firstName,
                'patronymic' => $patronymic,
                'privacy_level' => 'extended_family',
                'is_living' => true,
                'created_by' => $user->id,
                'user_id' => $user->id,
            ]);

            $user->update(['person_id' => $person->id]);

            // Link social account
            $this->linkAccountToUser($user, $provider, $socialiteUser);

            ActivityLog::log('oauth_register', $user, null, [
                'provider' => $provider,
            ]);

            return $user->fresh();
        });
    }

    protected function updateSocialAccount(SocialAccount $account, SocialiteUser $socialiteUser): void
    {
        $data = [
            'provider_email' => $socialiteUser->getEmail(),
            'provider_name' => $socialiteUser->getName(),
            'avatar_url' => $socialiteUser->getAvatar(),
        ];

        if ($socialiteUser->token) {
            $data['access_token'] = encrypt($socialiteUser->token);
        }

        if ($socialiteUser->refreshToken ?? null) {
            $data['refresh_token'] = encrypt($socialiteUser->refreshToken);
        }

        if (isset($socialiteUser->expiresIn)) {
            $data['token_expires_at'] = now()->addSeconds($socialiteUser->expiresIn);
        }

        $account->update($data);
    }

    protected function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);
        $firstName = $parts[0] ?? '';
        $patronymic = $parts[1] ?? '';

        return [$firstName, $patronymic];
    }
}
