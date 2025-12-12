<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyChild extends Model
{
    protected $table = 'family_children';

    public $timestamps = false;

    protected $fillable = [
        'family_id',
        'person_id',
        'child_order',
        'relationship_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * La familia a la que pertenece.
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * La persona (hijo).
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Alias para obtener el hijo.
     */
    public function child(): BelongsTo
    {
        return $this->person();
    }

    /**
     * Verifica si es hijo biologico.
     */
    public function isBiological(): bool
    {
        return $this->relationship_type === 'biological';
    }

    /**
     * Verifica si es adoptado.
     */
    public function isAdopted(): bool
    {
        return $this->relationship_type === 'adopted';
    }
}
