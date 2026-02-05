<?php

namespace Plugin\ReportsFanchart;

use App\Models\Person;
use App\Plugins\Contracts\ReportPluginInterface;
use App\Plugins\PluginServiceProvider;
use App\Plugins\Support\ReportRenderer;
use App\Plugins\Support\TreeTraversal;

class FanChartPlugin extends PluginServiceProvider implements ReportPluginInterface
{
    public function reportName(): string
    {
        return __('Grafico de Abanico');
    }

    public function reportDescription(): string
    {
        return __('Diagrama semicircular de ancestros con arcos concentricos por generacion');
    }

    public function reportIcon(): string
    {
        return 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z';
    }

    public function availableFormats(): array
    {
        return ['html', 'svg', 'pdf'];
    }

    public function generate(Person $person, string $format = 'html', array $options = []): mixed
    {
        $traversal = app(TreeTraversal::class);
        $renderer = app(ReportRenderer::class);

        $generations = $options['generations'] ?? 5;
        $fanData = $traversal->buildFanData($person, $generations);

        $data = [
            'person' => $person,
            'fanData' => $fanData,
            'generations' => $generations,
        ];

        if ($format === 'svg') {
            return $renderer->renderSvgView('reports-fanchart::svg', $data,
                __('Abanico') . ' - ' . $person->full_name . '.svg'
            );
        }

        if ($format === 'pdf') {
            $flatData = $this->flattenFanData($fanData);
            $data['flatData'] = $flatData;
            $data['totalFound'] = count($flatData);
            return $renderer->renderPdf('reports-fanchart::pdf', $data, [
                'filename' => __('Abanico') . ' - ' . $person->full_name . '.pdf',
                'orientation' => 'landscape',
            ]);
        }

        return view('reports-fanchart::report', $data);
    }

    /**
     * Aplanar estructura de arbol en lista Ahnentafel.
     * index 1 = persona raiz, 2 = padre, 3 = madre, 4 = abuelo paterno, etc.
     */
    protected function flattenFanData(array $node, int $index = 1, int $generation = 0, array &$result = []): array
    {
        $result[$index] = [
            'data' => $node['data'] ?? $node,
            'name' => $node['name'] ?? ($node['data']['name'] ?? ''),
            'generation' => $generation,
        ];
        if (!empty($node['children'])) {
            foreach ($node['children'] as $i => $child) {
                $this->flattenFanData($child, $index * 2 + $i, $generation + 1, $result);
            }
        }
        ksort($result);
        return $result;
    }

    /**
     * Etiqueta de generacion para ancestros.
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
            'person.show.sidebar' => 'reports-fanchart::hooks.sidebar-button',
            'tree.toolbar' => 'reports-fanchart::hooks.toolbar-button',
        ];
    }
}
