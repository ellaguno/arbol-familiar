<?php

namespace App\Plugins\Contracts;

use App\Models\Person;

interface ReportPluginInterface extends PluginInterface
{
    /**
     * Nombre del reporte (localizado).
     */
    public function reportName(): string;

    /**
     * Descripcion corta del reporte.
     */
    public function reportDescription(): string;

    /**
     * Icono SVG path o nombre de componente Blade.
     */
    public function reportIcon(): string;

    /**
     * Formatos de salida disponibles: ['html', 'pdf', 'svg']
     */
    public function availableFormats(): array;

    /**
     * Generar el reporte para una persona.
     *
     * @return mixed Contenido renderizado o Response
     */
    public function generate(Person $person, string $format = 'html', array $options = []): mixed;
}
