<?php

namespace Plugin\PhotoBanner\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Models\Plugin;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $plugin = Plugin::where('slug', 'photo-banner')->firstOrFail();
        $settings = $plugin->settings ?? [];

        return view('photo-banner::settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'banner_height' => 'required|integer|min:60|max:200',
            'scroll_speed' => 'required|integer|min:10|max:300',
            'max_images' => 'required|integer|min:10|max:200',
            'image_gap' => 'required|integer|min:0|max:16',
            'min_real_photos' => 'required|integer|min:5|max:30|lte:max_images',
        ]);

        $plugin = Plugin::where('slug', 'photo-banner')->firstOrFail();

        $settings = $plugin->settings ?? [];
        // Incrementar versión del cache para invalidar todas las caches de usuario
        $settings['cache_version'] = ($settings['cache_version'] ?? 0) + 1;
        $settings = array_merge($settings, $validated);

        $plugin->settings = $settings;
        $plugin->save();

        return redirect()->route('admin.photo-banner.settings')
            ->with('success', __('Configuracion del cintillo de fotos actualizada'));
    }
}
