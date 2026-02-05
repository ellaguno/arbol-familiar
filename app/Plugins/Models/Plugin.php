<?php

namespace App\Plugins\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'version',
        'author',
        'description',
        'status',
        'installed',
        'settings',
        'sort_order',
        'enabled_at',
    ];

    protected $casts = [
        'installed' => 'boolean',
        'settings' => 'array',
        'enabled_at' => 'datetime',
    ];

    /**
     * Verificar si el plugin esta habilitado.
     */
    public function isEnabled(): bool
    {
        return $this->status === 'enabled';
    }

    /**
     * Verificar si el plugin esta instalado.
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * Verificar si el plugin tiene error.
     */
    public function hasError(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Obtener un valor de configuracion del plugin.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Establecer un valor de configuracion del plugin.
     */
    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Scope: plugins habilitados.
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', 'enabled');
    }

    /**
     * Scope: plugins instalados.
     */
    public function scopeInstalled($query)
    {
        return $query->where('installed', true);
    }
}
