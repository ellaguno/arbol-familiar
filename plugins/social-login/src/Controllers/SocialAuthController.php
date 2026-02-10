<?php

namespace Plugin\SocialLogin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Plugin\SocialLogin\Services\SocialAuthService;
use SocialiteProviders\Manager\OAuth2\User as OAuth2User;

class SocialAuthController extends Controller
{
    protected array $validProviders = ['google', 'microsoft', 'facebook'];

    public function redirectToProvider(string $provider)
    {
        if (!in_array($provider, $this->validProviders)) {
            abort(404);
        }

        $service = new SocialAuthService();

        if (!$service->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', __('Este proveedor de inicio de sesion no esta habilitado.'));
        }

        return Socialite::driver($provider)
            ->scopes($this->getScopes($provider))
            ->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        if (!in_array($provider, $this->validProviders)) {
            abort(404);
        }

        $service = new SocialAuthService();

        if (!$service->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->with('error', __('Este proveedor de inicio de sesion no esta habilitado.'));
        }

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            ActivityLog::log('oauth_failed', null, null, [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')
                ->with('error', __('No se pudo completar el inicio de sesion con') . ' ' . ucfirst($provider) . '.');
        }

        try {
            $user = $service->handleCallback($provider, $socialiteUser);
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', $e->getMessage());
        }

        Auth::login($user, true);
        $user->resetLoginAttempts();
        $user->update(['last_login_at' => now()]);

        session()->regenerate();

        if (!$user->email_verified_at) {
            return redirect()->route('verification.notice');
        }

        if (!$user->first_login_completed) {
            return redirect()->route('welcome.first');
        }

        return redirect()->intended(route('dashboard'));
    }

    protected function getScopes(string $provider): array
    {
        return match ($provider) {
            'google' => ['openid', 'profile', 'email'],
            'microsoft' => ['User.Read'],
            'facebook' => ['email', 'public_profile'],
            default => [],
        };
    }
}
