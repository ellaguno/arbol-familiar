<?php

namespace Plugin\PresenceCommunication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPresence extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'last_seen_at',
        'current_page',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si el usuario esta en linea (visto en los ultimos 2 minutos).
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 2;
    }

    /**
     * Scope: usuarios en linea.
     */
    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(2));
    }
}
