<?php

namespace Plugin\PresenceCommunication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WebrtcRoomParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'room_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(WebrtcRoom::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('left_at');
    }
}
