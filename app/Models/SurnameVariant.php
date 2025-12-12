<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurnameVariant extends Model
{
    protected $table = 'surname_variants';

    public $timestamps = false;

    protected $fillable = [
        'person_id',
        'original_surname',
        'variant_1',
        'variant_2',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * La persona a la que pertenece esta variante.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Obtiene todas las variantes como array.
     */
    public function getAllVariantsAttribute(): array
    {
        return array_filter([
            $this->original_surname,
            $this->variant_1,
            $this->variant_2,
        ]);
    }

    /**
     * Verifica si un apellido coincide con alguna variante.
     */
    public function matches(string $surname): bool
    {
        $surname = mb_strtolower($surname);

        return mb_strtolower($this->original_surname) === $surname
            || ($this->variant_1 && mb_strtolower($this->variant_1) === $surname)
            || ($this->variant_2 && mb_strtolower($this->variant_2) === $surname);
    }

    /**
     * Scope para buscar por cualquier variante.
     */
    public function scopeSearchVariant($query, string $surname)
    {
        return $query->where(function ($q) use ($surname) {
            $q->where('original_surname', 'LIKE', "%{$surname}%")
              ->orWhere('variant_1', 'LIKE', "%{$surname}%")
              ->orWhere('variant_2', 'LIKE', "%{$surname}%");
        });
    }
}
