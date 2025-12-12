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
