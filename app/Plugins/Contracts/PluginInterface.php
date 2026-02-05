<?php

namespace App\Plugins\Contracts;

interface PluginInterface
{
    /**
     * Identificador unico del plugin.
     */
    public function slug(): string;

    /**
     * Nombre legible del plugin.
     */
    public function name(): string;

    /**
     * Version del plugin.
     */
    public function version(): string;

    /**
     * Tipo de plugin (report, communication, general).
     */
    public function type(): string;

    /**
     * Ejecutar al instalar (migraciones, seed).
     */
    public function install(): void;

    /**
     * Ejecutar al desinstalar (rollback migraciones).
     */
    public function uninstall(): void;

    /**
     * Ejecutar al habilitar.
     */
    public function enable(): void;

    /**
     * Ejecutar al deshabilitar.
     */
    public function disable(): void;

    /**
     * Registrar hooks en vistas.
     * Retorna array: ['hook.name' => 'vista-blade' o Closure]
     */
    public function hooks(): array;
}
