<?php

namespace App\Plugins;

use App\Plugins\Contracts\PluginInterface;
use Illuminate\Support\ServiceProvider;

abstract class PluginServiceProvider extends ServiceProvider implements PluginInterface
{
    /**
     * Ruta al directorio del plugin. Inyectada por PluginManager.
     */
    public string $pluginPath = '';

    /**
     * Manifest parseado (plugin.json). Inyectado por PluginManager.
     */
    public array $manifest = [];

    public function slug(): string
    {
        return $this->manifest['slug'] ?? '';
    }

    public function name(): string
    {
        $locale = app()->getLocale();
        $nameKey = 'name_' . $locale;

        return $this->manifest[$nameKey] ?? $this->manifest['name'] ?? '';
    }

    public function version(): string
    {
        return $this->manifest['version'] ?? '0.0.0';
    }

    public function type(): string
    {
        return $this->manifest['type'] ?? 'general';
    }

    /**
     * Registrar bindings en el contenedor.
     * Las subclases pueden sobreescribir para registrar servicios.
     */
    public function register(): void
    {
        //
    }

    /**
     * Inicializar el plugin: cargar rutas, vistas y hooks.
     */
    public function boot(): void
    {
        $this->bootRoutes();
        $this->bootViews();
        $this->bootHooks();
    }

    /**
     * Cargar rutas del plugin.
     */
    protected function bootRoutes(): void
    {
        $routeFile = $this->pluginPath . '/routes/web.php';
        if (file_exists($routeFile)) {
            $this->loadRoutesFrom($routeFile);
        }
    }

    /**
     * Cargar vistas del plugin con namespace.
     * Uso: @include('slug-del-plugin::nombre-vista')
     */
    protected function bootViews(): void
    {
        $viewPath = $this->pluginPath . '/resources/views';
        if (is_dir($viewPath)) {
            $this->loadViewsFrom($viewPath, $this->slug());
        }
    }

    /**
     * Registrar hooks en el HookManager.
     */
    protected function bootHooks(): void
    {
        if (!$this->app->bound(HookManager::class)) {
            return;
        }

        $hookManager = $this->app->make(HookManager::class);
        foreach ($this->hooks() as $hookName => $content) {
            $hookManager->register($hookName, $content, $this->slug());
        }
    }

    /**
     * Instalar el plugin: ejecutar migraciones.
     */
    public function install(): void
    {
        $migrationsPath = $this->pluginPath . '/database/migrations';
        if (is_dir($migrationsPath)) {
            $relativePath = str_replace(base_path() . '/', '', $migrationsPath);
            \Artisan::call('migrate', [
                '--path' => $relativePath,
                '--force' => true,
            ]);
        }
    }

    /**
     * Desinstalar el plugin: revertir migraciones.
     */
    public function uninstall(): void
    {
        $migrationsPath = $this->pluginPath . '/database/migrations';
        if (is_dir($migrationsPath)) {
            $relativePath = str_replace(base_path() . '/', '', $migrationsPath);
            \Artisan::call('migrate:rollback', [
                '--path' => $relativePath,
                '--force' => true,
            ]);
        }
    }

    public function enable(): void
    {
        //
    }

    public function disable(): void
    {
        //
    }

    public function hooks(): array
    {
        return [];
    }
}
