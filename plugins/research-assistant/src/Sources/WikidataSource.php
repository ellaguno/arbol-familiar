<?php

namespace Plugin\ResearchAssistant\Sources;

use Illuminate\Support\Facades\Http;

class WikidataSource implements GenealogySourceInterface
{
    protected bool $enabled = true;
    protected string $endpoint = 'https://query.wikidata.org/sparql';
    protected int $maxResults = 20;

    public function getName(): string
    {
        return 'Wikidata';
    }

    public function getId(): string
    {
        return 'wikidata';
    }

    public function getIcon(): string
    {
        return 'wikidata';
    }

    public function search(string $query, array $filters = []): array
    {
        $results = [];

        try {
            $sparql = $this->buildSparqlQuery($query, $filters);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/sparql-results+json',
                    'User-Agent' => 'MiFamilia/2.0 (genealogy research)',
                ])
                ->get($this->endpoint, [
                    'query' => $sparql,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $bindings = $data['results']['bindings'] ?? [];

                $seen = [];
                foreach ($bindings as $binding) {
                    $personUri = $binding['person']['value'] ?? null;
                    if ($personUri && isset($seen[$personUri])) {
                        continue;
                    }
                    $result = $this->parseBinding($binding);
                    if ($result) {
                        $seen[$personUri] = true;
                        $results[] = $result;
                    }
                }
            }
        } catch (\Exception $e) {
            $results[] = [
                'source' => $this->getName(),
                'type' => 'error',
                'title' => __('Error al buscar en Wikidata'),
                'snippet' => $e->getMessage(),
                'url' => null,
                'icon' => $this->getIcon(),
            ];
        }

        return $results;
    }

    /**
     * Build SPARQL query for person search.
     */
    protected function buildSparqlQuery(string $query, array $filters): string
    {
        $searchName = $this->escapeSparqlString(mb_strtolower(trim($query)));

        // Build optional date filters
        $dateFilters = '';
        if (!empty($filters['birth_year'])) {
            $year = (int) $filters['birth_year'];
            $dateFilters .= "  FILTER(!BOUND(?birthDate) || (YEAR(?birthDate) >= " . ($year - 10) . " && YEAR(?birthDate) <= " . ($year + 10) . "))\n";
        }
        if (!empty($filters['death_year'])) {
            $year = (int) $filters['death_year'];
            $dateFilters .= "  FILTER(!BOUND(?deathDate) || (YEAR(?deathDate) >= " . ($year - 10) . " && YEAR(?deathDate) <= " . ($year + 10) . "))\n";
        }

        return <<<SPARQL
SELECT ?person ?personLabel ?personDescription ?birthDate ?deathDate
       ?birthPlaceLabel ?deathPlaceLabel ?fatherLabel ?motherLabel
       ?spouseLabel ?occupationLabel ?image
WHERE {
  ?person wdt:P31 wd:Q5 .
  ?person rdfs:label ?name .
  FILTER(LANG(?name) = "es" || LANG(?name) = "en")
  FILTER(CONTAINS(LCASE(?name), "{$searchName}"))
  OPTIONAL { ?person wdt:P569 ?birthDate }
  OPTIONAL { ?person wdt:P570 ?deathDate }
  OPTIONAL { ?person wdt:P19 ?birthPlace }
  OPTIONAL { ?person wdt:P20 ?deathPlace }
  OPTIONAL { ?person wdt:P22 ?father }
  OPTIONAL { ?person wdt:P25 ?mother }
  OPTIONAL { ?person wdt:P26 ?spouse }
  OPTIONAL { ?person wdt:P106 ?occupation }
  OPTIONAL { ?person wdt:P18 ?image }
{$dateFilters}  SERVICE wikibase:label { bd:serviceParam wikibase:language "es,en" }
}
LIMIT {$this->maxResults}
SPARQL;
    }

    /**
     * Escape a string for use in a SPARQL double-quoted string literal.
     */
    protected function escapeSparqlString(string $value): string
    {
        return str_replace(
            ['\\', '"', "\n", "\r", "\t"],
            ['\\\\', '\\"', '\\n', '\\r', '\\t'],
            $value
        );
    }

    /**
     * Parse a SPARQL result binding into a structured result.
     */
    protected function parseBinding(array $binding): ?array
    {
        $personUri = $binding['person']['value'] ?? null;
        if (!$personUri) {
            return null;
        }

        $name = $binding['personLabel']['value'] ?? '';
        // Skip results where label is just the Q-ID
        if (preg_match('/^Q\d+$/', $name)) {
            return null;
        }

        $wikidataId = basename($personUri);

        $birthDate = $this->parseDate($binding['birthDate']['value'] ?? null);
        $deathDate = $this->parseDate($binding['deathDate']['value'] ?? null);

        $lifespan = '';
        if ($birthDate || $deathDate) {
            $lifespan = ($birthDate ?: '?') . ' - ' . ($deathDate ?: '');
        }

        $snippet = $binding['personDescription']['value'] ?? '';
        if ($lifespan) {
            $snippet = "({$lifespan}) " . $snippet;
        }

        return [
            'source' => $this->getName(),
            'type' => 'wikidata_person',
            'title' => $name,
            'snippet' => $snippet,
            'url' => "https://www.wikidata.org/wiki/{$wikidataId}",
            'icon' => $this->getIcon(),
            'wikidata_id' => $wikidataId,
            'birth_date' => $birthDate,
            'death_date' => $deathDate,
            'birth_place' => $binding['birthPlaceLabel']['value'] ?? null,
            'death_place' => $binding['deathPlaceLabel']['value'] ?? null,
            'father' => $this->cleanLabel($binding['fatherLabel']['value'] ?? null),
            'mother' => $this->cleanLabel($binding['motherLabel']['value'] ?? null),
            'spouse' => $this->cleanLabel($binding['spouseLabel']['value'] ?? null),
            'occupation' => $this->cleanLabel($binding['occupationLabel']['value'] ?? null),
            'image' => $binding['image']['value'] ?? null,
        ];
    }

    /**
     * Parse an ISO date string into a year or formatted date.
     */
    protected function parseDate(?string $dateStr): ?string
    {
        if (!$dateStr) {
            return null;
        }

        try {
            $date = new \DateTime($dateStr);
            return $date->format('Y');
        } catch (\Exception $e) {
            // Try to extract year
            if (preg_match('/(\d{4})/', $dateStr, $matches)) {
                return $matches[1];
            }
            return null;
        }
    }

    /**
     * Clean a label value (remove Q-IDs).
     */
    protected function cleanLabel(?string $label): ?string
    {
        if (!$label || preg_match('/^Q\d+$/', $label)) {
            return null;
        }
        return $label;
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
