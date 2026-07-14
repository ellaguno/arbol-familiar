<?php

namespace App\Plugins\Support;

use App\Models\Family;
use App\Models\Person;
use Illuminate\Support\Collection;

class TreeTraversal
{
    /**
     * Convierte persona a nodo para el arbol.
     */
    public function personToNode(Person $person): array
    {
        // Verificar si tiene padre y/o madre
        $family = $person->familiesAsChild()->first();
        $hasFather = $family && $family->husband_id;
        $hasMother = $family && $family->wife_id;

        // Verificar si tiene conyuge
        $hasSpouse = $person->familiesAsSpouse()->whereNotNull('husband_id')->whereNotNull('wife_id')->exists();

        // Obtener hermanos
        $siblings = $person->siblings;
        $siblingsCount = $siblings->count();
        $siblingsInfo = [];

        foreach ($siblings as $sibling) {
            $siblingsInfo[] = [
                'id' => $sibling->id,
                'name' => $sibling->shouldProtectMinorData() ? $sibling->first_name : $sibling->full_name,
                'gender' => $sibling->gender,
            ];
        }

        // Proteger datos de menores
        $isProtectedMinor = $person->shouldProtectMinorData();

        return [
            'id' => $person->id,
            'name' => $isProtectedMinor ? $person->first_name : $person->full_name,
            'firstName' => $person->first_name,
            'lastName' => $isProtectedMinor ? '' : trim($person->patronymic . ' ' . ($person->matronymic ?? '')),
            'gender' => $person->gender,
            'birthDate' => $isProtectedMinor ? null : ($person->birth_year ?? ($person->birth_date?->format('Y'))),
            'deathDate' => $isProtectedMinor ? null : ($person->death_year ?? ($person->death_date?->format('Y'))),
            'isLiving' => $person->is_living,
            'isMinor' => $person->is_minor_calculated,
            'isProtected' => $isProtectedMinor,
            'photo' => $isProtectedMinor ? null : ($person->photo_path ? asset('storage/' . $person->photo_path) : null),
            'hasEthnicHeritage' => $person->has_ethnic_heritage,
            'url' => route('persons.show', $person),
            'hasFather' => $hasFather,
            'hasMother' => $hasMother,
            'hasSpouse' => $hasSpouse,
            'siblingsCount' => $siblingsCount,
            'siblings' => $siblingsInfo,
            'hasEmail' => !empty($person->email),
            'hasUser' => !empty($person->user_id),
            'consentStatus' => $person->consent_status,
        ];
    }

    /**
     * Obtiene ancestros recursivamente (estructura de arbol para D3.js).
     */
    public function getAncestors(Person $person, int $generations, int $level = 1, array $visited = []): array
    {
        // Guardia de ciclos por rama: corta si esta persona ya esta en la cadena
        // de ascendencia actual (dato ciclico), sin bloquear el pedigree collapse
        // legitimo (un mismo ancestro alcanzable por dos lineas distintas).
        if ($level > $generations || in_array($person->id, $visited, true)) {
            return [];
        }
        $visited[] = $person->id;

        $ancestors = [];
        $family = $person->familiesAsChild()->with(['husband', 'wife'])->first();

        if ($family) {
            if ($family->husband) {
                $father = $this->personToNode($family->husband);
                $father['relation'] = 'father';
                $father['level'] = $level;
                $father['ancestors'] = $this->getAncestors($family->husband, $generations, $level + 1, $visited);
                $ancestors[] = $father;
            }

            if ($family->wife) {
                $mother = $this->personToNode($family->wife);
                $mother['relation'] = 'mother';
                $mother['level'] = $level;
                $mother['ancestors'] = $this->getAncestors($family->wife, $generations, $level + 1, $visited);
                $ancestors[] = $mother;
            }
        }

        return $ancestors;
    }

    /**
     * Obtiene descendientes recursivamente (estructura de arbol para D3.js).
     */
    public function getDescendants(Person $person, int $generations, int $level = 1, array $visited = []): array
    {
        // Guardia de ciclos por rama (persona descendiente de si misma).
        if ($level > $generations || in_array($person->id, $visited, true)) {
            return [];
        }
        $visited[] = $person->id;

        $descendants = [];
        $families = $person->familiesAsSpouse()->with(['husband', 'wife', 'children'])->get();

        foreach ($families as $family) {
            $spouse = $family->husband_id === $person->id ? $family->wife : $family->husband;

            $familyNode = [
                'familyId' => $family->id,
                'spouse' => $spouse ? $this->personToNode($spouse) : null,
                'marriageDate' => $family->marriage_date?->format('Y'),
                'status' => $family->status,
                'children' => [],
            ];

            foreach ($family->children as $child) {
                $childNode = $this->personToNode($child);
                $childNode['level'] = $level;
                $childNode['descendants'] = $this->getDescendants($child, $generations, $level + 1, $visited);
                $familyNode['children'][] = $childNode;
            }

            $descendants[] = $familyNode;
        }

        return $descendants;
    }

