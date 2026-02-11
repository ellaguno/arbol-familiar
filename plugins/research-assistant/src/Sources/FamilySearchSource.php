<?php

namespace Plugin\ResearchAssistant\Sources;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FamilySearchSource implements GenealogySourceInterface
{
    protected bool $enabled = true;
    protected string $baseUrl = 'https://www.familysearch.org';
    protected string $apiUrl = 'https://api.familysearch.org';
    protected string $identUrl = 'https://ident.familysearch.org';
    protected ?string $appKey = null;
    protected int $maxResults = 20;

    public function getName(): string
    {
        return 'FamilySearch';
    }

    public function getId(): string
    {
        return 'familysearch';
    }

    public function getIcon(): string
    {
        return 'familysearch';
    }

    public function search(string $query, array $filters = []): array
    {
        $results = [];

        // Try API search first if app key is configured
        if ($this->appKey) {
            $apiResults = $this->searchAPI($query, $filters);
            if (!empty($apiResults)) {
                $results = array_merge($results, $apiResults);
            }
        }

        // Always include search URLs as fallback/supplement
        $results[] = $this->generateRecordSearchUrl($filters, __('Buscar registros historicos'));
        $results[] = $this->generateTreeSearchUrl($filters, __('Buscar en arboles genealogicos'));
        $results[] = $this->generateCatalogSearchUrl($query, __('Buscar en catalogo'));

        return $results;
    }

    /**
     * Search FamilySearch API for person records.
     */
    protected function searchAPI(string $query, array $filters): array
    {
        $results = [];

        try {
            $token = $this->getAccessToken();
            if (!$token) {
                return [];
            }

            $searchQuery = $this->buildApiQuery($query, $filters);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/x-gedcomx-atom+json',
                    'User-Agent' => 'MiFamilia/2.0 (genealogy research)',
                ])
                ->get("{$this->apiUrl}/platform/tree/search", [
                    'q' => $searchQuery,
                    'count' => $this->maxResults,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $entries = $data['entries'] ?? [];

                foreach ($entries as $entry) {
                    $result = $this->parseGedcomXEntry($entry);
                    if ($result) {
                        $results[] = $result;
                    }
                }
            }
        } catch (\Exception $e) {
            // API failed - silently fall back to URL-only results
        }

