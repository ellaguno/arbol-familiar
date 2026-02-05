<?php

namespace Plugin\ReportsAncestors;

use App\Models\Person;
use App\Plugins\Contracts\ReportPluginInterface;
use App\Plugins\PluginServiceProvider;
use App\Plugins\Support\ReportRenderer;
use App\Plugins\Support\TreeTraversal;

class AncestorReportPlugin extends PluginServiceProvider implements ReportPluginInterface
{
    public function reportName(): string
    {
        return __('Reporte de Ancestros');
    }

    public function reportDescription(): string
    {
        return __('Lista de ancestros en formato Ahnentafel (numeracion estandar genealogica)');
    }

    public function reportIcon(): string
    {
        return 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01';
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
        $ancestors = $traversal->ancestors($person, $generations);

        // Numeracion Ahnentafel
        $ahnentafel = $this->buildAhnentafel($person, $traversal, $generations);

        $data = [
            'person' => $person,
            'ancestors' => $ancestors,
            'ahnentafel' => $ahnentafel,
            'generations' => $generations,
            'totalFound' => count($ahnentafel),
        ];

        if ($format === 'pdf') {
            return $renderer->renderPdf('reports-ancestors::pdf', $data, [
                'filename' => __('Ancestros') . ' - ' . $person->full_name . '.pdf',
            ]);
        }

        return view('reports-ancestors::report', $data);
    }

    /**
     * Construir lista Ahnentafel.
     * Numeracion: 1=persona, 2=padre, 3=madre, 4=abuelo paterno, etc.
     */
    protected function buildAhnentafel(Person $person, TreeTraversal $traversal, int $maxGenerations): array
    {
        $list = [];
        $queue = [[
            'person' => $person,
            'number' => 1,
            'generation' => 0,
        ]];

        while (!empty($queue)) {
            $item = array_shift($queue);
            $p = $item['person'];
            $num = $item['number'];
            $gen = $item['generation'];

            $list[$num] = [
                'number' => $num,
                'person' => $p,
                'generation' => $gen,
                'generation_label' => $this->generationLabel($gen),
            ];

            if ($gen < $maxGenerations) {
                $family = $p->familiesAsChild()->with(['husband', 'wife'])->first();
                if ($family) {
                    if ($family->husband) {
                        $queue[] = [
                            'person' => $family->husband,
                            'number' => $num * 2,
                            'generation' => $gen + 1,
                        ];
                    }
                    if ($family->wife) {
                        $queue[] = [
                            'person' => $family->wife,
                            'number' => $num * 2 + 1,
                            'generation' => $gen + 1,
                        ];
                    }
                }
            }
        }

        ksort($list);
        return $list;
    }

    /**
     * Etiqueta de generacion.
     */
    protected function generationLabel(int $generation): string
    {
        return match ($generation) {
            0 => __('Persona raiz'),
            1 => __('Padres'),
            2 => __('Abuelos'),
            3 => __('Bisabuelos'),
            4 => __('Tatarabuelos'),
            default => __(':n° generacion', ['n' => $generation]),
        };
    }

    public function hooks(): array
    {
        return [
            'person.show.sidebar' => 'reports-ancestors::hooks.sidebar-button',
            'tree.toolbar' => 'reports-ancestors::hooks.toolbar-button',
        ];
    }
}
