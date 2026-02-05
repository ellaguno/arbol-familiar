<?php

namespace Plugin\ResearchAssistant\Sources;

use Illuminate\Support\Facades\Http;

class WikipediaSource implements GenealogySourceInterface
{
    protected bool $enabled = true;
    protected string $apiUrl = 'https://es.wikipedia.org/w/api.php';
    protected int $maxResults = 10;

    public function getName(): string
    {
        return 'Wikipedia';
    }

    public function getId(): string
    {
        return 'wikipedia';
    }

    public function getIcon(): string
    {
        return 'wikipedia';
    }

    public function search(string $query, array $filters = []): array
    {
        $results = [];

        try {
            // Search Wikipedia API
            $response = Http::timeout(30)->get($this->apiUrl, [
                'action' => 'query',
                'list' => 'search',
                'srsearch' => $query,
                'format' => 'json',
                'srlimit' => $this->maxResults,
                'srprop' => 'snippet|titlesnippet|timestamp',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $searchResults = $data['query']['search'] ?? [];

                foreach ($searchResults as $item) {
                    $title = $item['title'];
                    $results[] = [
                        'source' => $this->getName(),
                        'type' => 'article',
                        'title' => $title,
                        'snippet' => strip_tags($item['snippet']),
                        'url' => 'https://es.wikipedia.org/wiki/' . urlencode(str_replace(' ', '_', $title)),
                        'timestamp' => $item['timestamp'] ?? null,
                        'icon' => $this->getIcon(),
                    ];
                }
            }

            // Also search for the person directly
            if (!empty($filters['given_name']) || !empty($filters['surname'])) {
                $personQuery = trim(($filters['given_name'] ?? '') . ' ' . ($filters['surname'] ?? ''));
                if ($personQuery !== $query) {
                    $personResults = $this->searchPerson($personQuery);
                    $results = array_merge($results, $personResults);
                }
            }

        } catch (\Exception $e) {
            // Return empty results on error, don't fail the entire search
            $results[] = [
                'source' => $this->getName(),
                'type' => 'error',
                'title' => __('Error al buscar en Wikipedia'),
                'snippet' => $e->getMessage(),
                'url' => null,
                'icon' => $this->getIcon(),
            ];
        }

        return $results;
    }

    /**
     * Search for a specific person.
     */
    protected function searchPerson(string $name): array
    {
        $results = [];

        try {
            $response = Http::timeout(30)->get($this->apiUrl, [
                'action' => 'query',
                'list' => 'search',
                'srsearch' => $name,
                'format' => 'json',
                'srlimit' => 5,
                'srprop' => 'snippet',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $searchResults = $data['query']['search'] ?? [];

                foreach ($searchResults as $item) {
                    $title = $item['title'];
                    $results[] = [
                        'source' => $this->getName(),
                        'type' => 'person_search',
                        'title' => $title,
                        'snippet' => strip_tags($item['snippet']),
                        'url' => 'https://es.wikipedia.org/wiki/' . urlencode(str_replace(' ', '_', $title)),
                        'icon' => $this->getIcon(),
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        return $results;
    }

    /**
     * Get summary of a Wikipedia article.
     */
    public function getSummary(string $title): ?string
    {
        try {
            $response = Http::timeout(30)->get($this->apiUrl, [
                'action' => 'query',
                'prop' => 'extracts',
                'exintro' => true,
                'explaintext' => true,
                'titles' => $title,
                'format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $pages = $data['query']['pages'] ?? [];
                foreach ($pages as $page) {
                    if (isset($page['extract'])) {
                        return $page['extract'];
                    }
                }
            }
        } catch (\Exception $e) {
            // Return null on error
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }
}
