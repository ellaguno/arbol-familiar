<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Services\GedcomExporter;
use App\Services\GedcomParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GedcomController extends Controller
{
    protected GedcomParser $parser;
    protected GedcomExporter $exporter;

    public function __construct(GedcomParser $parser, GedcomExporter $exporter)
    {
        $this->parser = $parser;
        $this->exporter = $exporter;
    }

    /**
     * Mostrar formulario de importacion.
     */
    public function import()
    {
        return view('gedcom.import');
    }

    /**
     * Procesar archivo GEDCOM o GEDZIP y mostrar preview.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max para GEDZIP
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Validar extensión manualmente (Laravel no reconoce .gdz)
        $allowedExtensions = ['ged', 'txt', 'zip', 'gdz'];
        if (!in_array($extension, $allowedExtensions)) {
            return back()->withErrors(['file' => __('Formato no válido. Use .ged, .txt, .zip o .gdz')]);
        }

        // Determinar si es GEDZIP
        $isGedzip = in_array($extension, ['zip', 'gdz']);
        $content = '';
        $mediaFiles = [];
        $tempMediaPath = null;

        if ($isGedzip) {
            try {
                $extracted = $this->extractGedzip($file);
                $content = $extracted['gedcom_content'];
                $mediaFiles = $extracted['media_files'];
                $tempMediaPath = $extracted['temp_path'];
            } catch (\Exception $e) {
                return back()->withErrors(['file' => $e->getMessage()]);
            }
        } else {
            $content = file_get_contents($file->getRealPath());
        }

        // Guardar GEDCOM temporalmente
        $tempPath = 'temp/gedcom_' . Auth::id() . '_' . time() . '.ged';
        Storage::put($tempPath, $content);

        $preview = $this->parser->getPreview($content);

        // Agregar info de medios físicos del ZIP al preview
        $preview['media_files'] = $mediaFiles;
        $preview['media_count'] = count($mediaFiles);

        return view('gedcom.preview', [
            'preview' => $preview,
            'tempPath' => $tempPath,
            'tempMediaPath' => $tempMediaPath,
            'fileName' => $file->getClientOriginalName(),
            'isGedzip' => $isGedzip,
        ]);
    }

    /**
     * Extraer contenido de archivo GEDZIP.
     */
    protected function extractGedzip($file): array
    {
        $zip = new \ZipArchive();
        $tempDir = storage_path('app/temp/gedzip_' . Auth::id() . '_' . time());

        if ($zip->open($file->getRealPath()) !== true) {
            throw new \Exception(__('No se pudo abrir el archivo ZIP.'));
        }

        // Crear directorio temporal
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip->extractTo($tempDir);
        $zip->close();

        // Buscar archivo .ged y archivos multimedia
        $gedcomContent = '';
        $mediaFiles = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            $relativePath = str_replace($tempDir . '/', '', $fileInfo->getPathname());
            $ext = strtolower($fileInfo->getExtension());

            if ($ext === 'ged') {
                $gedcomContent = file_get_contents($fileInfo->getPathname());
            } elseif ($this->isMediaFile($ext)) {
                $mediaFiles[] = [
                    'path' => $fileInfo->getPathname(),
                    'relative_path' => $relativePath,
                    'name' => $fileInfo->getFilename(),
                    'size' => $fileInfo->getSize(),
                    'extension' => $ext,
                ];
            }
        }

        if (empty($gedcomContent)) {
            // Limpiar directorio temporal
            $this->deleteDirectory($tempDir);
            throw new \Exception(__('No se encontró archivo GEDCOM en el ZIP.'));
        }

        return [
            'gedcom_content' => $gedcomContent,
            'media_files' => $mediaFiles,
            'temp_path' => $tempDir,
        ];
    }

    /**
     * Verificar si la extensión es un archivo multimedia válido.
     */
    protected function isMediaFile(string $ext): bool
    {
        $mediaExtensions = [
            // Imágenes
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff',
            // Documentos
            'pdf', 'doc', 'docx', 'txt',
            // Audio (futuro soporte)
            'wav', 'mp3',
        ];
        return in_array(strtolower($ext), $mediaExtensions);
    }

    /**
     * Confirmar importacion.
     */
    public function confirmImport(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'temp_media_path' => 'nullable|string',
            'privacy_level' => 'required|in:private,family,community,public',
            'check_duplicates' => 'boolean',
            'update_existing' => 'boolean',
            'import_media' => 'boolean',
        ]);

        $tempPath = $request->input('temp_path');
        $tempMediaPath = $request->input('temp_media_path');

        if (!Storage::exists($tempPath)) {
            // Limpiar directorio de medios si existe
            if ($tempMediaPath && is_dir($tempMediaPath)) {
                $this->deleteDirectory($tempMediaPath);
            }
            return redirect()->route('gedcom.import')
                ->with('error', __('El archivo temporal ha expirado. Por favor, sube el archivo nuevamente.'));
        }

        $content = Storage::get($tempPath);

        $options = [
            'privacy_level' => $request->input('privacy_level', 'family'),
            'check_duplicates' => $request->boolean('check_duplicates'),
            'update_existing' => $request->boolean('update_existing'),
            'import_media' => $request->boolean('import_media'),
            'temp_media_path' => $tempMediaPath,
        ];

        $result = $this->parser->import($content, $options);

        // Eliminar archivos temporales
        Storage::delete($tempPath);
        if ($tempMediaPath && is_dir($tempMediaPath)) {
            $this->deleteDirectory($tempMediaPath);
        }

        if ($result['success']) {
            return redirect()->route('gedcom.result')
                ->with('import_result', $result);
        }

        return redirect()->route('gedcom.import')
            ->with('error', __('Error al importar: ') . implode(', ', $result['errors']));
    }

    /**
     * Eliminar directorio recursivamente.
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $fileinfo->isDir() ? rmdir($fileinfo->getRealPath()) : unlink($fileinfo->getRealPath());
        }

        rmdir($dir);
    }

    /**
     * Mostrar resultado de importacion.
     */
    public function result()
    {
        $result = session('import_result');

        if (!$result) {
            return redirect()->route('gedcom.import');
        }

        return view('gedcom.result', ['result' => $result]);
    }

    /**
     * Mostrar formulario de exportacion.
     */
    public function export()
    {
        // Obtener persona del usuario como punto de partida predeterminado
        $userPerson = Auth::user()->person;

        // Obtener todas las personas para selector
        $persons = Person::orderBy('patronymic')
            ->orderBy('first_name')
            ->get();

        // Estadisticas
        $stats = [
            'total_persons' => Person::count(),
            'total_living' => Person::where('is_living', true)->count(),
            'total_deceased' => Person::where('is_living', false)->count(),
        ];

        return view('gedcom.export', compact('userPerson', 'persons', 'stats'));
    }

    /**
     * Procesar exportacion y descargar archivo.
     */
    public function download(Request $request)
    {
        $request->validate([
            'include_living' => 'boolean',
            'include_notes' => 'boolean',
            'include_events' => 'boolean',
            'start_person_id' => 'nullable|exists:persons,id',
            'generations' => 'nullable|integer|min:1|max:20',
            'export_type' => 'required|in:all,tree,selected',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'exists:persons,id',
        ]);

        $options = [
            'include_living' => $request->boolean('include_living', true),
            'include_notes' => $request->boolean('include_notes', true),
            'include_events' => $request->boolean('include_events', true),
        ];

        // Determinar que personas exportar
        switch ($request->input('export_type')) {
            case 'tree':
                $options['start_person_id'] = $request->input('start_person_id');
                $options['generations'] = $request->input('generations', 10);
                break;

            case 'selected':
                $options['person_ids'] = $request->input('person_ids', []);
                break;

            case 'all':
            default:
                // Exportar todo
                break;
        }

        $gedcomContent = $this->exporter->export($options);
        $stats = $this->exporter->getStats();

        // Generar nombre de archivo
        $fileName = 'mi_familia_' . date('Y-m-d_His') . '.ged';

        // Log de actividad
        $this->logActivity('gedcom_export', [
            'persons_exported' => $stats['persons'],
            'families_exported' => $stats['families'],
        ]);

        return response($gedcomContent)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Exportacion rapida de todo.
     */
    public function quickExport()
    {
        $options = [
            'include_living' => true,
            'include_notes' => true,
            'include_events' => true,
        ];

        $gedcomContent = $this->exporter->export($options);
        $fileName = 'mi_familia_full_' . date('Y-m-d') . '.ged';

        return response($gedcomContent)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Exportar arbol de persona especifica.
     */
    public function exportTree(Person $person, Request $request)
    {
        $generations = $request->input('generations', 10);

        $options = [
            'include_living' => $request->boolean('include_living', true),
            'include_notes' => $request->boolean('include_notes', true),
            'include_events' => true,
            'start_person_id' => $person->id,
            'generations' => $generations,
        ];

        $gedcomContent = $this->exporter->export($options);
        $fileName = 'tree_' . Str::slug($person->full_name) . '_' . date('Y-m-d') . '.ged';

        return response($gedcomContent)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Descargar plantilla GEDCOM de ejemplo.
     */
    public function template()
    {
        $template = <<<GEDCOM
0 HEAD
1 SOUR EXAMPLE
2 NAME Example GEDCOM File
1 DEST ANY
1 DATE 1 JAN 2024
1 SUBM @SUBM1@
1 GEDC
2 VERS 5.5.1
2 FORM LINEAGE-LINKED
1 CHAR UTF-8
1 LANG Spanish
0 @SUBM1@ SUBM
1 NAME Your Name
0 @I1@ INDI
1 NAME Juan /Garcia/
1 SEX M
1 BIRT
2 DATE 15 MAR 1950
2 PLAC Ciudad de Mexico, Mexico
1 DEAT
2 DATE 20 DEC 2020
2 PLAC Guadalajara, Mexico
1 OCCU Ingeniero
1 FAMS @F1@
0 @I2@ INDI
1 NAME Maria /Lopez/
1 SEX F
1 BIRT
2 DATE 8 JUL 1955
2 PLAC Monterrey, Mexico
1 FAMS @F1@
0 @I3@ INDI
1 NAME Pedro /Garcia/
1 SEX M
1 BIRT
2 DATE 3 NOV 1980
2 PLAC Ciudad de Mexico, Mexico
1 FAMC @F1@
0 @F1@ FAM
1 HUSB @I1@
1 WIFE @I2@
1 CHIL @I3@
1 MARR
2 DATE 12 JUN 1975
2 PLAC Ciudad de Mexico, Mexico
0 TRLR
GEDCOM;

        return response($template)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="gedcom_template.ged"');
    }

    /**
     * Registrar actividad.
     */
    protected function logActivity(string $action, array $data = []): void
    {
        \App\Models\ActivityLog::log(
            $action,
            Auth::user(),
            null,
            $data
        );
    }
}
