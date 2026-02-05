<?php

namespace App\Plugins\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportRenderer
{
    /**
     * Renderizar una vista Blade a HTML string.
     */
    public function renderHtml(string $view, array $data = []): string
    {
        return view($view, $data)->render();
    }

    /**
     * Renderizar una vista Blade a PDF con SVG embebido como imagen.
     *
     * @param string $view Nombre de la vista Blade
     * @param array $data Datos para la vista
     * @param array $options Opciones: 'paper' (default 'a4'), 'orientation' (default 'portrait'), 'filename', 'svgView'
     * @return \Illuminate\Http\Response
     */
    public function renderPdf(string $view, array $data = [], array $options = []): Response
    {
        $paper = $options['paper'] ?? 'a4';
        $orientation = $options['orientation'] ?? 'portrait';
        $filename = $options['filename'] ?? 'report.pdf';

        // Si hay una vista SVG, pre-renderizarla y pasarla como data URI
        if (!empty($options['svgView'])) {
            $svgHtml = view($options['svgView'], $data)->render();
            $data['svgDataUri'] = $this->svgToDataUri($svgHtml);
        }

        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation);

        if (!empty($options['stream'])) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    /**
     * Convierte contenido SVG a data URI para embeber en PDF.
     */
    public function svgToDataUri(string $svgContent): string
    {
        // Limpiar el SVG: remover espacios extra y sanitizar para URI
        $svgContent = trim($svgContent);
        // Codificar como base64 para evitar problemas con caracteres especiales
        return 'data:image/svg+xml;base64,' . base64_encode($svgContent);
    }

    /**
     * Renderizar contenido SVG como response.
     */
    public function renderSvg(string $svgContent, string $filename = 'chart.svg'): Response
    {
        return new Response($svgContent, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Renderizar una vista Blade que contiene SVG y descargarla.
     */
    public function renderSvgView(string $view, array $data = [], string $filename = 'chart.svg'): Response
    {
        $svgContent = view($view, $data)->render();
        return $this->renderSvg($svgContent, $filename);
    }

    /**
     * Renderizar SVG como PDF (landscape por default para charts).
     */
    public function renderSvgAsPdf(string $view, array $data = [], array $options = []): Response
    {
        $options['orientation'] = $options['orientation'] ?? 'landscape';
        $options['filename'] = $options['filename'] ?? 'chart.pdf';

        return $this->renderPdf($view, $data, $options);
    }
}
