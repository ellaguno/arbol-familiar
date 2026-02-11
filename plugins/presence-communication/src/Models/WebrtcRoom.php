<?php

namespace Plugin\PresenceCommunication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WebrtcRoom extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';

    protected $fillable = [
        'created_by',
        'media_type',
        'status',
        'max_participants',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signals()
    {
        return $this->hasMany(WebrtcSignal::class, 'room_id');
    }

    public function participantRecords()
    {
        return $this->hasMany(WebrtcRoomParticipant::class, 'room_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Obtiene IDs de participantes activos (que no han salido).
     */
    public function getParticipantIds(): array
    {
        return $this->participantRecords()
            ->active()
            ->pluck('user_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Agrega un participante al room.
     */
    public function addParticipant(int $userId): WebrtcRoomParticipant
    {
        // Verificar si ya esta activo
        $existing = $this->participantRecords()
            ->where('user_id', $userId)
            ->active()
            ->first();

        if ($existing) {
            return $existing;
        }

        return WebrtcRoomParticipant::create([
            'room_id' => $this->id,
            'user_id' => $userId,
            'joined_at' => now(),
        ]);
    }

    /**
     * Marca a un participante como salido.
     */
    public function removeParticipant(int $userId): void
    {
        $this->participantRecords()
            ->where('user_id', $userId)
            ->active()
            ->update(['left_at' => now()]);
    }

    /**
     * Verifica si un usuario es participante activo.
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->participantRecords()
            ->where('user_id', $userId)
            ->active()
            ->exists();
    }

    /**
     * Verifica si el room esta lleno.
     */
    public function isFull(): bool
    {
        return count($this->getParticipantIds()) >= $this->max_participants;
    }

    /**
     * Cuenta participantes activos.
     */
    public function activeParticipantCount(): int
    {
        return $this->participantRecords()->active()->count();
    }
}
