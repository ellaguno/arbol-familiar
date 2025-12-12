<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'gedcom_id',
        'husband_id',
        'wife_id',
        'marriage_date',
        'marriage_date_approx',
        'marriage_place',
        'divorce_date',
        'divorce_place',
        'status',
        'created_by',
    ];

    protected $casts = [
        'marriage_date' => 'date',
        'divorce_date' => 'date',
        'marriage_date_approx' => 'boolean',
    ];

    /**
     * El esposo en esta familia.
     */
    public function husband(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'husband_id');
    }

    /**
     * La esposa en esta familia.
     */
    public function wife(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'wife_id');
    }

    /**
     * Relaciones con hijos.
     */
    public function childRelations(): HasMany
    {
        return $this->hasMany(FamilyChild::class);
    }

    /**
     * Los hijos de esta familia.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'family_children', 'family_id', 'person_id')
            ->withPivot('child_order', 'relationship_type')
            ->orderBy('child_order');
    }

    /**
     * Usuario que creo esta familia.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Eventos GEDCOM de esta familia.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Obtiene ambos conyuges.
     */
    public function getSpousesAttribute()
    {
        return collect([$this->husband, $this->wife])->filter();
    }

    /**
     * Verifica si la familia tiene herencia etnica.
     */
    public function hasEthnicHeritage(): bool
    {
        return ($this->husband && $this->husband->has_ethnic_heritage)
            || ($this->wife && $this->wife->has_ethnic_heritage);
    }

    /**
     * Nombre para mostrar de la familia (nombres completos).
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [];

        if ($this->husband) {
            $parts[] = $this->husband->full_name;
        }
        if ($this->wife) {
            $parts[] = $this->wife->full_name;
        }

        return implode(' & ', $parts) ?: __('Familia sin nombre');
    }

    /**
     * Obtiene la etiqueta descriptiva de la familia.
     */
    public function getLabelAttribute(): string
    {
        $parts = [];

        if ($this->husband) {
            $parts[] = $this->husband->patronymic;
        }
        if ($this->wife) {
            $parts[] = $this->wife->patronymic;
        }

        return implode(' / ', $parts) ?: 'Familia sin nombre';
    }
}
