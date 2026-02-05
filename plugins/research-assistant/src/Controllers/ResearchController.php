<?php

namespace Plugin\ResearchAssistant\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Plugin\ResearchAssistant\Jobs\ProcessResearchJob;
use Plugin\ResearchAssistant\Models\ResearchSession;
use Plugin\ResearchAssistant\Services\AIService;
use Plugin\ResearchAssistant\Sources\FamilySearchSource;
use Plugin\ResearchAssistant\Sources\WikipediaSource;

class ResearchController extends Controller
{
    protected array $pluginSettings = [];

    public function __construct()
    {
        $this->middleware('auth');
        $this->loadPluginSettings();
    }

    /**
     * Load plugin settings from database.
     */
    protected function loadPluginSettings(): void
    {
        $plugin = \App\Plugins\Models\Plugin::where('name', 'research-assistant')->first();
        $this->pluginSettings = $plugin?->settings ?? [];
    }

    /**
     * Show the research index page.
     */
    public function index()
    {
        $recentSessions = ResearchSession::forUser(Auth::id())
            ->with('person')
            ->recent(10)
            ->get();

        $aiService = new AIService($this->pluginSettings);
        $providers = $aiService->getAvailableProviders();

        return view('research-assistant::index', [
            'recentSessions' => $recentSessions,
            'providers' => $providers,
            'sources' => $this->getAvailableSources(),
            'defaultProvider' => $this->pluginSettings['ai_provider'] ?? 'openrouter',
            'defaultModel' => $this->pluginSettings['ai_model'] ?? 'anthropic/claude-3.5-sonnet',
        ]);
    }

    /**
     * Show search form for a specific person.
     */
    public function searchPerson(Person $person)
    {
        $this->authorize('view', $person);

        $aiService = new AIService($this->pluginSettings);
        $providers = $aiService->getAvailableProviders();

        return view('research-assistant::search', [
            'person' => $person,
            'providers' => $providers,
            'sources' => $this->getAvailableSources(),
            'defaultProvider' => $this->pluginSettings['ai_provider'] ?? 'openrouter',
            'defaultModel' => $this->pluginSettings['ai_model'] ?? 'anthropic/claude-3.5-sonnet',
        ]);
    }

    /**
     * Process a research request.
     */
    public function search(Request $request)
    {
        $request->validate([
            'person_id' => 'nullable|exists:persons,id',
            'query' => 'required|string|max:1000',
            'sources' => 'required|array|min:1',
            'sources.*' => 'string|in:familysearch,wikipedia',
            'ai_provider' => 'required|string|in:openrouter,deepseek,openai,anthropic',
            'ai_model' => 'required|string|max:100',
        ]);

        // Verify person access if provided
        if ($request->person_id) {
            $person = Person::findOrFail($request->person_id);
            $this->authorize('view', $person);
        }

        // Check if AI provider is configured
        $aiService = new AIService($this->pluginSettings);
        $provider = $aiService->getProvider($request->ai_provider);

        if (!$provider || !$provider->isConfigured()) {
            return back()->withErrors([
                'ai_provider' => __('El proveedor de IA seleccionado no esta configurado. Por favor contacta al administrador.'),
            ])->withInput();
        }

        // Create research session
        $session = ResearchSession::create([
            'user_id' => Auth::id(),
            'person_id' => $request->person_id,
            'query' => $request->query,
            'ai_provider' => $request->ai_provider,
            'ai_model' => $request->ai_model,
            'status' => ResearchSession::STATUS_PENDING,
        ]);

        // Dispatch the job
        ProcessResearchJob::dispatch($session, $request->sources, $this->pluginSettings);

        return redirect()->route('research.session', $session)
            ->with('success', __('Investigacion iniciada. Los resultados apareceran en breve.'));
    }

    /**
     * Show a research session.
     */
    public function session(ResearchSession $session)
    {
        $this->authorizeSession($session);

        return view('research-assistant::session', [
            'session' => $session->load('person'),
        ]);
    }

    /**
     * Get session status (for AJAX polling).
     */
    public function status(ResearchSession $session)
    {
        $this->authorizeSession($session);

        return response()->json([
            'status' => $session->status,
            'status_label' => $session->status_label,
            'search_results' => $session->search_results,
            'ai_analysis' => $session->ai_analysis,
            'suggestions' => $session->suggestions,
            'tokens_used' => $session->tokens_used,
        ]);
    }

    /**
     * Delete a research session.
     */
    public function destroy(ResearchSession $session)
    {
        $this->authorizeSession($session);

        $session->delete();

        return redirect()->route('research.index')
            ->with('success', __('Sesion de investigacion eliminada.'));
    }

    /**
     * Authorize access to a session.
     */
    protected function authorizeSession(ResearchSession $session): void
    {
        if ($session->user_id !== Auth::id()) {
            abort(403, __('No tienes permiso para ver esta sesion.'));
        }
    }

    /**
     * Get available sources with their status.
     */
    protected function getAvailableSources(): array
    {
        $familySearch = new FamilySearchSource();
        $familySearch->setEnabled($this->pluginSettings['familysearch_enabled'] ?? true);

        $wikipedia = new WikipediaSource();
        $wikipedia->setEnabled($this->pluginSettings['wikipedia_enabled'] ?? true);

        return [
            $familySearch,
            $wikipedia,
        ];
    }
}
