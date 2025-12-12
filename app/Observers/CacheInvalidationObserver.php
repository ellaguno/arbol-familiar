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
            $this->clearTreeCachesForPerson($model->id);
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
            // Limpiar caches de ambos conyuges
            if ($model->husband_id) {
                $this->clearTreeCachesForPerson($model->husband_id);
                Cache::forget("dashboard_family_{$model->husband_id}");
            }
            if ($model->wife_id) {
                $this->clearTreeCachesForPerson($model->wife_id);
                Cache::forget("dashboard_family_{$model->wife_id}");
            }
            if ($model->created_by) {
                Cache::forget("dashboard_stats_{$model->created_by}");
            }
            Cache::forget('search_suggestions');
        }

        if ($class === \App\Models\FamilyChild::class) {
            // Al agregar/quitar un hijo, limpiar caches del hijo y de los padres
            $this->clearTreeCachesForPerson($model->person_id);

            // Limpiar caches de los padres (conyuges de la familia)
            $family = \App\Models\Family::find($model->family_id);
            if ($family) {
                if ($family->husband_id) {
                    $this->clearTreeCachesForPerson($family->husband_id);
                    Cache::forget("dashboard_family_{$family->husband_id}");
                }
                if ($family->wife_id) {
                    $this->clearTreeCachesForPerson($family->wife_id);
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
     * Limpia todos los caches del arbol para una persona.
     * Cubre todas las combinaciones de generaciones y direcciones.
     */
    protected function clearTreeCachesForPerson(int $personId): void
    {
        foreach ([2, 3, 4, 5] as $gen) {
            foreach (['both', 'ancestors', 'descendants'] as $dir) {
                Cache::forget("tree_data_{$personId}_{$gen}_{$dir}");
            }
            Cache::forget("tree_fan_{$personId}_{$gen}");
        }
    }
}
