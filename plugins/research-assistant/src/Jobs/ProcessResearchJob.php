<?php

namespace Plugin\ResearchAssistant\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Plugin\ResearchAssistant\Models\ResearchSession;
use Plugin\ResearchAssistant\Services\AIService;
use Plugin\ResearchAssistant\Sources\FamilySearchSource;
use Plugin\ResearchAssistant\Sources\WikipediaSource;

class ProcessResearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300; // 5 minutes

    public function __construct(
        public ResearchSession $session,
        public array $enabledSources,
        public array $pluginSettings = []
    ) {}

    public function handle(): void
    {
        try {
            // 1. Build person context
            $personContext = $this->buildPersonContext();

            // 2. Search external sources
            $this->session->update(['status' => ResearchSession::STATUS_SEARCHING]);
            $searchResults = $this->searchSources($personContext);
            $this->session->update(['search_results' => $searchResults]);

            // 3. Analyze with AI
            $this->session->update(['status' => ResearchSession::STATUS_ANALYZING]);

            $aiService = new AIService($this->pluginSettings);
            $analysis = $aiService->analyze(
                $this->session->query,
                $personContext,
                $searchResults,
                $this->session->ai_provider,
                $this->session->ai_model
            );

            // 4. Save results
            $this->session->update([
                'status' => ResearchSession::STATUS_COMPLETED,
                'ai_analysis' => ['content' => $analysis['analysis']],
                'suggestions' => $analysis['suggestions'],
                'tokens_used' => $analysis['tokens'],
            ]);

        } catch (\Exception $e) {
            $this->session->update([
                'status' => ResearchSession::STATUS_FAILED,
                'ai_analysis' => [
                    'error' => true,
                    'message' => $e->getMessage(),
                ],
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Build context from the associated person.
     */
    protected function buildPersonContext(): array
    {
        if (!$this->session->person) {
            return ['query' => $this->session->query];
        }

        $person = $this->session->person;

        $context = [
            'name' => $person->full_name,
            'given_name' => $person->given_names,
            'surname' => $person->surname,
            'birth_date' => $person->birth_date?->format('Y-m-d'),
            'birth_year' => $person->birth_date?->year,
            'birth_place' => $person->birth_place,
            'death_date' => $person->death_date?->format('Y-m-d'),
            'death_year' => $person->death_date?->year,
            'death_place' => $person->death_place,
            'occupation' => $person->occupation,
            'notes' => $person->notes,
        ];

        // Add family relationships
        if ($person->father) {
            $context['father'] = $person->father->full_name;
            $context['father_name'] = $person->father->given_names;
        }

        if ($person->mother) {
            $context['mother'] = $person->mother->full_name;
            $context['mother_name'] = $person->mother->given_names;
        }

        $spouses = $person->spouses;
        if ($spouses->isNotEmpty()) {
            $context['spouses'] = $spouses->pluck('full_name')->toArray();
            $context['spouse_name'] = $spouses->first()->given_names;
        }

        return $context;
    }

    /**
     * Search enabled sources.
     */
    protected function searchSources(array $personContext): array
    {
        $results = [];
        $sources = $this->getSourceInstances();

        foreach ($this->enabledSources as $sourceId) {
            if (!isset($sources[$sourceId])) {
                continue;
            }

            $source = $sources[$sourceId];

            if (!$source->isEnabled()) {
                continue;
            }

            try {
                $sourceResults = $source->search($this->session->query, $personContext);
                $results[$source->getName()] = $sourceResults;
            } catch (\Exception $e) {
                $results[$source->getName()] = [
                    [
                        'type' => 'error',
                        'title' => __('Error'),
                        'snippet' => $e->getMessage(),
                    ],
                ];
            }
        }

        return $results;
    }

    /**
     * Get instances of all available sources.
     */
    protected function getSourceInstances(): array
    {
        $familySearch = new FamilySearchSource();
        $familySearch->setEnabled($this->pluginSettings['familysearch_enabled'] ?? true);

        $wikipedia = new WikipediaSource();
        $wikipedia->setEnabled($this->pluginSettings['wikipedia_enabled'] ?? true);
        $wikipedia->setMaxResults($this->pluginSettings['max_results_per_source'] ?? 10);

        return [
            'familysearch' => $familySearch,
            'wikipedia' => $wikipedia,
        ];
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->session->update([
            'status' => ResearchSession::STATUS_FAILED,
            'ai_analysis' => [
                'error' => true,
                'message' => $exception->getMessage(),
            ],
        ]);
    }
}
