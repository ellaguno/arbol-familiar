<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_log';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Acciones comunes del sistema.
     */
    public const ACTIONS = [
        'login' => 'Inicio de sesion',
        'logout' => 'Cierre de sesion',
        'login_failed' => 'Intento de login fallido',
        'register' => 'Registro de usuario',
        'create_person' => 'Crear persona',
        'update_person' => 'Actualizar persona',
        'delete_person' => 'Eliminar persona',
        'create_family' => 'Crear familia',
        'update_family' => 'Actualizar familia',
        'upload_media' => 'Subir archivo',
        'delete_media' => 'Eliminar archivo',
        'send_invitation' => 'Enviar invitacion',
        'accept_invitation' => 'Aceptar invitacion',
        'import_gedcom' => 'Importar GEDCOM',
        'export_gedcom' => 'Exportar GEDCOM',
        'change_password' => 'Cambiar contrasena',
        'update_profile' => 'Actualizar perfil',
        'grant_access' => 'Otorgar acceso',
        'revoke_access' => 'Revocar acceso',
    ];

    /**
     * El usuario que realizo la accion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * El modelo afectado por la accion.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Obtiene la etiqueta legible de la accion.
     */
    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    /**
     * Registra una actividad.
     */
    public static function log(
        string $action,
        ?User $user = null,
        ?Model $subject = null,
        array $properties = []
    ): self {
        return self::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Scope por usuario.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope por accion.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope por rango de fechas.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope para actividad reciente.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
