<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Family;
use App\Models\FamilyChild;
use App\Models\Media;
use App\Models\Person;
use Illuminate\Support\Facades\Storage;

/**
 * Lógica de dominio de parentesco: construcción de relaciones familiares,
 * detección de parentesco y fusión de personas. Extraído de PersonController
 * para separar el dominio de la capa HTTP y poder reutilizarlo (p. ej. desde
 * MessageController al aceptar solicitudes) sin instanciar un controlador.
 */
class RelationshipService
{
    /**
     * Verifica si el menor tiene padre o madre con cuenta registrada.
     */
    public function minorHasRegisteredParents(Person $minor): bool
    {
        $family = $minor->familiesAsChild()->first();
        if (!$family) {
            return false;
        }

        // Verificar si el padre o la madre tienen user_id (cuenta registrada)
        if ($family->husband_id) {
            $father = Person::find($family->husband_id);
            if ($father && $father->user_id) {
                return true;
            }
        }

        if ($family->wife_id) {
            $mother = Person::find($family->wife_id);
            if ($mother && $mother->user_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica si un person_id es padre/madre de la persona dada.
     */
    public function isParentOf(int $personId, Person $child): bool
    {
        $family = $child->familiesAsChild()->first();
        if (!$family) {
            return false;
        }

        return $family->husband_id === $personId || $family->wife_id === $personId;
    }

    /**
     * Agrega relacion de conyuge.
     * Verifica que no exista ya una familia con los mismos conyuges para evitar duplicados.
     */
    public function addSpouseRelationship(Person $person, Person $related, array $data): void
    {
        // Determinar quien es esposo/esposa basado en genero
        $husband = $person->gender === 'M' ? $person : $related;
        $wife = $person->gender === 'M' ? $related : $person;

        // Si ambos son del mismo genero, usar el orden de parametros
        if ($person->gender === $related->gender) {
            $husband = $person;
            $wife = $related;
        }

        // Verificar que no exista ya una familia con estos conyuges
        $existingFamily = Family::where(function ($q) use ($husband, $wife) {
            $q->where(function ($inner) use ($husband, $wife) {
                $inner->where('husband_id', $husband->id)
                      ->where('wife_id', $wife->id);
            })->orWhere(function ($inner) use ($husband, $wife) {
                $inner->where('husband_id', $wife->id)
                      ->where('wife_id', $husband->id);
            });
        })->first();

        if ($existingFamily) {
            // Si ya existe, actualizar datos si se proporcionaron
            $updateData = [];
            if (!empty($data['marriage_date']) && !$existingFamily->marriage_date) {
                $updateData['marriage_date'] = $data['marriage_date'];
            }
            if (!empty($data['family_status'])) {
                $updateData['status'] = $data['family_status'];
            }
            if (!empty($updateData)) {
                $existingFamily->update($updateData);
            }
            return;
        }

        // Verificar si alguno tiene una familia sin conyuge (familia incompleta)
        $incompleteFamilyHusband = Family::where('husband_id', $husband->id)
            ->whereNull('wife_id')
            ->first();

        if ($incompleteFamilyHusband) {
            $incompleteFamilyHusband->update([
                'wife_id' => $wife->id,
                'marriage_date' => $data['marriage_date'] ?? $incompleteFamilyHusband->marriage_date,
                'status' => $data['family_status'] ?? $incompleteFamilyHusband->status,
            ]);
            return;
        }

        $incompleteFamilyWife = Family::where('wife_id', $wife->id)
            ->whereNull('husband_id')
            ->first();

        if ($incompleteFamilyWife) {
            $incompleteFamilyWife->update([
                'husband_id' => $husband->id,
                'marriage_date' => $data['marriage_date'] ?? $incompleteFamilyWife->marriage_date,
                'status' => $data['family_status'] ?? $incompleteFamilyWife->status,
            ]);
            return;
        }

        Family::create([
            'husband_id' => $husband->id,
            'wife_id' => $wife->id,
            'marriage_date' => $data['marriage_date'] ?? null,
            'status' => $data['family_status'] ?? 'married',
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Agrega relacion de padre/madre.
     *
     * @param Person $child La persona que sera el hijo
     * @param Person $parent La persona que sera el padre/madre
     * @param string|null $forceGender Si se especifica ('M' o 'F'), usa este genero en lugar del genero de la persona
     */
    public function addParentRelationship(Person $child, Person $parent, ?string $forceGender = null): void
    {
        // Usar el genero forzado si se proporciona, si no usar el de la persona
        $gender = $forceGender ?? $parent->gender;

        // Buscar si ya existe una familia para el hijo
        $family = $child->familiesAsChild()->first();

        if ($family) {
            // Agregar al padre/madre existente
            if ($gender === 'M' && !$family->husband_id) {
                $family->update(['husband_id' => $parent->id]);
            } elseif ($gender === 'F' && !$family->wife_id) {
                $family->update(['wife_id' => $parent->id]);
            }
        } else {
            // Crear nueva familia
            $family = Family::create([
                'husband_id' => $gender === 'M' ? $parent->id : null,
                'wife_id' => $gender === 'F' ? $parent->id : null,
                'status' => 'married',
                'created_by' => auth()->id(),
            ]);

            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $child->id,
                'relationship_type' => 'biological',
            ]);
        }
    }

    /**
     * Agrega relacion de hijo.
     */
    public function addChildRelationship(Person $parent, Person $child, ?int $familyId = null): void
    {
        // Si se especifica una familia, usarla
        if ($familyId) {
            $family = Family::find($familyId);
        }

        // Si no hay familia especificada o no se encontro, buscar una existente
        if (!isset($family) || !$family) {
            $family = Family::where(function ($q) use ($parent) {
                $q->where('husband_id', $parent->id)->orWhere('wife_id', $parent->id);
            })->first();
        }

        if (!$family) {
            // Crear nueva familia
            $family = Family::create([
                'husband_id' => $parent->gender === 'M' ? $parent->id : null,
                'wife_id' => $parent->gender === 'F' ? $parent->id : null,
                'status' => 'married',
                'created_by' => auth()->id(),
            ]);
        }

        // Agregar hijo si no existe
        if (!FamilyChild::where('family_id', $family->id)->where('person_id', $child->id)->exists()) {
            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $child->id,
                'relationship_type' => 'biological',
            ]);
        }
    }

    /**
     * Agrega relacion de hermano.
     */
    public function addSiblingRelationship(Person $person, Person $sibling): void
    {
        // Buscar familia del primer hermano
        $family = $person->familiesAsChild()->first();

        if ($family) {
            // Agregar hermano a la misma familia
            if (!FamilyChild::where('family_id', $family->id)->where('person_id', $sibling->id)->exists()) {
                FamilyChild::create([
                    'family_id' => $family->id,
                    'person_id' => $sibling->id,
                    'relationship_type' => 'biological',
                ]);
            }
        } else {
            // Crear familia con ambos como hijos
            $family = Family::create([
                'status' => 'married',
                'created_by' => auth()->id(),
            ]);

            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $person->id,
                'relationship_type' => 'biological',
            ]);

            FamilyChild::create([
                'family_id' => $family->id,
                'person_id' => $sibling->id,
                'relationship_type' => 'biological',
            ]);
        }
    }

