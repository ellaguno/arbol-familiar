<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Message extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'type',
        'broadcast_scope',
        'subject',
        'body',
        'related_person_id',
        'action_required',
        'action_status',
        'action_taken_at',
        'read_at',
    ];

    protected $casts = [
        'action_required' => 'boolean',
        'action_taken_at' => 'datetime',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * El remitente del mensaje.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * El destinatario del mensaje.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Persona relacionada con el mensaje.
     */
    public function relatedPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'related_person_id');
    }

    /**
     * Destinatarios de un mensaje broadcast.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }

    /**
     * Registro pivote del usuario actual (para broadcasts).
     */
    public function currentUserRecipient(): HasOne
    {
        return $this->hasOne(MessageRecipient::class)
            ->where('user_id', Auth::id());
    }

    /**
     * Verifica si es un mensaje de difusion.
     */
    public function isBroadcast(): bool
    {
        return $this->type === 'broadcast';
    }

    /**
     * Etiqueta del alcance de difusion.
     */
    public function getBroadcastScopeLabel(): string
    {
        return match ($this->broadcast_scope) {
            'all' => __('Todos los usuarios'),
            'family' => __('Mi familia'),
            default => '',
        };
    }

    /**
     * Obtener el registro pivote para un usuario especifico.
     */
    public function recipientPivot(int $userId): ?MessageRecipient
    {
        return $this->recipients()->where('user_id', $userId)->first();
    }

    /**
     * Verificar si un usuario es destinatario de este mensaje.
     */
    public function isRecipientOf(int $userId): bool
    {
        if (!$this->isBroadcast()) {
            return $this->recipient_id === $userId;
        }

        return $this->recipients()->where('user_id', $userId)->exists();
    }

    /**
     * Verificar si el mensaje fue leido por el usuario actual.
     */
    public function isReadByCurrentUser(): bool
    {
        if (!$this->isBroadcast()) {
            return $this->read_at !== null;
        }

        // Usar relacion eager-loaded si esta disponible
        if ($this->relationLoaded('currentUserRecipient')) {
            return $this->currentUserRecipient && $this->currentUserRecipient->read_at !== null;
        }

        $pivot = $this->recipientPivot(Auth::id());
        return $pivot && $pivot->read_at !== null;
    }

    /**
     * Conteo de destinatarios (para broadcasts).
     */
    public function getRecipientCountAttribute(): int
    {
        if (!$this->isBroadcast()) {
            return 1;
        }

        return $this->recipients()->count();
    }

    /**
     * Verifica si es un mensaje del sistema.
     */
    public function isSystemMessage(): bool
    {
        return $this->type === 'system' || ($this->sender_id === null && !$this->isBroadcast());
    }

    /**
     * Verifica si es una invitacion.
     */
    public function isInvitation(): bool
    {
        return $this->type === 'invitation';
    }

    /**
     * Verifica si es una solicitud de consentimiento.
     */
    public function isConsentRequest(): bool
    {
        return $this->type === 'consent_request';
    }

    /**
     * Verifica si es una solicitud de reclamar persona.
     */
    public function isPersonClaim(): bool
    {
        return $this->type === 'person_claim';
    }

    /**
     * Verifica si es una solicitud de fusion de personas.
     */
    public function isPersonMerge(): bool
    {
        return $this->type === 'person_merge';
    }

    /**
     * Verifica si ha sido leido.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Marca como leido.
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Acepta la accion requerida.
     */
    public function accept(): void
    {
        $this->update([
            'action_status' => 'accepted',
            'action_taken_at' => now(),
        ]);
    }

    /**
     * Deniega la accion requerida.
     */
    public function deny(): void
    {
        $this->update([
            'action_status' => 'denied',
            'action_taken_at' => now(),
        ]);
    }

    /**
     * Soft delete.
     */
    public function softDelete(): void
    {
        $this->update(['deleted_at' => now()]);
    }

    /**
     * Scope para mensajes no leidos.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para mensajes no eliminados.
     */
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope para mensajes que requieren accion.
     */
    public function scopeActionRequired($query)
    {
        return $query->where('action_required', true)
            ->where('action_status', 'pending');
    }

    /**
     * Scope por tipo.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
