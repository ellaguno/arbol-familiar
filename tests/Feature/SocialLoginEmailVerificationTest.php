<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Plugin\SocialLogin\Services\SocialAuthService;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Verifica que el enlace/auto-verificacion de social login solo confie en el
 * email cuando el proveedor lo da por verificado (previene account takeover via
 * email no verificado, p. ej. Google con email_verified=false).
 */
class SocialLoginEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verify(string $provider, array $map, array $raw): bool
    {
        $socialite = (new SocialiteUser())->setRaw($raw)->map($map);
        $method = new ReflectionMethod(SocialAuthService::class, 'providerEmailIsVerified');
        $method->setAccessible(true);

        return $method->invoke(new SocialAuthService(), $provider, $socialite);
    }

    public function test_google_unverified_email_is_rejected(): void
    {
        $this->assertFalse($this->verify('google',
            ['id' => 'g1', 'email' => 'victima@example.com', 'name' => 'V'],
            ['email_verified' => false]
        ));
    }

    public function test_google_missing_flag_is_rejected(): void
    {
        $this->assertFalse($this->verify('google',
            ['id' => 'g2', 'email' => 'victima@example.com', 'name' => 'V'],
            [] // sin flag -> conservador
        ));
    }

    public function test_google_verified_email_is_accepted(): void
    {
        $this->assertTrue($this->verify('google',
            ['id' => 'g3', 'email' => 'real@example.com', 'name' => 'R'],
            ['email_verified' => true]
        ));
    }

    public function test_facebook_and_microsoft_emails_are_trusted(): void
    {
        $this->assertTrue($this->verify('facebook',
            ['id' => 'f1', 'email' => 'user@example.com', 'name' => 'U'], []));
        $this->assertTrue($this->verify('microsoft',
            ['id' => 'm1', 'email' => 'user@example.com', 'name' => 'U'], []));
    }

    public function test_missing_email_is_rejected(): void
    {
        $this->assertFalse($this->verify('google',
            ['id' => 'g4', 'email' => null, 'name' => 'X'],
            ['email_verified' => true]
        ));
    }
}