    /**
     * Verifica si dos personas ya estan relacionadas (padre/madre, hijo, hermano o conyuge).
     */
    public function arePersonsRelated(Person $person1, Person $person2): bool
    {
        // Verificar si person2 es padre de person1
        if ($person1->father && $person1->father->id === $person2->id) return true;
        if ($person1->mother && $person1->mother->id === $person2->id) return true;

        // Verificar si person2 es hijo de person1
        if ($person1->children->contains('id', $person2->id)) return true;

        // Verificar si son hermanos
        if ($person1->siblings->contains('id', $person2->id)) return true;

        // Verificar si son cónyuges
        if ($person1->spouses->contains('id', $person2->id)) return true;

        return false;
    }

    /**
     * Determina el tipo de parentesco de $related respecto a $person.
     *
     * @return array{type: string, label: string}
     */
    public function determineRelationship(Person $person, Person $related): array
    {
        // Verificar si es padre
        if ($person->father && $person->father->id === $related->id) {
            return ['type' => 'father', 'label' => __('padre')];
        }

        // Verificar si es madre
        if ($person->mother && $person->mother->id === $related->id) {
            return ['type' => 'mother', 'label' => __('madre')];
        }

        // Verificar si es cónyuge
        if ($person->currentSpouse && $person->currentSpouse->id === $related->id) {
            return ['type' => 'spouse', 'label' => __('cónyuge')];
        }

        // Verificar si es hermano/a
        foreach ($person->siblings as $sibling) {
            if ($sibling->id === $related->id) {
                return ['type' => 'sibling', 'label' => $related->gender === 'F' ? __('hermana') : __('hermano')];
            }
        }

        // Verificar si es hijo/a
        foreach ($person->children as $child) {
            if ($child->id === $related->id) {
                return ['type' => 'child', 'label' => $related->gender === 'F' ? __('hija') : __('hijo')];
            }
        }

        return ['type' => 'other', 'label' => __('familiar')];
    }

