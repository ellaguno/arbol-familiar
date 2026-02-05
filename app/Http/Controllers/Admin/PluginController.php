<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Plugins\Models\Plugin;
use App\Plugins\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use ZipArchive;

class PluginController extends Controller
{
    protected PluginManager $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * Lista de plugins descubiertos.
     */
    public function index()
    {
        // Sync discovered plugins with DB
        $this->pluginManager->sync();

        $manifests = $this->pluginManager->getManifests();
        $plugins = Plugin::orderBy('sort_order')->orderBy('name')->get();

        // Merge manifest data with DB records
        $pluginList = [];
        foreach ($plugins as $plugin) {
            $manifest = $manifests[$plugin->slug] ?? null;
            $locale = app()->getLocale();
            $pluginList[] = [
                'record' => $plugin,
                'manifest' => $manifest,
                'name_localized' => $manifest['name_' . $locale] ?? $manifest['name'] ?? $plugin->name,
                'description_localized' => $manifest['description_' . $locale] ?? $manifest['description'] ?? $plugin->description,
                'type' => $manifest['type'] ?? 'general',
                'available' => $manifest !== null,  // plugin files exist on disk
            ];
        }

        return view('admin.plugins.index', compact('pluginList'));
    }

    /**
     * Instalar un plugin.
     */
    public function install(string $slug)
    {
        $success = $this->pluginManager->installPlugin($slug);

        if ($success) {
            return back()->with('success', __('Plugin instalado correctamente.'));
        }

        return back()->with('error', __('Error al instalar el plugin.'));
    }

    /**
     * Desinstalar un plugin.
     */
    public function uninstall(string $slug)
    {
        $success = $this->pluginManager->uninstallPlugin($slug);

        if ($success) {
            return back()->with('success', __('Plugin desinstalado correctamente.'));
        }

        return back()->with('error', __('Error al desinstalar el plugin.'));
    }

    /**
     * Habilitar/deshabilitar un plugin.
     */
    public function toggle(string $slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if (!$plugin) {
            return back()->with('error', __('Plugin no encontrado.'));
        }

        if ($plugin->isEnabled()) {
            $this->pluginManager->disablePlugin($slug);
            return back()->with('success', __('Plugin deshabilitado.'));
        } else {
            if (!$plugin->isInstalled()) {
                return back()->with('error', __('Debes instalar el plugin primero.'));
            }
            $this->pluginManager->enablePlugin($slug);
            return back()->with('success', __('Plugin habilitado.'));
        }
    }

    /**
     * Subir un plugin via archivo ZIP.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'plugin_zip' => 'required|file|mimes:zip|max:10240',
        ]);

        $zipFile = $request->file('plugin_zip');
        $zip = new ZipArchive();

        if ($zip->open($zipFile->getRealPath()) !== true) {
            return back()->with('error', __('No se pudo abrir el archivo ZIP.'));
        }

        // Buscar plugin.json dentro del ZIP (puede estar en raiz o en un subdirectorio)
        $manifestContent = null;
        $manifestPrefix = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === 'plugin.json') {
                $manifestContent = $zip->getFromIndex($i);
                $manifestPrefix = dirname($name);
                if ($manifestPrefix === '.') {
                    $manifestPrefix = '';
                }
                break;
            }
        }

        if (!$manifestContent) {
            $zip->close();
            return back()->with('error', __('El archivo ZIP no contiene un plugin.json valido.'));
        }

        $manifest = json_decode($manifestContent, true);

        if (!$manifest || empty($manifest['slug'])) {
            $zip->close();
            return back()->with('error', __('El plugin.json no contiene un slug valido.'));
        }

        $slug = $manifest['slug'];

        // Validar slug
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $zip->close();
            return back()->with('error', __('El slug del plugin contiene caracteres no permitidos.'));
        }

        // Verificar que no exista ya
        $targetDir = base_path('plugins/' . $slug);
        if (is_dir($targetDir)) {
            $zip->close();
            return back()->with('error', __('Ya existe un plugin con el slug ":slug".', ['slug' => $slug]));
        }

        // Crear directorio y extraer
        File::makeDirectory($targetDir, 0755, true);

        // Extraer archivos, ajustando el prefijo si el ZIP tiene carpeta contenedora
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            // Remover prefijo de carpeta contenedora
            if ($manifestPrefix && str_starts_with($name, $manifestPrefix . '/')) {
                $relativePath = substr($name, strlen($manifestPrefix) + 1);
            } else {
                $relativePath = $name;
            }

            if (empty($relativePath) || str_ends_with($relativePath, '/')) {
                // Es un directorio
                $dirPath = $targetDir . '/' . $relativePath;
                if (!is_dir($dirPath) && !empty($relativePath)) {
                    File::makeDirectory($dirPath, 0755, true);
                }
                continue;
            }

            // Crear subdirectorios si es necesario
            $fileDir = dirname($targetDir . '/' . $relativePath);
            if (!is_dir($fileDir)) {
                File::makeDirectory($fileDir, 0755, true);
            }

            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                file_put_contents($targetDir . '/' . $relativePath, $content);
            }
        }

        $zip->close();

        return back()->with('success', __('Plugin ":name" subido correctamente. Ahora puedes instalarlo.', [
            'name' => $manifest['name'] ?? $slug,
        ]));
    }

    /**
     * Eliminar un plugin del disco.
     */
    public function delete(string $slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();

        if ($plugin && ($plugin->isEnabled() || $plugin->isInstalled())) {
            return back()->with('error', __('Debes desinstalar el plugin antes de eliminarlo.'));
        }

        $success = $this->pluginManager->deletePluginFiles($slug);

        if ($success) {
            return back()->with('success', __('Plugin eliminado del disco.'));
        }

        return back()->with('error', __('No se pudo eliminar el plugin.'));
    }

    /**
     * Manual de plugins.
     */
    public function manual()
    {
        return view('admin.plugins.manual');
    }
}
