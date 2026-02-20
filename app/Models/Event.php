<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $table = 'events';

    public $timestamps = false;

    protected $fillable = [
        'person_id',
        'family_id',
        'type',
        'date',
        'date_approx',
        'place',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'date_approx' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Tipos de eventos GEDCOM comunes.
     */
    public const TYPES = [
        'BIRT' => 'Nacimiento',
        'DEAT' => 'Fallecimiento',
        'MARR' => 'Matrimonio',
        'DIV' => 'Divorcio',
        'BURI' => 'Entierro',
        'BAPM' => 'Bautismo',
        'CHR' => 'Bautizo',
        'CONF' => 'Confirmacion',
        'GRAD' => 'Graduacion',
        'EMIG' => 'Emigracion',
        'IMMI' => 'Inmigracion',
        'NATU' => 'Naturalizacion',
        'RESI' => 'Residencia',
        'OCCU' => 'Ocupacion',
        'RETI' => 'Retiro',
    ];

    /**
     * La persona asociada a este evento.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * La familia asociada a este evento.
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Obtiene la etiqueta legible del tipo.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Verifica si es un evento de persona.
     */
    public function isPersonEvent(): bool
    {
        return $this->person_id !== null;
    }

    /**
     * Verifica si es un evento de familia.
     */
    public function isFamilyEvent(): bool
    {
        return $this->family_id !== null;
    }

    /**
     * Scope por tipo.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para eventos de persona.
     */
    public function scopeForPerson($query, int $personId)
    {
        return $query->where('person_id', $personId);
    }

    /**
     * Scope para eventos de familia.
     */
    public function scopeForFamily($query, int $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    /**
     * Documentos vinculados a este evento.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Tipos de eventos disponibles para creacion manual.
     * Excluye BIRT y DEAT que se manejan en campos de persona.
     */
    public static function manualTypes(): array
    {
        return array_diff_key(self::TYPES, array_flip(['BIRT', 'DEAT']));
    }
}
