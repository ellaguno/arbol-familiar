<?php

namespace Plugin\ResearchAssistant\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Models\Plugin;
use Illuminate\Http\Request;
use Plugin\ResearchAssistant\Services\AIService;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the settings page.
     */
    public function index()
    {
        $plugin = Plugin::where('slug', 'research-assistant')->first();
        $settings = $plugin?->settings ?? [];

        $aiService = new AIService($settings);
        $providers = $aiService->getAvailableProviders();

        return view('research-assistant::settings', [
            'settings' => $settings,
            'providers' => $providers,
        ]);
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $rules = [
            'ai_provider' => 'required|string|in:openrouter,deepseek,openai,anthropic',
            'ai_model' => 'required|string|max:100',
            'openrouter_api_key' => 'nullable|string|max:255',
            'deepseek_api_key' => 'nullable|string|max:255',
            'openai_api_key' => 'nullable|string|max:255',
            'anthropic_api_key' => 'nullable|string|max:255',
            'familysearch_enabled' => 'boolean',
            'wikipedia_enabled' => 'boolean',
            'max_results_per_source' => 'integer|min:1|max:50',
        ];

        // Add custom_model validation if custom model is selected
        if ($request->ai_model === '_custom_') {
            $rules['custom_model'] = 'required|string|max:100';
        }

        $request->validate($rules);

        // Determine the actual model to use
        $aiModel = $request->ai_model === '_custom_'
            ? $request->custom_model
            : $request->ai_model;

        $plugin = Plugin::where('slug', 'research-assistant')->first();

        if (!$plugin) {
            return back()->withErrors(['error' => __('Plugin no encontrado.')]);
        }

        $settings = $plugin->settings ?? [];

        // Update basic settings
        $settings['ai_provider'] = $request->ai_provider;
        $settings['ai_model'] = $aiModel;
        $settings['familysearch_enabled'] = $request->boolean('familysearch_enabled');
        $settings['wikipedia_enabled'] = $request->boolean('wikipedia_enabled');
        $settings['max_results_per_source'] = $request->integer('max_results_per_source', 10);

        // Update API keys (encrypt if provided, keep existing if empty)
        foreach (['openrouter', 'deepseek', 'openai', 'anthropic'] as $provider) {
            $key = "{$provider}_api_key";
            $value = $request->input($key);

            if (!empty($value)) {
                $settings[$key] = encrypt($value);
            }
            // If empty and not explicitly clearing, keep existing value
        }

        $plugin->settings = $settings;
        $plugin->save();

        return back()->with('success', __('Configuracion guardada correctamente.'));
    }

    /**
     * Test an AI provider connection.
     */
    public function testProvider(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:openrouter,deepseek,openai,anthropic',
            'api_key' => 'required|string',
            'model' => 'required|string',
        ]);

        try {
            $aiService = new AIService([
                "{$request->provider}_api_key" => $request->api_key,
            ]);

            $provider = $aiService->getProvider($request->provider);
            $provider->setApiKey($request->api_key);

            $result = $provider->analyze('Di "Conexion exitosa" en espanol.', [
                'model' => $request->model,
                'max_tokens' => 50,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Conexion exitosa'),
                'response' => $result['content'],
                'tokens' => $result['tokens'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
