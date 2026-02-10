<?php

namespace Plugin\SocialLogin;

use App\Plugins\Models\Plugin;
use App\Plugins\PluginServiceProvider;
use Illuminate\Support\Facades\Schema;

class SocialLoginPlugin extends PluginServiceProvider
{
    protected array $providers = ['google', 'microsoft', 'facebook'];

    public function register(): void
    {
        $this->configureSocialiteProviders();
    }

    public function hooks(): array
    {
        return [
            'auth.login.social' => 'social-login::hooks.login-buttons',
        ];
    }

    public function getDefaultSettings(): array
    {
        return [
            'google_enabled' => false,
            'google_client_id' => '',
            'google_client_secret' => '',
            'microsoft_enabled' => false,
            'microsoft_client_id' => '',
            'microsoft_client_secret' => '',
            'microsoft_tenant' => 'common',
            'facebook_enabled' => false,
            'facebook_client_id' => '',
            'facebook_client_secret' => '',
            'auto_create_users' => true,
            'auto_verify_email' => true,
            'link_existing_accounts' => true,
        ];
    }

    protected function configureSocialiteProviders(): void
    {
        if (!Schema::hasTable('plugins')) {
            return;
        }

        $plugin = Plugin::where('slug', 'social-login')
            ->where('status', 'enabled')
            ->first();

        if (!$plugin) {
            return;
        }

        $settings = $plugin->settings ?? [];

        // Google
        if (!empty($settings['google_enabled']) && !empty($settings['google_client_id'])) {
            config([
                'services.google.client_id' => $settings['google_client_id'],
                'services.google.client_secret' => $this->decryptSetting($settings['google_client_secret'] ?? ''),
                'services.google.redirect' => url('/auth/social/google/callback'),
            ]);
        }

        // Microsoft
        if (!empty($settings['microsoft_enabled']) && !empty($settings['microsoft_client_id'])) {
            config([
                'services.microsoft.client_id' => $settings['microsoft_client_id'],
                'services.microsoft.client_secret' => $this->decryptSetting($settings['microsoft_client_secret'] ?? ''),
                'services.microsoft.redirect' => url('/auth/social/microsoft/callback'),
                'services.microsoft.tenant' => $settings['microsoft_tenant'] ?? 'common',
            ]);
        }

        // Facebook
        if (!empty($settings['facebook_enabled']) && !empty($settings['facebook_client_id'])) {
            config([
                'services.facebook.client_id' => $settings['facebook_client_id'],
                'services.facebook.client_secret' => $this->decryptSetting($settings['facebook_client_secret'] ?? ''),
                'services.facebook.redirect' => url('/auth/social/facebook/callback'),
            ]);
        }
    }

    protected function decryptSetting(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
