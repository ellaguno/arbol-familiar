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
     * Procesar archivo GEDCOM y mostrar preview.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:ged,txt|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        // Guardar archivo temporalmente
        $tempPath = 'temp/gedcom_' . Auth::id() . '_' . time() . '.ged';
        Storage::put($tempPath, $content);

        $preview = $this->parser->getPreview($content);

        return view('gedcom.preview', [
            'preview' => $preview,
            'tempPath' => $tempPath,
            'fileName' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Confirmar importacion.
     */
    public function confirmImport(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'privacy_level' => 'required|in:private,family,community,public',
            'check_duplicates' => 'boolean',
            'update_existing' => 'boolean',
        ]);

        $tempPath = $request->input('temp_path');

        if (!Storage::exists($tempPath)) {
            return redirect()->route('gedcom.import')
                ->with('error', __('El archivo temporal ha expirado. Por favor, sube el archivo nuevamente.'));
        }

        $content = Storage::get($tempPath);

        $options = [
            'privacy_level' => $request->input('privacy_level', 'family'),
            'check_duplicates' => $request->boolean('check_duplicates'),
            'update_existing' => $request->boolean('update_existing'),
        ];

        $result = $this->parser->import($content, $options);

        // Eliminar archivo temporal
        Storage::delete($tempPath);

        if ($result['success']) {
            return redirect()->route('gedcom.result')
                ->with('import_result', $result);
        }

        return redirect()->route('gedcom.import')
            ->with('error', __('Error al importar: ') . implode(', ', $result['errors']));
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