    /**
     * Construye datos para vista de abanico.
     */
    public function buildFanData(Person $person, int $generations, array $visited = []): array
    {
        $data = [
            'name' => $person->full_name,
            'data' => $this->personToNode($person),
        ];

        // Guardia de ciclos por rama.
        if ($generations > 0 && !in_array($person->id, $visited, true)) {
            $visited[] = $person->id;
            $family = $person->familiesAsChild()->with(['husband', 'wife'])->first();

            if ($family) {
                // Incluir fecha de matrimonio de los padres
                $data['marriageDate'] = $family->marriage_date?->format('Y');
                $data['marriagePlace'] = $family->marriage_place;

                $children = [];

                if ($family->husband) {
                    $children[] = $this->buildFanData($family->husband, $generations - 1, $visited);
                }

                if ($family->wife) {
                    $children[] = $this->buildFanData($family->wife, $generations - 1, $visited);
                }

                if (!empty($children)) {
                    $data['children'] = $children;
                }
            }
        }

        return $data;
    }

    /**
     * Construye arbol de descendientes compatible con d3.hierarchy().
     * Incluye conyuges como _spouses en cada nodo.
     */
    public function buildDescendantTree(Person $person, int $generations, array $visited = []): array
    {
        $node = [
            'name' => $person->shouldProtectMinorData() ? $person->first_name : $person->full_name,
            'data' => $this->personToNode($person),
            '_spouses' => [],
            'children' => [],
        ];

        // Guardia de ciclos por rama (persona descendiente de si misma).
        if ($generations <= 0 || in_array($person->id, $visited, true)) {
            return $node;
        }
        $visited[] = $person->id;

        $families = $person->familiesAsSpouse()->with(['husband', 'wife', 'children'])->get();

        foreach ($families as $family) {
            $spouse = $family->husband_id === $person->id ? $family->wife : $family->husband;
            if ($spouse) {
                $node['_spouses'][] = [
                    'data' => $this->personToNode($spouse),
                    'marriageDate' => $family->marriage_date?->format('Y'),
                ];
            }

            foreach ($family->children as $child) {
                $node['children'][] = $this->buildDescendantTree($child, $generations - 1, $visited);
            }
        }

        return $node;
    }

    /**
     * Retorna una coleccion plana de ancestros con atributo 'generation'.
     * Util para reportes y listados.
     */
    public function ancestors(Person $person, int $maxGenerations): Collection
    {
        $results = collect();
        $this->collectAncestors($person, 1, $maxGenerations, $results);

        return $results;
    }

    /**
     * Retorna una coleccion plana de descendientes con atributo 'generation'.
     * Util para reportes y listados.
     */
    public function descendants(Person $person, int $maxGenerations): Collection
    {
        $results = collect();
        $this->collectDescendants($person, 1, $maxGenerations, $results);

        return $results;
    }

    /**
     * Recolecta ancestros recursivamente en una coleccion plana.
     */
    protected function collectAncestors(Person $person, int $level, int $maxGenerations, Collection &$results, array $visited = []): void
    {
        // Guardia de ciclos por rama.
        if ($level > $maxGenerations || in_array($person->id, $visited, true)) {
            return;
        }
        $visited[] = $person->id;

        $family = $person->familiesAsChild()->with(['husband', 'wife'])->first();

        if (!$family) {
            return;
        }

        if ($family->husband) {
            $family->husband->setAttribute('generation', $level);
            $results->push($family->husband);
            $this->collectAncestors($family->husband, $level + 1, $maxGenerations, $results, $visited);
        }

        if ($family->wife) {
            $family->wife->setAttribute('generation', $level);
            $results->push($family->wife);
            $this->collectAncestors($family->wife, $level + 1, $maxGenerations, $results, $visited);
        }
    }

    /**
     * Recolecta descendientes recursivamente en una coleccion plana.
     */
    protected function collectDescendants(Person $person, int $level, int $maxGenerations, Collection &$results, array $visited = []): void
    {
        // Guardia de ciclos por rama.
        if ($level > $maxGenerations || in_array($person->id, $visited, true)) {
            return;
        }
        $visited[] = $person->id;

        $families = $person->familiesAsSpouse()->with(['children'])->get();

        foreach ($families as $family) {
            foreach ($family->children as $child) {
                $child->setAttribute('generation', $level);
                $results->push($child);
                $this->collectDescendants($child, $level + 1, $maxGenerations, $results, $visited);
            }
        }
    }
}
