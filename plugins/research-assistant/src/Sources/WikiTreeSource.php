<?php

namespace Plugin\ResearchAssistant\Sources;

use Illuminate\Support\Facades\Http;

class WikiTreeSource implements GenealogySourceInterface
{
    protected bool $enabled = true;
    protected string $apiUrl = 'https://api.wikitree.com/api.php';
    protected int $maxResults = 20;
    protected ?string $appId = null;

    public function getName(): string
    {
        return 'WikiTree';
    }

    public function getId(): string
    {
        return 'wikitree';
    }

    public function getIcon(): string
    {
        return 'wikitree';
    }

    public function search(string $query, array $filters = []): array
    {
        $results = [];

        try {
            $params = $this->buildSearchParams($query, $filters);

            $response = Http::timeout(30)
                ->asForm()
                ->withHeaders([
                    'User-Agent' => 'MiFamilia/2.0 (genealogy research)',
                ])
                ->post($this->apiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                $matches = $this->extractMatches($data);

                foreach ($matches as $match) {
                    $result = $this->parseProfile($match);
                    if ($result) {
                        $results[] = $result;
                    }
                }
            }
        } catch (\Exception $e) {
            $results[] = [
                'source' => $this->getName(),
                'type' => 'error',
                'title' => __('Error al buscar en WikiTree'),
                'snippet' => $e->getMessage(),
                'url' => null,
                'icon' => $this->getIcon(),
            ];
        }

        return $results;
    }

    /**
     * Build search parameters for the WikiTree API.
     */
    protected function buildSearchParams(string $query, array $filters): array
    {
        $params = [
            'action' => 'searchPerson',
            'fields' => 'Id,Name,FirstName,LastNameCurrent,BirthDate,DeathDate,BirthLocation,DeathLocation,Father,Mother,Gender,Photo',
            'limit' => $this->maxResults,
        ];

        if ($this->appId) {
            $params['appId'] = $this->appId;
        }

        // Use structured filters if available
        if (!empty($filters['given_name'])) {
            $params['FirstName'] = $filters['given_name'];
        }
        if (!empty($filters['surname'])) {
            $params['LastName'] = $filters['surname'];
        }

        // If no structured name filters, parse the query
        if (empty($params['FirstName']) && empty($params['LastName'])) {
            $parts = explode(' ', trim($query), 2);
            $params['FirstName'] = $parts[0] ?? '';
            if (isset($parts[1])) {
                $params['LastName'] = $parts[1];
            }
        }

        if (!empty($filters['birth_year'])) {
            $params['BirthDate'] = (string) $filters['birth_year'];
        }
        if (!empty($filters['death_year'])) {
            $params['DeathDate'] = (string) $filters['death_year'];
        }
        if (!empty($filters['birth_place'])) {
            $params['BirthLocation'] = $filters['birth_place'];
        }

        return $params;
    }

    /**
     * Extract matches from the API response.
     */
    protected function extractMatches(array $data): array
    {
        // WikiTree searchPerson returns results as [{"searchPerson": [...], "status": "..."}]
        // When no results: [{"searchPerson": 0}]
        if (isset($data[0]['searchPerson']) && is_array($data[0]['searchPerson'])) {
            return $data[0]['searchPerson'];
        }
        if (isset($data['searchPerson']) && is_array($data['searchPerson'])) {
            return $data['searchPerson'];
        }
        // Sometimes results are directly in the array
        if (isset($data[0]) && is_array($data[0]) && isset($data[0]['Id'])) {
            return $data;
        }

        return [];
    }

    /**
     * Parse a WikiTree profile into a structured result.
     */
    protected function parseProfile(array $profile): ?array
    {
        $wikiTreeId = $profile['Name'] ?? $profile['Id'] ?? null;
        if (!$wikiTreeId) {
            return null;
        }

        $firstName = $profile['FirstName'] ?? '';
        $lastName = $profile['LastNameCurrent'] ?? $profile['LastNameAtBirth'] ?? '';
        $fullName = trim("{$firstName} {$lastName}");

        if (empty($fullName)) {
            return null;
        }

        $birthDate = $this->formatDate($profile['BirthDate'] ?? null);
        $deathDate = $this->formatDate($profile['DeathDate'] ?? null);

        $lifespan = '';
        if ($birthDate || $deathDate) {
            $lifespan = ($birthDate ?: '?') . ' - ' . ($deathDate ?: '');
        }

        $snippet = '';
        $details = [];
        if ($lifespan) {
            $details[] = $lifespan;
        }
        if (!empty($profile['BirthLocation'])) {
            $details[] = $profile['BirthLocation'];
        }
        $snippet = implode(' | ', $details);

        return [
            'source' => $this->getName(),
            'type' => 'wikitree_profile',
            'title' => $fullName,
            'snippet' => $snippet,
            'url' => "https://www.wikitree.com/wiki/{$wikiTreeId}",
            'icon' => $this->getIcon(),
            'wikitree_id' => $wikiTreeId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birth_date' => $birthDate,
            'death_date' => $deathDate,
            'birth_place' => $profile['BirthLocation'] ?? null,
            'death_place' => $profile['DeathLocation'] ?? null,
            'father_id' => $profile['Father'] ?? null,
            'mother_id' => $profile['Mother'] ?? null,
            'gender' => $profile['Gender'] ?? null,
            'photo' => !empty($profile['Photo']) ? "https://www.wikitree.com/photo.php/thumb/{$profile['Photo']}" : null,
        ];
    }

    /**
     * Format a date string from WikiTree (YYYYMMDD or YYYY) to a year.
     */
    protected function formatDate(?string $dateStr): ?string
    {
        if (!$dateStr || $dateStr === '0000-00-00' || $dateStr === '0000') {
            return null;
        }

        // WikiTree often returns dates as YYYYMMDD or YYYY-MM-DD
        if (preg_match('/^(\d{4})/', $dateStr, $matches)) {
            $year = (int) $matches[1];
            return $year > 0 ? (string) $year : null;
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

    public function setAppId(?string $appId): void
    {
        $this->appId = $appId;
    }
}