    /**
     * Heuristica de similitud de nombres para sugerir posibles duplicados.
     */
    public function areNamesSimilar(Person $person1, Person $person2): bool
    {
        // Comparar nombres (case insensitive)
        $name1 = mb_strtolower(trim($person1->first_name));
        $name2 = mb_strtolower(trim($person2->first_name));

        // Nombres iguales
        if ($name1 === $name2) {
            return true;
        }

        // Similitud de Levenshtein (tolerancia de 2 caracteres)
        if (levenshtein($name1, $name2) <= 2) {
            return true;
        }

        // Uno contiene al otro (para nombres compuestos)
        if (str_contains($name1, $name2) || str_contains($name2, $name1)) {
            return true;
        }

        // Comparar apellidos
        $surname1 = mb_strtolower(trim($person1->patronymic));
        $surname2 = mb_strtolower(trim($person2->patronymic));

        if ($surname1 !== $surname2 && levenshtein($surname1, $surname2) > 2) {
            return false;
        }

        return false;
    }

    /**
     * Ejecuta la fusion de dos personas.
     * La persona $source se fusiona EN $target (target conserva su ID).
     */
    public function mergePersons(Person $source, Person $target): array
    {
        $merged = [
            'relationships' => 0,
            'media' => 0,
            'events' => 0,
            'fields' => [],
        ];

        // 1. Transferir relaciones familiares donde source es padre/madre
        $familiesAsHusband = Family::where('husband_id', $source->id)->get();
        foreach ($familiesAsHusband as $family) {
            // Verificar que target no sea ya el esposo de esa familia
            if ($family->husband_id !== $target->id) {
                $family->update(['husband_id' => $target->id]);
                $merged['relationships']++;
            }
        }

        $familiesAsWife = Family::where('wife_id', $source->id)->get();
        foreach ($familiesAsWife as $family) {
            if ($family->wife_id !== $target->id) {
                $family->update(['wife_id' => $target->id]);
                $merged['relationships']++;
            }
        }

        // 2. Transferir relaciones como hijo
        $familyChildren = FamilyChild::where('person_id', $source->id)->get();
        foreach ($familyChildren as $fc) {
            // Verificar que target no sea ya hijo de esa familia
            $exists = FamilyChild::where('family_id', $fc->family_id)
                ->where('person_id', $target->id)
                ->exists();
            if (!$exists) {
                $fc->update(['person_id' => $target->id]);
                $merged['relationships']++;
            } else {
                $fc->delete(); // Eliminar duplicado
            }
        }

        // 3. Transferir media (usa relacion polimorfica mediable_type/mediable_id)
        if (class_exists(Media::class)) {
            $mediaCount = Media::where('mediable_type', 'App\\Models\\Person')
                ->where('mediable_id', $source->id)
                ->update(['mediable_id' => $target->id]);
            $merged['media'] = $mediaCount;
        }

        // 4. Transferir eventos
        if (class_exists(Event::class)) {
            // Verificar si la tabla events tiene person_id o usa polimorfico
            try {
                $eventCount = Event::where('person_id', $source->id)
                    ->update(['person_id' => $target->id]);
                $merged['events'] = $eventCount;
            } catch (\Exception $e) {
                // Si falla, ignorar - la tabla puede tener estructura diferente
                $merged['events'] = 0;
            }
        }

        // 5. Completar campos vacios en target con datos de source
        $fieldsToMerge = [
            'nickname', 'birth_date', 'birth_place', 'birth_country',
            'death_date', 'death_place', 'death_country',
            'residence_place', 'residence_country', 'occupation',
            'email', 'phone', 'heritage_region', 'origin_town',
            'migration_decade', 'migration_destination', 'notes',
        ];

        foreach ($fieldsToMerge as $field) {
            if (empty($target->$field) && !empty($source->$field)) {
                $target->$field = $source->$field;
                $merged['fields'][] = $field;
            }
        }

        // Si source tiene foto y target no, transferir (con su thumbnail)
        if (empty($target->photo_path) && !empty($source->photo_path)) {
            $target->photo_path = $source->photo_path;
            $target->photo_thumbnail_path = $source->photo_thumbnail_path;
            $merged['fields'][] = 'photo_path';
        }

        // Si source tiene herencia etnica y target no
        if (!$target->has_ethnic_heritage && $source->has_ethnic_heritage) {
            $target->has_ethnic_heritage = true;
            $merged['fields'][] = 'has_ethnic_heritage';
        }

        $target->save();

        // 6. Eliminar persona source (ya transferimos todo)
        // Limpiar foto de source si target ya la tiene
        if ($source->photo_path && $source->photo_path !== $target->photo_path) {
            Storage::disk('public')->delete($source->photo_path);
        }

        $source->delete();

        return $merged;
    }
}
