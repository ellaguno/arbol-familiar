<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreeAccess extends Model
{
    protected $table = 'tree_access';

    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'accessor_id',
        'access_level',
        'include_documents',
        'expires_at',
    ];

    protected $casts = [
        'include_documents' => 'boolean',
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Dueño del árbol.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Usuario con acceso.
     */
    public function accessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accessor_id');
    }

    /**
     * Verifica si el acceso ha expirado.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verifica si el acceso esta activo.
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Verifica si tiene acceso de solo lectura basico.
     */
    public function hasViewBasicAccess(): bool
    {
        return in_array($this->access_level, ['view_basic', 'view_full', 'edit']);
    }

    /**
     * Verifica si tiene acceso de lectura completa.
     */
    public function hasViewFullAccess(): bool
    {
        return in_array($this->access_level, ['view_full', 'edit']);
    }

    /**
     * Verifica si tiene acceso de edicion.
     */
    public function hasEditAccess(): bool
    {
        return $this->access_level === 'edit';
    }

    /**
     * Verifica si puede ver documentos.
     */
    public function canViewDocuments(): bool
    {
        return $this->include_documents && $this->hasViewFullAccess();
    }

    /**
     * Scope para accesos activos.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para accesos de un accessor especifico.
     */
    public function scopeForAccessor($query, int $userId)
    {
        return $query->where('accessor_id', $userId);
    }

    /**
     * Scope para accesos de un owner especifico.
     */
    public function scopeForOwner($query, int $userId)
    {
        return $query->where('owner_id', $userId);
    }
}
