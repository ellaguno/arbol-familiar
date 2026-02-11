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
use Plugin\ResearchAssistant\Sources\WikidataSource;
use Plugin\ResearchAssistant\Sources\WikipediaSource;
use Plugin\ResearchAssistant\Sources\WikiTreeSource;

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
        $plugin = \App\Plugins\Models\Plugin::where('slug', 'research-assistant')->first();
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
        $defaultProvider = $this->pluginSettings['ai_provider'] ?? 'openrouter';
        $provider = $aiService->getProvider($defaultProvider);

        return view('research-assistant::index', [
            'recentSessions' => $recentSessions,
            'sources' => $this->getAvailableSources(),
            'aiConfigured' => $provider && $provider->isConfigured(),
            'providerName' => $provider ? $provider->getName() : 'N/A',
            'defaultModel' => $this->pluginSettings['ai_model'] ?? 'N/A',
        ]);
    }

    /**
     * Show search form for a specific person.
     */
    public function searchPerson(Person $person)
    {
        $this->authorize('view', $person);

        $aiService = new AIService($this->pluginSettings);
        $defaultProvider = $this->pluginSettings['ai_provider'] ?? 'openrouter';
        $provider = $aiService->getProvider($defaultProvider);

        return view('research-assistant::search', [
            'person' => $person,
            'sources' => $this->getAvailableSources(),
            'aiConfigured' => $provider && $provider->isConfigured(),
            'providerName' => $provider ? $provider->getName() : 'N/A',
            'defaultModel' => $this->pluginSettings['ai_model'] ?? 'N/A',
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
            'sources.*' => 'string|in:familysearch,wikipedia,wikidata,wikitree',
        ]);

        // Use admin-configured provider and model
        $aiProvider = $this->pluginSettings['ai_provider'] ?? 'openrouter';
        $aiModel = $this->pluginSettings['ai_model'] ?? null;

        if (!$aiModel) {
            return back()->withErrors([
                'query' => __('El administrador no ha configurado un modelo de IA.'),
            ])->withInput();
        }

        // Verify person access if provided
        if ($request->person_id) {
            $person = Person::findOrFail($request->person_id);
            $this->authorize('view', $person);
        }

        // Check if AI provider is configured
        $aiService = new AIService($this->pluginSettings);
        $provider = $aiService->getProvider($aiProvider);

        if (!$provider || !$provider->isConfigured()) {
            return back()->withErrors([
                'query' => __('El proveedor de IA no esta configurado. Por favor contacta al administrador.'),
            ])->withInput();
        }

        // Create research session
        $session = ResearchSession::create([
            'user_id' => Auth::id(),
            'person_id' => $request->input('person_id'),
            'query' => $request->input('query'),
            'ai_provider' => $aiProvider,
            'ai_model' => $aiModel,
            'status' => ResearchSession::STATUS_PENDING,
        ]);

        // Dispatch the job
        ProcessResearchJob::dispatch($session, $request->input('sources'), $this->pluginSettings);

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

        $wikidata = new WikidataSource();
        $wikidata->setEnabled($this->pluginSettings['wikidata_enabled'] ?? true);

        $wikiTree = new WikiTreeSource();
        $wikiTree->setEnabled($this->pluginSettings['wikitree_enabled'] ?? true);

        return [
            $familySearch,
            $wikipedia,
            $wikidata,
            $wikiTree,
        ];
    }
}
