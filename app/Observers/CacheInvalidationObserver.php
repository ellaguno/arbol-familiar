<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CacheInvalidationObserver
{
    public function saved(Model $model): void
    {
        $this->clearRelatedCaches($model);
    }

    public function deleted(Model $model): void
    {
        $this->clearRelatedCaches($model);
    }

    protected function clearRelatedCaches(Model $model): void
    {
        $class = get_class($model);

        if ($class === \App\Models\Person::class) {
            // Limpiar caché de esta persona y toda su línea directa
            $this->clearTreeCachesRecursive($model->id);
            Cache::forget("dashboard_family_{$model->id}");

            // Limpiar dashboard del creador
            if ($model->created_by) {
                Cache::forget("dashboard_stats_{$model->created_by}");
            }
            if ($model->user_id) {
                Cache::forget("dashboard_stats_{$model->user_id}");
            }

            // Limpiar sugerencias de busqueda
            Cache::forget('search_suggestions');
        }

        if ($class === \App\Models\Family::class) {
            // Limpiar caches de ambos conyuges y sus líneas
            if ($model->husband_id) {
                $this->clearTreeCachesRecursive($model->husband_id);
                Cache::forget("dashboard_family_{$model->husband_id}");
            }
            if ($model->wife_id) {
                $this->clearTreeCachesRecursive($model->wife_id);
                Cache::forget("dashboard_family_{$model->wife_id}");
            }
            if ($model->created_by) {
                Cache::forget("dashboard_stats_{$model->created_by}");
            }
            Cache::forget('search_suggestions');
        }

        if ($class === \App\Models\FamilyChild::class) {
            // Al agregar/quitar un hijo, limpiar caches del hijo y toda la cadena
            $this->clearTreeCachesRecursive($model->person_id);

            // Limpiar caches de los padres y sus ancestros
            $family = \App\Models\Family::find($model->family_id);
            if ($family) {
                if ($family->husband_id) {
                    $this->clearTreeCachesRecursive($family->husband_id);
                    Cache::forget("dashboard_family_{$family->husband_id}");
                }
                if ($family->wife_id) {
                    $this->clearTreeCachesRecursive($family->wife_id);
                    Cache::forget("dashboard_family_{$family->wife_id}");
                }
            }
            Cache::forget('search_suggestions');
        }

        if ($class === \App\Models\Media::class) {
            if ($model->created_by) {
                Cache::forget("dashboard_stats_{$model->created_by}");
            }
        }
    }

    /**
     * Limpia los caches del arbol para una persona y toda su línea directa.
     * Recorre ancestros hacia arriba y descendientes hacia abajo para
     * asegurar que todos los árboles que incluyen a esta persona se actualicen.
     */
    protected function clearTreeCachesRecursive(int $personId): void
    {
        $cleared = [];
        $this->clearAncestorCaches($personId, $cleared, 10);
        $this->clearDescendantCaches($personId, $cleared, 10);
    }

    /**
     * Limpia caches subiendo por los ancestros.
     */
    protected function clearAncestorCaches(int $personId, array &$cleared, int $maxDepth): void
    {
        if ($maxDepth <= 0 || in_array($personId, $cleared)) {
            return;
        }

        $this->clearTreeCachesForPerson($personId);
        Cache::forget("dashboard_family_{$personId}");
        $cleared[] = $personId;

        // Subir a los padres
        $person = \App\Models\Person::find($personId);
        if (!$person) {
            return;
        }

        $family = $person->familiesAsChild()->first();
        if ($family) {
            if ($family->husband_id && !in_array($family->husband_id, $cleared)) {
                $this->clearAncestorCaches($family->husband_id, $cleared, $maxDepth - 1);
            }
            if ($family->wife_id && !in_array($family->wife_id, $cleared)) {
                $this->clearAncestorCaches($family->wife_id, $cleared, $maxDepth - 1);
            }
        }
    }

    /**
     * Limpia caches bajando por los descendientes.
     */
    protected function clearDescendantCaches(int $personId, array &$cleared, int $maxDepth): void
    {
        if ($maxDepth <= 0 || in_array($personId, $cleared)) {
            return;
        }

        $this->clearTreeCachesForPerson($personId);
        Cache::forget("dashboard_family_{$personId}");
        $cleared[] = $personId;

        // Bajar a los hijos
        $familyIds = \App\Models\Family::where('husband_id', $personId)
            ->orWhere('wife_id', $personId)
            ->pluck('id');

        $childIds = \App\Models\FamilyChild::whereIn('family_id', $familyIds)
            ->pluck('person_id');

        foreach ($childIds as $childId) {
            if (!in_array($childId, $cleared)) {
                $this->clearDescendantCaches($childId, $cleared, $maxDepth - 1);
            }
        }
    }

    /**
     * Limpia todos los caches del arbol para una persona.
     * Cubre todas las combinaciones de generaciones y direcciones.
     */
    protected function clearTreeCachesForPerson(int $personId): void
    {
        foreach (range(1, 10) as $gen) {
            foreach (['both', 'ancestors', 'descendants'] as $dir) {
                Cache::forget("tree_data_{$personId}_{$gen}_{$dir}");
            }
            Cache::forget("tree_fan_{$personId}_{$gen}");
        }
    }
}
