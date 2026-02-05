<?php

namespace Plugin\ResearchAssistant\Sources;

class FamilySearchSource implements GenealogySourceInterface
{
    protected bool $enabled = true;
    protected string $baseUrl = 'https://www.familysearch.org';

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

        // Generate search URLs for different record types
        $results[] = $this->generateRecordSearchUrl($filters, __('Buscar registros historicos'));
        $results[] = $this->generateTreeSearchUrl($filters, __('Buscar en arboles genealogicos'));
        $results[] = $this->generateCatalogSearchUrl($query, __('Buscar en catalogo'));

        return $results;
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
}