        return $results;
    }

    /**
     * Get access token via unauthenticated session.
     */
    protected function getAccessToken(): ?string
    {
        $cacheKey = 'familysearch_token_' . md5($this->appKey ?? '');

        $token = Cache::get($cacheKey);
        if ($token) {
            return $token;
        }

        try {
            $response = Http::timeout(15)
                ->asForm()
                ->post("{$this->identUrl}/cis-web/oauth2/v3/token", [
                    'grant_type' => 'unauthenticated_session',
                    'client_id' => $this->appKey,
                    'ip_address' => '127.0.0.1',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;
                if ($token) {
                    Cache::put($cacheKey, $token, 3500);
                    return $token;
                }
            }
        } catch (\Exception $e) {
            // Token acquisition failed
        }

        return null;
    }

    /**
     * Build the API query string.
     */
    protected function buildApiQuery(string $query, array $filters): string
    {
        $parts = [];

        if (!empty($filters['given_name'])) {
            $parts[] = 'givenName:' . $this->quoteParam($filters['given_name']);
        }
        if (!empty($filters['surname'])) {
            $parts[] = 'surname:' . $this->quoteParam($filters['surname']);
        }

        // If no structured filters, use query as name
        if (empty($parts)) {
            $nameParts = explode(' ', trim($query), 2);
            if (isset($nameParts[0])) {
                $parts[] = 'givenName:' . $this->quoteParam($nameParts[0]);
            }
            if (isset($nameParts[1])) {
                $parts[] = 'surname:' . $this->quoteParam($nameParts[1]);
            }
        }

        if (!empty($filters['birth_year'])) {
            $year = (int) $filters['birth_year'];
            $parts[] = "birthLikeDate:{$year}-" . ($year + 10) . "~";
        }
        if (!empty($filters['birth_place'])) {
            $parts[] = 'birthLikePlace:' . $this->quoteParam($filters['birth_place']);
        }
        if (!empty($filters['death_year'])) {
            $year = (int) $filters['death_year'];
            $parts[] = "deathLikeDate:{$year}-" . ($year + 10) . "~";
        }
        if (!empty($filters['death_place'])) {
            $parts[] = 'deathLikePlace:' . $this->quoteParam($filters['death_place']);
        }
        if (!empty($filters['father_name'])) {
            $parts[] = 'fatherGivenName:' . $this->quoteParam($filters['father_name']);
        }
        if (!empty($filters['mother_name'])) {
            $parts[] = 'motherGivenName:' . $this->quoteParam($filters['mother_name']);
        }
        if (!empty($filters['spouse_name'])) {
            $parts[] = 'spouseGivenName:' . $this->quoteParam($filters['spouse_name']);
        }

        return implode(' ', $parts);
    }

    /**
     * Quote a parameter value for the FamilySearch query syntax.
     */
    protected function quoteParam(string $value): string
    {
        $value = str_replace('"', '', $value);
        if (str_contains($value, ' ')) {
            return '"' . $value . '"';
        }
        return $value;
    }

    /**
     * Parse a GEDCOM-X atom entry into a structured result.
     */
    protected function parseGedcomXEntry(array $entry): ?array
    {
        $content = $entry['content'] ?? [];
        $gedcomx = $content['gedcomx'] ?? $content;

        $persons = $gedcomx['persons'] ?? [];
        if (empty($persons)) {
            return null;
        }

        $person = $persons[0];
        $personId = $person['id'] ?? null;

        // Extract name
        $name = '';
        $names = $person['names'] ?? [];
        if (!empty($names)) {
            $nameForm = $names[0]['nameForms'][0] ?? [];
            $name = $nameForm['fullText'] ?? '';
            if (empty($name)) {
                $parts = $nameForm['parts'] ?? [];
                $nameParts = [];
                foreach ($parts as $part) {
                    $nameParts[] = $part['value'] ?? '';
                }
                $name = implode(' ', array_filter($nameParts));
            }
        }

        if (empty($name)) {
            return null;
        }

        // Extract facts (birth, death)
        $birthDate = null;
        $birthPlace = null;
        $deathDate = null;
        $deathPlace = null;

        $facts = $person['facts'] ?? [];
        foreach ($facts as $fact) {
            $type = $fact['type'] ?? '';
            if (str_contains($type, 'Birth')) {
                $birthDate = $fact['date']['original'] ?? ($fact['date']['formal'] ?? null);
                $birthPlace = $fact['place']['original'] ?? null;
            } elseif (str_contains($type, 'Death')) {
                $deathDate = $fact['date']['original'] ?? ($fact['date']['formal'] ?? null);
                $deathPlace = $fact['place']['original'] ?? null;
            }
        }

        // Extract year from date strings
        $birthYear = $this->extractYear($birthDate);
        $deathYear = $this->extractYear($deathDate);

        $lifespan = '';
        if ($birthYear || $deathYear) {
            $lifespan = ($birthYear ?: '?') . ' - ' . ($deathYear ?: '');
        }

        // Extract relationships
        $father = null;
        $mother = null;
        $spouse = null;
        $relationships = $gedcomx['relationships'] ?? [];
        foreach ($relationships as $rel) {
            $relType = $rel['type'] ?? '';
            if (str_contains($relType, 'ParentChild')) {
                // Find parent person name
                $parentRef = $rel['person1']['resourceId'] ?? null;
                if ($parentRef && $parentRef !== $personId) {
                    $parentName = $this->findPersonName($persons, $parentRef);
                    $parentGender = $this->findPersonGender($persons, $parentRef);
                    if ($parentGender === 'Male') {
                        $father = $parentName;
                    } else {
                        $mother = $parentName;
                    }
                }
            } elseif (str_contains($relType, 'Couple')) {
                $spouse1 = $rel['person1']['resourceId'] ?? null;
                $spouse2 = $rel['person2']['resourceId'] ?? null;
                $spouseRef = ($spouse1 === $personId) ? $spouse2 : $spouse1;
                if ($spouseRef) {
                    $spouse = $this->findPersonName($persons, $spouseRef);
                }
            }
        }

        $snippet = '';
        $details = [];
        if ($lifespan) {
            $details[] = $lifespan;
        }
        if ($birthPlace) {
            $details[] = $birthPlace;
        }
        $snippet = implode(' | ', $details);

        $score = $entry['score'] ?? $person['score'] ?? null;

        return [
            'source' => $this->getName(),
            'type' => 'person_record',
            'title' => $name,
            'snippet' => $snippet,
            'url' => $personId ? "https://www.familysearch.org/tree/person/details/{$personId}" : null,
            'icon' => $this->getIcon(),
            'person_id' => $personId,
            'birth_date' => $birthYear,
            'birth_place' => $birthPlace,
            'death_date' => $deathYear,
            'death_place' => $deathPlace,
            'father' => $father,
            'mother' => $mother,
            'spouse' => $spouse,
            'score' => $score,
        ];
    }

    /**
     * Find a person's name by ID from the persons array.
     */
    protected function findPersonName(array $persons, string $id): ?string
    {
        foreach ($persons as $p) {
            if (($p['id'] ?? '') === $id) {
                $names = $p['names'] ?? [];
                if (!empty($names)) {
                    $nameForm = $names[0]['nameForms'][0] ?? [];
                    return $nameForm['fullText'] ?? null;
                }
            }
        }
        return null;
    }

    /**
     * Find a person's gender by ID from the persons array.
     */
    protected function findPersonGender(array $persons, string $id): ?string
    {
        foreach ($persons as $p) {
            if (($p['id'] ?? '') === $id) {
                $gender = $p['gender'] ?? [];
                $type = $gender['type'] ?? '';
                if (str_contains($type, 'Male')) return 'Male';
                if (str_contains($type, 'Female')) return 'Female';
            }
        }
        return null;
    }

    /**
     * Extract year from a date string.
     */
    protected function extractYear(?string $dateStr): ?string
    {
        if (!$dateStr) {
            return null;
        }
        if (preg_match('/(\d{4})/', $dateStr, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Generate URL for historical records search.
     */
    protected function generateRecordSearchUrl(array $filters, string $description): array
    {
        $params = [];

        if (!empty($filters['given_name'])) {
            $params['q.givenName'] = $filters['given_name'];
        }
        if (!empty($filters['surname'])) {
            $params['q.surname'] = $filters['surname'];
        }
        if (!empty($filters['birth_place'])) {
            $params['q.birthLikePlace'] = $filters['birth_place'];
        }
        if (!empty($filters['birth_year'])) {
            $params['q.birthLikeDate.from'] = $filters['birth_year'] - 5;
            $params['q.birthLikeDate.to'] = $filters['birth_year'] + 5;
        }
        if (!empty($filters['death_place'])) {
            $params['q.deathLikePlace'] = $filters['death_place'];
        }
        if (!empty($filters['death_year'])) {
            $params['q.deathLikeDate.from'] = $filters['death_year'] - 5;
            $params['q.deathLikeDate.to'] = $filters['death_year'] + 5;
        }
        if (!empty($filters['father_name'])) {
            $params['q.fatherGivenName'] = $filters['father_name'];
        }
        if (!empty($filters['mother_name'])) {
            $params['q.motherGivenName'] = $filters['mother_name'];
        }
        if (!empty($filters['spouse_name'])) {
            $params['q.spouseGivenName'] = $filters['spouse_name'];
        }

        $url = $this->baseUrl . '/search/record/results';
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return [
            'source' => $this->getName(),
            'type' => 'search_url',
            'title' => __('Registros Historicos'),
            'description' => $description,
            'url' => $url,
            'requires_login' => true,
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * Generate URL for family tree search.
     */
    protected function generateTreeSearchUrl(array $filters, string $description): array
    {
        $params = [];

        if (!empty($filters['given_name'])) {
            $params['q.givenName'] = $filters['given_name'];
        }
        if (!empty($filters['surname'])) {
            $params['q.surname'] = $filters['surname'];
        }
        if (!empty($filters['birth_place'])) {
            $params['q.birthLikePlace'] = $filters['birth_place'];
        }
        if (!empty($filters['birth_year'])) {
            $params['q.birthLikeDate.from'] = $filters['birth_year'] - 10;
            $params['q.birthLikeDate.to'] = $filters['birth_year'] + 10;
        }

        $url = $this->baseUrl . '/tree/find/name';
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return [
            'source' => $this->getName(),
            'type' => 'search_url',
            'title' => __('Arboles Genealogicos'),
            'description' => $description,
            'url' => $url,
            'requires_login' => true,
            'icon' => $this->getIcon(),
        ];
    }

    /**
     * Generate URL for catalog search.
     */
    protected function generateCatalogSearchUrl(string $query, string $description): array
    {
        $url = $this->baseUrl . '/search/catalog/results?' . http_build_query([
            'query' => $query,
        ]);

        return [
            'source' => $this->getName(),
            'type' => 'search_url',
            'title' => __('Catalogo'),
            'description' => $description,
            'url' => $url,
            'requires_login' => false,
            'icon' => $this->getIcon(),
        ];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function setAppKey(?string $appKey): void
    {
        $this->appKey = $appKey;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }
}
