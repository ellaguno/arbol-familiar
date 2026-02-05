<?php

namespace Plugin\ReportsDescendants;

use App\Models\Person;
use App\Plugins\Contracts\ReportPluginInterface;
use App\Plugins\PluginServiceProvider;
use App\Plugins\Support\ReportRenderer;
use App\Plugins\Support\TreeTraversal;

class DescendantReportPlugin extends PluginServiceProvider implements ReportPluginInterface
{
    public function reportName(): string
    {
        return __('Reporte de Descendientes');
    }

    public function reportDescription(): string
    {
        return __('Arbol de descendientes con conyuges y familias en formato indentado');
    }

    public function reportIcon(): string
    {
        return 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4';
    }

    public function availableFormats(): array
    {
        return ['html', 'pdf'];
    }

    public function generate(Person $person, string $format = 'html', array $options = []): mixed
    {
        $traversal = app(TreeTraversal::class);
        $renderer = app(ReportRenderer::class);

        $generations = $options['generations'] ?? 10;
        $rawDescendants = $traversal->getDescendants($person, $generations);

        // Construir lista indentada a partir del arbol jerarquico (para PDF)
        $flatList = [];
        $this->flattenTree($person, $rawDescendants, 0, $flatList);

        $totalDescendants = $this->countDescendants($flatList);

        // Arbol jerarquico compatible con d3.hierarchy() (para vista interactiva)
        $descendantTree = $traversal->buildDescendantTree($person, $generations);

        $data = [
            'person' => $person,
            'descendantTree' => $descendantTree,
            'flatList' => $flatList,
            'generations' => $generations,
            'totalDescendants' => $totalDescendants,
        ];

        if ($format === 'pdf') {
            // Solo incluir SVG si hay pocos descendientes (evitar PDF enorme)
            $svgView = count($flatList) <= 80 ? 'reports-descendants::svg-tree' : null;
            return $renderer->renderPdf('reports-descendants::pdf', $data, [
                'filename' => __('Descendientes') . ' - ' . $person->full_name . '.pdf',
                'svgView' => $svgView,
            ]);
        }

        return view('reports-descendants::report', $data);
    }

    /**
     * Aplanar el arbol jerarquico en una lista indentada.
     * Cada entrada tiene: person, level, type (person|spouse), marriageYear, familyIndex
     */
    protected function flattenTree(Person $rootPerson, array $families, int $level, array &$flatList): void
    {
        // Agregar la persona raiz solo en nivel 0
        if ($level === 0) {
            $flatList[] = [
                'person' => $rootPerson,
                'level' => 0,
                'type' => 'person',
                'marriageYear' => null,
                'generation_label' => $this->generationLabel(0),
            ];
        }

        foreach ($families as $family) {
            // Agregar conyuge
            if ($family['spouse']) {
                $spousePerson = Person::find($family['spouse']['id']);
                if ($spousePerson) {
                    $flatList[] = [
                        'person' => $spousePerson,
                        'level' => $level + 1,
                        'type' => 'spouse',
                        'marriageYear' => $family['marriageDate'] ?? null,
                        'generation_label' => null,
                    ];
                }
            }

            // Agregar hijos
            foreach ($family['children'] as $child) {
                $childPerson = Person::find($child['id']);
                if ($childPerson) {
                    $childLevel = $level + 1;
                    $flatList[] = [
                        'person' => $childPerson,
                        'level' => $childLevel,
                        'type' => 'person',
                        'marriageYear' => null,
                        'generation_label' => $this->generationLabel($childLevel),
                    ];

                    // Recurrir para los descendientes de este hijo
                    if (!empty($child['descendants'])) {
                        $this->flattenTree($childPerson, $child['descendants'], $childLevel, $flatList);
                    }
                }
            }
        }
    }

    /**
     * Contar el total de descendientes (solo personas, no conyuges).
     */
    protected function countDescendants(array $flatList): int
    {
        $count = 0;
        foreach ($flatList as $entry) {
            if ($entry['type'] === 'person' && $entry['level'] > 0) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Etiqueta de generacion para descendientes.
     */
    protected function generationLabel(int $generation): string
    {
        return match ($generation) {
            0 => __('Persona raiz'),
            1 => __('Hijos'),
            2 => __('Nietos'),
            3 => __('Bisnietos'),
            4 => __('Tataranietos'),
            default => __(':n° generacion', ['n' => $generation]),
        };
    }

    public function hooks(): array
    {
        return [
            'person.show.sidebar' => 'reports-descendants::hooks.sidebar-button',
            'tree.toolbar' => 'reports-descendants::hooks.toolbar-button',
        ];
    }
}
