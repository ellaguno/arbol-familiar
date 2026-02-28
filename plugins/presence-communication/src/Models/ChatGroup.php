<?php

namespace Plugin\PresenceCommunication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'created_by',
        'max_participants',
        'status',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participantRecords()
    {
        return $this->hasMany(ChatGroupParticipant::class, 'group_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_group_id');
    }

    public function readStatuses()
    {
        return $this->hasMany(ChatGroupReadStatus::class, 'group_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * IDs de participantes activos.
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
     * Agrega un participante (idempotente).
     */
    public function addParticipant(int $userId, string $role = 'member'): ChatGroupParticipant
    {
        $existing = $this->participantRecords()
            ->where('user_id', $userId)
            ->active()
            ->first();

        if ($existing) {
            return $existing;
        }

        return ChatGroupParticipant::create([
            'group_id' => $this->id,
            'user_id' => $userId,
            'role' => $role,
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
     * Verifica si el grupo esta lleno.
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

    /**
     * Verifica si un usuario es admin del grupo.
     */
    public function isAdmin(int $userId): bool
    {
        return $this->participantRecords()
            ->where('user_id', $userId)
            ->where('role', 'admin')
            ->active()
            ->exists();
    }

    /**
     * Cuenta mensajes no leidos para un usuario.
     */
    public function getUnreadCountFor(int $userId): int
    {
        $readStatus = $this->readStatuses()
            ->where('user_id', $userId)
            ->first();

        $query = $this->messages()->where('sender_id', '!=', $userId);

        if ($readStatus && $readStatus->last_read_message_id) {
            $query->where('id', '>', $readStatus->last_read_message_id);
        }

        return $query->count();
    }
}
