<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    public $timestamps = false;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'type',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'external_url',
        'sort_order',
        'is_primary',
        'created_by',
        'event_id',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Modelo al que pertenece este media (Person o User).
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usuario que subio este archivo.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Evento asociado a este documento (opcional).
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Determina si un usuario puede ver este archivo, aplicando la privacidad
     * del recurso al que pertenece (persona, familia o evento). Centraliza la
     * logica usada por MediaController y por la ruta /storage/{path}.
     */
    public function canBeViewedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // El creador siempre puede ver su archivo.
        if ($this->created_by === $user->id) {
            return true;
        }

        // Media de una persona: usar la privacidad de la persona.
        if ($this->mediable_type === Person::class && $this->mediable_id) {
            $person = Person::find($this->mediable_id);
            return $person ? $person->canBeViewedBy($user) : false;
        }

        // Media de una familia: visible si algun conyuge es visible.
        if ($this->mediable_type === Family::class && $this->mediable_id) {
            $family = Family::find($this->mediable_id);
            if (!$family) {
                return false;
            }
            return ($family->husband && $family->husband->canBeViewedBy($user))
                || ($family->wife && $family->wife->canBeViewedBy($user));
        }

        // Media de un evento: usar la privacidad de la persona del evento.
        if ($this->mediable_type === Event::class && $this->mediable_id) {
            $event = Event::find($this->mediable_id);
            return $event && $event->person ? $event->person->canBeViewedBy($user) : false;
        }

        return false;
    }

    /**
     * Verifica si es una imagen.
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Verifica si es un documento.
     */
    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    /**
     * Verifica si es un enlace externo.
     */
    public function isLink(): bool
    {
        return $this->type === 'link';
    }

    /**
     * Obtiene la URL del archivo.
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->isLink()) {
            return $this->external_url;
        }

        if ($this->file_path) {
            return Storage::url($this->file_path);
        }

        return null;
    }

    /**
     * Obtiene el tamano formateado.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    /**
     * Verifica si el archivo es PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Scope para imagenes.
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope para documentos.
     */
    public function scopeDocuments($query)
    {
        return $query->where('type', 'document');
    }

    /**
     * Scope para enlaces.
     */
    public function scopeLinks($query)
    {
        return $query->where('type', 'link');
    }

    /**
     * Scope para media principal.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
