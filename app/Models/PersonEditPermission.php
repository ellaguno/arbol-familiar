<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonEditPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'user_id',
        'granted_by',
        'relationship_type',
        'granted_at',
        'expires_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Persona que puede ser editada.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Usuario que tiene permiso de edición.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuario que otorgó el permiso.
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Verifica si el permiso ha expirado.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verifica si el permiso está activo.
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Scope para permisos activos (no expirados).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para permisos de un usuario específico.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para permisos de una persona específica.
     */
    public function scopeForPerson($query, int $personId)
    {
        return $query->where('person_id', $personId);
    }

    /**
     * Obtiene la etiqueta del tipo de relación.
     */
    public function getRelationshipLabelAttribute(): string
    {
        return match($this->relationship_type) {
            'father' => __('Padre'),
            'mother' => __('Madre'),
            'spouse' => __('Cónyuge'),
            'child' => __('Hijo/a'),
            'sibling' => __('Hermano/a'),
            'other' => __('Otro'),
            default => $this->relationship_type,
        };
    }
}
