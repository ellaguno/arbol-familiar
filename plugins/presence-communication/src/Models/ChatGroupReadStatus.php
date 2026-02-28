<?php

namespace Plugin\PresenceCommunication\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroupReadStatus extends Model
{
    public $timestamps = false;

    protected $table = 'chat_group_read_status';

    protected $fillable = [
        'group_id',
        'user_id',
        'last_read_message_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];
}
