<?php

namespace Plugin\ReportsPedigree;

use App\Models\Person;
use App\Plugins\Contracts\ReportPluginInterface;
use App\Plugins\PluginServiceProvider;
use App\Plugins\Support\ReportRenderer;
use App\Plugins\Support\TreeTraversal;

class PedigreeChartPlugin extends PluginServiceProvider implements ReportPluginInterface
{
    public function reportName(): string
    {
        return __('Cuadro de Pedigri');
    }

    public function reportDescription(): string
    {
        return __('Cuadro de pedigri horizontal clasico con ancestros directos');
    }

    public function reportIcon(): string
    {
        return 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z';
    }

    public function availableFormats(): array
    {
        return ['html', 'svg', 'pdf'];
    }

    public function generate(Person $person, string $format = 'html', array $options = []): mixed
    {
        $traversal = app(TreeTraversal::class);
        $renderer = app(ReportRenderer::class);

        $generations = min(max((int) ($options['generations'] ?? 4), 2), 8);

        // Construir lista Ahnentafel para el pedigri (usado en PDF/SVG)
        $ahnentafel = $this->buildAhnentafel($person, $generations);

        // Construir estructura de arbol para D3.js (usado en HTML interactivo)
        $fanData = $traversal->buildFanData($person, $generations);

        $data = [
            'person' => $person,
            'ahnentafel' => $ahnentafel,
            'fanData' => $fanData,
            'generations' => $generations,
            'totalFound' => count($ahnentafel),
        ];

        if ($format === 'svg') {
            return $renderer->renderSvgView('reports-pedigree::svg', $data,
                __('Pedigri') . ' - ' . $person->full_name . '.svg'
            );
        }

        if ($format === 'pdf') {
            return $renderer->renderPdf('reports-pedigree::pdf', $data, [
                'filename' => __('Pedigri') . ' - ' . $person->full_name . '.pdf',
                'orientation' => 'landscape',
                'svgView' => 'reports-pedigree::svg',
            ]);
        }

        return view('reports-pedigree::report', $data);
    }

    /**
     * Construir lista Ahnentafel.
     * Numeracion: 1=persona, 2=padre, 3=madre, 4=abuelo paterno, etc.
     */
    protected function buildAhnentafel(Person $person, int $maxGenerations): array
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

            // Proteger datos de menores
            $isProtected = $p->shouldProtectMinorData();

            // Obtener foto como base64 para PDF (si existe y no es menor protegido)
            $photoBase64 = null;
            if (!$isProtected && $p->photo_path) {
                $photoPath = storage_path('app/public/' . $p->photo_path);
                if (file_exists($photoPath)) {
                    $photoData = file_get_contents($photoPath);
                    $mimeType = mime_content_type($photoPath);
                    $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($photoData);
                }
            }

            $list[$num] = [
                'number' => $num,
                'person' => $p,
                'generation' => $gen,
                'name' => $isProtected ? $p->first_name : $p->full_name,
                'birth_year' => $isProtected ? null : ($p->birth_year ?? ($p->birth_date?->format('Y'))),
                'death_year' => $isProtected ? null : ($p->death_year ?? ($p->death_date?->format('Y'))),
                'is_living' => $p->is_living,
                'gender' => $p->gender,
                'photo' => $photoBase64,
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

    public function hooks(): array
    {
        return [
            'person.show.sidebar' => 'reports-pedigree::hooks.sidebar-button',
            'tree.toolbar' => 'reports-pedigree::hooks.toolbar-button',
        ];
    }
}
