<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Family;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class GedcomExporter
{
    protected array $output = [];
    protected array $personIds = [];
    protected array $familyIds = [];
    protected array $options = [];

    /**
     * Exportar a formato GEDCOM.
     */
    public function export(array $options = []): string
    {
        $this->output = [];
        $this->personIds = [];
        $this->familyIds = [];
        $this->options = array_merge([
            'include_living' => true,
            'include_notes' => true,
            'include_events' => true,
            'person_ids' => null, // null = todos, array = IDs especificos
            'start_person_id' => null, // Persona raiz para exportar arbol
            'generations' => null, // null = todos
        ], $options);

        // Header
        $this->writeHeader();

        // Obtener personas a exportar
        $persons = $this->getPersonsToExport();

        // Obtener familias relacionadas
        $families = $this->getFamiliesToExport($persons);

        // Escribir individuos
        foreach ($persons as $person) {
            $this->writeIndividual($person);
        }

        // Escribir familias
        foreach ($families as $family) {
            $this->writeFamily($family);
        }

        // Footer
        $this->writeFooter();

        return implode("\n", $this->output);
    }

    /**
     * Obtener personas a exportar.
     */
    protected function getPersonsToExport(): Collection
    {
        $query = Person::with(['events', 'familiesAsChild', 'familiesAsHusband', 'familiesAsWife']);

        // Filtrar por IDs especificos
        if (!empty($this->options['person_ids'])) {
            $query->whereIn('id', $this->options['person_ids']);
        }

        // Si hay persona raiz, obtener arbol
        if ($this->options['start_person_id']) {
            $startPerson = Person::find($this->options['start_person_id']);
            if ($startPerson) {
                $ids = $this->collectTreePersonIds($startPerson, $this->options['generations'] ?? 10);
                $query->whereIn('id', $ids);
            }
        }

        // Excluir personas vivas si no se incluyen
        if (!$this->options['include_living']) {
            $query->where('is_living', false);
        }

        $persons = $query->get();

        // Guardar IDs
        $this->personIds = $persons->pluck('id')->toArray();

        return $persons;
    }

    /**
     * Recolectar IDs de personas en el arbol.
     * Usa la estructura families + family_children para encontrar relaciones.
     */
    protected function collectTreePersonIds(Person $person, int $maxGenerations, int $currentGen = 0): array
    {
        if ($currentGen >= $maxGenerations) {
            return [$person->id];
        }

        $ids = [$person->id];

        // Ancestros: buscar familias donde esta persona es hijo
        $familiesAsChild = $person->familiesAsChild()->with(['husband', 'wife'])->get();

        foreach ($familiesAsChild as $family) {
            // Padre (husband)
            if ($family->husband) {
                $ids = array_merge($ids, $this->collectTreePersonIds($family->husband, $maxGenerations, $currentGen + 1));
            }
            // Madre (wife)
            if ($family->wife) {
                $ids = array_merge($ids, $this->collectTreePersonIds($family->wife, $maxGenerations, $currentGen + 1));
            }
        }

        // Descendientes (solo si estamos en generacion 0)
        if ($currentGen === 0) {
            $children = $this->getChildren($person);
            foreach ($children as $child) {
                $ids = array_merge($ids, $this->collectDescendantIds($child, $maxGenerations, 1));
            }
        }

        return array_unique($ids);
    }

    /**
     * Recolectar IDs de descendientes.
     */
    protected function collectDescendantIds(Person $person, int $maxGenerations, int $currentGen): array
    {
        if ($currentGen >= $maxGenerations) {
            return [$person->id];
        }

        $ids = [$person->id];

        $children = $this->getChildren($person);

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->collectDescendantIds($child, $maxGenerations, $currentGen + 1));
        }

        return array_unique($ids);
    }

    /**
     * Obtener hijos de una persona.
     * Busca en familias donde es husband o wife y obtiene los children.
     */
    protected function getChildren(Person $person): Collection
    {
        $childIds = [];

        // Familias donde es esposo
        $familiesAsHusband = Family::where('husband_id', $person->id)->with('children')->get();
        foreach ($familiesAsHusband as $family) {
            foreach ($family->children as $child) {
                $childIds[] = $child->id;
            }
        }

        // Familias donde es esposa
        $familiesAsWife = Family::where('wife_id', $person->id)->with('children')->get();
        foreach ($familiesAsWife as $family) {
            foreach ($family->children as $child) {
                $childIds[] = $child->id;
            }
        }

        $childIds = array_unique($childIds);

        return Person::whereIn('id', $childIds)->get();
    }

    /**
     * Obtener familias a exportar.
     */
    protected function getFamiliesToExport(Collection $persons): Collection
    {
        $personIds = $persons->pluck('id')->toArray();

        $families = Family::where(function ($query) use ($personIds) {
            $query->whereIn('husband_id', $personIds)
                  ->orWhereIn('wife_id', $personIds);
        })->with(['husband', 'wife', 'children', 'events'])->get();

        // Guardar IDs
        $this->familyIds = $families->pluck('id')->toArray();

        return $families;
    }

    /**
     * Escribir header GEDCOM.
     */
    protected function writeHeader(): void
    {
        $this->output[] = '0 HEAD';
        $this->output[] = '1 SOUR MI_FAMILIA';
        $this->output[] = '2 NAME Mi Familia';
        $this->output[] = '2 VERS 1.0';
        $this->output[] = '1 DEST ANY';
        $this->output[] = '1 DATE ' . date('d M Y');
        $this->output[] = '2 TIME ' . date('H:i:s');
        $this->output[] = '1 SUBM @SUBM1@';
        $this->output[] = '1 GEDC';
        $this->output[] = '2 VERS 5.5.1';
        $this->output[] = '2 FORM LINEAGE-LINKED';
        $this->output[] = '1 CHAR UTF-8';
        $this->output[] = '1 LANG Spanish';

        // Submitter
        $this->output[] = '0 @SUBM1@ SUBM';
        $this->output[] = '1 NAME ' . (Auth::user()->full_name ?? 'Unknown');
    }

    /**
     * Escribir footer GEDCOM.
     */
    protected function writeFooter(): void
    {
        $this->output[] = '0 TRLR';
    }

    /**
     * Escribir individuo.
     */
    protected function writeIndividual(Person $person): void
    {
        $id = 'I' . $person->id;

        $this->output[] = "0 @{$id}@ INDI";

        // Nombre
        $name = $this->formatGedcomName($person);
        $this->output[] = "1 NAME {$name}";

        // Sexo
        $this->output[] = '1 SEX ' . ($person->gender === 'U' ? 'U' : $person->gender);

        // Nacimiento
        if ($person->birth_date || $person->birth_place) {
            $this->output[] = '1 BIRT';
            if ($person->birth_date) {
                $this->output[] = '2 DATE ' . $this->formatGedcomDate($person->birth_date);
            }
            if ($person->birth_place) {
                $this->output[] = '2 PLAC ' . $person->birth_place;
            }
        }

        // Muerte
        if (!$person->is_living) {
            $this->output[] = '1 DEAT';
            if ($person->death_date) {
                $this->output[] = '2 DATE ' . $this->formatGedcomDate($person->death_date);
            }
            if ($person->death_place) {
                $this->output[] = '2 PLAC ' . $person->death_place;
            }
        }

        // Apodo
        if ($person->nickname) {
            $this->output[] = '1 NICK ' . $person->nickname;
        }

        // Eventos adicionales
        if ($this->options['include_events']) {
            $this->writePersonEvents($person);
        }

        // Notas/Biografia
        if ($this->options['include_notes'] && $person->biography) {
            $this->writeNote($person->biography);
        }

        // Enlaces a familias como hijo
        $familiesAsChild = Family::where(function ($query) use ($person) {
            $query->whereHas('children', function ($q) use ($person) {
                $q->where('person_id', $person->id);
            });
        })->get();

        foreach ($familiesAsChild as $family) {
            $this->output[] = '1 FAMC @F' . $family->id . '@';
        }

        // Enlaces a familias como esposo/a
        $familiesAsSpouse = Family::where('husband_id', $person->id)
            ->orWhere('wife_id', $person->id)
            ->get();

        foreach ($familiesAsSpouse as $family) {
            $this->output[] = '1 FAMS @F' . $family->id . '@';
        }
    }

    /**
     * Escribir eventos de persona.
     */
    protected function writePersonEvents(Person $person): void
    {
        $events = $person->events ?? [];

        foreach ($events as $event) {
            $tag = $this->mapEventType($event->type);
            if (!$tag) continue;

            $this->output[] = "1 {$tag}";

            if ($event->date) {
                $this->output[] = '2 DATE ' . $this->formatGedcomDate($event->date);
            }
            if ($event->place) {
                $this->output[] = '2 PLAC ' . $event->place;
            }
            if ($this->options['include_notes'] && $event->description) {
                $this->output[] = '2 NOTE ' . $this->sanitizeText($event->description);
            }
        }
    }

    /**
     * Escribir familia.
     */
    protected function writeFamily(Family $family): void
    {
        $id = 'F' . $family->id;

        $this->output[] = "0 @{$id}@ FAM";

        // Esposo
        if ($family->husband_id && in_array($family->husband_id, $this->personIds)) {
            $this->output[] = '1 HUSB @I' . $family->husband_id . '@';
        }

        // Esposa
        if ($family->wife_id && in_array($family->wife_id, $this->personIds)) {
            $this->output[] = '1 WIFE @I' . $family->wife_id . '@';
        }

        // Matrimonio
        if ($family->marriage_date || $family->marriage_place) {
            $this->output[] = '1 MARR';
            if ($family->marriage_date) {
                $this->output[] = '2 DATE ' . $this->formatGedcomDate($family->marriage_date);
            }
            if ($family->marriage_place) {
                $this->output[] = '2 PLAC ' . $family->marriage_place;
            }
        }

        // Divorcio
        if ($family->status === 'divorced') {
            $this->output[] = '1 DIV';
            if ($family->divorce_date) {
                $this->output[] = '2 DATE ' . $this->formatGedcomDate($family->divorce_date);
            }
        }

        // Hijos
        foreach ($family->children as $child) {
            if (in_array($child->id, $this->personIds)) {
                $this->output[] = '1 CHIL @I' . $child->id . '@';
            }
        }
    }

    /**
     * Escribir nota.
     */
    protected function writeNote(string $text): void
    {
        $lines = explode("\n", $this->sanitizeText($text));
        $first = true;

        foreach ($lines as $line) {
            if ($first) {
                $this->output[] = '1 NOTE ' . $line;
                $first = false;
            } else {
                $this->output[] = '2 CONT ' . $line;
            }
        }
    }

    /**
     * Formatear nombre para GEDCOM.
     */
    protected function formatGedcomName(Person $person): string
    {
        $firstName = $person->first_name ?? '';
        $lastName = $person->patronymic ?? '';

        return "{$firstName} /{$lastName}/";
    }

    /**
     * Formatear fecha para GEDCOM.
     */
    protected function formatGedcomDate($date): string
    {
        if (!$date) return '';

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        $months = [
            1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR',
            5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AUG',
            9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC',
        ];

        return $date->day . ' ' . $months[$date->month] . ' ' . $date->year;
    }

    /**
     * Mapear tipo de evento a tag GEDCOM.
     */
    protected function mapEventType(string $type): ?string
    {
        $map = [
            'BIRT' => 'BIRT',
            'DEAT' => 'DEAT',
            'BAPM' => 'BAPM',
            'CHR' => 'CHR',
            'CONF' => 'CONF',
            'MARR' => 'MARR',
            'DIV' => 'DIV',
            'BURI' => 'BURI',
            'CREM' => 'CREM',
            'OCCU' => 'OCCU',
            'EDUC' => 'EDUC',
            'RESI' => 'RESI',
            'EMIG' => 'EMIG',
            'IMMI' => 'IMMI',
            'CENS' => 'CENS',
            'PROB' => 'PROB',
            'WILL' => 'WILL',
            'GRAD' => 'GRAD',
            'RETI' => 'RETI',
            'EVEN' => 'EVEN',
        ];

        return $map[$type] ?? null;
    }

    /**
     * Sanitizar texto para GEDCOM.
     */
    protected function sanitizeText(string $text): string
    {
        // Remover caracteres de control
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);

        // Limitar longitud de linea (GEDCOM recomienda max 255 caracteres)
        // Por ahora solo sanitizamos, la division en CONT se hace al escribir

        return $text;
    }

    /**
     * Obtener estadisticas de la exportacion.
     */
    public function getStats(): array
    {
        return [
            'persons' => count($this->personIds),
            'families' => count($this->familyIds),
            'lines' => count($this->output),
        ];
    }
}
