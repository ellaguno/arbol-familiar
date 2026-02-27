<?php

namespace Plugin\PresenceCommunication\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAuthorization extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_a_id', 'user_b_id', 'message_id', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Verifica si dos usuarios tienen autorizacion de chat.
     */
    public static function isAuthorized(int $userA, int $userB): bool
    {
        $min = min($userA, $userB);
        $max = max($userA, $userB);

        return static::where('user_a_id', $min)->where('user_b_id', $max)->exists();
    }

    /**
     * Crea autorizacion entre dos usuarios (idempotente).
     */
    public static function authorize(int $userA, int $userB, ?int $messageId = null): static
    {
        $min = min($userA, $userB);
        $max = max($userA, $userB);

        return static::firstOrCreate(
            ['user_a_id' => $min, 'user_b_id' => $max],
            ['message_id' => $messageId, 'created_at' => now()]
        );
    }
}
