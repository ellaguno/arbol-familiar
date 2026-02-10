<?php

namespace Plugin\SocialLogin\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Models\Plugin;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $plugin = Plugin::where('slug', 'social-login')->first();
        $settings = $plugin?->settings ?? [];

        return view('social-login::settings', [
            'settings' => $settings,
            'callbackUrls' => [
                'google' => url('/auth/social/google/callback'),
                'microsoft' => url('/auth/social/microsoft/callback'),
                'facebook' => url('/auth/social/facebook/callback'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'google_enabled' => 'boolean',
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'microsoft_enabled' => 'boolean',
            'microsoft_client_id' => 'nullable|string|max:255',
            'microsoft_client_secret' => 'nullable|string|max:255',
            'microsoft_tenant' => 'nullable|string|max:255',
            'facebook_enabled' => 'boolean',
            'facebook_client_id' => 'nullable|string|max:255',
            'facebook_client_secret' => 'nullable|string|max:255',
            'auto_create_users' => 'boolean',
            'auto_verify_email' => 'boolean',
            'link_existing_accounts' => 'boolean',
        ]);

        $plugin = Plugin::where('slug', 'social-login')->first();

        if (!$plugin) {
            return back()->withErrors(['error' => __('Plugin no encontrado.')]);
        }

        $settings = $plugin->settings ?? [];

        // Update toggles
        $settings['google_enabled'] = $request->boolean('google_enabled');
        $settings['microsoft_enabled'] = $request->boolean('microsoft_enabled');
        $settings['facebook_enabled'] = $request->boolean('facebook_enabled');
        $settings['auto_create_users'] = $request->boolean('auto_create_users');
        $settings['auto_verify_email'] = $request->boolean('auto_verify_email');
        $settings['link_existing_accounts'] = $request->boolean('link_existing_accounts');

        // Update client IDs (plain text)
        foreach (['google', 'microsoft', 'facebook'] as $provider) {
            $clientId = $request->input("{$provider}_client_id");
            if ($clientId !== null) {
                $settings["{$provider}_client_id"] = $clientId;
            }

            // Encrypt client secrets (keep existing if empty)
            $clientSecret = $request->input("{$provider}_client_secret");
            if (!empty($clientSecret)) {
                $settings["{$provider}_client_secret"] = encrypt($clientSecret);
            }
        }

        // Microsoft tenant
        $tenant = $request->input('microsoft_tenant');
        if ($tenant !== null) {
            $settings['microsoft_tenant'] = $tenant ?: 'common';
        }

        $plugin->settings = $settings;
        $plugin->save();

        return back()->with('success', __('Configuracion guardada correctamente.'));
    }
}
