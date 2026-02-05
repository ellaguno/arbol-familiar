<?php

namespace Plugin\PresenceCommunication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WebrtcSignal extends Model
{
    protected $fillable = [
        'caller_id',
        'callee_id',
        'type',
        'media_type',
        'payload',
        'consumed',
    ];

    protected $casts = [
        'consumed' => 'boolean',
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function callee()
    {
        return $this->belongsTo(User::class, 'callee_id');
    }

    /**
     * Senales pendientes para un usuario (como caller o callee).
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('consumed', false)
            ->where(function ($q) use ($userId) {
                $q->where('callee_id', $userId)
                  ->orWhere('caller_id', $userId);
            });
    }

    /**
     * Senales expiradas (para limpieza).
     */
    public function scopeExpired($query, int $minutes = 5)
    {
        return $query->where('created_at', '<', now()->subMinutes($minutes));
    }
}
