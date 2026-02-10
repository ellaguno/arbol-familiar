<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Family;
use App\Models\Media;
use App\Models\Person;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GedcomParser
{
    protected array $lines = [];
    protected int $currentLine = 0;
    protected array $individuals = [];
    protected array $families = [];
    protected array $mediaObjects = []; // GEDCOM OBJE records (@O001@ => data)
    protected array $idMapping = []; // GEDCOM ID -> Database ID
    protected array $mediaMapping = []; // GEDCOM media ID -> Database Media ID
    protected array $errors = [];
    protected array $warnings = [];
    protected array $stats = [
        'persons_created' => 0,
        'persons_updated' => 0,
        'families_created' => 0,
        'events_created' => 0,
        'families_skipped' => 0,
        'children_skipped' => 0,
        'media_imported' => 0,
        'media_linked' => 0,
        'media_skipped' => 0,
    ];

    /**
     * Parsear archivo GEDCOM.
     */
    public function parse(string $content): array
    {
        $this->lines = $this->normalizeLines($content);
        $this->currentLine = 0;
        $this->individuals = [];
        $this->families = [];
        $this->mediaObjects = [];
        $this->errors = [];
        $this->warnings = [];

        while ($this->currentLine < count($this->lines)) {
            $record = $this->parseRecord();
            if ($record) {
                if ($record['type'] === 'INDI') {
                    $this->individuals[$record['id']] = $record;
                } elseif ($record['type'] === 'FAM') {
                    $this->families[$record['id']] = $record;
                } elseif ($record['type'] === 'OBJE') {
                    $this->mediaObjects[$record['id']] = $record;
                }
            }
        }

        // Validar datos parseados
        $this->validateParsedData();

        return [
            'individuals' => $this->individuals,
            'families' => $this->families,
            'media_objects' => $this->mediaObjects,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Validar datos parseados para detectar inconsistencias.
     */
    protected function validateParsedData(): void
    {
        // Validar familias
        foreach ($this->families as $famId => &$family) {
            $husbId = $family['husb'] ?? null;
            $wifeId = $family['wife'] ?? null;
            $children = $family['children'] ?? [];

            // Validar que al menos uno de los padres exista
            if (!$husbId && !$wifeId) {
                $this->warnings[] = __('Familia :id no tiene padres definidos', ['id' => $famId]);
            }

            // Validar que los hijos no sean los mismos padres
            $validChildren = [];
            foreach ($children as $childId) {
                $isInvalid = false;
                $reason = '';

                // Hijo es el mismo que el padre
                if ($husbId && $childId === $husbId) {
                    $isInvalid = true;
                    $reason = __('Persona :id es padre y hijo en la misma familia :fam',
                        ['id' => $childId, 'fam' => $famId]);
                }

                // Hijo es el mismo que la madre
                if ($wifeId && $childId === $wifeId) {
                    $isInvalid = true;
                    $reason = __('Persona :id es madre e hijo en la misma familia :fam',
                        ['id' => $childId, 'fam' => $famId]);
                }

                // Verificar si la persona existe
                if (!isset($this->individuals[$childId])) {
                    $isInvalid = true;
                    $reason = __('Hijo :id en familia :fam no existe como individuo',
                        ['id' => $childId, 'fam' => $famId]);
                }

                if ($isInvalid) {
                    $this->warnings[] = $reason . ' - ' . __('Hijo omitido');
                } else {
                    $validChildren[] = $childId;
                }
            }
            $family['children'] = $validChildren;

            // Verificar que los padres existan como individuos
            if ($husbId && !isset($this->individuals[$husbId])) {
                $this->warnings[] = __('Esposo :id en familia :fam no existe como individuo',
                    ['id' => $husbId, 'fam' => $famId]);
                $family['husb'] = null;
            }

            if ($wifeId && !isset($this->individuals[$wifeId])) {
                $this->warnings[] = __('Esposa :id en familia :fam no existe como individuo',
                    ['id' => $wifeId, 'fam' => $famId]);
                $family['wife'] = null;
            }
        }

        // Detectar ciclos genealógicos (ancestro que también es descendiente)
        $this->detectGenealogicalCycles();
    }

    /**
     * Detectar ciclos genealógicos en los datos.
     */
    protected function detectGenealogicalCycles(): void
    {
        // Construir mapa de padres para cada individuo
        $parentMap = []; // childId => [fatherId, motherId]

        foreach ($this->families as $famId => $family) {
            $husbId = $family['husb'] ?? null;
            $wifeId = $family['wife'] ?? null;
            $children = $family['children'] ?? [];

            foreach ($children as $childId) {
                if (!isset($parentMap[$childId])) {
                    $parentMap[$childId] = ['fathers' => [], 'mothers' => []];
                }
                if ($husbId) {
                    $parentMap[$childId]['fathers'][] = $husbId;
                }
                if ($wifeId) {
                    $parentMap[$childId]['mothers'][] = $wifeId;
                }
            }
        }

        // Para cada individuo, verificar que no sea ancestro de sí mismo
        foreach ($this->individuals as $indiId => $indi) {
            $ancestors = $this->collectAncestors($indiId, $parentMap, []);
            if (in_array($indiId, $ancestors)) {
                $this->warnings[] = __('Ciclo genealógico detectado: :id es ancestro de sí mismo',
                    ['id' => $indiId]);
            }
        }
    }

    /**
     * Recolectar ancestros de un individuo recursivamente.
     */
    protected function collectAncestors(string $indiId, array $parentMap, array $visited): array
    {
        if (in_array($indiId, $visited)) {
            return []; // Evitar recursión infinita
        }

        $visited[] = $indiId;
        $ancestors = [];

        if (isset($parentMap[$indiId])) {
            foreach ($parentMap[$indiId]['fathers'] as $fatherId) {
                $ancestors[] = $fatherId;
                $ancestors = array_merge($ancestors, $this->collectAncestors($fatherId, $parentMap, $visited));
            }
            foreach ($parentMap[$indiId]['mothers'] as $motherId) {
                $ancestors[] = $motherId;
                $ancestors = array_merge($ancestors, $this->collectAncestors($motherId, $parentMap, $visited));
            }
        }

        return array_unique($ancestors);
    }

    /**
     * Obtener preview de los datos parseados.
     */
    public function getPreview(string $content): array
    {
        $data = $this->parse($content);

        $preview = [
            'total_individuals' => count($data['individuals']),
            'total_families' => count($data['families']),
            'total_media_objects' => count($data['media_objects']),
            'individuals' => [],
            'families' => [],
            'media_objects' => [],
            'errors' => $data['errors'],
            'warnings' => $data['warnings'],
        ];

        // Mostrar primeras 20 personas
        $count = 0;
        foreach ($data['individuals'] as $id => $indi) {
            if ($count >= 20) break;
            $preview['individuals'][] = [
                'gedcom_id' => $id,
                'name' => $indi['name'] ?? __('Sin nombre'),
                'birth_date' => $indi['birth']['date'] ?? null,
                'birth_place' => $indi['birth']['place'] ?? null,
                'death_date' => $indi['death']['date'] ?? null,
                'gender' => $indi['sex'] ?? 'U',
            ];
            $count++;
        }

        // Mostrar primeras 10 familias
        $count = 0;
        foreach ($data['families'] as $id => $fam) {
            if ($count >= 10) break;
            $husbName = isset($fam['husb']) && isset($data['individuals'][$fam['husb']])
                ? $data['individuals'][$fam['husb']]['name'] ?? '?'
                : '?';
            $wifeName = isset($fam['wife']) && isset($data['individuals'][$fam['wife']])
                ? $data['individuals'][$fam['wife']]['name'] ?? '?'
                : '?';

            $preview['families'][] = [
                'gedcom_id' => $id,
                'husband' => $husbName,
                'wife' => $wifeName,
                'children_count' => count($fam['children'] ?? []),
                'marriage_date' => $fam['marriage']['date'] ?? null,
            ];
            $count++;
        }

        // Mostrar primeros 10 objetos multimedia
        $count = 0;
        foreach ($data['media_objects'] as $id => $media) {
            if ($count >= 10) break;
            $filePath = $media['file'] ?? null;
            $preview['media_objects'][] = [
                'gedcom_id' => $id,
                'file' => $filePath,
                'title' => $media['title'] ?? basename($filePath ?? ''),
                'form' => $media['form'] ?? null,
            ];
            $count++;
        }

        return $preview;
    }

    /**
     * Importar datos parseados a la base de datos.
     */
    public function import(string $content, array $options = []): array
    {
        $data = $this->parse($content);

        if (!empty($data['errors'])) {
            return [
                'success' => false,
                'errors' => $data['errors'],
                'stats' => $this->stats,
            ];
        }

        DB::beginTransaction();

        try {
            // Primero crear todas las personas
            foreach ($data['individuals'] as $gedcomId => $indi) {
                $this->importIndividual($gedcomId, $indi, $options);
            }

            // Importar objetos multimedia si está habilitado
            if ($options['import_media'] ?? false) {
                $this->importMediaObjects($data['media_objects'], $options);
            }

            // Luego crear las familias y relaciones
            foreach ($data['families'] as $gedcomId => $fam) {
                $this->importFamily($gedcomId, $fam, $options);
            }

            // Vincular medios a personas
            if ($options['import_media'] ?? false) {
                $this->linkMediaToPersons($data['individuals'], $options);
            }

            // Actualizar relaciones padre/madre en personas
            $this->updateParentRelationships($data);

            DB::commit();

            return [
                'success' => true,
                'stats' => $this->stats,
                'id_mapping' => $this->idMapping,
                'warnings' => $this->warnings,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('GEDCOM Import Error: ' . $e->getMessage());

            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'stats' => $this->stats,
            ];
        }
    }

    /**
     * Importar individuo.
     */
    protected function importIndividual(string $gedcomId, array $data, array $options): void
    {
        $names = $this->parseGedcomName($data['name'] ?? '');

        $personData = [
            'first_name' => $names['first_name'] ?: __('Desconocido'),
            'patronymic' => $names['last_name'] ?: __('Desconocido'),
            'matronymic' => $names['maiden_name'] ?: '',
            'nickname' => $data['nickname'] ?? null,
            'gender' => $this->mapGender($data['sex'] ?? 'U'),
            'birth_date' => $this->parseGedcomDate($data['birth']['date'] ?? null),
            'birth_place' => $data['birth']['place'] ?? null,
            'death_date' => $this->parseGedcomDate($data['death']['date'] ?? null),
            'death_place' => $data['death']['place'] ?? null,
            'is_living' => !isset($data['death']),
            'biography' => $data['note'] ?? null,
            'privacy_level' => $options['privacy_level'] ?? 'extended_family',
            'created_by' => Auth::id(),
        ];

        // Buscar duplicados si la opcion esta habilitada
        $person = null;
        if ($options['check_duplicates'] ?? false) {
            $person = $this->findDuplicatePerson($personData);
        }

        if ($person) {
            // Actualizar persona existente si la opcion lo permite
            if ($options['update_existing'] ?? false) {
                $person->update(array_filter($personData));
                $this->stats['persons_updated']++;
            }
        } else {
            $person = Person::create($personData);
            $this->stats['persons_created']++;
        }

        $this->idMapping['INDI'][$gedcomId] = $person->id;

        // Crear eventos adicionales
        $this->importPersonEvents($person, $data);
    }

    /**
     * Importar eventos de persona.
     */
    protected function importPersonEvents(Person $person, array $data): void
    {
        // Bautizo
        if (isset($data['baptism'])) {
            $this->createEvent($person, 'BAPM', $data['baptism']);
        }

        // Confirmacion
        if (isset($data['confirmation'])) {
            $this->createEvent($person, 'CONF', $data['confirmation']);
        }

        // Ocupacion
        if (isset($data['occupation'])) {
            Event::create([
                'person_id' => $person->id,
                'type' => 'OCCU',
                'description' => $data['occupation'],
                'created_by' => Auth::id(),
            ]);
            $this->stats['events_created']++;
        }

        // Educacion
        if (isset($data['education'])) {
            Event::create([
                'person_id' => $person->id,
                'type' => 'EDUC',
                'description' => $data['education'],
                'created_by' => Auth::id(),
            ]);
            $this->stats['events_created']++;
        }

        // Emigracion
        if (isset($data['emigration'])) {
            $this->createEvent($person, 'EMIG', $data['emigration']);
        }

        // Inmigracion
        if (isset($data['immigration'])) {
            $this->createEvent($person, 'IMMI', $data['immigration']);
        }

        // Residencia
        if (isset($data['residence'])) {
            foreach ((array)$data['residence'] as $residence) {
                $this->createEvent($person, 'RESI', $residence);
            }
        }
    }

    /**
     * Crear evento.
     */
    protected function createEvent(Person $person, string $type, array $eventData): void
    {
        Event::create([
            'person_id' => $person->id,
            'type' => $type,
            'date' => $this->parseGedcomDate($eventData['date'] ?? null),
            'place' => $eventData['place'] ?? null,
            'description' => $eventData['note'] ?? null,
            'created_by' => Auth::id(),
        ]);
        $this->stats['events_created']++;
    }

    /**
     * Importar familia.
     */
    protected function importFamily(string $gedcomId, array $data, array $options): void
    {
        $husbandId = isset($data['husb']) ? ($this->idMapping['INDI'][$data['husb']] ?? null) : null;
        $wifeId = isset($data['wife']) ? ($this->idMapping['INDI'][$data['wife']] ?? null) : null;

        if (!$husbandId && !$wifeId) {
            $this->warnings[] = __('Familia :id sin esposos, omitida', ['id' => $gedcomId]);
            return;
        }

        $familyData = [
            'husband_id' => $husbandId,
            'wife_id' => $wifeId,
            'marriage_date' => $this->parseGedcomDate($data['marriage']['date'] ?? null),
            'marriage_place' => $data['marriage']['place'] ?? null,
            'divorce_date' => $this->parseGedcomDate($data['divorce']['date'] ?? null),
            'status' => isset($data['divorce']) ? 'divorced' : 'married',
            'created_by' => Auth::id(),
        ];

        $family = Family::create($familyData);
        $this->stats['families_created']++;
        $this->idMapping['FAM'][$gedcomId] = $family->id;

        // Crear evento de matrimonio
        if (isset($data['marriage'])) {
            Event::create([
                'family_id' => $family->id,
                'type' => 'MARR',
                'date' => $familyData['marriage_date'],
                'place' => $familyData['marriage_place'],
                'created_by' => Auth::id(),
            ]);
            $this->stats['events_created']++;
        }

        // Agregar hijos (evitar duplicados)
        if (isset($data['children'])) {
            foreach ($data['children'] as $childGedcomId) {
                $childId = $this->idMapping['INDI'][$childGedcomId] ?? null;
                if ($childId && !$family->children()->where('person_id', $childId)->exists()) {
                    $family->children()->attach($childId, ['child_order' => 0]);
                }
            }
        }
    }

    /**
     * Actualizar relaciones de padres.
     * Nota: Las relaciones padre-hijo se establecen via family_children,
     * no mediante columnas father_id/mother_id en persons.
     */
    protected function updateParentRelationships(array $data): void
    {
        // Las relaciones ya están establecidas mediante:
        // - families (husband_id, wife_id)
        // - family_children (family_id, person_id)
        // No se requiere actualización adicional.
    }

    /**
     * Normalizar lineas del archivo GEDCOM.
     */
    protected function normalizeLines(string $content): array
    {
        // Normalizar saltos de linea
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Eliminar BOM si existe
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Dividir en lineas y filtrar vacias
        return array_values(array_filter(
            explode("\n", $content),
            fn($line) => trim($line) !== ''
        ));
    }

    /**
     * Parsear un registro GEDCOM.
     */
    protected function parseRecord(): ?array
    {
        if ($this->currentLine >= count($this->lines)) {
            return null;
        }

        $line = $this->lines[$this->currentLine];
        $parsed = $this->parseLine($line);

        if ($parsed['level'] !== 0) {
            $this->currentLine++;
            return null;
        }

        // Registro de nivel 0
        $record = [
            'id' => $parsed['xref'],
            'type' => $parsed['tag'],
        ];

        $this->currentLine++;

        // Parsear sub-registros
        while ($this->currentLine < count($this->lines)) {
            $nextLine = $this->lines[$this->currentLine];
            $nextParsed = $this->parseLine($nextLine);

            if ($nextParsed['level'] === 0) {
                break;
            }

            $this->parseSubRecord($record, $nextParsed);
            $this->currentLine++;
        }

        return $record;
    }

    /**
     * Parsear una linea GEDCOM.
     */
    protected function parseLine(string $line): array
    {
        $line = trim($line);

        // Formato: LEVEL [XREF] TAG [VALUE]
        // Ejemplos: "0 @I1@ INDI", "1 NAME John /Doe/", "2 DATE 1 JAN 1900"
        if (preg_match('/^(\d+)\s+(@[^@]+@)?\s*(\w+)(.*)$/', $line, $matches)) {
            return [
                'level' => (int)$matches[1],
                'xref' => $matches[2] ? trim($matches[2], '@') : null,
                'tag' => $matches[3],
                'value' => trim($matches[4] ?? ''),
            ];
        }

        return [
            'level' => -1,
            'xref' => null,
            'tag' => '',
            'value' => '',
        ];
    }

    /**
     * Parsear sub-registro.
     */
    protected function parseSubRecord(array &$record, array $parsed): void
    {
        $tag = $parsed['tag'];
        $value = $parsed['value'];

        switch ($tag) {
            case 'NAME':
                $record['name'] = $value;
                break;

            case 'SEX':
                $record['sex'] = $value;
                break;

            case 'BIRT':
                $record['birth'] = $this->parseEventSubRecords();
                break;

            case 'DEAT':
                $record['death'] = $this->parseEventSubRecords();
                break;

            case 'BAPM':
            case 'CHR':
                $record['baptism'] = $this->parseEventSubRecords();
                break;

            case 'CONF':
                $record['confirmation'] = $this->parseEventSubRecords();
                break;

            case 'MARR':
                $record['marriage'] = $this->parseEventSubRecords();
                break;

            case 'DIV':
                $record['divorce'] = $this->parseEventSubRecords();
                break;

            case 'OCCU':
                $record['occupation'] = $value;
                break;

            case 'EDUC':
                $record['education'] = $value;
                break;

            case 'EMIG':
                $record['emigration'] = $this->parseEventSubRecords();
                break;

            case 'IMMI':
                $record['immigration'] = $this->parseEventSubRecords();
                break;

            case 'RESI':
                $record['residence'][] = $this->parseEventSubRecords();
                break;

            case 'NOTE':
                $record['note'] = $this->parseContinuedText($value);
                break;

            case 'NICK':
                $record['nickname'] = $value;
                break;

            case 'HUSB':
                $record['husb'] = trim($value, '@');
                break;

            case 'WIFE':
                $record['wife'] = trim($value, '@');
                break;

            case 'CHIL':
                $record['children'][] = trim($value, '@');
                break;

            case 'FAMC':
                $record['family_child'] = trim($value, '@');
                break;

            case 'FAMS':
                $record['family_spouse'][] = trim($value, '@');
                break;

            case 'OBJE':
                // Puede ser referencia (@O001@) o inline
                if (str_starts_with($value, '@') && str_ends_with($value, '@')) {
                    // Referencia a objeto multimedia
                    $record['media_refs'][] = trim($value, '@');
                } else {
                    // OBJE inline - parsear sub-registros
                    $record['media_inline'][] = $this->parseMediaSubRecords();
                }
                break;

            case 'FILE':
                // FILE puede aparecer directamente bajo OBJE nivel 0
                $record['file'] = $value;
                // Buscar FORM subordinado
                if ($this->currentLine + 1 < count($this->lines)) {
                    $formLine = $this->parseLine($this->lines[$this->currentLine + 1]);
                    if ($formLine['tag'] === 'FORM') {
                        $record['form'] = $formLine['value'];
                        $this->currentLine++;
                    }
                }
                break;

            case 'TITL':
                // TITL puede aparecer bajo OBJE
                if (!isset($record['name'])) { // No sobrescribir NAME de INDI
                    $record['title'] = $value;
                }
                break;
        }
    }

    /**
     * Parsear sub-registros de objeto multimedia (OBJE inline).
     */
    protected function parseMediaSubRecords(): array
    {
        $media = [];
        $startLine = $this->currentLine;
        $startLevel = $this->parseLine($this->lines[$startLine])['level'];

        while ($this->currentLine + 1 < count($this->lines)) {
            $nextLine = $this->lines[$this->currentLine + 1];
            $nextParsed = $this->parseLine($nextLine);

            if ($nextParsed['level'] <= $startLevel) {
                break;
            }

            $this->currentLine++;

            switch ($nextParsed['tag']) {
                case 'FILE':
                    $media['file'] = $nextParsed['value'];
                    // Parsear FORM subordinado
                    if ($this->currentLine + 1 < count($this->lines)) {
                        $formLine = $this->parseLine($this->lines[$this->currentLine + 1]);
                        if ($formLine['tag'] === 'FORM' && $formLine['level'] > $nextParsed['level']) {
                            $media['form'] = $formLine['value'];
                            $this->currentLine++;
                        }
                    }
                    break;
                case 'TITL':
                    $media['title'] = $nextParsed['value'];
                    break;
                case 'NOTE':
                    $media['note'] = $this->parseContinuedText($nextParsed['value']);
                    break;
            }
        }

        return $media;
    }

    /**
     * Parsear sub-registros de evento.
     */
    protected function parseEventSubRecords(): array
    {
        $event = [];
        $startLine = $this->currentLine;
        $startLevel = $this->parseLine($this->lines[$startLine])['level'];

        while ($this->currentLine + 1 < count($this->lines)) {
            $nextLine = $this->lines[$this->currentLine + 1];
            $nextParsed = $this->parseLine($nextLine);

            if ($nextParsed['level'] <= $startLevel) {
                break;
            }

            $this->currentLine++;

            switch ($nextParsed['tag']) {
                case 'DATE':
                    $event['date'] = $nextParsed['value'];
                    break;
                case 'PLAC':
                    $event['place'] = $nextParsed['value'];
                    break;
                case 'NOTE':
                    $event['note'] = $this->parseContinuedText($nextParsed['value']);
                    break;
            }
        }

        return $event;
    }

    /**
     * Parsear texto continuado (CONT/CONC).
     */
    protected function parseContinuedText(string $initialValue): string
    {
        $text = $initialValue;

        while ($this->currentLine + 1 < count($this->lines)) {
            $nextLine = $this->lines[$this->currentLine + 1];
            $nextParsed = $this->parseLine($nextLine);

            if ($nextParsed['tag'] === 'CONT') {
                $text .= "\n" . $nextParsed['value'];
                $this->currentLine++;
            } elseif ($nextParsed['tag'] === 'CONC') {
                $text .= $nextParsed['value'];
                $this->currentLine++;
            } else {
                break;
            }
        }

        return $text;
    }

    /**
     * Parsear nombre GEDCOM.
     *
     * Formatos soportados:
     * - "FirstName /LastName/" - Estándar GEDCOM
     * - "FirstName /Paterno/ /Materno/" - Webtrees con dos apellidos
     * - "FirstName /Paterno/ /Materno/ //" - Webtrees con marcador vacío
     * - "FirstName /Paterno/ Materno //" - Variante sin segunda barra inicial
     */
    protected function parseGedcomName(?string $name): array
    {
        if (!$name) {
            return [
                'first_name' => null,
                'last_name' => null,
                'maiden_name' => null,
            ];
        }

        $firstName = '';
        $lastName = '';
        $maidenName = null;

        // Formato Webtrees: "FirstName /Paterno/ /Materno/ //" o "FirstName /Paterno/ /Materno/"
        // Captura: nombre, apellido paterno entre //, apellido materno entre //
        if (preg_match('/^([^\/]*)\s*\/([^\/]*)\/\s*\/([^\/]*)\//', $name, $matches)) {
            $firstName = trim($matches[1]);
            $lastName = trim($matches[2]);
            $maidenName = trim($matches[3]) ?: null;
        }
        // Formato Webtrees variante: "FirstName /Paterno/ Materno //"
        elseif (preg_match('/^([^\/]*)\s*\/([^\/]*)\/\s+([^\/]+)\s*\/\//', $name, $matches)) {
            $firstName = trim($matches[1]);
            $lastName = trim($matches[2]);
            $maidenName = trim($matches[3]) ?: null;
        }
        // Formato estándar: "FirstName /LastName/"
        elseif (preg_match('/^([^\/]*)\s*\/([^\/]*)\/$/', $name, $matches)) {
            $firstName = trim($matches[1]);
            $lastName = trim($matches[2]);
        }
        // Formato con algo después: "FirstName /LastName/ suffix"
        elseif (preg_match('/^([^\/]*)\s*\/([^\/]*)\/\s*(.*)$/', $name, $matches)) {
            $firstName = trim($matches[1]);
            $lastName = trim($matches[2]);
            // El tercer grupo podría ser suffix, lo ignoramos
        }
        // Sin barras, todo es nombre
        else {
            $firstName = trim($name);
        }

        return [
            'first_name' => $firstName ?: null,
            'last_name' => $lastName ?: null,
            'maiden_name' => $maidenName,
        ];
    }

    /**
     * Parsear fecha GEDCOM.
     */
    protected function parseGedcomDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        // Limpiar prefijos de aproximacion
        $date = preg_replace('/^(ABT|BEF|AFT|CAL|EST|INT)\s+/i', '', $date);

        // Meses GEDCOM
        $months = [
            'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
            'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
            'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12',
        ];

        // Formato: "1 JAN 1900" o "JAN 1900" o "1900"
        if (preg_match('/^(\d{1,2})\s+([A-Z]{3})\s+(\d{4})$/i', $date, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = $months[strtoupper($matches[2])] ?? '01';
            $year = $matches[3];
            return "{$year}-{$month}-{$day}";
        } elseif (preg_match('/^([A-Z]{3})\s+(\d{4})$/i', $date, $matches)) {
            $month = $months[strtoupper($matches[1])] ?? '01';
            $year = $matches[2];
            return "{$year}-{$month}-01";
        } elseif (preg_match('/^(\d{4})$/', $date, $matches)) {
            return "{$matches[1]}-01-01";
        }

        return null;
    }

    /**
     * Mapear genero.
     */
    protected function mapGender(string $sex): string
    {
        return match (strtoupper($sex)) {
            'M' => 'M',
            'F' => 'F',
            default => 'U',
        };
    }

    /**
     * Buscar persona duplicada.
     *
     * Criterios de duplicado (todos deben coincidir):
     * - Mismo nombre
     * - Mismo apellido paterno
     * - Mismo apellido materno (si ambos lo tienen)
     * - Misma fecha de nacimiento (si ambos la tienen)
     *
     * Si no hay suficiente información para distinguir, NO se considera duplicado
     * para evitar falsos positivos como padre/hijo con mismo nombre.
     */
    protected function findDuplicatePerson(array $data): ?Person
    {
        // Requerir al menos nombre y apellido paterno
        if (!$data['first_name'] || !$data['patronymic']) {
            return null;
        }

        $query = Person::query()
            ->where('first_name', $data['first_name'])
            ->where('patronymic', $data['patronymic']);

        // Si el nuevo registro tiene apellido materno, buscarlo también
        if (!empty($data['matronymic'])) {
            $query->where('matronymic', $data['matronymic']);
        } else {
            // Sin apellido materno, requerir fecha de nacimiento para evitar
            // confundir padre e hijo con el mismo nombre
            if (!$data['birth_date']) {
                return null; // No hay suficiente información para detectar duplicado
            }
        }

        // Si hay fecha de nacimiento, incluirla en la búsqueda
        if ($data['birth_date']) {
            $query->where('birth_date', $data['birth_date']);
        }

        return $query->first();
    }

    /**
     * Importar objetos multimedia desde GEDCOM.
     */
    protected function importMediaObjects(array $mediaObjects, array $options): void
    {
        $tempMediaPath = $options['temp_media_path'] ?? null;

        foreach ($mediaObjects as $gedcomId => $mediaData) {
            $filePath = $mediaData['file'] ?? null;
            if (!$filePath) {
                $this->stats['media_skipped']++;
                continue;
            }

            // Buscar archivo en directorio temporal
            $sourcePath = $this->findMediaFile($filePath, $tempMediaPath);

            if (!$sourcePath) {
                $this->warnings[] = __('Archivo multimedia no encontrado: :file', ['file' => $filePath]);
                $this->stats['media_skipped']++;
                continue;
            }

            // Determinar tipo de medio
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff']);
            $type = $isImage ? 'image' : 'document';
            $subdir = $isImage ? 'images' : 'documents';

            // Generar nombre único y copiar archivo a storage
            $newFileName = Str::random(40) . '.' . $ext;
            $destPath = 'media/' . $subdir . '/' . $newFileName;

            Storage::disk('public')->put($destPath, file_get_contents($sourcePath));

            // Crear registro Media
            $media = Media::create([
                'type' => $type,
                'title' => $mediaData['title'] ?? basename($filePath),
                'description' => $mediaData['note'] ?? null,
                'file_path' => $destPath,
                'file_name' => basename($filePath),
                'file_size' => filesize($sourcePath),
                'mime_type' => mime_content_type($sourcePath),
                'created_by' => Auth::id(),
            ]);

            $this->mediaMapping[$gedcomId] = $media->id;
            $this->stats['media_imported']++;
        }
    }

    /**
     * Buscar archivo multimedia en directorio temporal.
     */
    protected function findMediaFile(string $filePath, ?string $tempMediaPath): ?string
    {
        if (!$tempMediaPath || !is_dir($tempMediaPath)) {
            return null;
        }

        // Normalizar separadores de ruta
        $filePath = str_replace('\\', '/', $filePath);

        // Intentar varias ubicaciones posibles
        $searchPaths = [
            $tempMediaPath . '/' . $filePath,
            $tempMediaPath . '/media/' . $filePath,
            $tempMediaPath . '/' . basename($filePath),
            $tempMediaPath . '/media/' . basename($filePath),
        ];

        // Si la ruta empieza con media/, también buscar sin ese prefijo
        if (str_starts_with($filePath, 'media/')) {
            $withoutPrefix = substr($filePath, 6);
            $searchPaths[] = $tempMediaPath . '/' . $withoutPrefix;
        }

        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Búsqueda recursiva por nombre de archivo
        $filename = basename($filePath);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempMediaPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() === $filename) {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * Vincular medios importados a personas.
     */
    protected function linkMediaToPersons(array $individuals, array $options): void
    {
        foreach ($individuals as $gedcomId => $indi) {
            $personId = $this->idMapping['INDI'][$gedcomId] ?? null;
            if (!$personId) continue;

            $firstImagePath = null;

            // Vincular referencias por pointer (@O001@)
            foreach ($indi['media_refs'] ?? [] as $mediaRef) {
                $mediaId = $this->mediaMapping[$mediaRef] ?? null;
                if ($mediaId) {
                    $media = Media::find($mediaId);
                    if ($media) {
                        $media->update([
                            'mediable_type' => 'App\\Models\\Person',
                            'mediable_id' => $personId,
                        ]);
                        $this->stats['media_linked']++;

                        // Guardar primera imagen para foto de perfil
                        if (!$firstImagePath && $media->type === 'image') {
                            $firstImagePath = $media->file_path;
                        }
                    }
                }
            }

            // Procesar medios inline
            foreach ($indi['media_inline'] ?? [] as $inlineMedia) {
                $inlineImagePath = $this->importInlineMedia($inlineMedia, $personId, $options);
                if (!$firstImagePath && $inlineImagePath) {
                    $firstImagePath = $inlineImagePath;
                }
            }

            // Asignar primera imagen como foto de perfil si la persona no tiene una
            if ($firstImagePath) {
                $person = Person::find($personId);
                if ($person && !$person->photo_path) {
                    $person->update(['photo_path' => $firstImagePath]);
                }
            }
        }
    }

    /**
     * Importar un medio inline (definido directamente en INDI).
     *
     * @return string|null Ruta del archivo si es imagen, null si no
     */
    protected function importInlineMedia(array $mediaData, int $personId, array $options): ?string
    {
        $filePath = $mediaData['file'] ?? null;
        if (!$filePath) return null;

        $tempMediaPath = $options['temp_media_path'] ?? null;
        $sourcePath = $this->findMediaFile($filePath, $tempMediaPath);

        if (!$sourcePath) {
            $this->warnings[] = __('Archivo multimedia inline no encontrado: :file', ['file' => $filePath]);
            $this->stats['media_skipped']++;
            return null;
        }

        // Determinar tipo
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff']);
        $type = $isImage ? 'image' : 'document';
        $subdir = $isImage ? 'images' : 'documents';

        // Copiar archivo
        $newFileName = Str::random(40) . '.' . $ext;
        $destPath = 'media/' . $subdir . '/' . $newFileName;

        Storage::disk('public')->put($destPath, file_get_contents($sourcePath));

        // Crear registro vinculado directamente a la persona
        Media::create([
            'mediable_type' => 'App\\Models\\Person',
            'mediable_id' => $personId,
            'type' => $type,
            'title' => $mediaData['title'] ?? basename($filePath),
            'description' => $mediaData['note'] ?? null,
            'file_path' => $destPath,
            'file_name' => basename($filePath),
            'file_size' => filesize($sourcePath),
            'mime_type' => mime_content_type($sourcePath),
            'created_by' => Auth::id(),
        ]);

        $this->stats['media_imported']++;
        $this->stats['media_linked']++;

        // Retornar ruta si es imagen (para asignar como foto de perfil)
        return $isImage ? $destPath : null;
    }
}
