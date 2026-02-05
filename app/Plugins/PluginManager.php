<?php

namespace App\Plugins;

use App\Plugins\Contracts\ReportPluginInterface;
use App\Plugins\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PluginManager
{
    /**
     * Plugins cargados: slug => instancia de PluginServiceProvider.
     */
    protected array $loaded = [];

    /**
     * Manifests descubiertos: slug => array parseado de plugin.json.
     */
    protected array $manifests = [];

    /**
     * Escanear el directorio plugins/ y retornar manifests descubiertos.
     */
    public function discover(): array
    {
        $pluginsPath = base_path('plugins');

        if (!is_dir($pluginsPath)) {
            return [];
        }

        $manifests = [];

        foreach (File::directories($pluginsPath) as $dir) {
            $manifestFile = $dir . '/plugin.json';
            if (file_exists($manifestFile)) {
                $manifest = json_decode(file_get_contents($manifestFile), true);
                if ($manifest && isset($manifest['slug'])) {
                    $manifest['_path'] = $dir;
                    $manifests[$manifest['slug']] = $manifest;
                }
            }
        }

        $this->manifests = $manifests;
        return $manifests;
    }

    /**
     * Cargar y registrar todos los plugins habilitados.
     */
    public function bootEnabled(): void
    {
        $this->discover();

        if (!Schema::hasTable('plugins')) {
            return;
        }

        $enabledSlugs = Plugin::where('status', 'enabled')
            ->pluck('slug')
            ->toArray();

        foreach ($enabledSlugs as $slug) {
            if (isset($this->manifests[$slug])) {
                try {
                    $this->loadPlugin($slug, $this->manifests[$slug]);
                } catch (\Throwable $e) {
                    Plugin::where('slug', $slug)->update([
                        'status' => 'error',
                    ]);
                    Log::error("Plugin '{$slug}' fallo al cargar: " . $e->getMessage(), [
                        'exception' => $e,
                    ]);
                }
            }
        }
    }

    /**
     * Cargar un plugin individual.
     */
    protected function loadPlugin(string $slug, array $manifest): void
    {
        $providerClassName = $manifest['provider'];
        $pluginPath = $manifest['_path'];

        // Construir FQCN: Plugin\{StudlySlug}\{ProviderClass}
        $namespace = 'Plugin\\' . $this->slugToStudly($slug);
        $fqcn = $namespace . '\\' . $providerClassName;

        if (!class_exists($fqcn)) {
            throw new \RuntimeException("Clase provider {$fqcn} no encontrada para plugin '{$slug}'");
        }

        /** @var PluginServiceProvider $provider */
        $provider = new $fqcn(app());
        $provider->pluginPath = $pluginPath;
        $provider->manifest = $manifest;

        app()->register($provider);
        $this->loaded[$slug] = $provider;
    }

    /**
     * Convertir slug a StudlyCase para namespace.
     * 'reports-ancestors' => 'ReportsAncestors'
     */
    protected function slugToStudly(string $slug): string
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));
    }

    /**
     * Habilitar un plugin.
     */
    public function enablePlugin(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin || !$plugin->isInstalled()) {
            return false;
        }

        $plugin->update([
            'status' => 'enabled',
            'enabled_at' => now(),
        ]);

        // Ejecutar callback enable del plugin si esta cargado
        if (isset($this->loaded[$slug])) {
            $this->loaded[$slug]->enable();
        }

        return true;
    }

    /**
     * Deshabilitar un plugin.
     */
    public function disablePlugin(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return false;
        }

        // Ejecutar callback disable del plugin si esta cargado
        if (isset($this->loaded[$slug])) {
            $this->loaded[$slug]->disable();
        }

        $plugin->update([
            'status' => 'disabled',
            'enabled_at' => null,
        ]);

        return true;
    }

    /**
     * Instalar un plugin (crear registro en BD y ejecutar migraciones).
     */
    public function installPlugin(string $slug): bool
    {
        if (!isset($this->manifests[$slug])) {
            $this->discover();
        }

        if (!isset($this->manifests[$slug])) {
            return false;
        }

        $manifest = $this->manifests[$slug];

        // Crear o actualizar registro en BD
        $plugin = Plugin::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $manifest['name'] ?? $slug,
                'version' => $manifest['version'] ?? '0.0.0',
                'author' => $manifest['author'] ?? null,
                'description' => $manifest['description'] ?? null,
                'installed' => true,
                'status' => 'disabled',
            ]
        );

        // Instanciar provider temporalmente para ejecutar install()
        $providerClassName = $manifest['provider'];
        $namespace = 'Plugin\\' . $this->slugToStudly($slug);
        $fqcn = $namespace . '\\' . $providerClassName;

        if (class_exists($fqcn)) {
            $provider = new $fqcn(app());
            $provider->pluginPath = $manifest['_path'];
            $provider->manifest = $manifest;
            $provider->install();
        }

        return true;
    }

    /**
     * Desinstalar un plugin (revertir migraciones y eliminar registro).
     */
    public function uninstallPlugin(string $slug): bool
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return false;
        }

        // Deshabilitar primero
        if ($plugin->isEnabled()) {
            $this->disablePlugin($slug);
        }

        // Ejecutar uninstall si tenemos el manifest
        if (isset($this->manifests[$slug])) {
            $manifest = $this->manifests[$slug];
            $providerClassName = $manifest['provider'];
            $namespace = 'Plugin\\' . $this->slugToStudly($slug);
            $fqcn = $namespace . '\\' . $providerClassName;

            if (class_exists($fqcn)) {
                $provider = new $fqcn(app());
                $provider->pluginPath = $manifest['_path'];
                $provider->manifest = $manifest;
                $provider->uninstall();
            }
        }

        $plugin->update([
            'installed' => false,
            'status' => 'disabled',
        ]);

        return true;
    }

    /**
     * Obtener plugins cargados.
     */
    public function getLoaded(): array
    {
        return $this->loaded;
    }

    /**
     * Obtener manifests descubiertos.
     */
    public function getManifests(): array
    {
        if (empty($this->manifests)) {
            $this->discover();
        }
        return $this->manifests;
    }

    /**
     * Verificar si un plugin esta habilitado.
     */
    public function isEnabled(string $slug): bool
    {
        return isset($this->loaded[$slug]);
    }

    /**
     * Obtener todos los plugins de tipo reporte cargados.
     */
    public function getReportPlugins(): array
    {
        return array_filter($this->loaded, function ($provider) {
            return $provider instanceof ReportPluginInterface;
        });
    }

    /**
     * Obtener el estado de un plugin desde la BD.
     */
    public function getPluginRecord(string $slug): ?Plugin
    {
        return Plugin::where('slug', $slug)->first();
    }

    /**
     * Eliminar archivos de un plugin del disco.
     */
    public function deletePluginFiles(string $slug): bool
    {
        $pluginsPath = base_path('plugins/' . $slug);

        if (!is_dir($pluginsPath)) {
            return false;
        }

        // Verificar que esta dentro del directorio plugins/
        $realPath = realpath($pluginsPath);
        $allowedPath = realpath(base_path('plugins'));

        if (!$realPath || !$allowedPath || !str_starts_with($realPath, $allowedPath)) {
            return false;
        }

        File::deleteDirectory($pluginsPath);

        // Eliminar de manifests en memoria
        unset($this->manifests[$slug]);

        // Eliminar registro de BD
        Plugin::where('slug', $slug)->delete();

        return true;
    }

    /**
     * Sincronizar plugins descubiertos con la BD.
     * Crea registros para plugins nuevos sin instalar.
     */
    public function sync(): void
    {
        $manifests = $this->getManifests();

        foreach ($manifests as $slug => $manifest) {
            Plugin::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $manifest['name'] ?? $slug,
                    'version' => $manifest['version'] ?? '0.0.0',
                    'author' => $manifest['author'] ?? null,
                    'description' => $manifest['description'] ?? null,
                    'installed' => false,
                    'status' => 'disabled',
                ]
            );
        }
    }
}
