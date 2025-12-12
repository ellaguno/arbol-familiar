<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inviter_id',
        'person_id',
        'email',
        'token',
        'status',
        'sent_at',
        'responded_at',
        'expires_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Boot del modelo para generar token automaticamente.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(
                    config('mi-familia.invitation_expiry_days', 30)
                );
            }
        });
    }

    /**
     * Usuario que envio la invitacion.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    /**
     * Persona invitada.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Verifica si la invitacion ha expirado.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verifica si esta pendiente.
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'sent']) && !$this->isExpired();
    }

    /**
     * Verifica si fue aceptada.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Verifica si fue rechazada.
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Marca como enviada.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Acepta la invitacion.
     */
    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'responded_at' => now(),
        ]);
    }

    /**
     * Rechaza la invitacion.
     */
    public function decline(): void
    {
        $this->update([
            'status' => 'declined',
            'responded_at' => now(),
        ]);
    }

    /**
     * Marca como expirada.
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Genera la URL para ver la invitacion (pagina donde el usuario decide).
     */
    public function getAcceptUrlAttribute(): string
    {
        return route('invitation.show', ['token' => $this->token]);
    }

    /**
     * Scope para invitaciones pendientes.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'sent'])
            ->where('expires_at', '>', now());
    }

    /**
     * Scope para invitaciones expiradas.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
            ->whereNotIn('status', ['accepted', 'declined', 'expired']);
    }

    /**
     * Busca por token.
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }
}
